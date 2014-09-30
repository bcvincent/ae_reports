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

$this_url = new moodle_url( $baseurl . basename(__FILE__ ));
$index_url = new moodle_url( $baseurl . "index.php" );
$submit_url = new moodle_url( $baseurl . basename(__FILE__ ));

$test_id = 1330;

echo "<p style='border:solid 1px;'><strong>tot no subj</strong>";
var_dump(get_user_timeontask_subj($test_id,null,true));
echo "</p>";
echo "<p style='border:solid 1px;'><strong>tot w/soc studies</strong>";
var_dump(get_user_timeontask_subj($test_id,"socstudy",true));
echo "</p>";
echo "<p style='border:solid 1px;'><strong>format tot 2mins..</strong>";
var_dump(format_timeontask(120));
echo "</p>";
echo "<p style='border:solid 1px;'><strong>logins</strong>";
var_dump(get_user_logins($test_id));
echo "</p>";
echo "<p style='border:solid 1px;'><strong>phone</strong>";
var_dump(get_user_phone($test_id));
echo "</p>";
echo "<p style='border:solid 1px;'><strong>last logged</strong>";
var_dump(get_user_lastlogged($test_id));
echo "</p>";
echo "<p style='border:solid 1px;'><strong>inactive</strong>";
var_dump(get_user_timeinactive($test_id));
echo "</p>";

?>