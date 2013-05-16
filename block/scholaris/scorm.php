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

require('../../config.php');
require_once($CFG->dirroot.'/mod/scholaris/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

$contextid = required_param('contextid',PARAM_INT);
$context = get_context_instance_by_id($contextid);

require_login();
require_capability('moodle/block:view', $context);

$instanceid = $context->instanceid;
$block = $DB->get_record('block_instances', array('id'=>$instanceid));

if(isset($block))
{
	if($block->blockname != "scholaris")
	{
		return;
	}
}
else
{
	return;
}

$PAGE->set_context($context);
$PAGE->set_url('/mod/scholaris/scorm.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_title("Zasoby Scholaris");
$PAGE->set_heading("Zasoby Scholaris");
echo $OUTPUT->header();

$emailRecord = $DB->get_record('config_plugins', array('plugin'=>'scholaris', 'name'=>'email'));
$tokenRecord = $DB->get_record('config_plugins', array('plugin'=>'scholaris', 'name'=>'token'));
if (isset($emailRecord))
{
	$email = $emailRecord->value;
}
if (isset($tokenRecord))
{
	$token = $tokenRecord->value;
}

$markup = '<fieldset style="border:0px; padding:1em; margin-top:2em">
		<div id="j-sidebar-container" class="span2" style="margin-bottom:1em">
			<div id="sidebar">
				<div class="sidebar-nav">
					<ul id="submenu" class="nav nav-list">
						<li id="nav_search">
							<a id="id1" onclick="linkOneOnClick()" href="http://creator.staging.scht.pl/external/search/email/'.$email.'" target="scholaristarget">Wyszukiwarka zasobów portalu</a>
						</li>
						<li id="nav_user">
							<a id="id2" onclick="linkTwoOnClick()" href="http://creator.staging.scht.pl/external/publishlist/email/'.$email.'" target="scholaristarget">Moje e-zasoby</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	    
		<div>
			<iframe id="testid" name="scholaristarget" width="900px;" height="800px;" style="border:none;"></iframe>
		</div>
	  	</fieldset></head>';

echo $OUTPUT->box($markup, "generalbox center clearfix");

//$strlastmodified = get_string("lastmodified");
//echo "<div class=\"modified\">$strlastmodified: ".userdate($page->timemodified)."</div>";

echo $OUTPUT->footer();
