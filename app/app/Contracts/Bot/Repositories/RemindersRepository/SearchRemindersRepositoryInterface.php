<?php

namespace App\Contracts\Bot\Repositories\RemindersRepository;

use App\DTO\Bot\Models\ReminderDTO;
use App\DTO\Bot\Models\ReminderTemplateDTO;

interface SearchRemindersRepositoryInterface
{
    public function getQueue(int $skip, int $amount): array;
    public function getReminderById(int $id): ReminderDTO;
    public function getReminderTemplateById(int $templateId): ReminderTemplateDTO;
}
