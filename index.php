<?php
/*
 *
 * KET Reports Plugins
 *
 * @package     report_ket_roster
 * @author      George Russell Pruitt <pruitt.russell@gmail.com>
 * @copyright   Kentucky Educational Television 2013
 * test here for sourcetree - bcvincent
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
		$start_date = optional_param('tot_start',null,PARAM_TEXT);
		$end_date = optional_param('tot_end',null,PARAM_TEXT);
		$subject = optional_param('s',null,PARAM_TEXT);
		if(!empty($groupid)){
			$params['g'] = $groupid;
		}
		if(!empty($start_date)){
			$start_date = date("Y-m-d", strtotime($start_date));
			$params['start'] = $start_date;
		}
		if(!empty($end_date)){
			$end_date = date("Y-m-d", strtotime($end_date));
			$params['end'] = $end_date;
		}
		if(!empty($subject)){
			$params['s'] = $subject;
		}
		if($debug){
			var_dump($params);
		} else {
			$report_url = new moodle_url("/report/ae_reports/timeontask.php",$params);
			redirect($report_url);
		}
		break;
	case 'teachertot':
		$start_date = optional_param('teacher_start',null,PARAM_TEXT);
		$end_date = optional_param('teacher_end',null,PARAM_TEXT);
		$subject = optional_param('s',null,PARAM_TEXT);
		if(!empty($groupid)){
			$params['g'] = $groupid;
		}
		if(!empty($start_date)){
			$start_date = date("Y-m-d", strtotime($start_date));
			$params['start'] = $start_date;
		}
		if(!empty($end_date)){
			$end_date = date("Y-m-d", strtotime($end_date));
			$params['end'] = $end_date;
		}
		if(!empty($subject)){
			$params['s'] = $subject;
		}
		if($debug){
			var_dump($params);
		} else {
			$report_url = new moodle_url("/report/ae_reports/teacher.php",$params);
			redirect($report_url);
		}
		break;
	case 'avgtot':
		$start_date = optional_param('avg_start',null,PARAM_TEXT);
		$end_date = optional_param('avg_end',null,PARAM_TEXT);
		$subject = optional_param('s',null,PARAM_TEXT);
		if(!empty($groupid)){
			$params['g'] = $groupid;
		}
		if(!empty($start_date)){
			$start_date = date("Y-m-d", strtotime($start_date));
			$params['start'] = $start_date;
		}
		if(!empty($end_date)){
			$end_date = date("Y-m-d", strtotime($end_date));
			$params['end'] = $end_date;
		}
		if(!empty($subject)){
			$params['s'] = $subject;
		}
		if($debug){
			var_dump($params);
		} else {
			$report_url = new moodle_url("/report/ae_reports/average.php",$params);
			redirect($report_url);
		}
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

$output = html_writer::start_tag('fieldset');
$output .= html_writer::nonempty_tag('legend','Student Reports');

if(has_capability('report/ae_reports:admin_reports', $context)){
	// start timeontask form
	$output .= html_writer::start_tag('div',array('class' => 'ae_reports_form'));
	$output .= html_writer::start_tag('form',array('method' => 'POST', 'action' => $submit_url));
	$output .= html_writer::nonempty_tag('h3','Average Time on Task');
	
	// hidden report field
	$output .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'r', 'value'=>'avgtot'));

	// build options for cohort select
	$options = array();
	// loop thru course categories
	foreach($groups as $group){
		// grab courses for current category
		$options[$group->id] = $group->cohortname;
	}
	// build cohort select
	$output .= html_writer::start_tag('p');
	$output .= html_writer::tag('label', 'Class', array( 'for' => 'g', 'class' => 'ae_reports_label' ));
	$dropdown_output = html_writer::select($options, 'g', 'test', 'Select Below', array('class' => 'ae_reports_select'));
	$output .= $dropdown_output;
	$output .= html_writer::end_tag('p');

	// subject select options
	$options = array();
	$options['math'] = 'Math';
	$options['science'] = 'Science';
	$options['socstudy'] = 'Social Studies';
	$options['language'] = 'Language Arts';

	// build subject select
	$output .= html_writer::start_tag('p');
	$output .= html_writer::tag('label', 'Subject', array( 'for' => 's', 'class' => 'ae_reports_label' ));
	$dropdown_output = html_writer::select($options, 's', 'test', 'Select Below', array('class' => 'ae_reports_select'));
	$output .= $dropdown_output;
	$output .= html_writer::end_tag('p');

	// build start date select
	$output .= html_writer::start_tag('p');
	$output .= html_writer::tag('label', 'Start Date', array( 'for' => 'avg_start', 'class' => 'ae_reports_label' ));
	$output .= html_writer::empty_tag('input', array('type' => 'text', 'name' => 'avg_start', 'id' => 'avg_start', 'class' => 'ae_reports_input'));
	$output .= html_writer::end_tag('p');

	// build end date select
	$output .= html_writer::start_tag('p');
	$output .= html_writer::tag('label', 'End Date', array( 'for' => 'avg_end', 'class' => 'ae_reports_label' ));
	$output .= html_writer::empty_tag('input', array('type' => 'text', 'name' => 'avg_end', 'id' => 'avg_end', 'class' => 'ae_reports_input'));
	$output .= html_writer::end_tag('p');

	// build submit button
	$output .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => $go_button));
	$output .= html_writer::end_tag('form');
	$output .= html_writer::end_tag('p');
	$output .= html_writer::end_tag('div');
	// end timeontask form
}

if(has_capability('report/ae_reports:teacher_reports', $context)){
	// start timeontask form
	$output .= html_writer::start_tag('div',array('class' => 'ae_reports_form'));
	$output .= html_writer::start_tag('form',array('method' => 'POST', 'action' => $submit_url));
	$output .= html_writer::nonempty_tag('h3','Roster Time on Task');
	
	// hidden report field
	$output .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'r', 'value'=>'roster'));

	// build options for cohort select
	$options = array();
	// loop thru course categories
	foreach($groups as $group){
		// grab courses for current category
		$options[$group->id] = $group->cohortname;
	}
	// build cohort select
	$output .= html_writer::start_tag('p');
	$output .= html_writer::tag('label', 'Class', array( 'for' => 'g', 'class' => 'ae_reports_label' ));
	$dropdown_output = html_writer::select($options, 'g', 'test', 'Select Below', array('class' => 'ae_reports_select'));
	$output .= $dropdown_output;
	$output .= html_writer::end_tag('p');

	// subject select options
	$options = array();
	$options['math'] = 'Math';
	$options['science'] = 'Science';
	$options['socstudy'] = 'Social Studies';
	$options['language'] = 'Language Arts';

	// build subject select
	$output .= html_writer::start_tag('p');
	$output .= html_writer::tag('label', 'Subject', array( 'for' => 's', 'class' => 'ae_reports_label' ));
	$dropdown_output = html_writer::select($options, 's', 'test', 'Select Below', array('class' => 'ae_reports_select'));
	$output .= $dropdown_output;
	$output .= html_writer::end_tag('p');

	// build start date select
	$output .= html_writer::start_tag('p');
	$output .= html_writer::tag('label', 'Start Date', array( 'for' => 'tot_start', 'class' => 'ae_reports_label' ));
	$output .= html_writer::empty_tag('input', array('type' => 'text', 'name' => 'tot_start', 'id' => 'tot_start', 'class' => 'ae_reports_input'));
	$output .= html_writer::end_tag('p');

	// build end date select
	$output .= html_writer::start_tag('p');
	$output .= html_writer::tag('label', 'End Date', array( 'for' => 'tot_end', 'class' => 'ae_reports_label' ));
	$output .= html_writer::empty_tag('input', array('type' => 'text', 'name' => 'tot_end', 'id' => 'tot_end', 'class' => 'ae_reports_input'));
	$output .= html_writer::end_tag('p');

	// build submit button
	$output .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => $go_button));
	$output .= html_writer::end_tag('form');
	$output .= html_writer::end_tag('p');
	$output .= html_writer::end_tag('div');
	// end timeontask form
}
$output .= html_writer::end_tag('fieldset');

// teacher time on task
if(has_capability('report/ae_reports:admin_reports', $context)){

	$output .= html_writer::start_tag('fieldset');
	$output .= html_writer::nonempty_tag('legend','Teacher Reports');

	// start roster form
	$output .= html_writer::start_tag('div',array('class' => 'ae_reports_form'));
	$output .= html_writer::start_tag('form',array('method' => 'POST', 'action' => $submit_url));
	$output .= html_writer::nonempty_tag('h3','Teacher Time on Task');
	
	// hidden report field
	$output .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'r', 'value'=>'teachertot'));
	
	$output .= html_writer::start_tag('p');
	// build options for select
	$options = array();
	// loop thru course categories
	foreach($groups as $group){
		// grab courses for current category
		$options[$group->id] = $group->cohortname;
	}
	// build cohort select
	$dropdown_output = html_writer::select($options, 'g', 'test', 'Select Below', array('class' => 'ae_reports_select'));
	$output .= html_writer::tag('label', 'Class', array( 'for' => 'g', 'class' => 'ae_reports_label' ));
	$output .= $dropdown_output;
	$output .= html_writer::end_tag('p');
	
	// build start date select
	$output .= html_writer::start_tag('p');
	$output .= html_writer::tag('label', 'Start Date', array( 'for' => 'teacher_start', 'class' => 'ae_reports_label' ));
	$output .= html_writer::empty_tag('input', array('type' => 'text', 'name' => 'teacher_start', 'id' => 'teacher_start', 'class' => 'ae_reports_input'));
	$output .= html_writer::end_tag('p');

	// build end date select
	$output .= html_writer::start_tag('p');
	$output .= html_writer::tag('label', 'End Date', array( 'for' => 'teacher_end', 'class' => 'ae_reports_label' ));
	$output .= html_writer::empty_tag('input', array('type' => 'text', 'name' => 'teacher_end', 'id' => 'teacher_end', 'class' => 'ae_reports_input'));
	$output .= html_writer::end_tag('p');
	
	// build go button
	$output .= html_writer::start_tag('p');
	$output .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => $go_button));
	$output .= html_writer::end_tag('form');
	$output .= html_writer::end_tag('p');
	$output .= html_writer::end_tag('div');
	// end roster form


	$output .= html_writer::end_tag('fieldset');

}

$PAGE->set_url('/report/ae_reports/index.php');
//$PAGE->requires->css($cssurl);
//$PAGE->requires->js('/report/ae_reports/ae_reports.js');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title($page_title);
$PAGE->set_heading($page_heading);

$PAGE->navbar->add("AE Reports", $index_url);


echo $OUTPUT->header();
echo '<link rel="stylesheet" href="styles.css">';
echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">';
//echo '<link rel="stylesheet" href="'.$cssurl.'">';
echo '<script src="//code.jquery.com/jquery-1.10.2.js"></script>';
echo '<script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>';
echo '<script src="ae_reports.js"></script>';
echo $output;
echo $OUTPUT->footer();
?>