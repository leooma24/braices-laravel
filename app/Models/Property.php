<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Property extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'address',
        'city',
        'state',
        'country',
        'zip',
        'price',
        'bedrooms',
        'bathrooms',
        'square_feet',
        'lot_size',
        'year_built',
        'photo_main',
        'property_status_id',
        'transaction_type_id',
        'youtube',
        'lat',
        'long',
        'township',
        'suburb',
        'front',
        'depth',
        'levels',
        'square_meters_contruction',
        'is_reservable',
        'max_guests',
        'price_per_night',
        'cleaning_fee',
        'check_in_time',
        'check_out_time',
    ];

    protected $casts = [
        'is_reservable' => 'boolean',
        'is_featured' => 'boolean',
        'featured_until' => 'datetime',
    ];

    public function isFeaturedNow(): bool
    {
        if (!$this->is_featured) {
            return false;
        }
        if (!$this->featured_until) {
            return true; // featured indefinido (admin sin fecha)
        }
        return $this->featured_until->isFuture();
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'properties';

    public function type()
    {
        return $this->hasOne(PropertyTypeModel::class, 'id', 'property_type_id');
    }

    public function propertyTypes()
    {
        return $this->belongsToMany(PropertyTypeModel::class, 'property_property_type', 'property_id', 'property_type_id');
    }

    public function countryName()
    {
        return $this->hasOne(Country::class, 'id', 'country');
    }

    public function stateName()
    {
        return $this->hasOne(State::class, 'id', 'state');
    }

    public function townshipName()
    {
        return $this->hasOne(Township::class, 'id', 'township');
    }

    public function suburbName()
    {
        return $this->hasOne(Suburb::class, 'id', 'suburb');
    }

    public function status()
    {
        return $this->hasOne(PropertyStatusModel::class, 'id', 'property_status_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function transaction()
    {
        return $this->hasOne(TransactionTypeModel::class, 'id', 'transaction_type_id');
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class, 'property_id', 'id');
    }

    public function getPhotoMainAttribute($value)
    {
        return $value ? asset('images/' . $value) : null;
    }

    public function isLand()
    {
        foreach ($this->propertyTypes as $type) {
            $name = STR::lower($type->name);
            if (STR::contains($name, 'terreno')) {
                return true;
            }
        }
        return false;
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'property_amenities');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function availability()
    {
        return $this->hasMany(PropertyAvailability::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function pricing()
    {
        return $this->hasMany(Pricing::class);
    }

    public function rules()
    {
        return $this->hasMany(PropertyRule::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function averageRating(): ?float
    {
        $avg = $this->reviews()->avg('rating');
        return $avg ? round((float) $avg, 1) : null;
    }
}
