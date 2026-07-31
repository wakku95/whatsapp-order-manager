<?php

namespace App\Enums;

enum OrderIntentEnum: string
{
    case CREATE_ORDER = 'create_order';
    case ADD_ITEM = 'add_item';
    case REMOVE_ITEM = 'remove_item';
    case CHANGE_QUANTITY = 'change_quantity';
    case CONFIRM_ORDER = 'confirm_order';
    case CANCEL_ORDER = 'cancel_order';
    case ASK_QUESTION = 'ask_question';
    case UNKNOWN = 'unknown';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
