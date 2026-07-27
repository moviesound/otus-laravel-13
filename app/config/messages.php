<?php

return [
    'stopAlgo' => ['stop', 'отмена', 'стоп', 'завершить', 'заверши', 'прекратить', 'отменить', 'закрыть', 'закройся', 'остановить', 'остановиcь', 'stop_algo', 'Stop algorythm', 'Завершить процедуру', 'Остановить процедуру', 'cancel'],
    'deny' => ['no', 'нет', 'отмена', 'отменить', 'стоп', 'неа', 'не-а', 'нету', 'нетушки', 'еще чего', 'ещё чего', 'ой нет', 'ой, нет', 'упс', 'упс, нет', 'упс нет'],
    'continue' => ['продолжить', 'пропустить', 'дальше', 'далее', 'skip', 'continue'],
    'back' => ['back', 'назад', 'вернуться', 'вернуть назад', 'верни', 'верни назад', 'вернись', 'вернись назад'],
    'approve' => ['yes', 'ага', 'агась', 'да', 'конечно', 'естественно'],
    'periodEveryDay' => ['every_day', 'every day', 'каждый день', 'раз в день', 'ежедневно', 'once a day', 'once day', 'once per day', 'once per a day', 'once in a day', 'once in day', 'each day'],
    'periodEveryWeek' => ['every_week', 'every week', 'каждую неделю', 'раз в неделю', 'еженедельно', 'once a week', 'once week', 'once per week', 'once per a week', 'once in a week', 'once in week', 'each week'],
    'periodEveryMonth' => ['every_month', 'every month', 'каждый месяц', 'раз в месяц', 'ежемесячно', 'once a month', 'once month', 'once per month', 'once per a month', 'once in a month', 'once in month', 'each month'],
    'periodEveryYear' => ['every_year', 'every year', 'каждый год', 'раз в год', 'ежегодно', 'once a year', 'once year', 'once per year', 'once per a year', 'once in a year', 'once in year', 'each year'],
    'genders' => [
        // Русский
        'мужской', 'женский',
        'мужчина', 'женщина',
        'муж', 'жен',
        'муж.', 'жен.',
        'м', 'ж',
        'м.', 'ж.',

        // English
        'male', 'female',
        'man', 'woman',
        'm', 'f',
        'm.', 'f.',

        // Español
        'masculino', 'femenino',
        'hombre', 'mujer',
        'masc', 'fem',
        'masc.', 'fem.',
    ],
    'genderMap' => [
        // Мужчина → 1
        'мужской' => 1,
        'мужчина' => 1,
        'муж' => 1,
        'муж.' => 1,
        'м' => 1,
        'м.' => 1,

        'male' => 1,
        'man' => 1,
        'm' => 1,
        'm.' => 1,

        'masculino' => 1,
        'hombre' => 1,
        'masc' => 1,
        'masc.' => 1,


        // Женщина → 2
        'женский' => 2,
        'женщина' => 2,
        'жен' => 2,
        'жен.' => 2,
        'ж' => 2,
        'ж.' => 2,

        'female' => 2,
        'woman' => 2,
        'f' => 2,
        'f.' => 2,

        'femenino' => 2,
        'mujer' => 2,
        'fem' => 2,
        'fem.' => 2,
    ],
];
