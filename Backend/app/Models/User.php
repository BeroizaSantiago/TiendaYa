<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens,  HasFactory ,Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


        public function stores()
        {
            return $this->belongsToMany(Store::class)
                ->withPivot('role')
                ->withTimestamps();
        }


    public function canUse(string $feature): bool
    {
        if (!$this->plan) {
            return false;
        }

        return match($feature) {
            'bulk_upload' => $this->plan->bulk_upload,
            'advanced_reports' => $this->plan->advanced_reports,
            'advanced_coupons' => $this->plan->advanced_coupons,
            'b2b' => $this->plan->b2b_enabled,
            'custom_integrations' => $this->plan->custom_integrations,
            default => false
        };
    }

}
