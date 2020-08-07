<?php

namespace local_moco_library\collection;
// TODO-VREPKA: Удалить когда дойдет до типового репозитория
class BaseSimpleCollection
{
    protected $items = [];

    public function push($item)
    {
        $this->items[] = $item;
    }

    public function pushMultiple(array $items)
    {
        foreach ($items as $item) {
            $this->push($item);
        }
    }

    /**
     * @return int
     */
    public function length()
    {
        return count($this->items);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->length() === 0;
    }

    public function getAll()
    {
        return $this->items;
    }
}
