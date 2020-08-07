<?php

namespace report_mocoquizaptitude\collection\item;

use local_moco_library\collection\CollectionItem;
use report_mocoquizaptitude\collection\QuestionnaireCollection;

class UserItem implements CollectionItem
{
    /** @var int */
    public $id;

    /** @var string */
    public $fullName;

    /** @var string */
    public $groupName;

    /** @var QuestionnaireCollection */
    public $questionnaires;

    /**
     * User constructor.
     *
     * @param int $id
     * @param string $fullName
     * @param string $groupName
     */
    public function __construct($id, $fullName, $groupName)
    {
        $this->id = (int)$id;
        $this->fullName = $fullName;
        $this->groupName = $groupName;

        $this->questionnaires = new QuestionnaireCollection();
    }

    public function getId()
    {
        return $this->id;
    }
}
