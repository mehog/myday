<?php

namespace App;

enum WeddingTaskPriority: string
{
    case High = 'high';
    case Normal = 'normal';
    case Low = 'low';

    public function label(): string
    {
        return __('checklist.priority.'.$this->value);
    }
}
