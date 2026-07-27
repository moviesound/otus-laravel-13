<?php

namespace Tests\Feature\Services\Bot\Entities;

use App\DTO\Bot\User\UserDTO;
use App\Models\Bot\CommonEntity;
use App\Models\Bot\TaskTemplate;
use App\Services\Bot\Entities\PlanningEntityCreator;
use Tests\BotTestCase;

class PlanningEntityCreatorTest extends BotTestCase
{
    public function test_creates_task_entity()
    {
        $user = new UserDTO(
            id: 1,
            name: 'Тест',
            sex: null,
            email: null,
            phone: null,
            phoneProved: 0,
            speaker: 'marina',
            tariffId: 1,
            language: 'ru',
            timezone: 'Europe/Moscow',
            locationId: null,
            birthDay: null,
            birthMonth: null,
            birthYear: null,
            politicsAgreed: 1,
            morningTimeWorkdays: '08:00',
            morningTimeHolidays: '10:00',
            eveningTimeWorkdays: '21:00',
            eveningTimeHolidays: '22:00',
            morningDigestStatus: 1,
            eveningDigestStatus: 1,
            digestCurrencies: 0,
            digestWeather: 1,
            userSocials: [],
        );


        $data = [
            'type' => 'task',

            'subType' => 'report',

            'title' => 'Передать показания за электричество',

            'tags' => [
                'показания',
            ],

            'repeating_type' => 'repeat',

            'repeat_type' => 'monthly',

            'month_days' => '18-22',
        ];


        $creator = app(
            PlanningEntityCreator::class
        );


        $entity = $creator->create(
            user: $user,
            data: $data,
            channel: 'telegram'
        );


        $this->assertInstanceOf(
            CommonEntity::class,
            $entity
        );


        $this->assertDatabaseHas(
            'common_entities',
            [
                'id' => $entity->id,
            ]
        );


        $template = TaskTemplate::query()
            ->where('user_id', $user->id)
            ->first();


        $this->assertNotNull($template);


        $this->assertEquals(
            'Передать показания за электричество',
            $template->title
        );

        $this->assertEquals(
            'report',
            $template->task_type
        );

        $this->assertEquals(
            'monthly',
            $template->repeat_type
        );

        $this->assertEquals(
            '18-22',
            $template->month_days
        );
    }
}
