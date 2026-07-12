<?php

namespace App\Repositories\Bot\PlanningRepository;

use App\Contracts\Bot\Repositories\PlanningRepository\SearchPlanningRepositoryInterface;
use App\Models\Bot\EventTemplate;
use App\Models\Bot\TaskTemplate;
use Illuminate\Support\Facades\DB;

class SearchPlanningRepository implements SearchPlanningRepositoryInterface
{
    public function searchTasksEventsByScore(int $userId, string $text, int $limit = 50, int $offset = 0): array
    {
        return DB::connection('main')->select(
            $this->getQueryTasksEventsByScore(),
            [
                'user_id' => $userId,
                'search' => $text,
                'limit' => $limit,
                'offset' => $offset,
            ]
        );
    }

    public function hasActive(int $userId)
    {
        return TaskTemplate::query()
                ->byUserId($userId)
                ->isActive()
                ->whereHas('tasks', fn ($q) => $q->isActive())
                ->exists()

            ||

            EventTemplate::query()
                ->byUserId($userId)
                ->isActive()
                ->whereHas('events', fn ($q) => $q->isActive())
                ->exists();
    }

    public function searchAllUsersTasksEvents(int $userId, int $limit = 20, int $offset = 0): array
    {
        return DB::connection('main')->select(
            $this->getQueryAllTasksEventsByUser(),
            [
                'user_id_tasks' => $userId,
                'user_id_events' => $userId,
                'limit' => $limit,
                'offset' => $offset,
            ]
        );
    }

    private function getQueryAllTasksEventsByUser()
    {
        return <<<SQL
SELECT
    p.entity_id,
    p.type,

    CASE
        WHEN p.type = 'task' THEN tt.title
        ELSE et.title
    END AS title,

    CASE
        WHEN p.type = 'task' THEN tt.description
        ELSE et.description
    END AS description,

    p.nearest_time,
    p.period_end,

    CASE
        WHEN p.type = 'task' THEN
            COALESCE(
                (
                    SELECT JSON_ARRAYAGG(tg.tag)
                    FROM tag_task ttag
                    INNER JOIN tags tg
                        ON tg.id = ttag.tag_id
                    WHERE ttag.task_template_id = p.entity_id
                ),
                JSON_ARRAY()
            )

        WHEN p.type = 'event' THEN
            COALESCE(
                (
                    SELECT JSON_ARRAYAGG(tg.tag)
                    FROM event_tag etag
                    INNER JOIN tags tg
                        ON tg.id = etag.tag_id
                    WHERE etag.event_template_id = p.entity_id
                ),
                JSON_ARRAY()
            )
    END AS tags,

    /*
    |--------------------------------------------------------------------------
    | Все reminder_templates сущности + последний reminder instance каждого template
    |--------------------------------------------------------------------------
    */
    COALESCE(
        (
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'template_id', rt.id,
                    'text', rt.text,
                    'remind_value', rt.remind_value,
                    'remind_type', rt.remind_type,

                    'reminder_id', r.id,
                    'date_remind', r.date_remind,
                    'reminder_status', r.status
                )
            )
            FROM reminder_templates rt

            LEFT JOIN reminders r
                ON r.id = (
                    SELECT MAX(r2.id)
                    FROM reminders r2
                    WHERE r2.template_id = rt.id
                      AND r2.status IN ('pending', 'processing')
                )

            WHERE rt.entity_type = p.type
              AND rt.entity_id = p.entity_id
        ),
        JSON_ARRAY()
    ) AS reminders,

    /*
    |--------------------------------------------------------------------------
    | Последний active reminder instance по всей задаче/событию
    |--------------------------------------------------------------------------
    */
    COALESCE(
        (
            SELECT JSON_OBJECT(
                'template_id', rt.id,
                'reminder_id', r.id,
                'text', rt.text,
                'remind_value', rt.remind_value,
                'remind_type', rt.remind_type,
                'date_remind', r.date_remind,
                'status', r.status
            )
            FROM reminder_templates rt
            INNER JOIN reminders r
                ON r.template_id = rt.id

            WHERE rt.entity_type = p.type
              AND rt.entity_id = p.entity_id
              AND r.status IN ('pending', 'processing')

            ORDER BY r.id DESC

            LIMIT 1
        ),
        JSON_OBJECT()
    ) AS latest_reminder

FROM (
    SELECT base.*
    FROM (
        /*
        |--------------------------------------------------------------------------
        | TASKS
        |--------------------------------------------------------------------------
        */
        SELECT
            tt.id AS entity_id,
            'task' AS type,

            COALESCE(
                t.deadline,
                t.period_start,
                tt.deadline,
                tt.period_start
            ) AS nearest_time,

            COALESCE(
                t.period_end,
                tt.period_end
            ) AS period_end

        FROM task_templates tt

        INNER JOIN tasks t
            ON t.id = (
                SELECT MAX(t2.id)
                FROM tasks t2
                WHERE t2.template_id = tt.id
                  AND t2.status IN ('pending', 'processing')
            )

        WHERE tt.user_id = :user_id_tasks
          AND tt.status = 1


        UNION ALL


        /*
        |--------------------------------------------------------------------------
        | EVENTS
        |--------------------------------------------------------------------------
        */
        SELECT
            et.id AS entity_id,
            'event' AS type,

            COALESCE(
                e.deadline,
                e.period_start,
                et.deadline,
                et.period_start
            ) AS nearest_time,

            COALESCE(
                e.period_end,
                et.period_end
            ) AS period_end

        FROM event_templates et

        INNER JOIN events e
            ON e.id = (
                SELECT MAX(e2.id)
                FROM events e2
                WHERE e2.template_id = et.id
                  AND e2.status IN ('pending', 'processing')
            )

        WHERE et.user_id = :user_id_events
          AND et.status = 1

    ) AS base

    WHERE base.nearest_time IS NOT NULL

    ORDER BY
        base.nearest_time ASC,
        base.type ASC,
        base.entity_id ASC

    LIMIT :limit OFFSET :offset
) AS p

LEFT JOIN task_templates tt
    ON p.type = 'task'
   AND tt.id = p.entity_id

LEFT JOIN event_templates et
    ON p.type = 'event'
   AND et.id = p.entity_id

ORDER BY
    p.nearest_time ASC,
    p.type ASC,
    p.entity_id ASC
SQL;
    }

    private function getQueryTasksEventsByScore(): string
    {
        return <<<SQL
WITH task_scores AS (

    SELECT
        tt.id,
        'task' AS type,
        (
            (MATCH(tt.title) AGAINST (:search IN BOOLEAN MODE) > 0) +
            (MATCH(tt.description) AGAINST (:search IN BOOLEAN MODE) > 0)
        ) AS score
    FROM task_templates tt
    WHERE tt.user_id = :user_id
      AND (
            MATCH(tt.title) AGAINST (:search IN BOOLEAN MODE)
         OR MATCH(tt.description) AGAINST (:search IN BOOLEAN MODE)
      )

    UNION ALL

    SELECT
        tt.id,
        'task',
        COUNT(*) AS score
    FROM tags t
    JOIN tag_task ttg
        ON ttg.tag_id = t.id
    JOIN task_templates tt
        ON tt.id = ttg.task_template_id
    WHERE tt.user_id = :user_id
      AND MATCH(t.tag) AGAINST (:search IN BOOLEAN MODE)
    GROUP BY tt.id

    UNION ALL

    SELECT
        tt.id,
        'task',
        COUNT(*) AS score
    FROM reminder_templates rt
    JOIN task_templates tt
        ON tt.id = rt.entity_id
    WHERE rt.entity_type = 'task'
      AND tt.user_id = :user_id
      AND MATCH(rt.text) AGAINST (:search IN BOOLEAN MODE)
    GROUP BY tt.id
),

event_scores AS (

    SELECT
        et.id,
        'event' AS type,
        (
            (MATCH(et.title) AGAINST (:search IN BOOLEAN MODE) > 0) +
            (MATCH(et.description) AGAINST (:search IN BOOLEAN MODE) > 0)
        ) AS score
    FROM event_templates et
    WHERE et.user_id = :user_id
      AND (
            MATCH(et.title) AGAINST (:search IN BOOLEAN MODE)
         OR MATCH(et.description) AGAINST (:search IN BOOLEAN MODE)
      )

    UNION ALL

    SELECT
        et.id,
        'event',
        COUNT(*) AS score
    FROM tags t
    JOIN event_tag etg
        ON etg.tag_id = t.id
    JOIN event_templates et
        ON et.id = etg.event_template_id
    WHERE et.user_id = :user_id
      AND MATCH(t.tag) AGAINST (:search IN BOOLEAN MODE)
    GROUP BY et.id

    UNION ALL

    SELECT
        et.id,
        'event',
        COUNT(*) AS score
    FROM reminder_templates rt
    JOIN event_templates et
        ON et.id = rt.entity_id
    WHERE rt.entity_type = 'event'
      AND et.user_id = :user_id
      AND MATCH(rt.text) AGAINST (:search IN BOOLEAN MODE)
    GROUP BY et.id
),

all_scores AS (

    SELECT
        id,
        type,
        SUM(score) AS score
    FROM (
        SELECT * FROM task_scores
        UNION ALL
        SELECT * FROM event_scores
    ) s
    GROUP BY id, type
)

SELECT
    s.id,
    s.type,
    s.score,

    CASE
        WHEN s.type = 'task' THEN tt.title
        ELSE et.title
    END AS title,

    CASE
        WHEN s.type = 'task' THEN tt.description
        ELSE et.description
    END AS description,

    CASE
        WHEN s.type = 'task' THEN (
            SELECT JSON_ARRAYAGG(t.tag)
            FROM tag_task ttg
            JOIN tags t ON t.id = ttg.tag_id
            WHERE ttg.task_template_id = tt.id
        )
        ELSE (
            SELECT JSON_ARRAYAGG(t.tag)
            FROM event_tag etg
            JOIN tags t ON t.id = etg.tag_id
            WHERE etg.event_template_id = et.id
        )
    END AS tags,

    CASE
        WHEN s.type = 'task' THEN (
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'text', rt.text,
                    'remind_type', rt.remind_type,
                    'remind_value', rt.remind_value
                )
            )
            FROM reminder_templates rt
            WHERE rt.entity_type = 'task'
              AND rt.entity_id = tt.id
        )
        ELSE (
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'text', rt.text,
                    'remind_type', rt.remind_type,
                    'remind_value', rt.remind_value
                )
            )
            FROM reminder_templates rt
            WHERE rt.entity_type = 'event'
              AND rt.entity_id = et.id
        )
    END AS reminders

FROM all_scores s

LEFT JOIN task_templates tt
    ON s.type = 'task'
   AND tt.id = s.id

LEFT JOIN event_templates et
    ON s.type = 'event'
   AND et.id = s.id

ORDER BY s.score DESC
LIMIT :limit OFFSET :offset;
SQL;
    }
}
