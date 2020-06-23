<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package     qtype_richiemartin
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class qtype_richiemartin_grading
{
    const TYPE = 'richiemartin';
    const INVESTIGATOR = 'Слідчий';
    const PATROL = 'Патрульний';
    const DISTRICT_OFFICER = 'Дільничний офіцер';
    const OPERATIVE_OFFICER = 'Оперуповноважений';

    //public function get_name()
    //{
    //    return self::TYPE;
    //}

    /**
     * Returns the question's grade.
     *
     * @param qtype_richiemartin_question $question
     * @param $answers
     *
     * @return int 1|0
     * @see qtype_richiemartin_grading::grade_question()
     */
    public function grade_question(qtype_richiemartin_question $question, $answers)
    {
        $answeredRows = 0;
        foreach ($question->order as $key => $rowid) {
            if ($this->grade_row($question, $key, $question->rows[$rowid], $answers) > 0) {
                ++$answeredRows;
            }
        }
        /** Возвращаем результат отношения правильных ответов к общему кол-ву ответов от 0 до 1 */
        return $answeredRows/count($question->order);
    }

    /**
     * Grade a specific row.
     * This is the same for all grading methods.
     * Either the student chose the correct response or not (single choice).
     *
     * @param qtype_richiemartin_question $question The question object.
     * @param string $key The field key of the row.
     * @param object $row The row object.
     * @param array $answers The answers array.
     *
     * @return float
     */
    public function grade_row(qtype_richiemartin_question $question, $key, $row, $answers)
    {
        /** если есть ответ, и сумма балов строки равна 11 - значит строка оценена верно */
        return (int)($question->isAnsweredRight($answers, $key, $row));
    }

    public function gradeResponseByScales(array $answers, $questionId)
    {
        global $DB, $USER;

        foreach ($this->getScalesNum() as $scale) {
            $grade = $DB->get_record('qtype_richiemartin_grades', ['questionid' => $questionId, 'userid' => $USER->id, 'scale' => $scale]);
            $amount = 0;
            foreach ($this->getScaleTemplate($scale) as $item) {
                $amount += $this->getRowCommonValue($answers, $item['row'] - 1, $item['column']);
            }

            $value = $this->getNameByAmountOfPoints($scale, $amount);

            if($grade) {
                $grade->amount = $amount;
                $grade->value = $value;
                $DB->update_record('qtype_richiemartin_grades', $grade);
            }else{
                $grade = new stdClass();
                $grade->questionid = $questionId;
                $grade->userid = $USER->id;
                $grade->scale = $scale;
                $grade->amount = $amount;
                $grade->value = $value;
                $DB->insert_record('qtype_richiemartin_grades', $grade);
            }
        }
    }

    public function cleanGradeResponseByScales($questionId)
    {
        global $DB, $USER;

        $DB->delete_records('qtype_richiemartin_grades', ['questionid' => $questionId, 'userid' => $USER->id]);
    }

    private function getRowCommonValue($answer, $rowNum, $columnNum)
    {
        $value = 0;
        if (isset($answer[$rowNum][$columnNum])) {
            $value = (int)$answer[$rowNum][$columnNum];
        }

        return $value;
    }

    public function getScalesNum()
    {
        return [7, 10, 11, 12];
    }

    private function getScaleTemplate($number)
    {
        switch ($number) {
            case 7:
                return [
                    ['row' => 12, 'column' => 1],
                    ['row' => 13, 'column' => 2],
                    ['row' => 16, 'column' => 1],
                    ['row' => 17, 'column' => 3],
                    ['row' => 23, 'column' => 2],
                    ['row' => 24, 'column' => 3],
                    ['row' => 27, 'column' => 3],
                    ['row' => 28, 'column' => 4],
                    ['row' => 31, 'column' => 3],
                    ['row' => 32, 'column' => 1],
                    ['row' => 33, 'column' => 4],
                ];
            case 10:
                return [
                    ['row' => 8, 'column' => 4],
                    ['row' => 13, 'column' => 3],
                    ['row' => 14, 'column' => 2],
                    ['row' => 18, 'column' => 1],
                    ['row' => 19, 'column' => 4],
                    ['row' => 21, 'column' => 2],
                    ['row' => 22, 'column' => 4],
                    ['row' => 24, 'column' => 4],
                    ['row' => 31, 'column' => 4],
                    ['row' => 32, 'column' => 2],
                    ['row' => 33, 'column' => 1],
                ];
            case 11:
                return [
                    ['row' => 1, 'column' => 4],
                    ['row' => 5, 'column' => 4],
                    ['row' => 8, 'column' => 2],
                    ['row' => 9, 'column' => 2],
                    ['row' => 10, 'column' => 4],
                    ['row' => 12, 'column' => 2],
                    ['row' => 14, 'column' => 1],
                    ['row' => 18, 'column' => 3],
                    ['row' => 25, 'column' => 2],
                    ['row' => 27, 'column' => 2],
                    ['row' => 32, 'column' => 3],
                ];
            case 12:
                return [
                    ['row' => 2, 'column' => 3],
                    ['row' => 6, 'column' => 3],
                    ['row' => 7, 'column' => 3],
                    ['row' => 8, 'column' => 3],
                    ['row' => 11, 'column' => 3],
                    ['row' => 21, 'column' => 4],
                    ['row' => 26, 'column' => 3],
                    ['row' => 28, 'column' => 1],
                    ['row' => 29, 'column' => 1],
                    ['row' => 32, 'column' => 4],
                    ['row' => 33, 'column' => 2],
                ];
        }
    }

    private function getNameByAmountOfPoints($scaleNum, $amount)
    {
        switch ($scaleNum) {
            case 7:
                return $this->calculateResultForScale_7($amount);
            case 10:
                return $this->calculateResultForScale_10($amount);
            case 11:
                return $this->calculateResultForScale_11($amount);
            case 12:
                return $this->calculateResultForScale_12($amount);
            default:
                return null;
        }
    }

    private function calculateResultForScale_7($amount)
    {
        switch (true) {
            case ($amount >= 34):
                return self::INVESTIGATOR;
            case (in_array($amount, [32, 33])):
                return self::OPERATIVE_OFFICER;
            case (in_array($amount, [30, 31])):
                return self::DISTRICT_OFFICER;
            default:
                return self::PATROL;
        }
    }

    private function calculateResultForScale_10($amount)
    {
        switch (true) {
            case ($amount >= 27):
                return self::OPERATIVE_OFFICER;
            case (in_array($amount, [25, 26])):
                return self::INVESTIGATOR;
            case (in_array($amount, [21, 24])):
                return self::DISTRICT_OFFICER;
            default:
                return self::PATROL;
        }
    }

    private function calculateResultForScale_11($amount)
    {
        switch (true) {
            case ($amount >= 34):
                return self::INVESTIGATOR;
            case (in_array($amount, [32, 33])):
                return self::OPERATIVE_OFFICER;
            case (in_array($amount, [30, 31])):
                return self::DISTRICT_OFFICER;
            default:
                return self::PATROL;
        }
    }

    private function calculateResultForScale_12($amount)
    {
        switch (true) {
            case ($amount >= 37):
                return self::INVESTIGATOR;
            case (in_array($amount, [35, 36])):
                return self::OPERATIVE_OFFICER;
            case (in_array($amount, [33, 34])):
                return self::DISTRICT_OFFICER;
            default:
                return self::PATROL;
        }
    }
}
