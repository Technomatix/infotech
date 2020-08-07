<?php

namespace local_moco_library\collection;
// TODO-VREPKA: Удалить когда дойдет до типового репозитория
class BaseCollection extends BaseSimpleCollection
{
    /** @var CollectionItem[] */
    protected $items = [];

    /**
     * @param CollectionItem $item
     */
    public function push($item)
    {
        $this->items[$item->getId()] = $item;
    }

    /**
     * @param $id
     *
     * @return CollectionItem|null
     */
    public function get($id)
    {
        return isset($this->items[$id]) ? $this->items[$id] : null;
    }

    /**
     * @param string|int $collectionItemKey
     *
     * @return bool
     */
    public function contains($collectionItemKey)
    {
        return in_array($collectionItemKey, array_keys($this->items));
    }
}
