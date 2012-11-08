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
// add_to_log($course->id, "bigbluebuttonbn", "join meeting", "view.php?course=$course->id&id=$USER->id", "$USER->id" );

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
if( !isset($bbbsession['welcome']) || $bbbsession['welcome'] == '') {
    $bbbsession['welcome'] = get_string('mod_form_field_welcome_default', 'bigbluebuttonbn'); 
}

$bbbsession['voicebridge'] = $bigbluebuttonbn->voicebridge;
$bbbsession['description'] = $bigbluebuttonbn->description;
$bbbsession['flag']['newwindow'] = $bigbluebuttonbn->newwindow;
$bbbsession['flag']['wait'] = $bigbluebuttonbn->wait;
$bbbsession['flag']['record'] = $bigbluebuttonbn->record;
$bbbsession['textflag']['newwindow'] = $bigbluebuttonbn->newwindow? 'true':'false';
$bbbsession['textflag']['wait'] = $bigbluebuttonbn->wait? 'true': 'false';
$bbbsession['textflag']['record'] = $bigbluebuttonbn->record? 'true': 'false';
if( $bigbluebuttonbn->record )
    $bbbsession['welcome'] .= '<br><br>'.get_string('bbbrecordwarning', 'bigbluebuttonbn');

$bbbsession['timeavailable'] = $bigbluebuttonbn->timeavailable;
$bbbsession['timedue'] = $bigbluebuttonbn->timedue;
$bbbsession['timeduration'] = intval($bigbluebuttonbn->timeduration / 60);
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







if( $moderator ) {
	//
	// Join as Moderator
	//
	print_object("Hello");
    print "<br />".get_string('view_login_moderator', 'bigbluebuttonbn' )."<br /><br />";
	print "<center><img src='pix/loading.gif' /></center>";
	
	print_object("Hello");
	$response = bigbluebuttonbn_getCreateMeetingArray( $bbbsession['meetingname'], $bbbsession['meetingid'], "", $bbbsession['modPW'], $bbbsession['viewerPW'], $bbbsession['salt'], $bbbsession['url'], $bbbsession['logoutURL'] );
	print_object($response);
	
	if (!$response) {
		// If the server is unreachable, then prompts the user of the necessary action
		error( 'Unable to join the meeting. Please check the url of the bigbluebuttonbn server AND check to see if the bigbluebuttonbn server is running.' );
	}

	if( $response['returncode'] == "FAILED" ) {
		// The meeting was not created
		if ($response['messageKey'] == "checksumError"){
			 error( get_string( 'index_checksum_error', 'bigbluebuttonbn' ));
		}
		else {
			error( $response['message'] );
		}
	}

	$joinURL = bigbluebuttonbn_getJoinURL($bbbsession['meetingid'], $bbbsession['username'], $bbbsession['modPW'], $bbbsession['salt'], $bbbsession['url'], $bbbsession['userID']);
	redirect( $joinURL );

} else {
	//
	// Login as a viewer, check if we need to wait
	//

	// "Viewer";
	if( $bigbluebuttonbn->wait ) {
		// check if the session is running; if not, user is not allowed to join
		// print "MeeingID: #".$bigbluebuttonbn->meetingid."#<br>";
		$arr = bigbluebuttonbn_getMeetingInfoArray( $bbbsession['meetingid'], $bbbsession['modPW'], $bbbsession['url'], $bbbsession['salt'] );
		$joinURL = bigbluebuttonbn_getJoinURL( $bbbsession['meetingid'], $bbbsession['username'], $bbbsession['viewerPW'], $bbbsession['salt'], $bbbsession['url'], $bbbsession['userID']);

		// print_object( $arr );
		// print "Is Meeting runnign: #".bigbluebuttonbn_isMeetingRunning( $bigbluebuttonbn->meetingid,  $bbbsession['url'], $bbbsession['salt'] )."#<br>";
		// print "BBB";
		
		if( bigbluebuttonbn_isMeetingRunning( $bbbsession['meetingid'], $bbbsession['url'], $bbbsession['salt'] ) == "true" ) {
			//
			// since the meeting is already running, we just join the session
			//
			print "<br />".get_string('view_login_viewer', 'bigbluebuttonbn' )."<br /><br />";
			print "<center><img src='pix/loading.gif' /></center>";
			
			redirect( $joinURL );

		} else {
			print "<br />".get_string('view_wait', 'bigbluebuttonbn' )."<br /><br />";
			print '<center><img src="pix/polling.gif"></center>';
		}
?>
<p></p>
<script type="text/javascript" src="js/libs/jquery/1.7.2/jquery.min.js"></script>
<script type="text/javascript" src="js/libs/heartbeat/0.1.1/heartbeat.js"></script>
<!-- script type="text/javascript" src="md5.js"></script -->
<!-- script type="text/javascript" src="jquery.xml2json.js"></script -->
<script type="text/javascript" >
                        $(document).ready(function(){
                        $.jheartbeat.set({
                        url: "<?php echo $CFG->wwwroot ?>/mod/bigbluebuttonbn/test.php?name=<?echo $bigbluebuttonbn->meetingid; ?>",
                        delay: 5000
                        }, function() {
                                mycallback();
                        });
                        });
                function mycallback() {
                        // Not elegant, but works around a bug in IE8
                        var isMeeting = ($("#HeartBeatDIV").text().search("true")  > 0 );
                        if ( isMeeting ) {
                                window.location = "<?php echo $joinURL ?>";
                        }
                }
</script>
<?php
	} else {
	
	//
	// Join as Viewer, no wait check
	//

	print "<br />".get_string('view_login_viewer', 'bigbluebuttonbn' )."<br /><br />";
	print "<center><img src='pix/loading.gif' /></center>";
	
	$response = bigbluebuttonbn_createMeetingArray( "" , $bigbluebuttonbn->meetingid, "", $bbbsession['modPW'], $bbbsession['viewerPW'], $bbbsession['salt'], $bbbsession['url'], $bbbsession['logoutURL'] );

	if (!$response) {
		// If the server is unreachable, then prompts the user of the necessary action
		error( 'Unable to join the meeting. Please contact your administrator.' );
	}

	if( $response['returncode'] == "FAILED" ) {
		// The meeting was not created
		if ($response['messageKey'] == "checksumError"){
			error( get_string( 'index_checksum_error', 'bigbluebuttonbn' ));
		}
		else {
			error( $response['message'] );
		}
	}

	$joinURL = bigbluebuttonbn_joinURL($bigbluebuttonbn->meetingid, $bbbsession['username'], $bbbsession['viewerPW'], $bbbsession['salt'], $bbbsession['url'], $bbbsession['userID']);
	redirect( $joinURL );

	}
}

// Finish the page
print_footer($course);

?>
