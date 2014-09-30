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

defined('MOODLE_INTERNAL') || die();




/**
 * calculates users time on task
 *
 * returns integer seconds
**/
function get_user_timeontask_subj($userid, $subj = NULL, $debug = false){
	
	global $DB, $CFG;
	// grab login times
	$logins = get_user_logins($userid);

	// used to calculate total time
	$sessions = new DateTime();
	$now = new DateTime();
	
	if($subj !== NULL){
		switch($subj){
			case "math":
				$where_items = "( ";
				$where_items .= "log.courseid = '12' OR ";
				$where_items .= "log.courseid = '13' ";
				$where_items .= ") AND ";
				break;
			case "science":
				$where_items = "( ";
				$where_items .= "log.courseid = '25' ";
				$where_items .= ") AND ";
				break;
			case "socstudy":
				$where_items = "( ";
//				$where_items .= "log.courseid = '19' ";
				$where_items .= "log.courseid = '19' OR ";
				$where_items .= "log.courseid = '2' ";
				$where_items .= ") AND ";
				break;
			case "language":
				$where_items = "( ";
				$where_items .= "log.courseid = '14' OR ";
				$where_items .= "log.courseid = '15' OR ";
				$where_items .= "log.courseid = '16' ";
				$where_items .= ") AND ";
				break;
			default:
				$where_items = "";
				break;
			}
	}

	// loop login times
	$i = 0;
	foreach($logins as $login){

		// get next login in loop
		if($i < sizeof($logins)){
			$next_login = $logins[$i+1];
		}

		// build actities sql
		if($next_login !== NULL){ // activities to be less then next login
			$activities_sql	= "SELECT "
											. "log.timecreated "
											. "FROM {logstore_standard_log} log "
											. "WHERE "
											. "$where_items"
											. "log.timecreated >= ? AND "
											. "log.timecreated < ? AND "
											. "log.action <> 'loggedout' AND "
											. "log.userid = ? ";
			$temp_params = array($login->timecreated,$next_login->timecreated,$userid);
			$cur_activities = $DB->get_records_sql($activities_sql,$temp_params);
		} else {
			$activities_sql	= "SELECT "
											. "log.timecreated "
											. "FROM {logstore_standard_log} log "
											. "WHERE "
											. "$where_items"
											. "log.timecreated >= ? AND "
											. "log.action <> 'loggedout' AND "
											. "log.userid = ? ";
			$temp_params = array($login->timecreated,$userid);
			$cur_activities = $DB->get_records_sql($activities_sql,$temp_params);
		}

		// calculate activities time
		$cur_activities = array_values($cur_activities);
		$first = current($cur_activities);
		end($cur_activities);
		$last = current($cur_activities);

		// total session time
		$total_time = calculate_activity_time($first->timecreated,$last->timecreated,$subj);
		$total_time_interval = new DateInterval($total_time);
		$sessions->add($total_time_interval);
		$i++;
		if($debug){
			var_dump($activities_sql);
			var_dump($temp_params);
			var_dump($total_time);
		}
	}
	unset($login);
	// create DateInterval
	$timeontask = $sessions->getTimestamp() - $now->getTimestamp();

	if($debug){
		var_dump($timeontask);
	} else {
		return $timeontask;
	}
}



/**
 * formats $seconds to H:M:S
 *
 * returns string time
**/
function format_timeontask($seconds){
	$int = new DateInterval("PT".$seconds."S");
	$now = new DateTime();
	$diff = new DateTime();
	$diff->add($int);
	$output = $now->diff($diff);
	return $output->format("%H:%I:%S");
}



/**
 * gets all logins for id
 *
 * returns array of login timestamps
**/
function get_user_logins($userid){

	global $DB;

	// build login sql
	$login_sql  = "SELECT "
							. "log.id, "
							. "log.timecreated "
							. "FROM {logstore_standard_log} log "
							. "WHERE "
							. "log.action = 'loggedin' AND "
							. "log.userid = ? "
							. "ORDER BY log.timecreated ASC";

	$logins = $DB->get_records_sql($login_sql,array($userid));
	$logins = array_values($logins);
	return $logins;
}



/**
 * gets phone number for id
 *
 * returns string phone 
**/
function get_user_phone($userid){
	
	global $DB;
	
  $phone_sql  = 'SELECT '
              . 'data.data as "phone", '
              . 'data.userid '
              . 'FROM '
              . '{user_info_data} data '
              . 'JOIN {user} ON {user}.id = data.userid '
              . 'JOIN {user_info_field} field ON field.id = data.fieldid '
              . 'WHERE '
              . '{user}.id = ? AND '
              . 'field.shortname = "Phone" '
              . 'LIMIT 0,1';
    
  $phone = $DB->get_records_sql($phone_sql,array($userid));
  $phone = array_values($phone);
	if(sizeof($phone)>0){
		if(!empty($phone[0]->phone)){
			return $phone[0]->phone;
		} else {
			return " ";
		}
		
	} else {
		return "Not Found";
	}

}



/**
 * grabs last login for id
 *
 * returns string date
**/
function get_user_lastlogged($userid){
	//
	global $DB;
	
	// build login sql
	$login_sql  = "SELECT "
							. "log.id, "
							. "log.timecreated "
							. "FROM {logstore_standard_log} log "
							. "WHERE "
							. "log.action = 'loggedin' AND "
							. "log.userid = ? "
							. "ORDER BY log.timecreated DESC LIMIT 0,1";

	$logins = $DB->get_records_sql($login_sql,array($userid));
	if(sizeof($logins)>0){
		$logins = array_values($logins);
		$output_date = $logins[0]->timecreated;
		//$output = date("Y-m-d",$output_date);
		return $output_date;
	} else {
		return NULL;
	}
}



/**
 * grabs last login for id and calculates inactive time
 *
 * returns string
**/
function get_user_timeinactive($userid){
	
	global $DB;
	
	// get last login
	$login = get_user_lastlogged($userid);
	
		// check if logins empty
	if ($login !== NULL){

		$start = new DateTime();
		$end = new DateTime();
		$end->setTimestamp($login);
		$time_since = $start->diff($end);
		
		return $time_since->format("%a Days");

	} else {

		// grab time user was created
		$created = $DB->get_record("user",array('id'=>$userid));
		if(sizeof($created)>0){
			$start = new DateTime();
			$end = new DateTime();
			$end->setTimestamp($created->timecreated);
			$time_since = $start->diff($end);
	
			return $time_since->format("%a Days");
			
		} else {
			
			return "Unknown";
			
		}

	}
	
}



/**
 * processes $data array into HTML table
 *
 * returns html string
**/
function generate_html_table($data,$id_fld = NULL,$print_id = false){

	global $CFG;

	$output = html_writer::start_tag('table',array('class' => 'tablesorter', 'id' => 'goodTable'));
  $output .= html_writer::start_tag('thead');
  $output .= html_writer::start_tag('tr');
  $output .= html_writer::nonempty_tag('th','#');
 	foreach($data[0] as $key => $value){
		if($key == $id_fld && $print_id == false){
			//$row_output .= html_writer::nonempty_tag('th',$key);
		} elseif($key == $id_fld && $print_id == true){
			$output .= html_writer::nonempty_tag('th',$key);
		} elseif($key !== $id_fld && $print_id == false){
			$output .= html_writer::nonempty_tag('th',$key);
		}
	}
  $output .= html_writer::end_tag('tr');
  $output .= html_writer::end_tag('thead');
  $output .= html_writer::start_tag('tbody');
  $users_count = 0;
  
  $row_output = "";
  // TODO convert to use array
  foreach($data as $row){
    $users_count++;
    $row_output .= html_writer::start_tag('tr');
    $row_output .= html_writer::nonempty_tag('th',$users_count);
		foreach($row as $key => $value){
	 		if($key == $id_fld && $print_id == false){
				//$row_output .= html_writer::nonempty_tag('td',$value);
			} elseif($key == $id_fld && $print_id == true){
				$row_output .= html_writer::nonempty_tag('td',$value);
			} elseif($key !== $id_fld && $print_id == false){
				$row_output .= html_writer::nonempty_tag('td',$value);
			}

		}
    $row_output .= html_writer::end_tag('tr');
  }
  $output .= $row_output;
  $output .= html_writer::end_tag('tbody');
	$output .= html_writer::end_tag('table');
	
	return $output;
}



/**
 * process $data array into csv
 * 
 * returns csv string
**/
function generate_csv($data,$id_fld = NULL,$print_id = false){
	$csv_header = "#,";
	// build header
 	foreach($data[0] as $key => $value){
 		// check whether to include ID field
 		if($key == $id_fld && $print_id == false){
			//$csv_header .= $key.",";
		} elseif($key == $id_fld && $print_id == true){
			$csv_header .= $key.",";
		} elseif($key !== $id_fld && $print_id == false){
			$csv_header .= $key.",";
		}
	}
	$csv_header = substr($csv_header,0,(strlen($csv_header)-1)).PHP_EOL;
  $users_count = 0;
  $row_output = "";
  // build rows
  foreach($data as $row){
    $users_count++;
		$row_output .= $users_count.",";
		foreach($row as $key => $value){
			// check whether to include ID field
	 		if($key == $id_fld && $print_id == false){
				//$row_output .= $value.",";
			} elseif($key == $id_fld && $print_id == true){
				$row_output .= $value.",";
			} elseif($key !== $id_fld && $print_id == false){
				$row_output .= $value.",";
			}
		}
		$row_output = substr($row_output,0,(strlen($row_output)-1)).PHP_EOL;
  }
  $output = $csv_header.$row_output;

	return $output;
}






/**
 * checks whether id has children
 *
 * returns boolean
**/
function has_children($id){
	
	global $DB;
	$sql = "SELECT * FROM {ketlicense} WHERE parent = ?";
	$childs = $DB->get_records_sql($sql,array($id));
	if(sizeof($childs)>0){
		return true;
	} else {
		return false;
	}
	
}



/**
 * Grabs all children of id
 *
 * returns array contains children id #s
**/
function get_children($id){
	global $DB;
	$sql = "SELECT * FROM {ketlicense} WHERE parent = ?";
	$childs = $DB->get_records_sql($sql,array($id));
	$output_array = array();
	if(sizeof($childs)>0){
		foreach($childs as $row){
			if(has_children($row->id)){
				$subchildren = get_children($row->id);
				foreach($subchildren as $subrow => $subval){
					$output_array[] = $subval;
				}
			} else {	
				$output_array[] = $row->id;
			}
		}
		return $output_array;
	} else {
		return false;
	}
}



/**
 * calculates the difference between two timestamps
 *
 * returns DateInterval object
**/
function calculate_activity_time($time_one,$time_two,$subj=NULL){
	if($time_one !== $time_two){
		$start = new DateTime();
		$start->setTimestamp($time_one);	
		$end = new DateTime();
		$end->setTimestamp($time_two);
		$end_interval = new DateInterval("PT10M");
		$end->add($end_interval);
		$time_since = $start->diff($end);
		return $time_since->format("PT%HH%IM%SS");
	} elseif($subj == NULL) {
		$date = new DateInterval("PT10M");
		return $date->format("PT%HH%IM%SS");
	} else {
		$date = new DateInterval("PT0S");
		return $date->format("PT%HH%IM%SS");
	} 
	
}