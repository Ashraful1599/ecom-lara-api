<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    // The attributes that are mass assignable
    protected $fillable = ['name', 'slug'];

    // Define relationships if needed (e.g., products associated with this tag)
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_tag');
    }
}


