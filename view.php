<?php
/**
 * Join a BigBlueButton room
 *
 * Authors:
 *      Fred Dixon (ffdixon [at] blindsidenetworks [dt] org)
 *      Jesus Federico (jesus [at] blindsidenetworks [dt] org)
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
$moderator = has_capability('mod/bigbluebuttonbn:moderate', $context);
$administrator = has_capability('moodle/category:manage', $context);

add_to_log($course->id, 'bigbluebuttonbn', 'view', "view.php?id=$cm->id", $bigbluebuttonbn->name, $cm->id);
//add_to_log($course->id, "bigbluebuttonbn", "join", "view.php?id=$cm->id", "$bigbluebuttonbn->id");
//add_to_log($course->id, "bigbluebuttonbn", "join meeting", "view.php?course=$course->id&id=$USER->id", "$USER->id" );

//Validates if the BigBlueButton server is running
//BigBlueButton server data
$bbbsession['salt'] = trim($CFG->bigbluebuttonbnSecuritySalt);
$bbbsession['url'] = trim(trim($CFG->bigbluebuttonbnServerURL),'/').'/';

$serverVersion = bigbluebuttonbn_getServerVersion($bbbsession['url']);
if ( !isset($serverVersion) ) { //Server is not working
    if ( $administrator )
        print_error( 'view_error_unable_join', 'bigbluebuttonbn', $CFG->wwwroot.'/admin/settings.php?section=modsettingbigbluebuttonbn' );
    else if ( $moderator )
        print_error( 'view_error_unable_join_teacher', 'bigbluebuttonbn', $CFG->wwwroot.'/course/view.php?id='.$bigbluebuttonbn->course );
    else
        print_error( 'view_error_unable_join_student', 'bigbluebuttonbn', $CFG->wwwroot.'/course/view.php?id='.$bigbluebuttonbn->course );
} else {
    $xml = bigbluebuttonbn_wrap_simplexml_load_file( bigbluebuttonbn_getMeetingsURL( $bbbsession['url'], $bbbsession['salt'] ) );
    if ( !isset($xml) || $xml->returncode == 'FAILED' ){ // The salt is wrong
        if ( $administrator )
            print_error( 'view_error_unable_join', 'bigbluebuttonbn', $CFG->wwwroot.'/admin/settings.php?section=modsettingbigbluebuttonbn' );
        else if ( $moderator )
            print_error( 'view_error_unable_join_teacher', 'bigbluebuttonbn', $CFG->wwwroot.'/course/view.php?id='.$bigbluebuttonbn->course );
        else
            print_error( 'view_error_unable_join_student', 'bigbluebuttonbn', $CFG->wwwroot.'/course/view.php?id='.$bigbluebuttonbn->course );
    }
}

/// Print the page header
$strbigbluebuttonbns = get_string('modulenameplural', 'bigbluebuttonbn');
$strbigbluebuttonbn  = get_string('modulename', 'bigbluebuttonbn');

$navlinks = array();
$navlinks[] = array('name' => $strbigbluebuttonbns, 'link' => "index.php?id=$course->id", 'type' => 'activity');
$navlinks[] = array('name' => format_string($bigbluebuttonbn->name), 'link' => '', 'type' => 'activityinstance');

$navigation = build_navigation($navlinks);

print_header_simple(format_string($bigbluebuttonbn->name), '', $navigation, '', '', true,
update_module_button($cm->id, $course->id, $strbigbluebuttonbn), navmenu($course, $cm));

//
// BigBlueButton Setup
//
//Server data
$bbbsession['modPW'] = $bigbluebuttonbn->moderatorpass;
$bbbsession['viewerPW'] = $bigbluebuttonbn->viewerpass;
//User data
$bbbsession['username'] = $USER->firstname.' '.$USER->lastname;
$bbbsession['userID'] = $USER->id;
$bbbsession['flag']['moderator'] = $moderator;
$bbbsession['textflag']['moderator'] = $moderator? 'true': 'false';
$bbbsession['flag']['administrator'] = $administrator;
$bbbsession['textflag']['administrator'] = $administrator? 'true': 'false';

//Database info related to the activity
$bbbsession['meetingname'] = $bigbluebuttonbn->name;
$bbbsession['welcome'] = $bigbluebuttonbn->welcome;
if( !isset($bbbsession['welcome']) || $bbbsession['welcome'] == null || $bbbsession['welcome'] == '') {
    $bbbsession['welcome'] = get_string('mod_form_field_welcome_default', 'bigbluebuttonbn'); 
}

$bbbsession['voicebridge'] = $bigbluebuttonbn->voicebridge;
$bbbsession['description'] = $bigbluebuttonbn->description;
$bbbsession['flag']['openoutside'] = $bigbluebuttonbn->openoutside;
$bbbsession['flag']['wait'] = $bigbluebuttonbn->wait;
$bbbsession['flag']['record'] = $bigbluebuttonbn->record;
$bbbsession['textflag']['openoutside'] = $bigbluebuttonbn->openoutside? 'true':'false';
$bbbsession['textflag']['wait'] = $bigbluebuttonbn->wait? 'true': 'false';
$bbbsession['textflag']['record'] = $bigbluebuttonbn->record? 'true': 'false';
if( $bigbluebuttonbn->record )
    $bbbsession['welcome'] .= '<br><br>'.get_string('bbbrecordwarning', 'bigbluebuttonbn');

$bbbsession['timeavailable'] = $bigbluebuttonbn->timeavailable;
$bbbsession['timedue'] = $bigbluebuttonbn->timedue;
$bbbsession['timeduration'] = $bigbluebuttonbn->timeduration;
if( $bbbsession['timeduration'] > 0 )
    $bbbsession['welcome'] .= '<br><br>'.str_replace("%duration%", ''.$bbbsession['timeduration'], get_string('bbbdurationwarning', 'bigbluebuttonbn'));

//Additional info related to the course
$bbbsession['coursename'] = $course->fullname;
$bbbsession['courseid'] = $course->id;
$bbbsession['cm'] = $cm;

//Operation URLs
$bbbsession['courseURL'] = $CFG->wwwroot.'/course/view.php?id='.$bigbluebuttonbn->course;
$bbbsession['logoutURL'] = $CFG->wwwroot.'/mod/bigbluebuttonbn/view_end.php?id='.$id;

//Metadata
$bbbsession['origin'] = "Moodle";
$bbbsession['originVersion'] = $CFG->release;
$parsedUrl = parse_url($CFG->wwwroot);
$bbbsession['originServerName'] = $parsedUrl['host'];
$bbbsession['originServerUrl'] = $CFG->wwwroot;
$bbbsession['originServerCommonName'] = '';
$bbbsession['originTag'] = 'moodle-mod_bigbluebuttonbn 1.0.8';
$bbbsession['context'] = $course->fullname;
$bbbsession['contextActivity'] = $bigbluebuttonbn->name;
$bbbsession['contextActivityDescription'] = $bigbluebuttonbn->description;

$bbbsession['bigbluebuttonbnid'] = $bigbluebuttonbn->id;
/// find out current groups mode
groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/bigbluebuttonbn/view.php?id=' . $cm->id);
if (groups_get_activity_groupmode($cm) == 0) {  //No groups mode
    $bbbsession['meetingid'] = $bigbluebuttonbn->meetingid.'-'.$bbbsession['courseid'].'-'.$bbbsession['bigbluebuttonbnid'];
} else {                                        // Separate groups mode
    //If doesnt have group
    $bbbsession['group'] = groups_get_activity_group($cm);
    if( $bbbsession['group'] == '0' ){
        if ( $bbbsession['flag']['administrator'] ) {
            $groups_in_activity = groups_get_activity_allowed_groups($cm);
            if ( count($groups_in_activity) == 0 ){ //There are no groups at all
                print_error( 'view_error_no_group', 'bigbluebuttonbn', $CFG->wwwroot.'/course/view.php?id='.$course->id );
                exit;
            } else { // There is only 1 group
                $bbbsession['group'] = current($groups_in_activity)->id;
            }
        } else if ( $bbbsession['flag']['moderator'] ) {
            $groups_in_activity = groups_get_activity_allowed_groups($cm);
            if ( count($groups_in_activity) == 0 ){ //There are no groups at all
                print_error( 'view_error_no_group_teacher', 'bigbluebuttonbn', $CFG->wwwroot.'/course/view.php?id='.$course->id );
                exit;
            } else { // There is only 1 group
                $bbbsession['group'] = current($groups_in_activity)->id;
            }
        } else {
            print_error( 'view_error_no_group_student', 'bigbluebuttonbn', $CFG->wwwroot.'/course/view.php?id='.$course->id );
            exit;
        }

    }

    $bbbsession['meetingid'] = $bigbluebuttonbn->meetingid.'-'.$bbbsession['courseid'].'-'.$bbbsession['bigbluebuttonbnid'].'['.$bbbsession['group'].']';
    //if ($moderator) // Take off the option visible groups
    //    $PAGE->requires->js_init_call('M.mod_bigbluebuttonbn.setusergroups');
}

if( $moderator)
    $bbbsession['joinURL'] = bigbluebuttonbn_getJoinURL($bbbsession['meetingid'], $bbbsession['username'], $bbbsession['modPW'], $bbbsession['salt'], $bbbsession['url'], $bbbsession['userID']);
else
    $bbbsession['joinURL'] = bigbluebuttonbn_getJoinURL($bbbsession['meetingid'], $bbbsession['username'], $bbbsession['viewerPW'], $bbbsession['salt'], $bbbsession['url'], $bbbsession['userID']);

//
// BigBlueButton Setup Ends
//

echo '<script type="text/javascript" src="'.$CFG->wwwroot.'/mod/bigbluebuttonbn/js/libs/jquery/1.7.2/jquery.min.js"></script>'."\n";
echo '<script type="text/javascript" src="'.$CFG->wwwroot.'/mod/bigbluebuttonbn/js/libs/heartbeat/0.1.1/heartbeat.js"></script>'."\n";
echo '<script type="text/javascript" src="'.$CFG->wwwroot.'/mod/bigbluebuttonbn/js/bigbluebuttonbn.js"></script>'."\n";

echo '<script type="text/javascript" >var logouturl = "'.$bbbsession['logoutURL'].'";</script>'."\n";
echo '<script type="text/javascript" >var openoutside = "'.$bbbsession['textflag']['openoutside'].'";</script>'."\n";
echo '<script type="text/javascript" >var waitformoderator = "'.$bbbsession['textflag']['wait'].'";</script>'."\n";
echo '<script type="text/javascript" >var ismoderator = "'.$bbbsession['textflag']['moderator'].'";</script>'."\n";
echo '<script type="text/javascript" >var meetingid = "'.$bbbsession['meetingid'].'";</script>'."\n";
echo '<script type="text/javascript" >var joinurl = "'.$bbbsession['joinURL'].'";</script>'."\n";
echo '<script type="text/javascript" >var wwwroot = "'.$CFG->wwwroot.'";</script>'."\n";
echo '<script type="text/javascript" >var cmid = "'.$cm->id.'";</script>'."\n";


if (!$bigbluebuttonbn->timeavailable ) {
    if (!$bigbluebuttonbn->timedue || time() <= $bigbluebuttonbn->timedue){
        //GO JOINING
        bigbluebuttonbn_view_joining( $bbbsession );

    } else {
        //CALLING AFTER
        print_heading(get_string('bbbfinished', 'bigbluebuttonbn'));
        print_box_start();

        bigbluebuttonbn_view_after( $bbbsession );
        
        print_box_end();
    }

} else if ( time() < $bigbluebuttonbn->timeavailable ){
    //CALLING BEFORE
    print_heading(get_string('bbbnotavailableyet', 'bigbluebuttonbn'));
    print_box_start();

    bigbluebuttonbn_view_before( $bbbsession );

    print_box_end();
    
} else if (!$bigbluebuttonbn->timedue || time() <= $bigbluebuttonbn->timedue ) {
    //GO JOINING
    bigbluebuttonbn_view_joining( $bbbsession );

} else {
    //CALLING AFTER
    print_heading(get_string('bbbfinished', 'bigbluebuttonbn'));
    print_box_start();
    
    bigbluebuttonbn_view_after( $bbbsession );

    print_box_end();
    
}


// Finish the page
print_footer($course);


function bigbluebuttonbn_view_joining( $bbbsession ){
    echo '<script type="text/javascript" >var bigbluebuttonbn_view = "join";</script>'."\n";

    if( $bbbsession['flag']['moderator'] || !$bbbsession['flag']['wait'] ) {  // If is a moderator or if is a viewer and no waiting is required
        //
        // Join directly
        //
        $metadata = array("meta_origin" => $bbbsession['origin'],
                "meta_originVersion" => $bbbsession['originVersion'],
                "meta_originServerName" => $bbbsession['originServerName'],
                "meta_originServerCommonName" => $bbbsession['originServerCommonName'],
                "meta_originTag" => $bbbsession['originTag'],
                "meta_context" => $bbbsession['context'],
                "meta_contextActivity" => $bbbsession['contextActivity'],
                "meta_contextActivityDescription" => $bbbsession['contextActivityDescription'],
                "meta_meetingModerators" => $bbbsession['contextTeacherEmail'],
                "meta_meetingAttendees" => $bbbsession['contextStudentEmail'],
                "meta_email" => $bbbsession['contextTeacherEmail'],
                "meta_recording" => $bbbsession['textflag']['record']);
        $response = bigbluebuttonbn_getCreateMeetingArray( $bbbsession['meetingname'], $bbbsession['meetingid'], $bbbsession['welcome'], $bbbsession['modPW'], $bbbsession['viewerPW'], $bbbsession['salt'], $bbbsession['url'], $bbbsession['logoutURL'], $bbbsession['textflag']['record'], $bbbsession['timeduration'], $bbbsession['voicebridge'], $metadata );
        
        if (!$response) {
            // If the server is unreachable, then prompts the user of the necessary action
            if ( $bbbsession['flag']['administrator'] )
                print_error( 'view_error_unable_join', 'bigbluebuttonbn', $CFG->wwwroot.'/admin/settings.php?section=modsettingbigbluebuttonbn' );
            else if ( $bbbsession['flag']['moderator'] )
                print_error( 'view_error_unable_join_teacher', 'bigbluebuttonbn', $CFG->wwwroot.'/admin/settings.php?section=modsettingbigbluebuttonbn' );
            else
                print_error( 'view_error_unable_join_student', 'bigbluebuttonbn', $CFG->wwwroot.'/admin/settings.php?section=modsettingbigbluebuttonbn' );

        } else if( $response['returncode'] == "FAILED" ) {
            // The meeting was not created
            if ($response['messageKey'] == "checksumError"){
                print_error( get_string( 'index_error_checksum', 'bigbluebuttonbn' ));
            } else {
                print_error( $response['message'] );
            }
        } else if ($response['hasBeenForciblyEnded'] == "true"){
            print_error( get_string( 'index_error_forciblyended', 'bigbluebuttonbn' ));

        } else { ///////////////Everything is ok /////////////////////
            
            bigbluebuttonbn_log($bbbsession, 'Create');
            
            if ( groups_get_activity_groupmode($bbbsession['cm']) > 0 && count(groups_get_activity_allowed_groups($bbbsession['cm'])) > 1 ){
                print '<script type="text/javascript" >var joining = "false";</script>';
                print get_string('view_groups_selection', 'bigbluebuttonbn' )."&nbsp;&nbsp;<input type='button' onClick='bigbluebuttonbn_joinURL()' value='".get_string('view_groups_selection_join', 'bigbluebuttonbn' )."'>";

            } else {
                print '<script type="text/javascript" >var joining = "true";</script>';

                if( $bbbsession['flag']['moderator'] )
                    print "<br />".get_string('view_login_moderator', 'bigbluebuttonbn' )."<br /><br />";
                else
                    print "<br />".get_string('view_login_viewer', 'bigbluebuttonbn' )."<br /><br />";

                print "<center><img src='pix/loading.gif' /></center>";

            }
        }

    } else {    // "Viewer" && Waiting for moderator is required;

        //print_object( bigbluebuttonbn_wrap_simplexml_load_file(bigbluebuttonbn_getIsMeetingRunningURL( $bbbsession['meetingid'], $bbbsession['url'], $bbbsession['salt'] )) );
        echo '<script type="text/javascript" >var joining = "true";</script>'."\n";

        print "<div align='center'>";
        $xml = bigbluebuttonbn_wrap_simplexml_load_file(bigbluebuttonbn_getIsMeetingRunningURL( $bbbsession['meetingid'], $bbbsession['url'], $bbbsession['salt'] )); 
        if( $xml->running == "true" ) {
        //if( bigbluebuttonbn_wrap_simplexml_load_file(bigbluebuttonbn_isMeetingRunning( $bbbsession['meetingid'], $bbbsession['url'], $bbbsession['salt'] )) == "true" ) {
            //
            // since the meeting is already running, we just join the session
            //
            print "<br />".get_string('view_login_viewer', 'bigbluebuttonbn' )."<br /><br />";
            print "<center><img src='pix/loading.gif' /></center>";

        } else {
            print "<br />".get_string('view_wait', 'bigbluebuttonbn' )."<br /><br />";
            print '<center><img src="pix/polling.gif"></center>';
        }

        print "</div>";

    }

}

function bigbluebuttonbn_view_before( $bbbsession ){

    echo '<script type="text/javascript" >'."\n";
    echo '    var joining = "false";'."\n";
    echo '    var bigbluebuttonbn_view = "before";'."\n";
    echo '</script>'."\n";

    echo '<table>';
    if ($bbbsession['timeavailable']) {
        echo '<tr><td class="c0">'.get_string('mod_form_field_availabledate','bigbluebuttonbn').':</td>';
        echo '    <td class="c1">'.userdate($bbbsession['timeavailable']).'</td></tr>';
    }
    if ($bbbsession['timedue']) {
        echo '<tr><td class="c0">'.get_string('mod_form_field_duedate','bigbluebuttonbn').':</td>';
        echo '    <td class="c1">'.userdate($bbbsession['timedue']).'</td></tr>';
    }
    echo '</table>';

}

function bigbluebuttonbn_view_after( $bbbsession ){

    echo '<script type="text/javascript" >'."\n";
    echo '    var joining = "false";'."\n";
    echo '    var bigbluebuttonbn_view = "after";'."\n";
    echo '    var view_recording_list_recording = "'.get_string('view_recording_list_recording', 'bigbluebuttonbn').'";'."\n";
    echo '    var view_recording_list_course = "'.get_string('view_recording_list_course', 'bigbluebuttonbn').'";'."\n";
    echo '    var view_recording_list_activity = "'.get_string('view_recording_list_activity', 'bigbluebuttonbn').'";'."\n";
    echo '    var view_recording_list_description = "'.get_string('view_recording_list_description', 'bigbluebuttonbn').'";'."\n";
    echo '    var view_recording_list_date = "'.get_string('view_recording_list_date', 'bigbluebuttonbn').'";'."\n";
    echo '    var view_recording_list_actionbar = "'.get_string('view_recording_list_actionbar', 'bigbluebuttonbn').'";'."\n";
    echo '</script>'."\n";

    $recordingsArray = bigbluebuttonbn_getRecordingsArray($bbbsession['meetingid'], $bbbsession['url'], $bbbsession['salt']);

    if ( !isset($recordingsArray) || array_key_exists('messageKey', $recordingsArray)) {   // There are no recordings for this meeting
        if ( $bbbsession['flag']['record'] )
            print_string('bbbnorecordings', 'bigbluebuttonbn');

    } else {                                                                                // Actually, there are recordings for this meeting
        echo '    <center>'."\n";

        echo '      <table cellpadding="0" cellspacing="0" border="0" class="display" id="example">'."\n";
        echo '        <thead>'."\n";
        echo '        </thead>'."\n";
        echo '        <tbody>'."\n";
        echo '        </tbody>'."\n";
        echo '        <tfoot>'."\n";
        echo '        </tfoot>'."\n";
        echo '      </table>'."\n";

        echo '    </center>'."\n";

    }

}

?>
