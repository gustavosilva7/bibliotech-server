<?php

namespace App\Enums;

enum StatusLendingEnum: int
{
    case Scheduled = 0;
    case Pendent = 1;
    case Delayed = 2;
    case Finished = 3;


    public static function getDescription(int $value): string
    {
        return match ($value) {
            self::Scheduled => 'Agendado',
            self::Pendent => 'Pendente',
            self::Delayed => 'Atrasado',
            self::Finished => 'Finalizado'
        };
    }
}
