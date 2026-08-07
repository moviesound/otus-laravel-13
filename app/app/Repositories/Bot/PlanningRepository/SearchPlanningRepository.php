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
                ->whereHas('tasks', fn($q) => $q->isActive())
                ->exists()

            ||

            EventTemplate::query()
                ->byUserId($userId)
                ->isActive()
                ->whereHas('events', fn($q) => $q->isActive())
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
WITH task_plans AS (
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

    WHERE tt.user_id = :user_id
      AND tt.status = 1
      AND COALESCE(
            t.deadline,
            t.period_start,
            tt.deadline,
            tt.period_start
          ) IS NOT NULL
),

event_plans AS (
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

    WHERE et.user_id = :user_id
      AND et.status = 1
      AND COALESCE(
            e.deadline,
            e.period_start,
            et.deadline,
            et.period_start
          ) IS NOT NULL
),

task_scores AS (
    SELECT
        tp.entity_id,
        'task' AS type,
        (
            (MATCH(tt.title) AGAINST (:search IN BOOLEAN MODE) > 0) +
            (MATCH(tt.description) AGAINST (:search IN BOOLEAN MODE) > 0)
        ) AS score
    FROM task_plans tp
    INNER JOIN task_templates tt
        ON tt.id = tp.entity_id
    WHERE MATCH(tt.title) AGAINST (:search IN BOOLEAN MODE)
       OR MATCH(tt.description) AGAINST (:search IN BOOLEAN MODE)

    UNION ALL

    SELECT
        tp.entity_id,
        'task' AS type,
        COUNT(*) AS score
    FROM task_plans tp
    INNER JOIN tag_task ttg
        ON ttg.task_template_id = tp.entity_id
    INNER JOIN tags tg
        ON tg.id = ttg.tag_id
    WHERE MATCH(tg.tag) AGAINST (:search IN BOOLEAN MODE)
    GROUP BY tp.entity_id
),

event_scores AS (
    SELECT
        ep.entity_id,
        'event' AS type,
        (
            (MATCH(et.title) AGAINST (:search IN BOOLEAN MODE) > 0) +
            (MATCH(et.description) AGAINST (:search IN BOOLEAN MODE) > 0)
        ) AS score
    FROM event_plans ep
    INNER JOIN event_templates et
        ON et.id = ep.entity_id
    WHERE MATCH(et.title) AGAINST (:search IN BOOLEAN MODE)
       OR MATCH(et.description) AGAINST (:search IN BOOLEAN MODE)

    UNION ALL

    SELECT
        ep.entity_id,
        'event' AS type,
        COUNT(*) AS score
    FROM event_plans ep
    INNER JOIN event_tag etg
        ON etg.event_template_id = ep.entity_id
    INNER JOIN tags tg
        ON tg.id = etg.tag_id
    WHERE MATCH(tg.tag) AGAINST (:search IN BOOLEAN MODE)
    GROUP BY ep.entity_id
),

all_scores AS (
    SELECT
        entity_id,
        type,
        SUM(score) AS score
    FROM (
        SELECT * FROM task_scores
        UNION ALL
        SELECT * FROM event_scores
    ) s
    GROUP BY entity_id, type
),

all_plans AS (
    SELECT * FROM task_plans
    UNION ALL
    SELECT * FROM event_plans
),

page AS (
    SELECT
        s.entity_id,
        s.type,
        s.score,
        p.nearest_time,
        p.period_end
    FROM all_scores s
    INNER JOIN all_plans p
        ON p.entity_id = s.entity_id
       AND p.type = s.type
    WHERE s.score > 0
    ORDER BY
        s.score DESC,
        p.nearest_time ASC,
        s.type ASC,
        s.entity_id ASC
    LIMIT :limit OFFSET :offset
)

SELECT
    p.entity_id,
    p.entity_id AS id,
    p.type,
    p.score,

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

FROM page p

LEFT JOIN task_templates tt
    ON p.type = 'task'
   AND tt.id = p.entity_id

LEFT JOIN event_templates et
    ON p.type = 'event'
   AND et.id = p.entity_id

ORDER BY
    p.score DESC,
    p.nearest_time ASC,
    p.type ASC,
    p.entity_id ASC
SQL;
    }

    public function countAllUsersTasksEvents(int $userId): int
    {
        $result = DB::connection('main')->selectOne(
            $this->getCountAllTasksEventsByUser(),
            [
                'user_id_tasks' => $userId,
                'user_id_events' => $userId,
            ]
        );

        return (int)$result->total;
    }

    private function getCountAllTasksEventsByUser(): string
    {
        return <<<SQL
SELECT COUNT(*) AS total
FROM (
    /*
    |--------------------------------------------------------------------------
    | TASKS
    |--------------------------------------------------------------------------
    */
    SELECT
        tt.id

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
      AND COALESCE(
            t.deadline,
            t.period_start,
            tt.deadline,
            tt.period_start
          ) IS NOT NULL


    UNION ALL


    /*
    |--------------------------------------------------------------------------
    | EVENTS
    |--------------------------------------------------------------------------
    */
    SELECT
        et.id

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
      AND COALESCE(
            e.deadline,
            e.period_start,
            et.deadline,
            et.period_start
          ) IS NOT NULL

) AS plans
SQL;
    }
}
