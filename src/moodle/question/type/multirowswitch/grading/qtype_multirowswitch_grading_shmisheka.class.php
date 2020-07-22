<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/multirowswitch/grading/qtype_multirowswitch_grading.class.php');

class qtype_multirowswitch_grading_shmisheka extends qtype_multirowswitch_grading
{
    const TYPE = 'shmisheka';
    const RESPONSE_ANSWERED_TRUE_VALUE = 1;
    const RESPONSE_ANSWERED_FALSE_VALUE = 2;

    protected function gradesTableName()
    {
        return 'qtype_shmisheka_grades';
    }

    protected function getSelectedValue($question, $response)
    {
        $selectedRows = [];
        foreach ($question->order as $key => $rowid) {
            $row = $question->rows[$rowid];
            $keyValue = $row->number > 0 ? $row->number -1 : 0;
            $field = $question->field($keyValue);
            if (isset($response[$field]) && in_array((int)$response[$field], [self::RESPONSE_ANSWERED_TRUE_VALUE, self::RESPONSE_ANSWERED_FALSE_VALUE])) {
                $selectedRows[$row->number . ''] = $response[$field] * 1;
            }
        }

        return $selectedRows;
    }

    protected function getCalculatedRowsByScale($scale)
    {
        $rows = [
            '1' => [
                'factor' => 2,
                'values' => [
                    '7' => 1,
                    '19' => 1,
                    '22' => 1,
                    '29' => 1,
                    '41' => 1,
                    '44' => 1,
                    '63' => 1,
                    '66' => 1,
                    '73' => 1,
                    '51' => 2,
                ]
            ],
            '5' => [
                'factor' => 3,
                'values' => [
                    '1' => 1,
                    '11' => 1,
                    '23' => 1,
                    '33' => 1,
                    '45' => 1,
                    '55' => 1,
                    '67' => 1,
                    '77' => 1,
                ]
            ],
            '6' => [
                'factor' => 3,
                'values' => [
                    '9' => 1,
                    '21' => 1,
                    '43' => 1,
                    '75' => 1,
                    '87' => 1,
                    '31' => 2,
                    '53' => 2,
                    '65'=> 2,
                ]
            ],
            '7' => [
                'factor' => 3,
                'values' => [
                    '16' => 1,
                    '27' => 1,
                    '38' => 1,
                    '49' => 1,
                    '60' => 1,
                    '71' => 1,
                    '82' => 1,
                    '5' => 2,
                ]
            ]
        ];

        return isset($rows[$scale]) ? $rows[$scale] : [];
    }

    /**
     * @param qtype_multirowswitch_question $question
     * @param $response
     *
     * @return mixed
     */
    protected function calculateResult($question, $response)
    {
        /**

        1   Демонстративність
        ((Якщо «№ твердження»=7 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=19 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=22 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=29 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=41 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=44 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=63 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=66 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=73 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=51 і «Так»=0 то 1 інакше 0))*2
        5   Гіпертимність
        ((Якщо «№ твердження»=1 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=11 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=23 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=33 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=45 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=55 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=67 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=77 і «Так»=1 то 1 інакше 0))*3
        6   Дистимічність
        ((Якщо «№ твердження»=9 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=21 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=43 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=75 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=87 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=31 і «Так»=0 то 1 інакше 0) + (Якщо «№ твердження»=53 і «Так»=0 то 1 інакше 0) + (Якщо «№ твердження»=65 і «Так»=0 то 1 інакше 0))*3
        7   Тривожність
        ((Якщо «№ твердження»=16 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=27 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=38 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=49 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=60 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=71 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=82 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=5 і «Так»=0 то 1 інакше 0))*3

         */

        $selectedRowValues = $this->getSelectedValue($question, $response);
        $selectedRowKeys = array_keys($selectedRowValues);
        $scales = ['1', '5', '6', '7',];
        $amount = [];

        foreach ($scales as $scale) {
            $amount[$scale] = 0;
            $scaleData = $this->getCalculatedRowsByScale($scale);
            foreach($scaleData['values'] as $rowNumber => $rowValue){
                if(in_array($rowNumber, $selectedRowKeys) && $selectedRowValues[$rowNumber] === $rowValue){
                    $amount[$scale]++;
                }
            }
            $amount[$scale] = $amount[$scale] * $scaleData['factor'];
        }

        return $amount;
    }

    /**
     * @param $response
     * @param qtype_multirowswitch_question $question
     *
     * @throws dml_exception
     */
    public function gradeResponseByScale($response, $question)
    {
        global $USER, $DB;

        foreach ($this->calculateResult($question, $response) as $scale => $amount) {
            $grade = new stdClass();
            $grade->questionid = $question->id;
            $grade->userid = $USER->id;
            $grade->scale = $scale;
            $grade->amount = $amount;
            $DB->insert_record($this->gradesTableName(), $grade);
        }
    }
}
