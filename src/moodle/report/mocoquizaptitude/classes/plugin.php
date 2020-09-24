<?php

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot . '/local/moco_report_filters/lib.php');
require_once($CFG->dirroot . '/local/moco_common_ui/lib.php');
require_once($CFG->libdir . '/excellib.class.php');

use report_mocoquizaptitude\collection\item\AcquiredProfession;
use report_mocoquizaptitude\collection\StructureCollection;
use report_mocoquizaptitude\helper\Helper;
use report_mocoquizaptitude\collection\UserCollection;
use report_mocoquizaptitude\collection\item\UserItem;
use report_mocoquizaptitude\collection\item\Questionnaire;

defined('MOODLE_INTERNAL') || die();

class report_mocoquizaptitude_plugin
{
    public $cfg;

    /** @var \moodle_database $db */
    public $db;

    public $session;

    public $user;

    public $url;

    public $getreport;

    public $courseid;

    public $quizid;

    public $divisions;

    public $users;

    public $ajax;

    public $csv;

    public $context;

    protected $where = [];

    protected $join = [];

    /** @var \local_moco_report_filters_plugin */
    protected $filterrenderer;

    public function __construct($cfg, $page, $db, $session, $user)
    {
        $this->cfg = $cfg;
        $this->page = $page;
        $this->db = $db;
        $this->session = $session;
        $this->user = $user;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getParams()
    {
        $this->getreport = optional_param('getreport', false, PARAM_TEXT);
        $this->courseid = optional_param('courseid', '', PARAM_TEXT);
        $this->divisions = optional_param('d', '', PARAM_TEXT);
        $this->users = optional_param('u', '', PARAM_TEXT);
        $this->csv = optional_param('csv', 0, PARAM_INT);
        $this->ajax = optional_param('ajax', false, PARAM_TEXT);
    }

    public function setContext()
    {
        $this->context = context_user::instance($this->user->id);
    }

    public function prepareFilters()
    {
        $this->filterrenderer = new local_moco_report_filters_plugin();
    }

    public function loadHeadScripts()
    {
        local_moco_common_ui_plugin::loadScript('select2');
        local_moco_common_ui_plugin::loadScript('datetimepicker');
        local_moco_common_ui_plugin::loadScript('placeholder');
        local_moco_common_ui_plugin::loadScript('bootstrap-editable');
        local_moco_common_ui_plugin::loadScript('recalctable');

        $this->page->requires->js_call_amd('report_mocoquizaptitude/mocoquizaptitude', 'init', [current_language()]);
        $this->page->requires->css(new moodle_url($this->cfg->wwwroot . '/local/moco_report_filters/styles.css'));
        $this->page->requires->css(new moodle_url($this->cfg->wwwroot . '/report/mocoquizaptitude/css/styles.css'));
    }

    public function renderFilters()
    {
        $output = [];
        $output[] = html_writer::start_div('row mb-2');
        $output[] = html_writer::start_div('col form-horizontal filtersBody', ['id' => 'filtersBody']);
        $output[] = html_writer::start_div('no-padding col-md-12 col-lg-12 col-sm-12 col-xs-12');
        $output[] = html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'page', 'value' => '1']);
        $output[] = html_writer::end_div();
        $output[] = $this->filterrenderer->renderFilterCourseContent(true, false);
        $output[] = $this->filterrenderer->renderFilterDivisionContent();
        $output[] = $this->filterrenderer->renderFilterEmploeesContent();
        $output[] = $this->filterrenderer->renderFilterButtonsContent();
        $output[] = html_writer::end_div();
        $output[] = html_writer::end_div();

        echo implode("", $output);
    }

    public function getReport()
    {
        $helper = new Helper();
        $this->where[] = $helper->addCourseWhereString($this->courseid);
        list($this->where[], $this->join) = $helper->addDivisionWhereString($this->divisions, $this->cfg, $this->join);
        list($this->where[], $this->join) = $helper->addUsersWhereString($this->users, $this->join);
        $this->where = array_diff($this->where, ['']);

        $data = $this->prepareResultData();
        if ($this->csv) {
            $this->createExcel($data);
        }

        $helper->showTable($data);
        $helper->showRecommendationTables($data);
    }

    /**
     * @return stdClass[]
     * @throws dml_exception
     */
    protected function getUsers()
    {
        $sql = 'SELECT u.id, CONCAT(u.lastname, " ", u.firstname, " ",u.middlename) AS full_name
                FROM {moco_cube_quiz_results} mcqr
                JOIN {user} u ON u.id = mcqr.user_id
                JOIN {course} c ON c.id = mcqr.course_id
                JOIN {user_enrolments} ue ON (ue.id = mcqr.enrolment_id)
                JOIN {enrol} uee ON (uee.id = ue.enrolid AND uee.courseid = mcqr.course_id)
                ' . implode(" ", $this->join) . '
                WHERE mcqr.id IS NOT NULL AND c.visible > 0';
        if ($this->where) {
            $sql .= " AND " . implode(' AND ', $this->where);
        }

        $sql .= ' GROUP BY u.id';
        $sql .= ' ORDER BY u.lastname ASC, u.firstname ASC, u.middlename ASC';

        return $this->db->get_records_sql($sql);
    }

    /**
     * @param int[] $userIds
     *
     * @return stdClass[]
     * @throws dml_exception
     */
    protected function getUserGroups($userIds)
    {
        if (count($userIds)) {
            $result = \local_moco_library\db\Helper::getInstance()->addWhereInParamOne('eg.user_id', $userIds);
            $sql = 'SELECT eg.user_id, group_concat(g.name) AS group_name
                FROM {moco_employee_groups} eg
                JOIN {moco_groups} g ON eg.group_id = g.id
                WHERE ' . $result['where'] . '
                GROUP BY eg.user_id';

            return $this->db->get_records_sql($sql, $result['params']);
        }

        return [];
    }

    /**
     * @return UserCollection
     * @throws dml_exception
     */
    protected function getUserCollection()
    {
        $userCollection = new UserCollection();
        $userData = $this->getUsers();
        if ($userData) {
            foreach (array_chunk($userData, 100) as $userBlock) {
                $userIds = array_map(function ($user) {
                    return $user->id;
                }, $userBlock);

                $userGroups = $this->getUserGroups($userIds);
                $userGroupKeys = array_keys($userGroups);
                foreach ($userBlock as $user) {
                    $groupName = in_array($user->id, $userGroupKeys) ? $userGroups[$user->id]->group_name : 'No group';
                    $userCollection->push(
                        new UserItem(
                            $user->id,
                            trim($user->full_name),
                            $groupName
                        )
                    );
                }
            }
        }

        return $userCollection;
    }

    /**
     * @return UserCollection
     * @throws dml_exception
     */
    protected function prepareResultData()
    {
        $userCollection = $this->getUserCollection();

        foreach (StructureCollection::createInstance()->getAll() as $structure) {
            foreach (array_chunk($userCollection->getIds(), 100) as $ids) {
                $professionCollections = $structure->getData($this->db, $this->courseid, $ids);
                foreach ($professionCollections as $userId => $collection) {
                    $userCollection->get($userId)->questionnaires->push(new Questionnaire($structure->getTemplate(), $collection));
                }
            }
        }

        return $userCollection;
    }

    protected function createExcel($data)
    {
        $headformat = ['v_align' => 'center', 'h_align' => 'center', 'size' => 13, 'border' => 1, 'text_wrap' => true];
        $nameformat = ['v_align' => 'center', 'h_align' => 'center', 'size' => 13, 'border' => 1, 'text_wrap' => true];
        $bodyformat = ['v_align' => 'center', 'h_align' => 'center', 'border' => 1, 'text_wrap' => true];
        $heading = ['v_align' => 'center', 'h_align' => 'center', 'size' => 15, 'bold' => 1, 'italic' => 1, 'text_wrap' => true];

        $workbook = new MoodleExcelWorkbook('report_mocoquizaptitude.xlsx', 'Excel2007');

        $worksheet = $workbook->add_worksheet(get_string('pluginname', 'report_mocoquizaptitude'));

        $worksheet->set_column(0, 0, 5);
        $worksheet->set_column(1, 2, 60);
        $worksheet->set_column(3, 4, 25);
        $worksheet->write(0, 0, get_string('pluginname', 'report_mocoquizaptitude'), $workbook->add_format($heading));
        $worksheet->set_row(0, 50);
        $worksheet->merge_cells(0, 0, 0, 4);
        $worksheet->write(2, 0, '№', $workbook->add_format($headformat));
        $worksheet->write(2, 1, get_string('head1', 'report_mocoquizaptitude'), $workbook->add_format($headformat));
        $worksheet->write(2, 2, get_string('head2', 'report_mocoquizaptitude'), $workbook->add_format($headformat));
        $worksheet->write(2, 3, get_string('head3', 'report_mocoquizaptitude'), $workbook->add_format($headformat));
        $worksheet->write(2, 4, get_string('head4', 'report_mocoquizaptitude'), $workbook->add_format($headformat));
        $worksheet->set_row(2, 25);

        $i = 3;

        foreach ($data->getAll() as $item) {
            $worksheet->write($i, 0, $item->fullName . ', ' . $item->groupName, $workbook->add_format($nameformat));
            $worksheet->merge_cells($i, 0, $i, 4);
            $worksheet->set_row($i, 25);

            $i++;
            $rowNumber = 1;
            /** @var Questionnaire $questionnaire */
            foreach ($item->questionnaires->getAll() as $questionnaire) {
                $rowspan = $questionnaire->professions->length() - 1;

                $worksheet->write($i, 0, $rowNumber, $workbook->add_format($bodyformat));
                $worksheet->merge_cells($i, 0, $i + $rowspan, 0);

                $worksheet->write($i, 1, $questionnaire->template->getDirection(), $workbook->add_format($bodyformat));
                $worksheet->merge_cells($i, 1, $i + $rowspan, 1);

                $worksheet->write($i, 2, $questionnaire->template->getName(), $workbook->add_format($bodyformat));
                $worksheet->merge_cells($i, 2, $i + $rowspan, 2);

                $worksheet->set_row($i, 20);

                /** @var AcquiredProfession $profession */
                foreach ($questionnaire->professions->getAll() as $profession) {
                    $worksheet->write($i, 3, $profession->scale, $workbook->add_format($bodyformat));
                    $worksheet->write($i, 4, $profession->value, $workbook->add_format($bodyformat));
                    $worksheet->set_row($i, 20);
                    $i++;
                }

                $rowNumber++;
            }
        }

        $workbook->close();
        die();
    }
}
