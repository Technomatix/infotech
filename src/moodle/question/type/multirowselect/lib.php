<?php
defined('MOODLE_INTERNAL') || die();

define('QTYPE_MULTIROWSELECT_NUMBER_OF_ROWS', 1);

function getNumberOfRowsData()
{
    return [
        'jonescrendall' => 15,
        'bfq' => 44,
    ];
}

function getNumberOfRows($type)
{
    $types = getNumberOfRowsData();

    return isset($types[$type]) ? $types[$type] : 0;
}

function getMaxAnswerValue($type)
{
    $types = [
        'jonescrendall' => 4,
        'bfq' => 5,
    ];

    return isset($types[$type]) ? $types[$type] : 0;
}

/**
 * Checks file/image access for multirowselect questions.
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
function qtype_multirowselect_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = [])
{
    global $CFG;
    require_once($CFG->libdir . '/questionlib.php');
    question_pluginfile($course, $context, 'qtype_multirowselect', $filearea, $args, $forcedownload, $options);
}
