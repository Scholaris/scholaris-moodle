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
 * Scholaris module configuration form
 *
 * @package    mod
 * @subpackage scholaris
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/scholaris/locallib.php');
require_once($CFG->libdir.'/filelib.php');

class mod_scholaris_mod_form extends moodleform_mod {
    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $config = get_config('scholaris');

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $this->add_intro_editor($config->requiremodintro);

        //-------------------------------------------------------
        $mform->addElement('header', 'contentsection', get_string('contentheader', 'scholaris'));
        $mform->addElement('editor', 'scholaris', get_string('content', 'scholaris'), null, scholaris_get_editor_options($this->context));
        $mform->addRule('scholaris', get_string('required'), 'required', null, 'client');

        //-------------------------------------------------------
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
        
        $mform->addElement('header', 'scholarissection', get_string('scholarisheader', 'scholaris'));
        $mform->addElement('html', $markup);
        //-------------------------------------------------------
        $mform->addElement('header', 'optionssection', get_string('optionsheader', 'scholaris'));

        if ($this->current->instance) {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        } else {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions));
        }
        if (count($options) == 1) {
            $mform->addElement('hidden', 'display');
            $mform->setType('display', PARAM_INT);
            reset($options);
            $mform->setDefault('display', key($options));
        } else {
            $mform->addElement('select', 'display', get_string('displayselect', 'scholaris'), $options);
            $mform->setDefault('display', $config->display);
            $mform->setAdvanced('display', $config->display_adv);
        }

        if (array_key_exists(RESOURCELIB_DISPLAY_POPUP, $options)) {
            $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'scholaris'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupwidth', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupwidth', PARAM_INT);
            $mform->setDefault('popupwidth', $config->popupwidth);
            $mform->setAdvanced('popupwidth', $config->popupwidth_adv);

            $mform->addElement('text', 'popupheight', get_string('popupheight', 'scholaris'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupheight', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupheight', PARAM_INT);
            $mform->setDefault('popupheight', $config->popupheight);
            $mform->setAdvanced('popupheight', $config->popupheight_adv);
        }

        $mform->addElement('advcheckbox', 'printheading', get_string('printheading', 'scholaris'));
        $mform->setDefault('printheading', $config->printheading);
        $mform->setAdvanced('printintro', $config->printheading_adv);
        $mform->addElement('advcheckbox', 'printintro', get_string('printintro', 'scholaris'));
        $mform->setDefault('printintro', $config->printintro);
        $mform->setAdvanced('printintro', $config->printintro_adv);

        // add legacy files flag only if used
        if (isset($this->current->legacyfiles) and $this->current->legacyfiles != RESOURCELIB_LEGACYFILES_NO) {
            $options = array(RESOURCELIB_LEGACYFILES_DONE   => get_string('legacyfilesdone', 'scholaris'),
                             RESOURCELIB_LEGACYFILES_ACTIVE => get_string('legacyfilesactive', 'scholaris'));
            $mform->addElement('select', 'legacyfiles', get_string('legacyfiles', 'scholaris'), $options);
            $mform->setAdvanced('legacyfiles', 1);
        }

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();

        //-------------------------------------------------------
        $mform->addElement('hidden', 'revision');
        $mform->setType('revision', PARAM_INT);
        $mform->setDefault('revision', 1);
    }

    function data_preprocessing(&$default_values) {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('scholaris');
            $default_values['scholaris']['format'] = $default_values['contentformat'];
            $default_values['scholaris']['text']   = file_prepare_draft_area($draftitemid, $this->context->id, 'mod_scholaris', 'content', 0, scholaris_get_editor_options($this->context), $default_values['content']);
            $default_values['scholaris']['itemid'] = $draftitemid;
        }
        if (!empty($default_values['displayoptions'])) {
            $displayoptions = unserialize($default_values['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $default_values['printintro'] = $displayoptions['printintro'];
            }
            if (isset($displayoptions['printheading'])) {
                $default_values['printheading'] = $displayoptions['printheading'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $default_values['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $default_values['popupheight'] = $displayoptions['popupheight'];
            }
        }
    }
}

