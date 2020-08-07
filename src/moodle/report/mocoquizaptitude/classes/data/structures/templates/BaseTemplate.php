<?php

namespace report_mocoquizaptitude\data\structures\templates;

class BaseTemplate
{
    /** @var string */
    protected $direction;

    /** @var string */
    protected $name;

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
