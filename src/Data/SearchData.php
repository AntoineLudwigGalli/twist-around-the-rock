<?php

namespace App\Data;

use App\Entity\Category;
use App\Entity\Color;
use App\Entity\Stone;

class SearchData
{
    /**
     * @var string
     */
    public string $q = '';

    /**
     * @var Category[]
     */
    public array $categories = [];

    /**
     * @var Color[]
     */
    public array $colors = [];

    /**
     * @var Stone[]
     */
    public array $stones = [];

    /**
     * @var null|integer
     */
    public ?int $max;

    /**
     * @var null|integer
     */
    public ?int $min;

    /**
     * @var boolean
     */
    public bool $available = true;

}