<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

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

}
