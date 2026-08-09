<?php

namespace App\Services\Bot\Helpers\Scenarios\Reminders;

use App\Services\Bot\Helpers\Messages\MessengerTextResolver;
use App\Services\Bot\Helpers\Words\PluralFormatter;

final readonly class ReminderFormatter
{
    public function __construct(
        private MessengerTextResolver $textResolver,
    ) {
    }

    public function list(
        array $reminders,
        string $messenger,
        string $lang,
        bool $withTitle = true,
    ): string {
        if (empty($reminders)) {
            return '';
        }

        $result = '';

        if ($withTitle) {
            $result .= $this->textResolver->get(
                    'reminders',
                    $messenger,
                    $lang,
                ) . ":\n";
        }

        foreach ($reminders as $index => $reminder) {

            $result .= ($index + 1) . '. ';

            $result .= $this->textResolver->get(
                'reminde_before_n_day',
                $messenger,
                $lang,
                [
                    'days' => sprintf(
                        '%d %s',
                        $reminder['value'],
                        PluralFormatter::word(
                            $this->textResolver,
                            $reminder['value'],
                            $reminder['type'],
                            $messenger,
                            $lang,
                        ),
                    ),
                ],
            );

            if (!empty($reminder['text'])) {
                $result .= ': ' . $reminder['text'];
            }

            $result .= "\n";
        }

        return rtrim($result);
    }
}
