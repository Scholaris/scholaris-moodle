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
 * Scholaris module version information
 *
 * @package    mod
 * @subpackage scholaris
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/scholaris/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

$id      = optional_param('id', 0, PARAM_INT); // Course Module ID
$p       = optional_param('p', 0, PARAM_INT);  // Page instance ID
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

if ($p) {
    if (!$page = $DB->get_record('scholaris', array('id'=>$p))) {
        print_error('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('scholaris', $page->id, $page->course, false, MUST_EXIST);

} else {
    if (!$cm = get_coursemodule_from_id('scholaris', $id)) {
        print_error('invalidcoursemodule');
    }
    $page = $DB->get_record('scholaris', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/scholaris:view', $context);

add_to_log($course->id, 'scholaris', 'view', 'view.php?id='.$cm->id, $page->id, $cm->id);

// Update 'viewed' state if required by completion system
require_once($CFG->libdir . '/completionlib.php');
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/scholaris/view.php', array('id' => $cm->id));

$options = empty($page->displayoptions) ? array() : unserialize($page->displayoptions);

if ($inpopup and $page->display == RESOURCELIB_DISPLAY_POPUP) {
    $PAGE->set_pagelayout('popup');
    $PAGE->set_title($course->shortname.': '.$page->name);
    if (!empty($options['printheading'])) {
        $PAGE->set_heading($page->name);
    } else {
        $PAGE->set_heading('');
    }
    echo $OUTPUT->header();

} else {
    $PAGE->set_title($course->shortname.': '.$page->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($page);
    echo $OUTPUT->header();

    if (!empty($options['printheading'])) {
        echo $OUTPUT->heading(format_string($page->name), 2, 'main', 'pageheading');
    }
}

if (!empty($options['printintro'])) {
    if (trim(strip_tags($page->intro))) {
        echo $OUTPUT->box_start('mod_introbox', 'pageintro');
        echo format_module_intro('scholaris', $page, $cm->id);
        echo $OUTPUT->box_end();
    }
}

$content = file_rewrite_pluginfile_urls($page->content, 'pluginfile.php', $context->id, 'mod_scholaris', 'content', $page->revision);
$formatoptions = new stdClass;
$formatoptions->noclean = true;
$formatoptions->overflowdiv = true;
$formatoptions->context = $context;
$content = format_text($content, $page->contentformat, $formatoptions);

//---------- Scholaris token edition
	$body = $content;
	$attrs = explode(' ',trim($body,'[]'));
	$width = 0;
	foreach ($attrs as $attrstr){
		$attr = explode('=', $attrstr);
		if($attr[0]=='width'){
			$width = trim($attr[1],'"');
		}
	}
	$height = floor( ( (int)$width * 3 ) / 4 );
	$body = str_replace(array(
		'[scholaris ',
		'sl_token="',
		'po_token="',
		']'
		), array(
		'<iframe id="scholaris" ',
		'src="http://creator.staging.scht.pl/preview/show/token/',
		'src="http://portal.staging.scht.pl/resources/run/token/',
		' scrolling="no" frameborder="1" style="border:1px solid #d42ad4; overflow:hidden; height: '.$height.'px" allowtransparency="true"></iframe>'
		), $body);
$content = $body;
//---------------------------------

echo $OUTPUT->box($content, "generalbox center clearfix");

$strlastmodified = get_string("lastmodified");
echo "<div class=\"modified\">$strlastmodified: ".userdate($page->timemodified)."</div>";

echo $OUTPUT->footer();
