<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneCommande extends Model
{
    use HasFactory;
    public function commande(){
        return $this->belongsTo(Commande::class, 'commande_id', 'id');
    }

    public function article(){
        return $this->hasOne(Article::class, 'article_id', 'id');
    }
}
