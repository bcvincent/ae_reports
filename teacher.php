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
$start_date = optional_param('teacher_start',null,PARAM_TEXT);
$end_date = optional_param('teacher_start',null,PARAM_TEXT);
//$subject = optional_param('s',null,PARAM_TEXT);
$debug = optional_param('debug',null,PARAM_TEXT);

$cssurl = $baseurl."styles.css";    // easy CSS style linking
$url = $baseurl . basename(__FILE__);
$this_url = new moodle_url( $url );
$index_url = new moodle_url( $baseurl . "index.php" );
$submit_url = new moodle_url( $baseurl . basename(__FILE__ ));

$page_title = get_string("pluginname",$plugin);
$page_heading = get_string("pluginadministration",$plugin);
$view_button = get_string("viewbutton",$plugin);

// get group info
$group = $DB->get_record('ketlicense',array( 'id' => $groupid));
$group_heading = $group->cohortname;
	
// get children of group and subchildren
$children = get_children($groupid);

$where_items = "( ";
$query_arr = array();

if(has_children($group->id)){	
	// build where statements
	foreach($children as $child => $value){
		$where_items .= 'member.license = ? OR ';
		// put values into array
		$query_arr[] = $value;
	}
	$where_items = substr($where_items,0,(strlen($where_items)-3));
	$where_items .= ") ";
}	else {
	$where_items = 'member.license = ? ';
	$query_arr[] = $groupid;
}


// make additional where parameters

// build start date 
if($start_date){
	$where_items .= " AND member.startdate <= ? ";
	$query_arr[] = strtotime($end_date);
	$start_heading = " ".date("M j Y",strtotime($start_date));
}

// build end date
if($end_date){
	$where_items .= " AND ( member.enddate >= ? OR member.enddate = '0' ) ";
	$query_arr[] = strtotime($start_date);
	$end_heading = " - ".date("M j Y",strtotime($end_date));
}

/*
// currently unused
if($subject){
	//$where_items .= " AND member.enddate < ? ";
	//$query_arr[] = "";
	switch($subject){
		case "math":
			$subj_heading = "Math";
			break;
		case "language":
			$subj_heading = "Language Arts";
			break;
		case "science":
			$subj_heading = "Science";
			break;
		case "socstudy":
			$subj_heading = "Social Studies";
			break;
	}
}
*/



$users = get_roster_teacher($where_items,$query_arr);
$user_data = array();
$array_count = 0;

$tot_start = new DateTime();
$tot_diff = new DateTime();

foreach($users as $user){
	$temparray = array();
	foreach($user as $key => $value){
		$temparray[$key] = $value;
	}
	$temparray['phone'] = get_user_phone($user->id);
	$timeontask = get_user_timeontask_subj($user->id,$subject);
	$timeontask_int = new DateInterval("PT".$timeontask."S");
	$temparray['time on task'] = format_timeontask($timeontask);
	$last_logged = get_user_lastlogged($user->id);
	if($last_logged == NULL) {
		$temparray['last login'] = "Never";
	} else {
		$temparray['last login'] = date("Y-m-d",$last_logged);
	}
	$temparray['days inactive'] = get_user_timeinactive($user->id);
	$user_data[$array_count] = $temparray;
	
	$tot_diff->add($timeontask_int);
	$array_count++;
}
unset($user);

$tot_seconds = $tot_diff->getTimestamp() - $tot_start->getTimestamp();
$avg_seconds = $tot_seconds / sizeof($users);




switch($type){

	case "csv":
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=report.csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		//echo generate_csv($user_data,"id",false);
		echo generate_csv($user_data,"id");
		echo "Total Records,".sizeof($users).PHP_EOL;
		echo "Total Time on Task,".gmdate("H:i:s",$avg_seconds).PHP_EOL;
		break;
	default:
			
		$PAGE->set_url('/report/ae_reports/timeontask.php');
		$PAGE->requires->css($cssurl);
		$PAGE->requires->js($tablejsurl);
		$PAGE->requires->js($accjsurl);
		$PAGE->set_context(context_system::instance());
		$PAGE->set_pagelayout('standard');
		$PAGE->set_title($page_title);
		$PAGE->set_heading($page_heading);
		
		
		$PAGE->navbar->add("AE Reports", $index_url);
		$PAGE->navbar->add("Time on Task", $this_url);
		
		echo $OUTPUT->header();
		echo '<script src="//code.jquery.com/jquery-1.10.2.js"></script>';
		echo '<script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>';
		if(sizeof($user_data) > 0){
			if($debug){
				var_dump($where_items);
				var_dump($usersinfo_sql);
				var_dump($query_arr);
				var_dump($user_data);
			} else {
				echo html_writer::start_tag('div',array('id' => 'ae_reports_display'));
				//echo html_writer::start_tag('div',array('class' => 'ae_reports_view'));
				echo html_writer::nonempty_tag('h3',"Teacher Time on Task: $group_heading");
				echo html_writer::nonempty_tag('h5',"$subj_heading$start_heading$end_heading");
				echo generate_html_table($user_data,"id");
				//echo html_writer::nonempty_tag('p',"<strong>Reported Users</strong>: ".sizeof($user_data));
				echo html_writer::nonempty_tag('p',"<strong>Time on Task</strong>: ".gmdate("H:i:s",$avg_seconds));
				echo html_writer::link(new moodle_url($this_url,array( 'g' => $groupid,'type' => 'csv')),"Download CSV");
				//echo html_writer::end_tag('div');
				echo html_writer::end_tag('div');
			}
		} else {
			echo "No records found.";
		}
		echo $OUTPUT->footer();
		break;
}
?>