<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserSocial;
use App\Models\UserState;
use App\Models\CommonEntity;
use App\Models\CommonEntityTask;
use App\Models\CommonEntityEvent;
use App\Models\CommonEntityReminder;
use App\Models\TaskTemplate;
use App\Models\EventTemplate;
use App\Models\Task;
use App\Models\Event;
use App\Models\Tag;
use App\Models\ReminderTemplate;
use App\Models\Reminder;
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

        //pivot table common_entity_task
        CommonEntityTask::create([
            'entity_id' => $entity->id,
            'child_id' => $task->id,
        ]);

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
        CommonEntityEvent::create([
            'entity_id' => $entity->id,
            'child_id' => $event->id,
        ]);

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
            $reminder = Reminder::factory()->create([
                'template_id' => $template->id,
            ]);

            CommonEntityReminder::create([
                'entity_id' => $entity->id,
                'child_id' => $reminder->id,
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
