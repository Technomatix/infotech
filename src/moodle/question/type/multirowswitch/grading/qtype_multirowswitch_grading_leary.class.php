<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/multirowswitch/grading/qtype_multirowswitch_grading.class.php');

class qtype_multirowswitch_grading_leary extends qtype_multirowswitch_grading
{
    const TYPE = 'leary';
    const RESPONSE_ANSWERED_TRUE_VALUE = 1;

    public function grade_question(qtype_multirowswitch_question $question, $answers)
    {
        $correctRows = 0;
        foreach ($question->order as $key => $rowid) {
            if ($this->grade_row($question, $key, $question->rows[$rowid], $answers)) {
                ++$correctRows;
            }
        }

        return $correctRows > 0 ? 1 : 0;
    }

    protected function gradesTableName()
    {
        return 'qtype_leary_grades';
    }

    protected function getSelectedValue($question, $response)
    {
        $selectedRows = [];
        foreach ($question->order as $key => $rowid) {
            $row = $question->rows[$rowid];
            $keyValue = $row->number > 0 ? $row->number -1 : 0;
            $field = $question->field($keyValue);
            if (isset($response[$field]) && in_array((int)$response[$field], [self::RESPONSE_ANSWERED_TRUE_VALUE])) {
                $selectedRows[] = $row->number;
            }
        }

        return $selectedRows;
    }

    protected function getCalculatedRowsByScale($scale)
    {
        $rows = [
            '3' => [9,10,11,12,41,42,43,44,73,74,75,76,105,106,107,108],
            '4' => [13,14,15,16,45,46,47,48,77,78,79,80,109,110,111,112],
            '5' => [17,18,19,20,49,50,51,52,81,82,83,84,113,114,115,116],
            '8' => [29,30,31,32,61,62,63,64,93,94,95,96,125,126,127,128],
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
        3-й октант
        (Якщо «№ Визначення»=9 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=10 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=11 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=12 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=41 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=42 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=43 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=44 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=73 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=74 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=75 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=76 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=105 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=106 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=107 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=108 і «Відповідь Так»=1 то 1 інакше 0) +
        */
        /**
        4-й октант
        (Якщо «№ Визначення»=13 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=14 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=15 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=16 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=45 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=46 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=47 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=48 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=77 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=78 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=79 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=80 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=109 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=110 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=111 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=112 і «Відповідь Так»=1 то 1 інакше 0) +
         */
        /**
        5-й октант
        (Якщо «№ Визначення»=17 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=18 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=19 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=20 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=49 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=50 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=51 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=52 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=81 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=82 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=83 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=84 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=113 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=114 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=115 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=116 і «Відповідь Так»=1 то 1 інакше 0) +
         */
        /**
        8-й октант
        (Якщо «№ Визначення»=29 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=30 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=31 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=32 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=61 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=62 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=63 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=64 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=93 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=94 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=95 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=96 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=125 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=126 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=127 і «Відповідь Так»=1 то 1 інакше 0) +
        (Якщо «№ Визначення»=128 і «Відповідь Так»=1 то 1 інакше 0) +
         */

        $selectedRows = $this->getSelectedValue($question, $response);
        $scales = ['3', '4', '5', '8',];
        $amount = [];

        foreach ($scales as $scale) {
            $amount[$scale] = 0;
            foreach($this->getCalculatedRowsByScale($scale) as $rowValue){
                if(in_array($rowValue, $selectedRows)){
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
