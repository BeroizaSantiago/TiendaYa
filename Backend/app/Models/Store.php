<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'plan_id',
        'active',
    ];

    public function products()
    {
        return $this->hasMany(\App\Models\Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function owner()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
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
