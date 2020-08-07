<?php

namespace report_mocoquizaptitude\collection\item;

use local_moco_library\collection\SimpleCollectionItem;

class AcquiredProfession implements SimpleCollectionItem
{
    /** @var string */
    public $scale;

    /** @var string */
    public $value;

    public function __construct($scale, $value)
    {
        $this->scale = $scale;
        $this->value = $value;
    }
}
