<?php

defined('MOODLE_INTERNAL') || die;

$plugin->version = 2020072400;         // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires = 2014110400;         // Requires this Moodle version
$plugin->component = 'report_mocoquizaptitude'; // Full name of the plugin (used for diagnostics)
$plugin->dependencies = [
    'local_moco_user_sync' => 2019072202,
    'local_moco_data_cubes' => 2019082100,
    'local_moco_report_filters' => 2019090300,
];
