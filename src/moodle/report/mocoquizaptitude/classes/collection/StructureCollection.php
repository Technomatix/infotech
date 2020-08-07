<?php

namespace report_mocoquizaptitude\collection;

use local_moco_library\collection\BaseSimpleCollection;
use report_mocoquizaptitude\data\structures\Amthauer;
use report_mocoquizaptitude\data\structures\BFQ;
use report_mocoquizaptitude\data\structures\ITO;
use report_mocoquizaptitude\data\structures\JonesCrendall;
use report_mocoquizaptitude\data\structures\Leary;
use report_mocoquizaptitude\data\structures\RichieMartin;
use report_mocoquizaptitude\data\structures\Shmisheka;
use report_mocoquizaptitude\data\structures\TenWords;

class StructureCollection extends BaseSimpleCollection
{
    public static function createInstance()
    {
        $instance = new self();
        $instance->push(ITO::getInstance());
        $instance->push(Shmisheka::getInstance());
        $instance->push(BFQ::getInstance());
        $instance->push(RichieMartin::getInstance());
        $instance->push(JonesCrendall::getInstance());
        $instance->push(TenWords::getInstance());
        $instance->push(Amthauer::getInstance());
        $instance->push(Leary::getInstance());

        return $instance;
    }
}
