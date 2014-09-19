<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    report_ae_reports
 * @author     George Russell Pruitt <pruitt.russell@gmail.com>
 * @copyright  Kentucky Educational Television 2013
 *
**/

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/setup.php');
require_once(dirname(__FILE__) . '/lib.php');

require_login();


$context = context_system::instance();
if(!has_capability('report/ae_reports:view', $context)){
    die("Missing required permissions to view this page.");
}


$groupid = optional_param('g', null, PARAM_TEXT);
$cssurl = $baseurl."styles.css";    // easy CSS style linking

$url = $baseurl . basename(__FILE__);
$this_url = new moodle_url( $url );
$index_url = new moodle_url( $baseurl . "index.php" );
$submit_url = new moodle_url( $baseurl . basename(__FILE__ ));



$page_title = get_string("pluginname",$plugin);
$page_heading = get_string("pluginadministration",$plugin);
$view_button = get_string("viewbutton",$plugin);

// check permissions for which courses to grab    
if( has_capability('local/ketlicense:manageall', $context) ) {
	// has global admin rights
  $groups_sql     = 'SELECT '
                  . 'id, '
                  . '{ketlicense}.groupname as "class" '
                  . 'FROM '
                  . '{ketlicense} '
                  . 'WHERE '
                  . '{ketlicense}.level = "group";';
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


$groups = $DB->get_records_sql($groups_sql,array( 'id' => $USER->id ) );

// get group info
//$group = $DB->get_record('ketlicense',array( 'id' => $groupid));



// BUILD REPORT OPTIONS

// start form
$output = html_writer::start_tag('div',array('class' => 'ae_reports_form'));
$output .= html_writer::start_tag('form',array('method' => 'GET', 'action' => $submit_url));

$options = array();
// loop thru course categories
foreach($groups as $group){
	// grab courses for current category
	$options[$group->id] = $group->groupname;
}

// build output
$dropdown_output = html_writer::select($options, 'g', 'test', 'Select Below', array('class' => 'ae_reports_select'));

$output .= $dropdown_output;
$output .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => $view_button));
$output .= html_writer::end_tag('form');
$output .= html_writer::end_tag('p');
$output .= html_writer::end_tag('div');


// END REPORT OPTIONS



$PAGE->set_url('/report/ae_reports/timeontask.php');
$PAGE->requires->css($cssurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title($page_title);
$PAGE->set_heading($page_heading);


$PAGE->navbar->add("AE Reports", $index_url);
$PAGE->navbar->add("Time on Task", $this_url);

echo $OUTPUT->header();
echo $output;
echo $OUTPUT->footer();
?>