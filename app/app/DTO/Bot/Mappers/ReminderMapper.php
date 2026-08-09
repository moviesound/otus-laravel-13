<?php

namespace App\DTO\Bot\Mappers;



use App\DTO\Bot\Models\ReminderDTO;
use App\DTO\Bot\Models\ReminderQueueDTO;
use App\DTO\Bot\Models\ReminderTemplateDTO;
use App\Models\Bot\Reminder;
use App\Models\Bot\ReminderQueue;
use App\Models\Bot\ReminderTemplate;

class ReminderMapper
{
    public static function reminderTemplateFromModel(ReminderTemplate $model): ReminderTemplateDTO
    {
        return new ReminderTemplateDTO(
            id: $model->id,
            user_id: $model->user_id,
            text: $model->text,
            remind_type: $model->remind_type,
            remind_value: $model->remind_value,
            is_sub_task: $model->is_sub_task,
            entity_type: $model->entity_type,
            entity_id: $model->entity_id,
            has_call: $model->has_call,
            has_sms: $model->has_sms,

            created_at: $model->created_at,
            updated_at: $model->updated_at,
        );
    }


    public static function reminderFromModel(Reminder $model): ReminderDTO
    {
        return new ReminderDTO(
            id: $model->id,
            template_id: $model->template_id,
            date_remind: $model->date_remind,
            status: $model->status,

            created_at: $model->created_at,
            updated_at: $model->updated_at,
        );
    }


    public static function reminderQueueFromModel(ReminderQueue $model): ReminderQueueDTO
    {
        return new ReminderQueueDTO(
            id: $model->id,
            reminder_id: $model->reminder_id,
            user_id: $model->user_id,
            channel: $model->channel,
            status: $model->status,
            sent_times: $model->sent_times,
            last_sent_at: $model->last_sent_at,
            date_remind: $model->date_remind,
            process_name: $model->process_name,
            locked_by: $model->locked_by,
            locked_at: $model->locked_at,

            created_at: $model->created_at,
            updated_at: $model->updated_at,
        );
    }
}
