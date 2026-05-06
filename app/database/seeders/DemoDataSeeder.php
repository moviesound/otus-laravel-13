<?php

namespace Database\Seeders;

use App\Models\Bot\CommonEntity;
use App\Models\Bot\CommonEntityEvent;
use App\Models\Bot\CommonEntityReminder;
use App\Models\Bot\CommonEntityTask;
use App\Models\Bot\Event;
use App\Models\Bot\EventTemplate;
use App\Models\Bot\Reminder;
use App\Models\Bot\ReminderTemplate;
use App\Models\Bot\Tag;
use App\Models\Bot\Task;
use App\Models\Bot\TaskTemplate;
use App\Models\Bot\User;
use App\Models\Bot\UserSocial;
use App\Models\Bot\UserState;
use Illuminate\Database\Seeder;

/**
 * Run command:
 * php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // create user
        $users = User::factory()
            ->count(10)
            ->create();

        foreach ($users as $user) {
            // create user state
            $this->createUserState($user);

            // creare user_social
            $this->createUserSocials($user);

            // rand amount of events and tasks
            $entitiesCount = rand(1, 5);

            //create tasks and events
            CommonEntity::factory()
                ->count($entitiesCount)
                ->create()
                ->each(function ($entity) use ($user) {

                    $type = rand(0, 1) ? 'task' : 'event';

                    if ($type === 'task') {
                        $this->handleTaskFlow($user, $entity);
                    } else {
                        $this->handleEventFlow($user, $entity);
                    }
                });
        }
    }

    /**
     * Data for tables tasks, task_templates
     */
    private function handleTaskFlow(User $user, CommonEntity $entity): void
    {
        // template
        $template = TaskTemplate::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->attachTags($template);

        // runtime task
        $task = Task::factory()->create([
            'template_id' => $template->id,
        ]);

        // pivot table common_entity_tasks
        $entity->tasks()->attach($task->id);

        // reminders
        $this->createReminders($template, $entity);
    }

    /**
     * Data for tables events, event_templates
     */
    private function handleEventFlow(User $user, CommonEntity $entity): void
    {
        // template
        $template = EventTemplate::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->attachTags($template);

        // runtime event
        $event = Event::factory()->create([
            'template_id' => $template->id,
        ]);

        //pivot table common_entity_event
        $entity->events()->attach($event->id);


        // reminders
        $this->createReminders($template, $entity);
    }

    /**
     * Data for table reminders
     */
    private function createReminders(EventTemplate|TaskTemplate $template, CommonEntity $entity): void
    {
        $reminderTemplates = ReminderTemplate::factory()
            ->count(rand(0, 2))
            ->create([
                'user_id' => $template->user_id,
                'entity_type' => $template->getMorphClass(),
                'entity_id' => $template->id,
            ]);

        foreach ($reminderTemplates as $reminderTemplate) {
            Reminder::factory()->create([
                'template_id' => $template->id,
            ]);
        }
    }

    /**
     * Data for table tags
     */
    private function attachTags($template): void
    {
        $tags = Tag::factory()
            ->count(rand(1, 3))
            ->create();

        $template->tags()->attach($tags->pluck('id'));
    }

    /**
     * Data for table user_socials
     */
    private function createUserSocials(User $user): void
    {
        $types = collect(['telegram', 'vk', 'max'])
            ->random(rand(1, 3))
            ->unique()
            ->values();

        foreach ($types as $index => $type) {
            UserSocial::factory()->create([
                'user_id' => $user->id,
                'type' => $type,
                'is_main' => $index === 0 ? 1 : 0,
            ]);
        }
    }

    private function createUserState(User $user): void
    {
       UserState::factory()->create([
            'user_id' => $user->id,
        ]);
    }
}
