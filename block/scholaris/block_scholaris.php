<?php

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/filelib.php');

class block_scholaris extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_scholaris');
    }

    function get_content() {
        global $CFG, $DB, $OUTPUT;
        
        if ($this->content !== null) {
        	return $this->content;
        }
        
        $this->content         =  new stdClass;
        $this->content->text   = '<a href="'.$CFG->wwwroot.'/blocks/scholaris/scorm.php?contextid='.$this->context->id.'">Lista zasobów</a>';
        $this->content->footer = '';
        
        return $this->content;
    }

    /**
     * Returns the role that best describes this blocks contents.
     *
     * @return string 'navigation'
     */
    public function get_aria_role() {
        return 'navigation';
    }

    
    /**
     * Set the applicable formats for this block to all
     * @return array
     */
    function applicable_formats() {
        return array('all' => true);
    }
}


