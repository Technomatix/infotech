<?php
defined('MOODLE_INTERNAL') || die();

define('QTYPE_AMTHAUER6_NUMBER_OF_OPTIONS', 20);

/**
 * Checks file/image access for amthauer6 questions.
 *
 * @category files
 *
 * @param stdClass $course        course object
 * @param stdClass $cm            course module object
 * @param stdClass $context       context object
 * @param string   $filearea      file area
 * @param array    $args          extra arguments
 * @param bool     $forcedownload whether or not force download
 * @param array    $options       additional options affecting the file serving
 *
 * @return bool
 */
function qtype_amthauer6_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload,
        array $options = array()) {
    global $CFG;
    require_once($CFG->libdir.'/questionlib.php');
    question_pluginfile($course, $context, 'qtype_amthauer6', $filearea, $args, $forcedownload,
    $options);
}
