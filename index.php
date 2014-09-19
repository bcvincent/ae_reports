<?php
/*
 *
 * KET Reports Plugins
 *
 * @package     report_ket_roster
 * @author      George Russell Pruitt <pruitt.russell@gmail.com>
 * @copyright   Kentucky Educational Television 2013
 *
 */
 
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/setup.php');
require_once(dirname(__FILE__) . '/lib.php');

require_login();

$context = context_system::instance();
if(!has_capability('report/ae_reports:view', $context)){
    die("Missing required permissions to view this page.");
}

// ************  
// ** variables
$groupid = optional_param('g', null, PARAM_TEXT);
$report = optional_param('r', null, PARAM_TEXT);
$cssurl = $baseurl."styles.css";    // easy CSS style linking
$index_url = new moodle_url( $baseurl . "index.php" );
$submit_url = new moodle_url( $baseurl . basename(__FILE__ ));


$go_button = get_string("gobutton",$plugin);

// based on report selection
switch($report){
	case 'roster':
		$roster_report = new moodle_url("/report/ae_reports/roster.php",array('g' => $groupid));
		redirect($roster_report);
		break;
	default:
		break;
}

// check permissions for which courses to grab    
if( has_capability('local/ketlicense:manageall', $context) ) {
	// has global admin rights
	$groups_sql     = 'SELECT '
	                . 'id, '
	                . 'mdl_ketlicense.cohortname '
	                . 'FROM '
	                . 'mdl_ketlicense '
	                . 'WHERE '
	                . 'mdl_ketlicense.admin = ?';
} else {
	// only pull groups associated with user
  $groups_sql     = "SELECT DISTINCT "
                  . "{ketlicense}.id, "
                  . "{ketlicense}.groupname, "
                  . "FROM "
                  . "{groups} "
                  . "INNER JOIN "
                  . "{course} ON {course}.id = {groups}.courseid "
                  . "INNER JOIN "
                  . "{context} ON {context}.instanceid = {course}.id "
                  . "INNER JOIN "
                  . "{ketlicense_group} ON {ketlicense_group}.groupid = {groups}.id "
                  . "INNER JOIN "
                  . "{ketlicense} ON {ketlicense_group}.license = {ketlicense}.id "
                  . "INNER JOIN "
                  . "{groups_members} ON {groups_members}.groupid = {groups}.id "
                  . "INNER JOIN "
                  . "{user} ON {user}.id = {groups_members}.userid "
                  . "INNER JOIN "
                  . "{role_assignments} ON {role_assignments}.userid = {user}.id "
                  . "INNER JOIN "
                  . "{role} ON {role}.id = {role_assignments}.roleid "
                  . "WHERE "
                  . "{user}.id = ? AND "
                  . "{role}.shortname = 'teacher' AND "
                  . "{context}.contextlevel = '50';";
                    
}


$groups = $DB->get_records_sql($groups_sql,array($USER->id));
//print_r($groups);
//die($groups_sql);


// start form
$output = html_writer::start_tag('div',array('class' => 'ae_reports_form'));
$output .= html_writer::start_tag('form',array('method' => 'GET', 'action' => $submit_url));
$options = array();
// loop thru course categories
foreach($groups as $group){
	// grab courses for current category
	$options[$group->id] = $group->cohortname;
}

// hidden report
$output .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'r', 'value'=>'roster'));

// build output
$dropdown_output = html_writer::select($options, 'g', 'test', 'Select Below', array('class' => 'ae_reports_select'));

$output .= $dropdown_output;
$output .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => $go_button));
$output .= html_writer::end_tag('form');
$output .= html_writer::end_tag('p');
$output .= html_writer::end_tag('div');

$PAGE->set_url('/report/ae_reports/index.php');
$PAGE->requires->css($cssurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title($page_title);
$PAGE->set_heading($page_heading);

$PAGE->navbar->add("AE Reports", $index_url);


echo $OUTPUT->header();
echo $output;
echo $OUTPUT->footer();
?>