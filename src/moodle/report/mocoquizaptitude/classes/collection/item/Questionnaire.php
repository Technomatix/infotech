<?php

namespace report_mocoquizaptitude\collection\item;

use local_moco_library\collection\SimpleCollectionItem;
use report_mocoquizaptitude\collection\ProfessionCollection;
use report_mocoquizaptitude\data\structures\templates\BaseTemplate;

class Questionnaire implements SimpleCollectionItem
{
    /** @var BaseTemplate */
    public $template;

    /** @var ProfessionCollection */
    public $professions;

    public function __construct(BaseTemplate $template, ProfessionCollection $professions)
    {
        $this->professions = $professions;
        $this->template = $template;
    }
}
