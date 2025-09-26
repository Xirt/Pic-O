<?php

namespace App\ValueObjects;

class Position
{
    public function __construct(public int $x, public int $y, public int $width, public int $height)
    {
    }

    public function centerX(): float
    {
        return $this->x + $this->width / 2;
    }

    public function centerY(): float
    {
        return $this->y + $this->height / 2;
    }

    public function distanceTo(Position $other): float
    {
        return hypot($this->centerX() - $other->centerX(), $this->centerY() - $other->centerY());
    }
}
