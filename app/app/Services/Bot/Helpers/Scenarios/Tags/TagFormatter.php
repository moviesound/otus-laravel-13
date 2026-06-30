<?php

namespace App\Services\Bot\Helpers\Scenarios\Tags;

final class TagFormatter
{
    public static function nums(array $tags): string
    {
        $tags = array_values(self::clean($tags));

        return implode("\n", array_map(
            fn($t, $i) => ($i + 1) . '. ' . $t,
            $tags,
            array_keys($tags)
        ));
    }

    public static function comma(array $tags): string
    {
        return implode(', ', self::clean($tags));
    }

    public static function hash(array $tags): string
    {
        return implode(' ', array_map(
            fn($t) => '#' . $t,
            self::clean($tags)
        ));
    }

    private static function clean(array $tags): array
    {
        $cleaned = array_map(function ($tag) {
            $tag = trim($tag);

            if ($tag === '') {
                return null;
            }

            if (preg_match('/^([\p{L}\p{N}_]+)/u', $tag, $m)) {
                return $m[1];
            }

            return null;
        }, $tags);

        return array_values(array_filter($cleaned));
    }

    public static function parse(string $input): array
    {
        $input = mb_trim($input);

        if ($input === '') {
            return [];
        }

        $normalized = preg_replace(
            "/\r\n|\r/u",
            "\n",
            $input
        );

        $items = mb_strpos($normalized, "\n") !== false
            ? explode("\n", $normalized)
            : explode(",", $normalized);

        $items = array_map(
            static fn(string $item) => mb_trim($item),
            $items
        );

        $items = array_filter(
            $items,
            static fn(string $item) => $item !== ''
        );

        return array_values(
            array_unique($items)
        );
    }
}
