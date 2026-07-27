<?php

namespace App\Services\Bot\Helpers\Scenarios\Messages;

use App\Services\Bot\Messengers\MessengerTextResolver;

final class MessageButtons
{
    /**
     * Универсальная сборка клавиатуры
     *
     * @param array $items list of callback_data keys
     * @param string $messenger
     * @param string $lang
     * @param int $perRow
     * @param bool $withFooter
     * @param bool $isEdit
     */
    public static function make(
        array                 $items,
        MessengerTextResolver $textResolver,
        string                $messenger,
        string                $lang,
        int                   $perRow = 3,
    ): array
    {
        $keyboard = [];
        $row = [];

        foreach ($items as $item) {
            $row[] = self::getButton($textResolver, $messenger, $lang, $item);

            if (count($row) === $perRow) {
                $keyboard[] = $row;
                $row = [];
            }
        }

        if (!empty($row)) {
            $keyboard[] = $row;
        }

        return $keyboard;
    }

    private static function getButton(
        MessengerTextResolver $textResolver,
        string $messenger,
        string $lang,
        mixed $item
    ): array
    {
        if (is_array($item)) {
            $button = [
                'text' => $item['name'] ?? 'unknown',
                'callback_data' => $item['callback'] ?? 'unknown',
            ];
        } else {
            $button = [
                'text' => $textResolver->get(
                    $item,
                    $messenger,
                    $lang,
                ),
                'callback_data' => $item,
            ];
        }
        return $button;
    }

    /**
     * Дефолтные кнопки
     * @param MessengerTextResolver $textResolver
     * @param string $messenger
     * @param string $lang
     * @param bool $backBtn
     * @param bool $skipBtn
     * @param bool $cancelBtn
     * @return array
     */
    public static function defaultActions(
        MessengerTextResolver $textResolver,
        string $messenger,
        string $lang,
        bool $backBtn = false,
        bool $skipBtn = false,
        bool $cancelBtn = false,
    ): array {
        $row = [];

        if ($backBtn) {
            $row[] = [
                'text' => $textResolver->get('back', $messenger, $lang),
                'callback_data' => 'back',
            ];
        }

        if ($skipBtn) {
            $row[] = [
                'text' => $textResolver->get('skip', $messenger, $lang),
                'callback_data' => 'skip',
            ];
        }

        if ($cancelBtn) {
            $row[] = [
                'text' => $textResolver->get('cancel', $messenger, $lang),
                'callback_data' => 'cancel',
            ];
        }

        return $row;
    }
}
