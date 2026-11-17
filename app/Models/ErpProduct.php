<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * ErpProduct
 *
 * Read-only Eloquent model that maps directly to iDempiere's m_product table
 * via the 'idempiere' PostgreSQL connection. Use this for direct DB queries
 * when you need raw ERP data without going through the REST API.
 */
class ErpProduct extends Model
{
    // Tell Laravel to use the iDempiere PostgreSQL connection
    protected $connection = 'idempiere';

    // Table name in iDempiere (lives in the 'adempiere' schema)
    protected $table = 'm_product';

    // Primary key
    protected $primaryKey = 'm_product_id';

    public $timestamps = false;

    // iDempiere product type codes
    public const TYPE_ITEM     = 'I'; // physical stocked product
    public const TYPE_SERVICE  = 'S'; // service
    public const TYPE_EXPENSE  = 'E'; // expense
    public const TYPE_RESOURCE = 'R'; // resource

    protected $casts = [
        'isactive'        => 'boolean',
        'issummary'       => 'boolean',
        'ispurchased'     => 'boolean',
        'issold'          => 'boolean',
        'created'         => 'datetime',
        'updated'         => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** Only active products */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('isactive', 'Y');
    }

    /** Only stocked / sellable items (ProductType = 'I') */
    public function scopeItems(Builder $query): Builder
    {
        return $query->where('producttype', self::TYPE_ITEM);
    }

    /** Only products that are marked as sold (shown on storefront) */
    public function scopeSold(Builder $query): Builder
    {
        return $query->where('issold', 'Y');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /** Human-readable product type label */
    public function getProductTypeLabelAttribute(): string
    {
        return match($this->producttype) {
            self::TYPE_ITEM     => 'Item',
            self::TYPE_SERVICE  => 'Service',
            self::TYPE_EXPENSE  => 'Expense',
            self::TYPE_RESOURCE => 'Resource',
            default             => 'Unknown',
        };
    }
}