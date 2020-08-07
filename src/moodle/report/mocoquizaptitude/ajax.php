<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/local/moco_report_filters/lib.php');
/** @var \local_moco_report_filters_plugin $filterrenderer */
$filterrenderer = new local_moco_report_filters_plugin();
$s = required_param('s', PARAM_TEXT);
$q = trim(optional_param('q', '', PARAM_TEXT));
$page_limit = optional_param('page_limit', 10, PARAM_INT);
$page = optional_param('page', 1, PARAM_INT);

switch ($s){
    case 'getsubdivisions':
        $filterrenderer->getDataFromMoco($s, $q, $page_limit, $page, '');
    break;
    case 'getsubpersons':
        $s = is_siteadmin($USER) ? 'getpersons' : $s;
        $filterrenderer->getPersons($s, $q, $page_limit, $page);
    break;
    case 'getcourses':
        $category = optional_param('category', 0, PARAM_INT);
        $mod = optional_param('mod', 'manager', PARAM_TEXT);
        $courses = $filterrenderer->getCoursesArray('moco_cube_quiz_results', $q, $category, $mod, $page, $page_limit);
        echo json_encode($courses);
    break;
    default:
        echo json_encode(false);
}
