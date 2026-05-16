<?php

namespace App\Support\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;
use Throwable;

class TelegramPrettyFormatter extends LineFormatter
{
    public function format(LogRecord $record): string
    {
        $level = $record['level_name'] ? '🚨 ' . strtoupper($record['level_name']) : 'ℹ️ LOG';

        $time = $record['datetime']
            ?->setTimezone(new \DateTimeZone('Europe/Moscow'))
            ->format('Y-m-d H:i:s') ?? '';

        $message = $record['message'] ?? '';

        $context = $record['context'] ?? [];

        $userId = $context['userId'] ?? null;

        $exceptionText = '';
        if (!empty($context['exception']) && $context['exception'] instanceof Throwable) {
            $e = $context['exception'];

            $trace = $this->shortTrace($e);

            $exceptionText =
                "\n\n<b>Exception:</b> " . get_class($e) .
                "\n<b>Location:</b> " . $e->getFile() . ":" . $e->getLine() .
                "\n\n<b>Trace:</b>\n<blockquote expandable>" . $trace . "</blockquote>";
        }

        $header =
            "<b>{$level}</b>\n" .
            "<b>Time:</b> {$time}";

        if ($userId) {
            $header .= "\n<b>UserId:</b> {$userId}";
        }

        $body =
            "\n\n<b>Message:</b>\n" .
            $this->escape($message);

        return $header . $body . $exceptionText;
    }

    private function shortTrace(\Throwable $e): string
    {
        $lines = explode("\n", $e->getTraceAsString());

        $formatted = collect($lines)
            ->filter() // убираем пустые строки
            ->take(6)  // ограничиваем размер
            ->map(function ($line) {
                // выделяем номер frame (#0, #1 ...)
                if (preg_match('/^(#\d+)/', $line, $m)) {
                    $frame = "<b>" . $m[1] . "</b>";

                    // заменяем только #0/#1 в начале строки
                    $line = preg_replace('/^#\d+/', $frame,  $this->escape($line));
                }

                return $line;
            })
            ->implode("\n\n"); // 👈 ключевой момент — пустая строка между frames

        return $formatted;
    }

    private function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
