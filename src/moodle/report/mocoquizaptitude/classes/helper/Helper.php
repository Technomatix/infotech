<?php

namespace report_mocoquizaptitude\helper;

require_once($CFG->libdir . '/filelib.php');

use html_writer;
use report_mocoquizaptitude\collection\item\AcquiredProfession;
use report_mocoquizaptitude\collection\item\Questionnaire;
use report_mocoquizaptitude\collection\UserCollection;

class Helper
{
    protected function prepareComaSeparatedIds($string)
    {
        $userIds = array_filter(
            array_map(
                function ($item) {
                    return ctype_digit(trim($item)) ? trim($item) : null;
                },
                explode(',', trim($string))
            )
        );

        return implode(',', $userIds);
    }

    public function addCourseWhereString($courseIds)
    {
        $courseIds = $this->prepareComaSeparatedIds($courseIds);

        return strlen($courseIds) ? 'mcqr.course_id IN (' . $courseIds . ')' : '';
    }

    public function addDivisionWhereString($d, $cfg, $join)
    {
        $where = '';
        if ($d !== '') {
            $serviceUrl = $cfg->mocoroot . '/index.php/api/report/getdivwithchildrens?moco_rest_key=' . $cfg->moco_rest_key . '&q=' . $d;
            $c = new \curl();
            $curlResponse = $c->get($serviceUrl);
            if ($curlResponse === false) {
                $divs = $d;
            } else {
                $curlResponse = json_decode($curlResponse);
                if ($curlResponse->code === 200) {
                    $divs = str_replace("\"", "", json_encode($curlResponse->data));
                } else {
                    $divs = $d;
                }
            }
            $divs = $this->prepareComaSeparatedIds($divs);
            if (strlen($divs)) {
                $join['e'] = 'LEFT JOIN {moco_employee} e ON e.user_id = u.id';
                $join['d'] = 'LEFT JOIN {moco_division} d ON d.id = e.division_id';
                $where .= 'd.moco_id IN (' . $divs . ')';
            }
        }

        return [$where, $join];
    }

    public function addUsersWhereString($userIds, $join)
    {
        $where = '';
        $userIds = $this->prepareComaSeparatedIds($userIds);
        if ($userIds !== '') {
            $join['e'] = 'LEFT JOIN {moco_employee} e ON e.user_id = u.id';
            $where .= 'e.user_id IN (' . $userIds . ')';
        }

        return [$where, $join];
    }

    public function addEnrolWhereString($es, $ef)
    {
        if ($es !== '' && $ef !== '') {
            $es = strtotime($es . ' 00:00:00');
            $ef = strtotime($ef . ' 23:59:59');
            $where[] = '((ue.timeend = 0 OR ue.timeend >= ' . $es . ') AND (ue.timestart = 0 OR ue.timestart  <= ' . $ef . '))';
        } else if ($es !== '') {
            $es = strtotime($es . ' 00:00:00');
            $where[] = '(ue.timeend = 0 OR ue.timeend >= ' . $es . ')';
        } else if ($ef !== '') {
            $ef = strtotime($ef . ' 23:59:59');
            $where[] = '(ue.timestart = 0 OR ue.timestart  <= ' . $ef . ')';
        } else {
            $where = [];
        }

        return implode("", $where);
    }

    protected function getHtmlThead()
    {
        $headerCellClass = 'header text-center';
        $thead = [];

        $thead[] = html_writer::start_tag('thead');
        $thead[] = html_writer::start_tag('tr');
        $thead[] = html_writer::start_tag('td', ['class' => $headerCellClass, 'scope' => 'col', 'width' => '5%']);
        $thead[] = '№';
        $thead[] = html_writer::end_tag('td');
        $thead[] = html_writer::start_tag('td', ['class' => $headerCellClass, 'scope' => 'col', 'width' => '25%']);
        $thead[] = get_string('head1', 'report_mocoquizaptitude');
        $thead[] = html_writer::end_tag('td');
        $thead[] = html_writer::start_tag('td', ['class' => $headerCellClass, 'scope' => 'col', 'width' => '40%']);
        $thead[] = get_string('head2', 'report_mocoquizaptitude');
        $thead[] = html_writer::end_tag('td');
        $thead[] = html_writer::start_tag('td', ['class' => $headerCellClass, 'scope' => 'col', 'width' => '15%']);
        $thead[] = get_string('head3', 'report_mocoquizaptitude');
        $thead[] = html_writer::end_tag('td');
        $thead[] = html_writer::start_tag('td', ['class' => $headerCellClass, 'scope' => 'col', 'width' => '15%']);
        $thead[] = get_string('head4', 'report_mocoquizaptitude');
        $thead[] = html_writer::end_tag('td');
        $thead[] = html_writer::end_tag('tr');
        $thead[] = html_writer::end_tag('thead');

        return implode("", $thead);
    }

    /**
     * @param UserCollection $data
     *
     * @return string
     */
    protected function getHtmlTbody($data)
    {
        $tbody = [];
        $tbody[] = html_writer::start_tag('tbody');
        foreach ($data->getAll() as $item) {
            $tbody[] = html_writer::start_tag('tr');
            $tbody[] = html_writer::start_tag('td', ['class' => 'text-center', 'colspan' => 5, 'width' => '100%']);
            $tbody[] = $item->fullName . ', ' . $item->groupName;
            $tbody[] = html_writer::end_tag('td');
            $tbody[] = html_writer::end_tag('tr');

            $rowNumber = 1;

            /** @var Questionnaire $questionnaire */
            foreach ($item->questionnaires->getAll() as $questionnaire) {
                $rowspan = $questionnaire->professions->length();

                $tbody[] = html_writer::start_tag('tr');
                $tbody[] = html_writer::start_tag('td', ['class' => 'text-center', 'rowspan' => $rowspan]);
                $tbody[] = $rowNumber;
                $tbody[] = html_writer::end_tag('td');
                $tbody[] = html_writer::start_tag('td', ['class' => 'text-center', 'rowspan' => $rowspan]);
                $tbody[] = $questionnaire->template->getDirection();
                $tbody[] = html_writer::end_tag('td');
                $tbody[] = html_writer::start_tag('td', ['class' => 'text-center', 'rowspan' => $rowspan]);
                $tbody[] = $questionnaire->template->getName();
                $tbody[] = html_writer::end_tag('td');

                $professionCount = $questionnaire->professions->length();
                /** @var AcquiredProfession $profession */
                foreach ($questionnaire->professions->getAll() as $profession) {
                    $tbody[] = html_writer::start_tag('td', ['class' => 'text-center']);
                    $tbody[] = $profession->scale;
                    $tbody[] = html_writer::end_tag('td');
                    $tbody[] = html_writer::start_tag('td', ['class' => 'text-center']);
                    $tbody[] = $profession->value;
                    $tbody[] = html_writer::end_tag('td');
                    $professionCount--;
                    if ($professionCount > 0) {
                        $tbody[] = html_writer::end_tag('tr');
                        $tbody[] = html_writer::start_tag('tr');
                    }
                }

                $tbody[] = html_writer::end_tag('tr');
                $rowNumber++;
            }
        }
        $tbody[] = html_writer::end_tag('tbody');

        return implode("", $tbody);
    }

    public function showTable($data)
    {
        $table = [];
        $table[] = html_writer::start_div('col panel panel-default');
        $table[] = html_writer::start_tag('table', ['class' => 'items table table-normal table-hover reportTable', 'id' => 'mocoquizaptitude']);
        $table[] = $this->getHtmlThead();
        $table[] = $this->getHtmlTbody($data);
        $table[] = html_writer::end_tag('table');
        $table[] = html_writer::end_div();

        echo implode("", $table);
    }
}