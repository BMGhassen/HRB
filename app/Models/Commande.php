<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $casts = [
        'erp_synced_at' => 'datetime',
    ];

    public function ligneCommandes(){
        return $this->hasMany(LigneCommande::class, 'commande_id', 'id');
    }
    public function client(){
        return $this->belongsTo(User::class, 'client_id', 'id');
    }

    public function getTotal(){
        $total = 0;
        foreach($this->ligneCommandes as $lc){
           $total += $lc->article->prix * $lc->qte;
        }
        return $total;
    }
}
