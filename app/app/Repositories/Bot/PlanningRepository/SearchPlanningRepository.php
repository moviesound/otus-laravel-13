<?php

namespace App\Repositories\Bot\PlanningRepository;

use App\Contracts\Bot\Repositories\PlanningRepository\SearchPlanningRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SearchPlanningRepository implements SearchPlanningRepositoryInterface
{
    public function searchTasksEventsByScore(int $userId, string $text, int $limit = 50, int $offset = 0): array
    {
        return DB::connection('yozh')->select(
            $this->getQueryTasksEventsByScore(),
            [
                'user_id' => $userId,
                'search' => $text,
                'limit' => $limit,
                'offset' => $offset,
            ]
        );
    }

    public function searchAllUsersTasksEvents(int $userId, int $limit = 20, int $offset = 0): array
    {
        return DB::connection('yozh')->select(
            $this->getQueryAllTasksEventsByUser(),
            [
                'user_id' => $userId,
                'limit' => $limit,
                'offset' => $offset,
            ]
        );
    }

    private function getQueryAllTasksEventsByUser()
    {
        return <<<SQL
WITH latest_tasks AS (
    SELECT t1.*
    FROM tasks t1
    INNER JOIN (
        SELECT template_id, MAX(id) AS max_id
        FROM tasks
        GROUP BY template_id
    ) t2 ON t1.id = t2.max_id
),

latest_events AS (
    SELECT e1.*
    FROM events e1
    INNER JOIN (
        SELECT template_id, MAX(id) AS max_id
        FROM events
        GROUP BY template_id
    ) e2 ON e1.id = e2.max_id
),

latest_reminders AS (
    SELECT r1.*
    FROM reminders r1
    INNER JOIN (
        SELECT template_id, MAX(id) AS max_id
        FROM reminders
        GROUP BY template_id
    ) r2 ON r1.id = r2.max_id
)

SELECT
    tt.id AS entity_id,
    'task' AS type,

    tt.title,
    tt.description,

    COALESCE(
        t.deadline,
        t.period_start
    ) AS nearest_time,

    t.period_end AS period_end,

    /* TAGS TASK */
    COALESCE((
        SELECT JSON_ARRAYAGG(tg.tag)
        FROM tag_task ttg
        JOIN tags tg ON tg.id = ttg.tag_id
        WHERE ttg.task_template_id = tt.id
    ), JSON_ARRAY()) AS tags,

    /* REMINDERS TASK */
    COALESCE((
        SELECT JSON_ARRAYAGG(JSON_OBJECT(
            'text', rt.text,
            'remind_value', rt.remind_value,
            'remind_type', rt.remind_type
        ))
        FROM reminder_templates rt
        WHERE rt.entity_type = 'task'
          AND rt.entity_id = tt.id
    ), JSON_ARRAY()) AS reminders

FROM task_templates tt
LEFT JOIN latest_tasks t ON t.template_id = tt.id
WHERE tt.user_id = :user_id

UNION ALL

SELECT
    et.id AS entity_id,
    'event' AS type,

    et.title,
    et.description,

    COALESCE(
        e.deadline,
        e.period_start
    ) AS nearest_time,

    e.period_end AS period_end,

    /* TAGS EVENT */
    COALESCE((
        SELECT JSON_ARRAYAGG(tg.tag)
        FROM event_tag etg
        JOIN tags tg ON tg.id = etg.tag_id
        WHERE etg.event_template_id = et.id
    ), JSON_ARRAY()) AS tags,

    /* REMINDERS EVENT */
    COALESCE((
        SELECT JSON_ARRAYAGG(JSON_OBJECT(
            'text', rt.text,
            'remind_value', rt.remind_value,
            'remind_type', rt.remind_type
        ))
        FROM reminder_templates rt
        WHERE rt.entity_type = 'event'
          AND rt.entity_id = et.id
    ), JSON_ARRAY()) AS reminders

FROM event_templates et
LEFT JOIN latest_events e ON e.template_id = et.id
WHERE et.user_id = :user_id

ORDER BY nearest_time ASC
LIMIT :limit OFFSET :offset;
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
