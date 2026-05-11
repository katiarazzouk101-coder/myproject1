<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'image'
    ];

    public function products(): HasMany{
        return $this->hasMany(Product::class);
    }

    protected static function booted()
{
    static::deleting(function ($category) {
        $category->products->each->delete(); // Soft delete or manual cascade
    });
}
}
