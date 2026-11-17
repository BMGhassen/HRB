<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'rs', 'tel', 'MatF', 'nom', 'resp', 'Adresse', 'Region', 'vendeur_id', 'email', 'encours', 'erp_synced_at'];

    public function user()
    {
        return $this->hasOne(User::class, 'code_client', 'code');
    }
}
