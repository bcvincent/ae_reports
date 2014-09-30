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

$this_url = new moodle_url( $baseurl . basename(__FILE__ ));
$index_url = new moodle_url( $baseurl . "index.php" );
$submit_url = new moodle_url( $baseurl . basename(__FILE__ ));
$groupid = required_param('g', PARAM_TEXT);
$type = optional_param('type',null,PARAM_TEXT);
$debug = optional_param('debug',null,PARAM_BOOL);
	
// get group info
$group = $DB->get_record('ketlicense',array( 'id' => $groupid));
if(has_children($group->id)){
	
	$children = get_children($groupid);
	$where_items = "( ";
	foreach($children as $child => $value){
		$where_items .= 'member.license = ? OR ';
		$query_arr[] = $value;
	}
	$where_items = substr($where_items,0,(strlen($where_items)-3));
	$where_items .= " ) ";
	$usersinfo_sql  = 'SELECT '
	                . 'u.id, '
	                . 'concat(u.firstname," ",u.lastname) as "Name", '
	                . 'u.email as "Email" '
	                . 'FROM '
	                . '{user} u '
	                . 'JOIN '
	                . '{ketlicense_member} member ON u.id = member.user '
	                . 'WHERE '
	                . "$where_items "
	                . ' AND '
	                . 'member.enddate = "0" '
	                . 'ORDER BY '
	                . 'u.lastname ASC ';
	
} else {
	$usersinfo_sql  = 'SELECT '
	                . 'u.id, '
	                . 'concat(u.firstname," ",u.lastname) as "Name", '
	                . 'u.email as "Email" '
	                . 'FROM '
	                . '{user} u '
	                . 'JOIN '
	                . '{ketlicense_member} member ON u.id = member.user '
	                . 'WHERE '
	                . 'member.license = ? AND '
	                . 'member.enddate = "0" '
	                . 'ORDER BY '
	                . 'u.lastname ASC ';
	$query_arr = array($group->id);
}
$users = $DB->get_records_sql($usersinfo_sql,$query_arr);
$user_data = array();
$array_count = 0;

$tot_start = new DateTime();
$tot_diff = new DateTime();

foreach($users as $user){
	$temparray = array();
	foreach($user as $key => $value){
		$temparray[$key] = $value;
	}
	$temparray['Phone'] = get_user_phone($user->id);
	$timeontask = get_user_timeontask($user->id);
	$timeontask_int = new DateInterval("PT".$timeontask."S");
	$temparray['Time on Task'] = format_timeontask($timeontask);
	$last_logged = get_user_lastlogged($user->id);
	if($last_logged == NULL) {
		$temparray['Last Logged'] = "Never";
	} else {
		$temparray['Last Logged'] = date("Y-m-d",$last_logged);
	}
	$temparray['Time Inactive'] = get_user_timeinactive($user->id);
	$user_data[$array_count] = $temparray;
	
	$tot_diff->add($timeontask_int);
	$array_count++;
}
unset($user);

$tot_seconds = $tot_diff->getTimestamp() - $tot_start->getTimestamp();
$avg_seconds = $tot_seconds / sizeof($users);

//var_dump($user_data);


/*
$avg_tot = $tot_start->diff($tot_diff);
$avg_output = $avg_tot->format("%H:%M:%S");
*/

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
			
		$PAGE->set_url($this_url, array('g'=>$groupid));
		$PAGE->requires->css($cssurl);
		$PAGE->requires->js($tablejsurl);
		$PAGE->requires->js($accjsurl);
		$PAGE->set_context(context_system::instance());
		$PAGE->set_pagelayout('standard');
		$PAGE->set_title($page_title);
		$PAGE->set_heading($page_heading);
		
		
		$PAGE->navbar->add("AE Reports", $index_url);
		$PAGE->navbar->add("Roster", $this_url);
		
		echo $OUTPUT->header();
		echo '<script src="//code.jquery.com/jquery-1.10.2.js"></script>';
		echo '<script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>';
		if(sizeof($user_data) > 0){
			if($debug){
				var_dump($usersinfo_sql);
				var_dump($query_arr);
				var_dump($user_data);
			} else {
				echo generate_html_table($user_data,"id");
				echo "<br/><p><strong>Average Time on Task</strong>: ".gmdate("H:i:s",$avg_seconds)."</p>";
				echo html_writer::link(new moodle_url($this_url,array( 'g' => $groupid,'type' => 'csv')),"Download CSV");
			}
		} else {
			echo "No records found.";
		}
		echo $OUTPUT->footer();
		break;
}

?>