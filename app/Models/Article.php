<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref', 'designation', 'qte_stock', 'qte_instance', 'qte_reserve', 'prix', 'photo', 'description',
        // ERP sync fields
        'erp_product_id', 'erp_category', 'erp_synced_at',
    ];


    protected $casts = [
        'erp_synced_at' => 'datetime',
        'prix'          => 'float',
        'qte_stock'     => 'integer',
        'qte_instance'  => 'integer',
        'qte_reserve'   => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function ligneCommande(){
        return $this->hasMany(LigneCommande::class, 'article_id', 'id');
    }

    public function equivalent(){
        return $this->hasMany(equivalent::class,'prod_id', 'id');
    }

    public function origin(){
        return $this->hasMany(Origin::class,'prod_id', 'id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** Only articles that have been synced from iDempiere */
    public function scopeErpSynced(Builder $query): Builder
    {
        return $query->whereNotNull('erp_product_id');
    }

    /** Only articles that were NOT synced from iDempiere (manual entries) */
    public function scopeManual(Builder $query): Builder
    {
        return $query->whereNull('erp_product_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Returns true if this article is linked to an iDempiere product */
    public function isErpSynced(): bool
    {
        return $this->erp_product_id !== null;
    }

    public function getPrice()
    {
        $price = $this->prix;
        return number_format($price, 3, ',', ' ');
    }
}
