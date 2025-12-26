<?php

namespace App\ValueObjects;

/**
 * Represents a rectangular position and size on a 2D canvas.
 * Includes helper methods for center coordinates and distance calculations.
 */
class Dimension
{
    /**
     * Constructor.
     *
     * @param int $width  The width in pixels.
     * @param int $height The height in pixels.
     */
    public function __construct(public int $width, public int $height)
    {
    }

    /**
     * Returns the dimension as an associative array.
     *
     * @return array{width:int, height:int}
     */
    public function toArray(): array
    {
        return ['width' => $this->width, 'height' => $this->height];
    }
}
