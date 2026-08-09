<?php

namespace App\Repositories\Bot;

use App\Contracts\Bot\Repositories\EventRepositoryInterface;
use App\DTO\Bot\Mappers\EventMapper;
use App\DTO\Bot\Models\EventDTO;
use App\DTO\Bot\Models\EventTemplateDTO;
use App\Models\Bot\Event;
use App\Models\Bot\EventTemplate;

class EventRepository implements EventRepositoryInterface
{

    public function getEvent(int $templateId): EventDTO
    {
        $event = Event::query()
            ->byEventTplId($templateId)
            ->firstOrFail();

        return EventMapper::eventFromModel($event);
    }


    public function getTemplate(int $templateId): EventTemplateDTO
    {
        $template = EventTemplate::query()
            ->findOrFail($templateId);

        return EventMapper::templateFromModel($template);
    }
}
