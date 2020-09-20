<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'manufacturer_id',
        'name',
        'slug',
        'vendor_code',
        'description',
        'price',
        'warranty',
        'stock'
    ];

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo
     */
    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }


    protected static function boot()
    {
        parent::boot();

        static::saving(function($model){
            $model->slug = time() . '_' . Str::slug($model->name);
        });
    }
}
