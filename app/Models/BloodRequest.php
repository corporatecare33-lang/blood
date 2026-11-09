<?php

namespace App\Models;

use App\Enums\BloodGroup;
use App\Enums\RequestStatus;
use App\Support\SiteCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloodRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'patient_name', 'blood_group', 'bags', 'needed_at', 'hospital',
        'district_id', 'contact_name', 'contact_mobile', 'note', 'status',
    ];

    protected function casts(): array
    {
        return [
            'blood_group' => BloodGroup::class,
            'status' => RequestStatus::class,
            'needed_at' => 'datetime',
            'bags' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => SiteCache::forgetRequests());
        static::deleted(fn () => SiteCache::forgetRequests());
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Open requests whose need date has not passed by more than a day. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', RequestStatus::Open)
            ->where('needed_at', '>=', now()->subDay());
    }

    public function isUrgent(): bool
    {
        return $this->needed_at->isBefore(now()->addHours(24));
    }
}
