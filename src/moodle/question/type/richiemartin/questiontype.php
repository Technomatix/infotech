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

global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/type/richiemartin/lib.php');

/**
 * The richiemartin question type.
 */
class qtype_richiemartin extends question_type
{
    /**
     * Sets the default options for the question.
     *
     * (non-PHPdoc)
     *
     * @see question_type::set_default_options()
     */
    public function set_default_options($question)
    {
        $richiemartinconfig = get_config('qtype_richiemartin');

        if (!isset($question->options)) {
            $question->options = new stdClass();
        }
        if (!isset($question->options->numberofrows)) {
            $question->options->numberofrows = count($this->getQuestionRows());
        }
        if (!isset($question->options->numberofcolumns)) {
            $question->options->numberofcolumns = QTYPE_RICHIEMARTIN_NUMBER_OF_RESPONSES;
        }
        if (!isset($question->options->shuffleanswers)) {
            $question->options->shuffleanswers = $richiemartinconfig->shuffleanswers;
        }

        if (!isset($question->options->rows)) {
            $rows = [];
            for ($i = 1; $i <= $question->options->numberofrows; ++$i) {
                $row = new stdClass();
                $row->number = $i;
                $row->optiontext = '';
                $row->optionitems = [];
                $rows[] = $row;
            }
            $question->options->rows = $rows;
        }

        if (!isset($question->options->columns)) {
            $columns = [];
            for ($i = 1; $i <= $question->options->numberofcolumns; ++$i) {
                $column = new stdClass();
                $column->number = $i;
                if (isset($richiemartinconfig->{'responsetext' . $i})) {
                    $responsetextcol = $richiemartinconfig->{'responsetext' . $i};
                } else {
                    $responsetextcol = '';
                }
                $column->responsetext = $responsetextcol;
                $column->responsetextformat = FORMAT_MOODLE;
                $columns[] = $column;
            }
            $question->options->columns = $columns;
        }
    }

    /**
     * Loads the question options, rows, columns and weights from the database.
     *
     * (non-PHPdoc)
     *
     * @see question_type::get_question_options()
     */
    public function get_question_options($question)
    {
        global $DB;

        parent::get_question_options($question);

        // Retrieve the question options.
        $question->options = $DB->get_record('qtype_richiemartin_options', ['questionid' => $question->id]);
        // Retrieve the question rows (richiemartin options).
        $question->options->rows = $this->getQuestionRows();
        // Retrieve the question columns.
        $question->options->columns = $DB->get_records('qtype_richiemartin_columns', ['questionid' => $question->id], 'number ASC', '*', 0, $question->options->numberofcolumns);

        foreach ($question->options->columns as $key => $column) {
            $question->{'responsetext_' . $column->number} = $column->responsetext;
        }

        return true;
    }

    /**
     * Stores the question options in the database.
     *
     * (non-PHPdoc)
     *
     * @see question_type::save_question_options()
     */
    public function save_question_options($question)
    {
        global $DB;

        // Insert all the new options.
        $options = $DB->get_record('qtype_richiemartin_options', ['questionid' => $question->id]);
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->shuffleanswers = '';
            $options->numberofcolumns = '';
            $options->numberofrows = '';
            $options->id = $DB->insert_record('qtype_richiemartin_options', $options);
        }

        $options->shuffleanswers = $question->shuffleanswers;
        $options->numberofrows = $question->numberofrows;
        $options->numberofcolumns = $question->numberofcolumns;
        $DB->update_record('qtype_richiemartin_options', $options);

        $oldcolumns = $DB->get_records('qtype_richiemartin_columns', ['questionid' => $question->id], 'number ASC');

        // Insert all new columns.
        for ($i = 1; $i <= $options->numberofcolumns; ++$i) {
            $column = array_shift($oldcolumns);
            if (!$column) {
                $column = new stdClass();
                $column->questionid = $question->id;
                $column->number = $i;
                $column->responsetext = '';
                $column->responsetextformat = FORMAT_MOODLE;

                $column->id = $DB->insert_record('qtype_richiemartin_columns', $column);
            }

            // Perform an update.
            $column->responsetext = $question->{'responsetext_' . $i};
            $column->responsetextformat = FORMAT_MOODLE;
            $DB->update_record('qtype_richiemartin_columns', $column);
        }
    }

    /**
     * Initialise the common question_definition fields.
     *
     * @param question_definition $question the question_definition we are creating.
     * @param object $questiondata the question data loaded from the database.
     */
    protected function initialise_question_instance(question_definition $question, $questiondata)
    {
        parent::initialise_question_instance($question, $questiondata);

        $question->shuffleanswers = $questiondata->options->shuffleanswers;
        $question->numberofrows = $questiondata->options->numberofrows;
        $question->numberofcols = $questiondata->options->numberofcolumns;
        $question->rows = $this->getQuestionRows();
        $question->columns = $questiondata->options->columns;
    }

    /**
     * Custom method for deleting richiemartin questions.
     *
     * @param $questionid
     * @param $contextid
     *
     * @throws dml_exception
     * @see question_type::delete_question()
     */
    public function delete_question($questionid, $contextid)
    {
        global $DB;
        $DB->delete_records('qtype_richiemartin_options', ['questionid' => $questionid]);
        $DB->delete_records('qtype_richiemartin_columns', ['questionid' => $questionid]);
        parent::delete_question($questionid, $contextid);
    }

    private function getQuestionRows()
    {
        $rows = [
            [
                'number' => 1,
                'optiontext' => "Я вважаю, що міг би зробити великий внесок на такій роботі, де…",
                'optionitems' => [
                    1 => 'гарна заробітна плата та інші види винагород;',
                    2 => 'є можливість установити гарні взаємини з колегами по роботі;',
                    3 => 'я міг би впливати на прийняття рішень і демонструвати свої переваги як працівника;',
                    4 => 'у мене є можливість удосконалюватися і зростати як особистість;',
                ],
            ],
            [
                'number' => 2,
                'optiontext' => "Я не бажав би працювати там, де…",
                'optionitems' => [
                    1 => 'відсутні чіткі вказівки, що саме від мене вимагається;',
                    2 => 'практично відсутній зворотний зв’язок і оцінка ефективності моєї роботи;',
                    3 => 'те, чим я займаюся, виглядає малокорисним і малоцінним;',
                    4 => 'погані умови роботи, занадто галасливо або брудно;',
                ],
            ],
            [
                'number' => 3,
                'optiontext' => "Для мене важливо, щоб моя робота…",
                'optionitems' => [
                    1 => 'була пов’язана зі значним різноманіттям й змінами;',
                    2 => 'давала мені можливість працювати з широким колом людей;',
                    3 => 'забезпечувала мені чіткі вказівки, щоб я знав, що від мене вимагається;',
                    4 => 'дозволяла мені добре вивчати тих людей, з якими я працюю;',
                ],
            ],
            [
                'number' => 4,
                'optiontext' => "Я вважаю, що не був би дуже зацікавлений роботою, яка…",
                'optionitems' => [
                    1 => 'забезпечувала б мені мало контактів з іншими людьми;',
                    2 => 'майже не була б помітною для інших людей;',
                    3 => 'не мала б конкретних завдань, так що я не розумів би, що від мене вимагається;',
                    4 => 'була б пов’язана з певним обсягом рутинних справ;',
                ],
            ],
            [
                'number' => 5,
                'optiontext' => "Робота мені подобається, якщо…",
                'optionitems' => [
                    1 => 'я чітко уявляю собі, що від мене вимагається;',
                    2 => 'у мене зручне робоче місце й мене рідко відволікають;',
                    3 => 'у мене гарні заохочення й заробітна плата;',
                    4 => 'дозволяє мені вдосконалювати свої професійні якості;',
                ],
            ],
            [
                'number' => 6,
                'optiontext' => "Вважаю, що мені б сподобалося, якщо…",
                'optionitems' => [
                    1 => "були б гарні умови роботи і відсутній тиск на мене;",
                    2 => "у мене був би високий посадовий оклад;",
                    3 => "робота в дійсності була б корисна й приносила мені задоволення;",
                    4 => "мої досягнення й робота оцінювалися б гідно;",
                ]
            ],
            [
                'number' => 7,
                'optiontext' => "Я не вважаю, що робота повинна…",
                'optionitems' => [
                    1 => "бути слабко структурованою, так що незрозуміло, що ж слід робити;",
                    2 => "надавати занадто мало можливостей дізнатися про інших людей;",
                    3 => "бути малозначимою й малокорисною для суспільства або нецікавою для виконання;",
                    4 => "залишатися невизнаною або щоб її виконання сприймалося як само собою зрозуміле;",
                ]
            ],
            [
                'number' => 8,
                'optiontext' => "Робота, що приносить задоволення…",
                'optionitems' => [
                    1 => "пов’язана зі значною різноманітністю, змінами і сповнює ентузіазмом;",
                    2 => "дає можливість удосконалювати свої професійні якості та розвиватися як особистість;",
                    3 => "є корисною й значимою для суспільства;",
                    4 => "дозволяє бути креативним (застосовувати творчий підхід) і експериментувати з новими ідеями;",
                ]
            ],
            [
                'number' => 9,
                'optiontext' => "Важливо, щоб робота…",
                'optionitems' => [
                    1 => "визнавалася й цінувалася організацією, у якій я працюю;",
                    2 => "створювала б можливості для особистісного розвитку й удосконалення;",
                    3 => "була пов’язана з більшою різноманітністю і змінами;",
                    4 => "дозволяла б працівникові впливати на інших;",
                ]
            ],
            [
                'number' => 10,
                'optiontext' => "Я не вважаю, що робота буде приносити задоволення, якщо…",
                'optionitems' => [
                    1 => "у процесі її виконання мало можливостей встановлювати контакти з різними людьми;",
                    2 => "посадовий оклад і заохочення недостатні;",
                    3 => "я не можу встановлювати й підтримувати добрі стосунки з колегами по роботі;",
                    4 => "у мене дуже мало самостійності або можливостей для прояву гнучкості;",
                ]
            ],
            [
                'number' => 11,
                'optiontext' => "Хороша робота – це така, яка…",
                'optionitems' => [
                    1 => "забезпечує гарні робочі умови;",
                    2 => "має чіткі інструкції й роз’яснення з приводу змісту роботи;",
                    3 => "передбачає виконання цікавих і корисних завдань;",
                    4 => "дозволяє одержати визнання особистих досягнень і якості роботи;",
                ]
            ],
            [
                'number' => 12,
                'optiontext' => "Ймовірно, що я не буду добре працювати, якщо…",
                'optionitems' => [
                    1 => "замало можливостей ставити перед собою цілі й досягати їх;",
                    2 => "не матиму можливості вдосконалювати свої особисті якості;",
                    3 => "важка робота не отримує визнання й відповідного заохочення;",
                    4 => "на робочому місці пильно, брудно або галасливо;",
                ]
            ],
            [
                'number' => 13,
                'optiontext' => "При визначенні службових обов’язків важливо…",
                'optionitems' => [
                    1 => "надати людям можливість краще дізнатися один про одного;",
                    2 => "надати працівникові можливість ставити цілі й досягати їх;",
                    3 => "забезпечити умови для прояву працівниками творчої ініціативи;",
                    4 => "забезпечити комфортність і чистоту робочого місця;",
                ]
            ],
            [
                'number' => 14,
                'optiontext' => "Ймовірно, я не захочу працювати там, де…",
                'optionitems' => [
                    1 => "у мене буде мало самостійності й можливостей для вдосконалення своєї особистості;",
                    2 => "не заохочуються дослідження і наукова допитливість;",
                    3 => "дуже мало контактів із широким колом людей;",
                    4 => "відсутні гідні надбавки й додаткові пільги;",
                ]
            ],
            [
                'number' => 15,
                'optiontext' => "Я був би задоволений, якщо…",
                'optionitems' => [
                    1 => "була б можливість впливати на прийняття рішення іншими працівниками;",
                    2 => "робота надавала б широку різноманітність і зміни;",
                    3 => "мої досягнення були б оцінені іншими людьми;",
                    4 => "я точно знав би, що від мене потрібно і як я повинен це виконувати;",
                ]
            ],
            [
                'number' => 16,
                'optiontext' => "Робота менше задовольняла б мене, якщо…",
                'optionitems' => [
                    1 => "не дозволяла б ставити й досягати складних цілей;",
                    2 => "я чітко не знав би правил і процедур виконання роботи;",
                    3 => "рівень оплати моєї праці не відповідав би рівню складності виконуваної роботи;",
                    4 => "я практично не міг би впливати на прийняті рішення й на те, що роблять інші;",
                ]
            ],
            [
                'number' => 17,
                'optiontext' => "Я вважаю, що посада повинна надавати…",
                'optionitems' => [
                    1 => "чіткі посадові інструкції й вказівки про те, що від мене вимагається;",
                    2 => "можливість краще пізнавати своїх колег по роботі;",
                    3 => "можливості виконувати складні виробничі завдання, що вимагають напруги всіх сил;",
                    4 => "різноманітність, зміни й заохочення;",
                ]
            ],
            [
                'number' => 18,
                'optiontext' => "Робота приносила б менше задоволення, якщо…",
                'optionitems' => [
                    1 => "не допускала можливості хоча б невеликого творчого внеску;",
                    2 => "здійснювалася б ізольовано, тобто я повинен був би працювати один;",
                    3 => "був би відсутній сприятливий психологічний клімат, у якому я міг би професійно зростати;",
                    4 => "не надавала б можливості впливати на прийняття рішень;",
                ]
            ],
            [
                'number' => 19,
                'optiontext' => "Я хотів би працювати там, де…",
                'optionitems' => [
                    1 => "інші люди визнають і цінують виконувану мною роботу;",
                    2 => "у мене буде можливість впливати на те, що роблять інші;",
                    3 => "є гідна система надбавок і додаткових пільг;",
                    4 => "можна висувати й апробовувати нові ідеї, проявляти креативність;",
                ]
            ],
            [
                'number' => 20,
                'optiontext' => "Навряд чи я хотів би працювати там, де…",
                'optionitems' => [
                    1 => "не існує різноманітності або змін у роботі;",
                    2 => "у мене буде мало можливостей впливати на прийняті рішення;",
                    3 => "заробітна плата не надто висока;",
                    4 => "умови роботи недостатньо добрі;",
                ]
            ],
            [
                'number' => 21,
                'optiontext' => "Я вважаю, що робота, яка приносить задоволення, має передбачати",
                'optionitems' => [
                    1 => "наявність чітких вказівок, щоб працівники знали, що від них вимагається;",
                    2 => "можливість проявляти креативність (творчий підхід);",
                    3 => "можливість зустрічатися з цікавими людьми;",
                    4 => "почуття радості й дійсно цікаві завдання;",
                ]
            ],
            [
                'number' => 22,
                'optiontext' => "Робота не буде приносити задоволення, якщо…",
                'optionitems' => [
                    1 => "передбачені незначні надбавки й додаткові пільги;",
                    2 => "умови роботи несприятливі або в приміщенні дуже галасливо;",
                    3 => "не буде можливості порівнювати свою роботу з роботою інших;",
                    4 => "не заохочуються дослідження, творчий підхід і нові ідеї;",
                ]
            ],
            [
                'number' => 23,
                'optiontext' => "Найголовніше, щоб робота забезпечувала мені…",
                'optionitems' => [
                    1 => "безліч контактів із широким колом цікавих мені людей;",
                    2 => "можливість встановлення й досягнення цілей;",
                    3 => "можливість впливати на прийняття рішень;",
                    4 => "високий рівень заробітної плати;",
                ]
            ],
            [
                'number' => 24,
                'optiontext' => "Я не думаю, що мені подобалася б робота, де…",
                'optionitems' => [
                    1 => "умови праці несприятливі, на робочому місці брудно або галасливо;",
                    2 => "мало шансів впливати на інших людей;",
                    3 => "мало можливостей для досягнення поставлених цілей;",
                    4 => "я не міг би проявляти креативність (творчість) і пропонувати нові ідеї;",
                ]
            ],
            [
                'number' => 25,
                'optiontext' => "У процесі організації роботи важливо…",
                'optionitems' => [
                    1 => "забезпечити чистоту й комфортність робочого місця;",
                    2 => "створити умови для прояву самостійності;",
                    3 => "передбачити можливість різноманітності й змін;",
                    4 => "забезпечити широкі можливості контактів з іншими людьми;",
                ]
            ],
            [
                'number' => 26,
                'optiontext' => "Швидше за все, я не захотів би працювати там, де…",
                'optionitems' => [
                    1 => "умови роботи некомфортні, тобто галасливо, брудно і т ін;",
                    2 => "мало можливостей встановлювати контакти з іншими людьми;",
                    3 => "робота не є цікавою або корисною;",
                    4 => "робота рутинна й завдання рідко змінюються;",
                ]
            ],
            [
                'number' => 27,
                'optiontext' => "Робота приносить задоволення, ймовірно, коли…",
                'optionitems' => [
                    1 => "люди визнають і цінують добре виконану роботу;",
                    2 => "існують широкі можливості для маневру й прояву гнучкості;",
                    3 => "можна ставити перед собою складні й сміливі цілі;",
                    4 => "існує можливість краще пізнати своїх колег;",
                ]
            ],
            [
                'number' => 28,
                'optiontext' => "Мені б не сподобалася робота, яка…",
                'optionitems' => [
                    1 => "не була б корисною й не приносила почуття задоволення;",
                    2 => "не містила б у собі стимулу до змін;",
                    3 => "не дозволяла б мені встановлювати дружні стосунки з іншими;",
                    4 => "була б неконкретною й не мала б складних завдань;",
                ]
            ],
            [
                'number' => 29,
                'optiontext' => "Я б виявив бажання працювати там, де…",
                'optionitems' => [
                    1 => "робота цікава й корисна;",
                    2 => "люди можуть встановлювати тривалі, дружні стосунки;",
                    3 => "мене оточували б цікаві люди;",
                    4 => "я міг би впливати на прийняття рішень;",
                ]
            ],
            [
                'number' => 30,
                'optiontext' => "Я не вважаю, що робота повинна…",
                'optionitems' => [
                    1 => "передбачати, щоб люди більшу частину часу працювали самостійно;",
                    2 => "давати мало шансів на визнання особистих досягнень;",
                    3 => "перешкоджати встановленню відносин із колегами;",
                    4 => "складатися в основному з рутинних обов’язків;",
                ]
            ],
            [
                'number' => 31,
                'optiontext' => "Добре спланована робота обов’язково…",
                'optionitems' => [
                    1 => "передбачає достатній набір пільг і безліч надбавок;",
                    2 => "має конкретні рекомендації з виконання й чіткі посадові обов’язки;",
                    3 => "передбачає можливість ставити цілі й досягати їх;",
                    4 => "стимулює й заохочує висування нових ідей;",
                ]
            ],
            [
                'number' => 32,
                'optiontext' => "Я вважав би, що робота не приносить задоволення, якщо…",
                'optionitems' => [
                    1 => "не міг би виконувати складну перспективну роботу;",
                    2 => "було б мало можливостей для прояву креативності;",
                    3 => "допускалося б замало самостійності;",
                    4 => "за самою сутністю робота не була б корисною або потрібною;",
                ]
            ],
            [
                'number' => 33,
                'optiontext' => "Найбільш значущими характеристиками посади є…",
                'optionitems' => [
                    1 => "можливість для творчого підходу й оригінального нестандартного мислення;",
                    2 => "важливі обов’язки, виконання яких приносить задоволення;",
                    3 => "можливість встановлювати добрі стосунки з колегами; ",
                    4 => "наявність значимих цілей, яких має досягти працівник;",
                ]
            ],
        ];

        foreach ($rows as &$row) {
            $row = (object)$row;
            $row->optiontextformat = 1;
        }

        return $rows;
    }
}
