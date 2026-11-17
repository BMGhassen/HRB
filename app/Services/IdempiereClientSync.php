<?php

namespace App\Services;

use App\Models\Client;
use Exception;
use Illuminate\Support\Facades\Log;

class IdempiereClientSync
{
    protected IdempiereService $erp;

    public function __construct(IdempiereService $erp)
    {
        $this->erp = $erp;
    }

    /**
     * Sync all active Customers from iDempiere into the local clients table.
     * Pre-fetches addresses in bulk to avoid N+1 API calls.
     */
    public function sync(): array
    {
        $created   = 0;
        $updated   = 0;
        $errors    = 0;
        $skip      = 0;
        $batchSize = 100;

        // Pre-fetch all locations in bulk
        $locations = $this->fetchLocations();

        $page = 0;
        do {
            $result = $this->erp->getModel('C_BPartner', [
                '$filter' => "IsCustomer eq 'Y'",
                '$select' => 'Value,Name,TaxID,IsActive',
                '$top'    => $batchSize,
                '$skip'   => $page * $batchSize,
                '$orderby'=> 'Value asc',
            ]);

            $records = $result['records'] ?? [];

            if (empty($records)) {
                break;
            }

            foreach ($records as $bp) {
                try {
                    $bpId = $bp['id'];
                    $code = $bp['Value'] ?? null;
                    $rs   = $bp['Name']  ?? null;
                    $matF = $bp['TaxID'] ?? null;

                    if (!$bp['IsActive']) { $skip++; continue; }
                    if (!$code) { $skip++; continue; }

                    $loc     = $locations[$bpId] ?? [];
                    $adresse = $loc['adresse'] ?? null;
                    $region  = $loc['region']  ?? null;
                    
                    // Extract numeric phone number if it exists
                    $phoneRaw = $loc['phone'] ?? null;
                    $tel      = $phoneRaw ? (int) preg_replace('/\D/', '', $phoneRaw) : null;

                    $data = [
                        'rs'      => $rs,
                        'MatF'    => $matF,
                        'nom'     => null, // Not fetching from AD_User anymore
                        'tel'     => $tel,
                        'email'   => null, // Not fetching from AD_User anymore
                        'Adresse' => $adresse,
                        'Region'  => $region,
                    ];

                    $existing = Client::where('code', $code)->first();
                    if ($existing) {
                        $existing->update($data);
                        $updated++;
                    } else {
                        Client::create(array_merge(['code' => $code], $data));
                        $created++;
                    }
                } catch (Exception $e) {
                    Log::error("[ClientSync] Error syncing BPartner {$bpId}: " . $e->getMessage());
                    $errors++;
                }
            }

            $page++;
        } while (count($records) === $batchSize);

        Log::info("[ClientSync] Done.", compact('created', 'updated', 'errors', 'skip'));
        return compact('created', 'updated', 'errors', 'skip');
    }

    /**
     * Fetch ALL BPartner locations (C_BPartner_Location) in bulk pages.
     * Returns [ bpartner_id => ['adresse' => ..., 'region' => ..., 'phone' => ...] ]
     * Prefers Ship To locations. Falls back to any location.
     */
    protected function fetchLocations(): array
    {
        $locations = [];
        $batchSize = 200;
        $page      = 0;

        do {
            $result  = $this->erp->getModel('C_BPartner_Location', [
                '$select' => 'C_BPartner_ID,C_Location_ID,Name,IsShipTo,Phone',
                '$top'    => $batchSize,
                '$skip'   => $page * $batchSize,
            ]);
            $records = $result['records'] ?? [];

            foreach ($records as $loc) {
                $bpId    = $loc['C_BPartner_ID']['id'] ?? null;
                $isShip  = !empty($loc['IsShipTo']);
                
                $address1 = $loc['C_Location_ID']['Address1'] ?? null;
                $city     = $loc['C_Location_ID']['City']    ?? null;
                $region   = $loc['C_Location_ID']['RegionName'] ?? $loc['Name'] ?? null;
                $phone    = $loc['Phone'] ?? null;
                
                // Construct the address using Address1 and City
                $addressParts = array_filter([$address1, $city]);
                $address = trim(implode(', ', $addressParts));

                if (!$bpId) continue;

                // Always overwrite with a Ship To location; only set once if no Ship To found yet
                if (!isset($locations[$bpId]) || $isShip) {
                    $locations[$bpId] = [
                        'adresse' => $address ?: null,
                        'region'  => $region  ?: null,
                        'phone'   => $phone   ?: null,
                    ];
                }
            }
            $page++;
        } while (count($records) === $batchSize);

        return $locations;
    }
}
