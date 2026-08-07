<?php

namespace App\Repositories\Bot;

use App\Contracts\Bot\Repositories\UserRepositoryInterface;
use App\DTO\Bot\User\UserDTO;
use App\DTO\Bot\User\UserSocialDTO;
use App\Models\Bot\User;
use App\Models\Bot\UserSocial;
use App\Models\Bot\UserState;

class UserRepository implements UserRepositoryInterface
{
    public function findBySocial(
        string $type,
        string|int $socialId
    ): ?UserDTO {
        $social = UserSocial::query()
            ->where('type', $type)
            ->where('social_id', $socialId)
            ->with('user.socials')
            ->first();


        if (!$social) {
            return null;
        }

        return $this->mapUser($social->user);
    }

    public function createUserWithSocial(
        string $messenger,
        string|int $chatId
    ): UserDTO {
        $user = User::create([
            'name' => 'Друг',
            'sex' => 1,

            'language' => 'ru',
            'timezone' => 'Europe/Moscow',

            'tariff_id' => 1,

            'phone_proved' => 0,
            'speaker' => 'marina',

            'politics_agreed' => 0,

            'morning_time_workdays' => '08:00',
            'morning_time_holidays' => '10:00',
            'evening_time_workdays' => '21:00',
            'evening_time_holidays' => '22:00',

            'morning_digest_status' => 1,
            'evening_digest_status' => 1,

            'digest_currencies' => 0,
            'digest_weather' => 1,

            'status' => 1,
            'is_setted' => 0,
        ]);


        UserSocial::create([
            'user_id' => $user->id,
            'type' => $messenger,
            'social_id' => $chatId,
            'is_main' => 1,
        ]);


        UserState::create([
            'user_id' => $user->id,
            'balance' => 0,
            'currency' => 'RUB',
        ]);


        $user->load('socials');


        return $this->mapUser($user);
    }

    private function mapUser(User $user): UserDTO
    {
        return new UserDTO(
            id: $user->id,
            name: $user->name,
            sex: $user->sex,

            email: $user->email,
            phone: $user->phone,
            phoneProved: $user->phone_proved,

            speaker: $user->speaker,
            tariffId: $user->tariff_id,
            language: $user->language,

            timezone: $user->timezone,

            locationId: $user->location_id,

            birthDay: $user->birth_day,
            birthMonth: $user->birth_month,
            birthYear: $user->birth_year,

            politicsAgreed: $user->politics_agreed,

            morningTimeWorkdays: $user->morning_time_workdays,
            morningTimeHolidays: $user->morning_time_holidays,
            eveningTimeWorkdays: $user->evening_time_workdays,
            eveningTimeHolidays: $user->evening_time_holidays,

            morningDigestStatus: $user->morning_digest_status,
            eveningDigestStatus: $user->evening_digest_status,

            digestCurrencies: $user->digest_currencies,
            digestWeather: $user->digest_weather,

            userSocials: collect($user->socials)
                ->map(fn(UserSocial $social) => new UserSocialDTO(
                    id: $social->id,
                    userId: $social->user_id,
                    type: $social->type,
                    socialId: $social->social_id,
                    isMain: $social->is_main,
                    keyboard: $social->keyboard,
                    currentFolderS3: $social->current_folder_s3,
                ))
                ->toArray()
        );
    }

    public function getUserByChatIdAndMessengerType(
        string $chatId,
        string $messangerType
    ): UserDTO
    {
        $user = User::query()
            ->whereHas('socials', function ($q) use ($chatId, $messangerType) {
                $q->byType($messangerType)
                    ->bySocialId($chatId);
            })
            ->with('socials')
            ->firstOrFail();

        return new UserDTO(
            id: $user->id,
            name: $user->name,
            sex: $user->sex,
            email: $user->email,
            phone: $user->phone,
            phoneProved: $user->phone_proved,
            speaker: $user->speaker,
            tariffId: $user->tariff_id,
            language: $user->language,
            timezone: $user->timezone,
            locationId: $user->location_id,
            birthDay: $user->birth_day,
            birthMonth: $user->birth_month,
            birthYear: $user->birth_year,
            politicsAgreed: $user->politics_agreed,
            morningTimeWorkdays: $user->morning_time_workdays,
            morningTimeHolidays: $user->morning_time_holidays,
            eveningTimeWorkdays: $user->evening_time_workdays,
            eveningTimeHolidays: $user->evening_time_holidays,
            morningDigestStatus: $user->morning_digest_status,
            eveningDigestStatus: $user->evening_digest_status,
            digestCurrencies: $user->digest_currencies,
            digestWeather: $user->digest_weather,
            userSocials: $user->socials->map(
                fn(UserSocial $s) => new UserSocialDTO(
                    id: $s->id,
                    userId: $s->user_id,
                    type: $s->type,
                    socialId: $s->social_id,
                    isMain: $s->is_main,
                    keyboard: $s->keyboard,
                    currentFolderS3: $s->current_folder_s3,
                )
            )->toArray(),
        );
    }

    public function getUserIdByChatIdAndMessengerType(
        string $chatId,
        string $messangerType
    ): int
    {
        return UserSocial::query()
            ->byType($messangerType)
            ->bySocialId($chatId)
            ->value('user_id');
    }

    public function getSocialUserIdByChatIdAndMessengerType(
        string $chatId,
        string $messangerType
    ): int
    {
        return UserSocial::query()
            ->byType($messangerType)
            ->bySocialId($chatId)
            ->value('id');
    }

    public function agreeOnPolitics(int $userId): bool
    {
        User::query()
            ->where('id', $userId)
            ->update([
                'politics_agreed' => 1,
            ]);
        return true;
    }

    public function getUserAndSocialsById(int $userId): UserDTO
    {
        $user = User::query()
            ->with('socials')
            ->findOrFail($userId);

        return $this->mapUser($user);
    }
}
