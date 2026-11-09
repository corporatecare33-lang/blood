<?php

namespace App\Models;

use App\Enums\BloodGroup;
use App\Enums\Gender;
use App\Enums\Occupation;
use App\Enums\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'mobile', 'email', 'gender', 'blood_group', 'occupation', 'date_of_birth',
        'last_donated_at', 'division_id', 'district_id', 'thana_id', 'address', 'password',
        'role', 'is_available', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'last_donated_at' => 'date',
            'blood_group' => BloodGroup::class,
            'gender' => Gender::class,
            'occupation' => Occupation::class,
            'role' => Role::class,
            'is_available' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function thana(): BelongsTo
    {
        return $this->belongsTo(Thana::class);
    }

    public function bloodRequests(): HasMany
    {
        return $this->hasMany(BloodRequest::class);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [Role::Admin, Role::SuperAdmin], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === Role::SuperAdmin;
    }

    /** Days left before this donor may give whole blood again; 0 when eligible. */
    public function daysUntilEligible(): int
    {
        if (! $this->last_donated_at) {
            return 0;
        }

        $next = $this->last_donated_at->copy()->addDays(config('bandhan.donation_gap_days'));

        return max(0, (int) now()->startOfDay()->diffInDays($next, false));
    }

    public function isEligible(): bool
    {
        return $this->is_available && $this->daysUntilEligible() === 0;
    }

    public function scopeDonors(Builder $query): Builder
    {
        return $query->where('role', Role::Donor)->where('is_active', true);
    }

    public function scopeEligible(Builder $query): Builder
    {
        return $query->where('is_available', true)->where(function (Builder $q) {
            $q->whereNull('last_donated_at')
                ->orWhereDate('last_donated_at', '<=', now()->subDays(config('bandhan.donation_gap_days')));
        });
    }

    public function initials(): string
    {
        return collect(preg_split('/\s+/u', trim($this->name)))
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }
}
