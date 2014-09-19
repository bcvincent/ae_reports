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
 *
 *
 *
**/
function get_user_timeontask($userid){
	//
}



/**
 *
 *
 *
**/
function get_user_phone($userid){
	//
}



/**
 *
 *
 *
**/
function get_user_lastlogged($userid){
	//
}



/**
 *
 *
 *
**/
function get_user_timeinactive($userid){
	//
}



/**
 *
 *
 *
**/
function generate_html_table($data){

	global $CFG;

	$output = html_writer::start_tag('table',array('class' => 'tablesorter', 'id' => 'goodTable'));
  $output .= html_writer::nonempty_tag('caption',$page_title);
  $output .= html_writer::start_tag('thead');
  $output .= html_writer::start_tag('tr');
  $output .= html_writer::nonempty_tag('th','#');
 	foreach($data[0] as $key => $value){
		$output .= html_writer::nonempty_tag('th',$key);
	}
  $output .= html_writer::end_tag('tr');
  $output .= html_writer::end_tag('thead');
  $output .= html_writer::start_tag('tbody');
  $users_count = 0;
  
  // TODO convert to use array
  foreach($data as $row){
    $users_count++;
    $row_output .= html_writer::start_tag('tr');
    $row_output .= html_writer::nonempty_tag('th',$users_count);
		foreach($row as $key => $value){
			$row_output .= html_writer::nonempty_tag('td',$value);
		}
    $row_output .= html_writer::end_tag('tr');
  }
  $output .= $row_output;
  $output .= html_writer::end_tag('tbody');
	$output .= html_writer::end_tag('table');
	
	return $output;
}



/**
 *
 * 
 *
**/
function generate_csv($data){
	$csv_header = "#,";
 	foreach($data[0] as $key => $value){
		$csv_header .= $key.",";
		}
	$csv_header = substr($csv_header,0,(strlen($csv_header)-1))."\r\n";
  $users_count = 0;
  $row_output = "";
  // TODO convert to use array
  foreach($data as $row){
    $users_count++;
		$row_output .= $users_count.",";
		foreach($row as $key => $value){
			$row_output .= $value.",";
		}
		$row_output = substr($row_output,0,(strlen($row_output)-1))."\r\n";
  }
  $output = $csv_header.$row_output;

	return $output;
}



/**
 *
 * 
 *
**/
function display_report_excel($data){
	//
}



/**
 *
 *
 *
**/
function get_select_dates(){
	// get current date
	$today = new DateTime('NOW');
	// determine timeline
	
	// build array
}



/**
 *
 *
 *
**/
function build_date_select(){
	//
}



/**
 *
 *
 *
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
 *
 *
 *
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