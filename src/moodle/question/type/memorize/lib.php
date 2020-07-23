<?php
defined('MOODLE_INTERNAL') || die();

define('QTYPE_MEMORIZE_NUMBER_OF_ROWS', 10);
define('QTYPE_MEMORIZE_EXPECTED_ROWS', 10);

/**
 * Checks file/image access for memorize questions.
 *
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 *
 * @return bool
 * @category files
 *
 */
function qtype_memorize_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = [])
{
    global $CFG;
    require_once($CFG->libdir . '/questionlib.php');
    question_pluginfile($course, $context, 'qtype_memorize', $filearea, $args, $forcedownload, $options);
}
