<?php
/*
 * Upgrade
 *
 * Authors:
 *      Fred Dixon (ffdixon [at] blindsidenetworks [dt] org)
 *      Jesus Federico (jesus [at] blindsidenetworks [dt] org)
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @copyright 2010-2012 Blindside Networks Inc.
 * @package mod/bigbluebuttonbn
 */

// This file keeps track of upgrades to
// the bigbluebuttonbn module

function xmldb_bigbluebuttonbn_upgrade($oldversion=0) {

    global $CFG, $THEME, $db;
    //$dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
    
    $result = true;
    
    if ($result && $oldversion < 2012110710) {
    ///////// bigbluebuttonbn
    /// Define table bigbluebuttonbn to be modified
        $table = new XMLDBTable('bigbluebuttonbn');
        
    /// Adding new fields to table bigbluebuttonbn
        $field = new XMLDBField('welcome');
        $field->setAttributes(XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, null, '');
        add_field($table, $field, $continue=true, $feedback=true);

        $field = new XMLDBField('openoutside');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        add_field($table, $field, $continue=true, $feedback=true);
        
        $field = new XMLDBField('timedue');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        add_field($table, $field, $continue=true, $feedback=true);
        
        $field = new XMLDBField('timeavailable');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        add_field($table, $field, $continue=true, $feedback=true);
        
        $field = new XMLDBField('record');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        add_field($table, $field, $continue=true, $feedback=true);
        
        $field = new XMLDBField('description');
        $field->setAttributes(XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, '');
        add_field($table, $field, $continue=true, $feedback=true);

        $field = new XMLDBField('timeduration');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        add_field($table, $field, $continue=true, $feedback=true);

        $field = new XMLDBField('voicebridge');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '5', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        add_field($table, $field, $continue=true, $feedback=true);
        
        ///////// bigbluebuttonbn_log
        /// Define table bigbluebuttonbn_log to be created
        $table = new XMLDBTable('bigbluebuttonbn_log');
        
        /// Adding fields to table bigbluebuttonbn_log
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('meetingid', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        $table->addFieldInfo('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('bigbluebuttonbnid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('record', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('event', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null, null, null);
        
        /// Adding keys to table bigbluebuttonbn_log
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        
        /// Launch create table for bigbluebuttonbn_log
        $result = $result && create_table($table);
    }

    //if ($result && $oldversion < 2012110720) {
    //    ///////// bigbluebuttonbn
    //    /// Define table bigbluebuttonbn to be modified
    //    $table = new XMLDBTable('bigbluebuttonbn');
    //
    //    $field = new XMLDBField('welcome');
    //    $field->setAttributes(XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, null, '');
    //    add_field($table, $field, $continue=true, $feedback=true);
    //    
    //}
    
    return $result;
}

?>
