<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class equivalent extends Model
{
    public function article()
    {
        return $this->belongsTo(Article::class, 'prod_id', 'id');
    }
    use HasFactory;
}
