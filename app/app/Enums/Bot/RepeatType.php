<?php

namespace App\Enums\Bot;

enum RepeatType: string
{
    case None = 'none';
    case Daily = 'every_n_days';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';
}
