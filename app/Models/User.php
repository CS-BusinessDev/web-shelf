<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasPanelShield;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'email_verified_at',
        'business_entity_id',
        'job_title_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasVerifiedEmail();
    }

    public function businessEntity(): BelongsTo
    {
        return $this->belongsTo(BusinessEntity::class);
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function assetTransfers()
    {
        return $this->hasMany(AssetTransfer::class);
    }

    public function assetTransfersFrom()
    {
        return $this->hasMany(AssetTransfer::class, 'from_user_id');
    }

    public function assetTransfersTo()
    {
        return $this->hasMany(AssetTransfer::class, 'to_user_id');
    }

    public function assetTransferDetails()
    {
        return $this->hasManyThrough(
            AssetTransferDetail::class, // Model tujuan akhir (AssetTransferDetail)
            AssetTransfer::class, // Model perantara (AssetTransfer)
            'from_user_id', // Foreign key di AssetTransfer (relasi ke User)
            'asset_transfer_id', // Foreign key di AssetTransferDetail (relasi ke AssetTransfer)
            'id', // Local key di User
            'id'  // Local key di AssetTransfer
        );
    }

    public function isDuplicate(): bool
    {
        return User::where('name', $this->name)
            ->where('id', '!=', $this->id)  // Hindari perbandingan dengan dirinya sendiri
            ->exists();
    }
}
