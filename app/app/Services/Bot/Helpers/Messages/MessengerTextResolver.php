<?php

namespace App\Services\Bot\Helpers\Messages;

use App\Contracts\SysTextInterface;

final class MessengerTextResolver
{
    public function __construct(
        private readonly SysTextInterface $sysText,

    ) {}

    public function get(
        string $alias,
        string $messenger,
        string $lang,
        array $replace = []
    ): string {
        $messengerAlias = $messenger . '_' . $alias;

        $text = $this->sysText->get($messengerAlias, $lang, $replace);

        if ($text === $messengerAlias) {
            return $this->sysText->get($alias, $lang, $replace);
        }

        return $text;
    }

    public function header(
        string $messenger,
        string $lang,
        string $typeAlias,
        string $stepAlias
    ): string {
        $typeText = $this->get($typeAlias, $messenger, $lang);
        $stepText = $this->get($stepAlias, $messenger, $lang);

        $typeOk = $typeText !== $typeAlias;//translation exists
        $stepOk = $stepText !== $stepAlias;//translation exists

        if (!$typeOk || !$stepOk) {
            return '';
        }

        return $typeText . "\n" . $stepText . "\n\n";
    }


}
