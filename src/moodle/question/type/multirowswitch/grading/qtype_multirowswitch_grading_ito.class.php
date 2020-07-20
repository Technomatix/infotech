<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/multirowswitch/grading/qtype_multirowswitch_grading.class.php');

class qtype_multirowswitch_grading_ito extends qtype_multirowswitch_grading
{
    const TYPE = 'ito';
    const RESPONSE_ANSWERED_TRUE_VALUE = 1;
    const RESPONSE_ANSWERED_FALSE_VALUE = 2;

    protected function gradesTableName()
    {
        return 'qtype_ito_grades';
    }

    protected function getSelectedValue($question, $response)
    {
        $selectedRows = [];
        foreach ($question->order as $key => $rowid) {
            $row = $question->rows[$rowid];
            $keyNum = $row->number > 0 ? $row->number - 1 : 0;
            $field = $question->field($keyNum);
            if (isset($response[$field]) && in_array((int)$response[$field], [self::RESPONSE_ANSWERED_TRUE_VALUE, self::RESPONSE_ANSWERED_FALSE_VALUE])) {
                $selectedRows[$row->number . ''] = $response[$field] * 1;
            }
        }

        return $selectedRows;
    }

    protected function getCalculatedRowsByScale($scale)
    {
        $rows = [
            'L' => [
                '16' => 1,
                '31' => 1,
                '45' => 1,
                '46' => 1,
                '60' => 1,
                '61' => 1,
                '75' => 1,
                '76' => 1,
                '90'=> 1,
            ],
            'K' => [
                '2' => 1,
                '17' => 1,
                '32' => 1,
                '47' => 1,
                '62' => 1,
                '64' => 1,
                '77' => 1,
                '79' => 1,
                '91'=> 2,
            ],
            '2' => [
                '4' => 1,
                '19' => 1,
                '21' => 1,
                '34' => 1,
                '49' => 1,
                '50' => 1,
                '6' => 2,
                '65' => 2,
                '80'=> 2,
            ],
            '3' => [
                '7' => 1,
                '22' => 1,
                '36' => 1,
                '37' => 1,
                '52' => 1,
                '53' => 1,
                '68' => 1,
                '66' => 2,
                '81'=> 2,
            ],
            '4' => [
                '9' => 1,
                '24' => 1,
                '26' => 1,
                '39' => 1,
                '41' => 1,
                '56' => 1,
                '71' => 2,
                '83' => 2,
                '86'=> 2,
            ],
            '7' => [
                '8' => 1,
                '23' => 1,
                '38' => 1,
                '52' => 1,
                '54' => 1,
                '69' => 1,
                '84' => 1,
                '67' => 2,
                '82'=> 2,
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

        L   Брехня
        (Якщо «№ твердження»=16 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=31 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=45 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=46 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=60 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=61 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=75 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=76 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=90 і «Так»=1 то 1 інакше 0)
        K   Агравація
        (Якщо «№ твердження»=2 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=17 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=32 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=47 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=62 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=64 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=77 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=79 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=91 і «Так»=0 то 1 інакше 0)
        2   Спонтанність
        (Якщо «№ твердження»=4 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=19 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=21 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=34 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=49 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=50 і «Так»=1 то 1 інакше 0) +  (Якщо «№ твердження»=6 і «Так»=0 то 1 інакше 0) +  (Якщо «№ твердження»=65 і «Так»=0 то 1 інакше 0) + (Якщо «№ твердження»=80 і «Так»=0 то 1 інакше 0)
        3   Агресивність
        (Якщо «№ твердження»=7 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=22 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=36 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=37 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=51 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=53 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=68 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=66 і «Так»=0 то 1 інакше 0) + (Якщо «№ твердження»=81 і «Так»=0 то 1 інакше 0)
        4   Ригідність
        (Якщо «№ твердження»=9 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=24 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=26 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=39 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=41 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=56 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=71 і «Так»=0 то 1 інакше 0) + (Якщо «№ твердження»=83 і «Так»=0 то 1 інакше 0) + (Якщо «№ твердження»=86 і «Так»=0 то 1 інакше 0)
        7   Тривожність
        (Якщо «№ твердження»=8 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=23 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=38 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=52 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=54 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=69 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=84 і «Так»=1 то 1 інакше 0) + (Якщо «№ твердження»=67 і «Так»=0 то 1 інакше 0) + (Якщо «№ твердження»=82 і «Так»=0 то 1 інакше 0)

         */

        $selectedRowValues = $this->getSelectedValue($question, $response);
        $selectedRowKeys = array_keys($selectedRowValues);
        $scales = ['L', 'K', '2', '3', '4', '7',];
        $amount = [];

        foreach ($scales as $scale) {
            $amount[$scale] = 0;
            foreach($this->getCalculatedRowsByScale($scale) as $rowNumber => $rowValue){
                if(in_array($rowNumber, $selectedRowKeys) && $selectedRowValues[$rowNumber] === $rowValue){
                    $amount[$scale]++;
                }
            }
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
