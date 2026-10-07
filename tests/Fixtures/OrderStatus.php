<?php

declare(strict_types=1);

namespace AvianUi\AvianUi\Tests\Fixtures;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Shipped = 'shipped';
}
