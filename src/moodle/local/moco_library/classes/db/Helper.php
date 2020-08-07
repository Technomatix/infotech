<?php

namespace local_moco_library\db;

// TODO-VREPKA: перенести в типовый и тут удалить

class Helper
{
    public static function getInstance()
    {
        return new self();
    }

    public function addWhereInParams(array &$where, array &$params, $fieldNameWithAlias, $value, $tail = '')
    {
        $result = $this->addWhereInParamOne($fieldNameWithAlias, $value, $tail);

        $where[] = $result['where'];
        $params = array_merge($params, $result['params']);
    }

    public function addWhereInParamOne($fieldNameWithAlias, $value, $tail = '')
    {
        $where = 'TRUE';
        $params = [];

        $paramName = str_replace('.', '', $fieldNameWithAlias) . $tail;
        if (is_array($value)) {
            $paramIds = [];
            foreach ($value as $id) {
                $paramIds[$paramName . '_' . strtolower($id)] = $id;
            }

            if (count($paramIds)) {
                $where = $fieldNameWithAlias . ' IN (:' . implode(', :', array_keys($paramIds)) . ')';
                $params = $paramIds;
            }
        } else {
            $where = $fieldNameWithAlias . '=:' . $paramName;
            $params[$paramName] = $value;
        }

        return ['where' => $where, 'params' => $params];
    }
}
