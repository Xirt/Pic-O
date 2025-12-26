<?php

namespace App\ValueObjects;

/**
 * Represents a two-dimensional size with width and height.
 */
class Position
{
    /**
     * Constructor.
     *
     * @param int $x      The top-left X coordinate.
     * @param int $y      The top-left Y coordinate.
     * @param int $width  The width of the rectangle.
     * @param int $height The height of the rectangle.
     */
    public function __construct(public int $x, public int $y, public int $width, public int $height)
    {
    }

    /**
     * Get the X coordinate of the rectangle's center.
     *
     * @return float
     */
    public function centerX(): float
    {
        return $this->x + $this->width / 2;
    }

    /**
     * Get the Y coordinate of the rectangle's center.
     *
     * @return float
     */
    public function centerY(): float
    {
        return $this->y + $this->height / 2;
    }

    /**
     * Calculate the distance from this position to another.
     *
     * @param Position $other The other position to measure distance to.
     *
     * @return float The Euclidean distance between the centers.
     */
    public function distanceTo(Position $other): float
    {
        return hypot($this->centerX() - $other->centerX(), $this->centerY() - $other->centerY());
    }
}
