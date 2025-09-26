<?php

namespace App\ValueObjects;

class Dimension
{
    public function __construct(public int $width, public int $height)
    {
    }

    public function toArray(): array
    {
        return ['width' => $this->width, 'height' => $this->height];
    }
}
