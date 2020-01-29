<?php

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'block_videodirectory';  // Recommended since 2.0.2 (MDL-26035). Required since 3.0 (MDL-48494)
$plugin->version = 1579528032;  // YYYYMMDDHH (year, month, day, 24-hr time)
$plugin->requires = 2016052300; // YYYYMMDDHH (This is the release version for Moodle 2.0)
$plugin->dependencies = array(
    'local_video_directory' => ANY_VERSION
);

