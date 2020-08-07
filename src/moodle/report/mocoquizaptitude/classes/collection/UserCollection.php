<?php

namespace report_mocoquizaptitude\collection;

use local_moco_library\collection\BaseCollection;

class UserCollection extends BaseCollection
{
    /**
     * @return int[]
     */
    public function getIds()
    {
        return array_keys($this->items);
    }
}
