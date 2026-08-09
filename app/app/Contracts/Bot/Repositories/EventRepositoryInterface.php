<?php

namespace App\Contracts\Bot\Repositories;

use App\DTO\Bot\Models\EventDTO;
use App\DTO\Bot\Models\EventTemplateDTO;

interface EventRepositoryInterface
{
    public function getEvent(int $templateId): EventDTO;
    public function getTemplate(int $templateId): EventTemplateDTO;
}
