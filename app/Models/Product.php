<?php
  
namespace App\Models;
  
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'detail', 'price', 'category_id'
    ];

    protected $casts = [
        'price' => 'float',
    ];

    public function category(): BelongsTo{
        return $this->belongsTo(Category::class);
    }

    public function getRelatedProducts()
    {
        $candidates = self::where('id', '!=', $this->id)->get();

        $scored = $candidates->map(function ($item) {
            $score = 0;

            // 70 points for same category
            if ($item->category_id === $this->category_id) {
                $score += 70;
            }

            // 0–30 points for name similarity
            similar_text($this->name, $item->name, $percent);
            $score += ($percent * 0.3); // max 30 points

            $item->similarity_score = $score;
            return $item;
        });

        // Sort by score and return top 4 as array or resource
        return $scored->sortByDesc('similarity_score')->take(4)->values()->map(function ($product) {
            return $product;
        });
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ratings()
    {
        return $this->belongsToMany(User::class, 'product_user_rating')
                    ->withPivot('rating')
                    ->withTimestamps();
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'likes', 'product_id', 'user_id')->withTimestamps();
    }
}