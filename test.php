<?php

/**
 * Test for when a meeting is running.
 *
 * Authors:
 *      Fred Dixon (ffdixon [at] blindsidenetworks [dt] org)
 *      Jesus Federico  (jesus [at] blindsidenetworks [dt] com)    
 *
 * @package   mod_bigbluebuttonbn
 * @copyright 2010-2012 Blindside Networks
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // bigbluebuttonbn instance ID

if ($id) {
    if (! $cm = get_coursemodule_from_id('bigbluebuttonbn', $id)) {
        error('Course Module ID was incorrect');
    }

    if (! $course = get_record('course', 'id', $cm->course)) {
        error('Course is misconfigured');
    }

    if (! $bigbluebuttonbn = get_record('bigbluebuttonbn', 'id', $cm->instance)) {
        error('Course module is incorrect');
    }

} else if ($a) {
    if (! $bigbluebuttonbn = get_record('bigbluebuttonbn', 'id', $a)) {
        error('Course module is incorrect');
    }
    if (! $course = get_record('course', 'id', $bigbluebuttonbn->course)) {
        error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('bigbluebuttonbn', $bigbluebuttonbn->id, $course->id)) {
        error('Course Module ID was incorrect');
    }

} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
print_object($cm);

if ($bigbluebuttonbn = get_record('bigbluebuttonbn', 'id', '1')) {
    print_object("Yes");
} else {
    print_object("No");
}
print_object($bigbluebuttonbn);

?>
