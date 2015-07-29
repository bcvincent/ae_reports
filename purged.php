<?php

/**
 *
 * test here on branch bcvincent
 *
**/
function get_user_timeontask($userid, $debug = false){
	
	global $DB, $CFG;
	// grab login times
	$logins = get_user_logins($userid);

	// used to calculate total time
	$sessions = new DateTime();
	$now = new DateTime();
	

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
											. "log.timecreated >= ? AND "
											. "log.timecreated < ? AND "
											. "log.userid = ? ";
			$cur_activities = $DB->get_records_sql($activities_sql,array($login->timecreated,$next_login->timecreated,$userid));
		} else {
			$activities_sql	= "SELECT "
											. "log.timecreated "
											. "FROM {logstore_standard_log} log "
											. "WHERE "
											. "log.timecreated >= ? AND "
											. "log.userid = ? ";
			$cur_activities = $DB->get_records_sql($activities_sql,array($login->timecreated,$userid));
		}
		
		// calculate activities time
		$cur_activities = array_values($cur_activities);
		$first = current($cur_activities);
		end($cur_activities);
		$last = current($cur_activities);

		// total session time
		$total_time = calculate_activity_time($first->timecreated,$last->timecreated);
		$total_time_interval = new DateInterval($total_time);
		$sessions->add($total_time_interval);
		$i++;
	}
	unset($login);
	// create DateInterval
	$timeontask = $sessions->getTimestamp() - $now->getTimestamp();

	if($debug){
		echo $timeontask;
	} else {
		return $timeontask;
	}
}



/**
 *
 * 
 *
**/
function display_report_excel($data){
	//
}
