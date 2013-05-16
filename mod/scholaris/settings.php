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
 * Scholaris module admin settings and defaults
 *
 * @package    mod
 * @subpackage scholaris
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_OPEN, RESOURCELIB_DISPLAY_POPUP));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_OPEN);

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configcheckbox('scholaris/requiremodintro',
        get_string('requiremodintro', 'admin'), get_string('configrequiremodintro', 'admin'), 1));
    $settings->add(new admin_setting_configmultiselect('scholaris/displayoptions',
        get_string('displayoptions', 'scholaris'), get_string('configdisplayoptions', 'scholaris'),
        $defaultdisplayoptions, $displayoptions));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('scholarismodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox_with_advanced('scholaris/printheading',
        get_string('printheading', 'scholaris'), get_string('printheadingexplain', 'scholaris'),
        array('value'=>1, 'adv'=>false)));
    $settings->add(new admin_setting_configcheckbox_with_advanced('scholaris/printintro',
        get_string('printintro', 'scholaris'), get_string('printintroexplain', 'scholaris'),
        array('value'=>0, 'adv'=>false)));
    $settings->add(new admin_setting_configselect_with_advanced('scholaris/display',
        get_string('displayselect', 'scholaris'), get_string('displayselectexplain', 'scholaris'),
        array('value'=>RESOURCELIB_DISPLAY_OPEN, 'adv'=>true), $displayoptions));
    $settings->add(new admin_setting_configtext_with_advanced('scholaris/popupwidth',
        get_string('popupwidth', 'scholaris'), get_string('popupwidthexplain', 'scholaris'),
        array('value'=>620, 'adv'=>true), PARAM_INT, 7));
    $settings->add(new admin_setting_configtext_with_advanced('scholaris/popupheight',
        get_string('popupheight', 'scholaris'), get_string('popupheightexplain', 'scholaris'),
        array('value'=>450, 'adv'=>true), PARAM_INT, 7));
    //--- scholaris settings ---------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('scholaris/scholarisheading', '<br>Scholaris - ustawienia', ''));
    $settings->add(new admin_setting_configtext('scholaris/email',
        get_string('email', 'scholaris'), get_string('email', 'scholaris'),
        array('value'=>1), PARAM_TEXT, 40));
	$settings->add(new admin_setting_configpasswordunmask('scholaris/token',
        get_string('token', 'scholaris'), get_string('token', 'scholaris'),
        array('value'=>''), PARAM_TEXT, 40));
	
	$markup = '<fieldset style="border:0px; padding:1em; margin-top:2em">
	<legend>Zasoby Scholaris</legend>
	<div id="j-sidebar-container" class="span2" style="margin-bottom:1em">
		<div id="sidebar">
			<div class="sidebar-nav">
				<ul id="submenu" class="nav nav-list">
					<li id="nav_search">
						<a id="id1" onclick="linkOneOnClick()" href="http://creator.staging.scht.pl/external/search/email/" target="scholaristarget">Wyszukiwarka zasobów portalu</a>
					</li>
					<li id="nav_user">
						<a id="id2" onclick="linkTwoOnClick()" href="http://creator.staging.scht.pl/external/publishlist/email/" target="scholaristarget">Moje e-zasoby</a>
					</li>
				</ul>
			</div>
		</div>
	</div>

	<div>
		<iframe id="testid" name="scholaristarget" width="900px;" height="800px;" style="border:none;"></iframe>
	</div>
  	</fieldset></head>';
	
	$script = '<script type="text/javascript">
			 //<![CDATA[
			
			function linkOneOnClick()
			{
				var email = document.getElementById("id_s_scholaris_email").value;
				document.getElementById("id1").setAttribute("href", "http://creator.staging.scht.pl/external/search/email/" + email);
			}
  
			function linkTwoOnClick()
			{
				var email = document.getElementById("id_s_scholaris_email").value;
				document.getElementById("id2").setAttribute("href", "http://creator.staging.scht.pl/external/publishlist/email/" + email);
			}
			//]]>
			</script>';
	$settings->add(new admin_setting_heading('scholaris/heading', $script, $markup));
}
