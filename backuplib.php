<?php 
/**
 * Backup for bigbluebuttonbn
 *
 * Authors:
 *      Fred Dixon (ffdixon [at] blindsidenetworks [dt] org)
 *      Jesus Federico (jesus [at] blindsidenetworks [dt] org)
 *
 * @package   mod_bigbluebuttonbn
 * @copyright 2010-2012 Blindside Networks Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */

//This php script contains all the stuff to backup/restore
//bigbluebuttonbn mods

//This is the "graphical" structure of the BigBlueButtonBN mod:
//
//                       bigbluebuttonbn
//                     (CL,pk->id)
//
// Meaning: pk->primary key field of the table
//          fk->foreign key to link with parent
//          nt->nested field (recursive data)
//          CL->course level info
//          UL->user level info
//          files->table may have files)
//
//-----------------------------------------------------------

//This function executes all the backup procedure about this mod
function bigbluebuttonbn_backup_mods($bf,$preferences) {
    global $CFG;

    $status = true;

    ////Iterate over bigbluebuttonbn table
    if ($bigbluebuttonbns = get_records ("bigbluebuttonbn","course", $preferences->backup_course,"id")) {
        foreach ($bigbluebuttonbns as $bigbluebuttonbn) {
            if (backup_mod_selected($preferences,'bigbluebuttonbn',$bigbluebuttonbn->id)) {
                $status = bigbluebuttonbn_backup_one_mod($bf,$preferences,$bigbluebuttonbn);
            }
        }
    }
    return $status;
}

function bigbluebuttonbn_backup_one_mod($bf,$preferences,$bigbluebuttonbn) {

    global $CFG;

    if (is_numeric($bigbluebuttonbn)) {
        $bigbluebuttonbn = get_record('bigbluebuttonbn','id',$bigbluebuttonbn);
    }

    $status = true;

    //Start mod
    fwrite ($bf,start_tag("MOD",3,true));
    //Print assignment data
    fwrite ($bf,full_tag("ID",4,false,$bigbluebuttonbn->id));
    fwrite ($bf,full_tag("MODTYPE",4,false,"bigbluebuttonbn"));
    fwrite ($bf,full_tag("NAME",4,false,$bigbluebuttonbn->name));
    fwrite ($bf,full_tag("WELCOME",4,false,$bigbluebuttonbn->welcome));
    fwrite ($bf,full_tag("OPENOUTSIDE",4,false,$bigbluebuttonbn->openoutside));
    fwrite ($bf,full_tag("WAIT",4,false,$bigbluebuttonbn->wait));
    fwrite ($bf,full_tag("TIMEDUE",4,false,$bigbluebuttonbn->timedue));
    fwrite ($bf,full_tag("TIMEAVAILABLE",4,false,$bigbluebuttonbn->timeavailable));
    fwrite ($bf,full_tag("RECORD",4,false,$bigbluebuttonbn->record));
    fwrite ($bf,full_tag("DESCRIPTION",4,false,$bigbluebuttonbn->description));
    fwrite ($bf,full_tag("TIMEDURATION",4,false,$bigbluebuttonbn->timeduration));
    fwrite ($bf,full_tag("MEETINGID",4,false,$bigbluebuttonbn->meetingid));
    fwrite ($bf,full_tag("MODERATORPASS",4,false,$bigbluebuttonbn->moderatorpass));
    fwrite ($bf,full_tag("VIEWERPASS",4,false,$bigbluebuttonbn->viewerpass));
    fwrite ($bf,full_tag("VOICEBRIDGE",4,false,$bigbluebuttonbn->voicebridge));
    fwrite ($bf,full_tag("TIMEMODIFIED",4,false,$bigbluebuttonbn->timemodified));
    //End mod
    $status = fwrite ($bf,end_tag("MOD",3,true));

    return $status;
}

////Return an array of info (name,value)
function bigbluebuttonbn_check_backup_mods($course,$user_data=false,$backup_unique_code,$instances=null) {
    if (!empty($instances) && is_array($instances) && count($instances)) {
        $info = array();
        foreach ($instances as $id => $instance) {
            $info += bigbluebuttonbn_check_backup_mods_instances($instance,$backup_unique_code);
        }
        return $info;
    }

    //First the course data
    $info[0][0] = get_string("modulenameplural","bigbluebuttonbn");
    $info[0][1] = count_records("bigbluebuttonbn", "course", "$course");
    return $info;
}

////Return an array of info (name,value)
function bigbluebuttonbn_check_backup_mods_instances($instance,$backup_unique_code) {
    //First the course data
    $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
    $info[$instance->id.'0'][1] = '';
    return $info;
}

?>
