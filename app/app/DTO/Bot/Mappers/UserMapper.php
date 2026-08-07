<?php

namespace App\DTO\Bot\Mappers;

use App\DTO\Bot\User\UserDTO;
use App\DTO\Bot\User\UserSocialDTO;
use App\Models\Bot\User;
use App\Models\Bot\UserSocial;

class UserMapper
{
    public static function fromModel(User $model): UserDTO
    {
        return new UserDTO(
            id: $model->id,
            name: $model->name,
            sex: $model->sex,

            email: $model->email,
            phone: $model->phone,
            phoneProved: $model->phone_proved,

            speaker: $model->speaker,
            tariffId: $model->tariff_id,
            language: $model->language,

            timezone: $model->timezone,

            locationId: $model->location_id,

            birthDay: $model->birth_day,
            birthMonth: $model->birth_month,
            birthYear: $model->birth_year,

            politicsAgreed: $model->politics_agreed,

            morningTimeWorkdays: $model->morning_time_workdays,
            morningTimeHolidays: $model->morning_time_holidays,
            eveningTimeWorkdays: $model->evening_time_workdays,
            eveningTimeHolidays: $model->evening_time_holidays,

            morningDigestStatus: $model->morning_digest_status,
            eveningDigestStatus: $model->evening_digest_status,

            digestCurrencies: $model->digest_currencies,
            digestWeather: $model->digest_weather,

            userSocials: $model->relationLoaded('socials')
                ? $model->socials
                    ->map(fn(UserSocial $social) => self::socialFromModel($social))
                    ->toArray()
                : [],
        );
    }


    public static function socialFromModel(UserSocial $model): UserSocialDTO
    {
        return new UserSocialDTO(
            id: $model->id,
            userId: $model->user_id,

            type: $model->type,
            socialId: $model->social_id,

            isMain: $model->is_main,
            keyboard: $model->keyboard,
            currentFolderS3: $model->current_folder_s3,
        );
    }
}
