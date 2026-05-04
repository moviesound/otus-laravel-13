<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;


#[Fillable([
    'is_setted', 'status', 'name', 'sex', 'email',
    'phone', 'phone_proved', 'speaker', 'tariff_id',
    'language', 'location_id', 'birth_day', 'birth_month',
    'birth_year', 'politics_agreed', 'morning_time_workdays',
    'morning_time_holidays', 'evening_time_workdays',
    'evening_time_holidays', 'morning_digest_status',
    'evening_digest_status', 'digest_currencies', 'digest_weather',
])]
class User extends Model
{
    use HasFactory;
    protected $table = 'users';

    protected $casts = [
        'is_setted' => 'integer',
        'status' => 'integer',
        'sex' => 'integer',
        'phone_proved' => 'integer',
        'tariff_id' => 'integer',
        'location_id' => 'integer',
        'birth_day' => 'integer',
        'birth_month' => 'integer',
        'birth_year' => 'integer',
        'politics_agreed' => 'integer',
        'morning_digest_status' => 'integer',
        'evening_digest_status' => 'integer',
        'digest_currencies' => 'integer',
        'digest_weather' => 'integer',
    ];

    /* Scopes */

    #[Scope]
    protected function isActive(Builder $query)
    {
        return $query->where('status', 1);
    }

    #[Scope]
    protected function isSetted(Builder $query)
    {
        return $query->where('is_setted', 1);
    }

    #[Scope]
    protected function isPoliticsAgreed(Builder $query)
    {
        return $query->where('politics_agreed', 1);
    }

    #[Scope]
    protected function isPhoneProved(Builder $query)
    {
        return $query->where('phone_proved', 1);
    }

    #[Scope]
    protected function isMorningDigestEnabled(Builder $query)
    {
        return $query->where('morning_digest_status', 1);
    }

    #[Scope]
    protected function isEveningDigestEnabled(Builder $query)
    {
        return $query->where('evening_digest_status', 1);
    }

    #[Scope]
    protected function byEmail(Builder $query, string $email)
    {
        return $query->where('email', $email);
    }

    #[Scope]
    protected function byPhone(Builder $query, string $phone)
    {
        return $query->where('phone', $phone);
    }

    #[Scope]
    protected function byUserId(Builder $query, int $userId)
    {
        return $query->where('id', $userId);
    }

    /* Relations */

    public function state(): HasOne
    {
        return $this->hasOne(UserState::class, 'user_id');
    }

    public function socials(): HasMany
    {
        return $this->hasMany(UserSocial::class, 'user_id');
    }

    public function tariffs(): HasMany
    {
        return $this->hasMany(UserTariff::class, 'user_id');
    }

    public function phoneProofs(): HasMany
    {
        return $this->hasMany(UserPhoneProof::class, 'user_id');
    }

    public function reminderQueues(): HasMany
    {
        return $this->hasMany(ReminderQueue::class, 'user_id');
    }


}
