<?php

namespace App\Repositories\Bot;

use App\Contracts\Bot\Repositories\UserRepositoryInterface;
use App\DTO\Bot\User\UserDTO;
use App\DTO\Bot\User\UserSocialDTO;
use App\Models\Bot\User;
use App\Models\Bot\UserSocial;

class UserRepository implements UserRepositoryInterface
{
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
}
