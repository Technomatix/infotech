<?php

require(dirname(__FILE__) . '/../../config.php');

require_login();

$reportPlugin = new report_mocoquizaptitude_plugin($CFG, $PAGE, $DB, $SESSION, $USER);
$reportPlugin->setContext();

require_capability('report/mocoquizaptitude:view', $reportPlugin->context);

$reportPlugin->setUrl(new moodle_url("/report/mocoquizaptitude/index.php"));
$reportPlugin->getParams();

if (!$reportPlugin->getreport) {
    $reportPlugin->prepareFilters();
    $PAGE->set_url($reportPlugin->url, array());
    $PAGE->set_pagelayout('report');
    $PAGE->set_context($reportPlugin->context);
    $PAGE->set_title(get_string('pluginname', 'report_mocoquizaptitude'));
    $PAGE->navbar->add(get_string('reports'));
    $PAGE->navbar->add(get_string('pluginname', 'report_mocoquizaptitude'));
    $reportPlugin->loadHeadScripts();

    echo $OUTPUT->header();

    echo $OUTPUT->heading(get_string('configlog', 'report_mocoquizaptitude'));

    $reportPlugin->renderFilters();

    echo html_writer::start_div('row tableSection');
    echo html_writer::end_div();

    echo $OUTPUT->footer();
    die();
}

raise_memory_limit(MEMORY_EXTRA);

$reportPlugin->getReport();
