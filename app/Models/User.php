<?php
  
namespace App\Models;
  
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
  
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
  
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
  
    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function ratedProducts()
    {
        return $this->belongsToMany(Product::class, 'product_user_rating')
                    ->withPivot('rating')
                    ->withTimestamps();
    }

    public function likedProducts()
    {
        return $this->belongsToMany(Product::class, 'likes', 'user_id', 'product_id')->withTimestamps();
    }
}