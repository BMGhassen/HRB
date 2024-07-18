<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref', 'designation', 'stock', 'instance', 'reserve', 'prix', 'photo', 'description'
    ];

    public function ligneCommande(){
        return $this->belongsTo(LigneCommande::class, 'article_id', 'id');
    }

    public function equivalent(){
        return $this->hasMany(equivalent::class,'prod_id', 'id');
    }

    public function origin(){
        return $this->hasMany(Origin::class,'prod_id', 'id');
    }

    public function getPrice()
    {
         $price=$this->prix;
        return number_format($price,3,',',' ');
     }

}
