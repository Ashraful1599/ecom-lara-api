<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // The attributes that are mass assignable
    protected $fillable = ['name', 'slug'];

    // Define relationships if needed (e.g., products belonging to this category)

    // Category.php

    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }


}
