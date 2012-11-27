<?php
/**
 * View and administrate BigBlueButton playback recordings
 *
 * Authors:
 *    Jesus Federico  (jesus [at] blindsidenetworks [dt] com)    
 *
 * @package   mod_bigbluebuttonbn
 * @copyright 2012 Blindside Networks Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

require_login($course, true);

$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
$moderator = has_capability('mod/bigbluebuttonbn:moderate', $coursecontext);

$url = trim(trim($CFG->bigbluebuttonbnServerURL),'/').'/';
$salt = trim($CFG->bigbluebuttonbnSecuritySalt);

$meetingID = optional_param('meetingID', 0, PARAM_TEXT);
if( $meetingID ){
    echo bigbluebuttonbn_getMeetingXML( $meetingID, $url, $salt );
} else {
    echo 'false';
}

