<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_date',
        'business_entity_id',
        'name',
        'category_id',
        'brand_id',
        'type',
        'serial_number',
        'imei1',
        'imei2',
        'item_price',
        'asset_location_id',
        'is_available',
    ];
    // Relasi ke tabel business_entities
    public function businessEntity()
    {
        return $this->belongsTo(BusinessEntity::class);
    }

    // Relasi ke tabel categories
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relasi ke tabel brands
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    // Relasi ke tabel asset_locations
    public function assetLocation()
    {
        return $this->belongsTo(AssetLocation::class);
    }

    // Relasi ke tabel asset_transfers
    public function assetTransfers()
    {
        return $this->hasMany(AssetTransfer::class);
    }

    private function formatDiff($value, $unit)
    {
        return $value . ' ' . $unit;
    }

    public function getItemAgeAttribute()
    {
        $purchaseDate = Carbon::parse($this->attributes['purchase_date']);
        $now = Carbon::now();

        $diffInDays = $purchaseDate->diffInDays($now);
        $diffInMonths = $purchaseDate->diffInMonths($now);
        $diffInYears = $purchaseDate->diffInYears($now);

        if ($diffInYears > 0) {
            return $this->formatDiff($diffInYears, 'tahun');
        } elseif ($diffInMonths > 0) {
            return $this->formatDiff($diffInMonths, 'bulan');
        } else {
            return $this->formatDiff($diffInDays, 'hari');
        }
    }
}
