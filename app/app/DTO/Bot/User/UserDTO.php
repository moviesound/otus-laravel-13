<?php

namespace App\DTO\Bot\User;

final class UserDTO
{
    /**
     * @param UserSocialDTO[] $userSocials
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?int $sex,

        public ?string $email,
        public ?string $phone,
        public int $phoneProved,

        public string $speaker,
        public int $tariffId,
        public string $language,

        public string $timezone,

        public ?int $locationId,

        public ?int $birthDay,
        public ?int $birthMonth,
        public ?int $birthYear,

        public int $politicsAgreed,

        public string $morningTimeWorkdays,
        public string $morningTimeHolidays,
        public string $eveningTimeWorkdays,
        public string $eveningTimeHolidays,

        public int $morningDigestStatus,
        public int $eveningDigestStatus,

        public int $digestCurrencies,
        public int $digestWeather,

        /** @var UserSocialDTO[] */
        public array $userSocials,
    ) {}
}
