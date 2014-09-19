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


$plugin = "report_ae_reports";
$baseurl = "/report/ae_reports/";  			 								// used for URL combinations
$cssurl = $baseurl."styles.css";    										// easy CSS style linking
$tablejsurl = $baseurl."jquery.tablesorter.min.js";    	// tablesorter js
$accjsurl = $baseurl."accessible.js";    								// accessible js
$page_title = get_string("pluginname",$plugin);
$page_heading = get_string("pluginadministration",$plugin);