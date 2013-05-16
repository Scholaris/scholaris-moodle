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

require_once($CFG->dirroot.'/grade/export/lib.php');
require_once($CFG->libdir.'/filelib.php');

class grade_export_scholariscsv extends grade_export {

	private $studentColumnName = "Student";
	private $emailColumnName = "E-mail";
	private $quizzColumnName = "Nazwa testu";
	private $dateCoulumnName = "Data";
	private $userResultColumnName = "Punkty";
	private $maxResultColumnName = "Max";
	private $userFieldsArray = array("student","email");
    
	public $plugin = 'scholariscsv';
    public $updatedgradesonly = false; // default to export ALL grades

    /**
     * To be implemented by child classes
     * @param boolean $feedback
     * @param boolean $publish Whether to output directly, or send as a file
     * @return string
     */
       public function print_grades($feedback = false) {
        global $CFG;
        require_once($CFG->libdir.'/filelib.php');

        $export_tracking = $this->track_exports();

        $strgrades = get_string('grades');

        /// Calculate file name
        $shortname = format_string($this->course->shortname, true, array('context' => context_course::instance($this->course->id)));
        $downloadfilename = clean_filename("$shortname $strgrades.csv");

        make_temp_directory('gradeexport');
        $tempfilename = $CFG->tempdir .'/gradeexport/'. md5(sesskey().microtime().$downloadfilename);
        if (!$handle = fopen($tempfilename, 'w+b')) {
            print_error('cannotcreatetempdir');
        }

        /// time stamp to ensure uniqueness of batch export
        //fwrite($handle,  'csv_export_'.time()."\n");

		$profilefields = $this->get_user_profile_fields_scholaris($this->userFieldsArray);
		$headerNamesArray = array();
        foreach ($profilefields as $field) {
			$columnName = $this->getColumnName($field->shortname);
			array_push($headerNamesArray, $columnName);
        }
		array_push($headerNamesArray, $this->quizzColumnName);
		array_push($headerNamesArray, $this->dateCoulumnName);
		array_push($headerNamesArray, $this->userResultColumnName);
		array_push($headerNamesArray, $this->maxResultColumnName);
		
		$gradeItems = array();
		foreach ($this->columns as $grade_item) {
			array_push($gradeItems, $this->format_column_name($grade_item));
        }
		fputcsv($handle, $headerNamesArray, ";");
		
        $export_buffer = array();

        $geub = new grade_export_update_buffer();
        $gui = new graded_users_iterator($this->course, $this->columns, $this->groupid);
        $gui->require_active_enrolment($this->onlyactive);
        $gui->init();
        while ($userdata = $gui->next_user()) {
            $user = $userdata->user;
			$i = 0;
            foreach ($userdata->grades as $itemid => $grade) {   //$grade -> class: grade_grade
                if ($export_tracking) {
                    $status = $geub->track($grade);
                }

                /*$gradestr = $this->format_grade($grade);
                if (is_numeric($gradestr)) {
                    $myxls->write_number($i,$j++,$gradestr);
                }
                else {
                    $myxls->write_string($i,$j++,$gradestr);
                }*/
				
				foreach ($profilefields as $field) {
					if($field->shortname == "student")
					{
						$field->shortname = "firstname";
						$fieldvalue = grade_helper::get_user_field_value($user, $field);
						$field->shortname = "lastname";
						$fieldvalue .= " ".grade_helper::get_user_field_value($user, $field);
						$field->shortname = "student";
					}
					else
					{
						$fieldvalue = grade_helper::get_user_field_value($user, $field);
					}
					array_push($export_buffer, $fieldvalue);
				}
				
				array_push($export_buffer, $gradeItems[$i]);
				array_push($export_buffer, $this->get_dategraded($grade));
				
				$gradestr = $this->format_grade($grade);
				array_push($export_buffer, $gradestr);
				
				array_push($export_buffer, $this->format_grade_maxgrade($grade));

                // writing feedback if requested
                /*if ($this->export_feedback) {
                    //fwrite($handle, $this->format_feedback($userdata->feedbacks[$itemid]).',');
					array_push($export_buffer, $this->format_feedback($userdata->feedbacks[$itemid]));
                }*/
				
				fputcsv($handle, $export_buffer, ";");
				$export_buffer = array();
				$i++;
            }
        }
        fclose($handle);
        $gui->close();
        $geub->close();

        @header("Content-type: text/csv; charset=UTF-8");
        send_temp_file($tempfilename, $downloadfilename, false);
	}
	
	   private function get_user_profile_fields_scholaris($fieldsArray) {
		$fields = array();
		
		foreach($fieldsArray as $field)
		{
			$obj = new stdClass();
			$obj->customid  = 0;
			$obj->shortname = $field;
			//$obj->fullname  = get_string($field);
			$fields[] = $obj;
		}

        return $fields;
    }
	
	   private function getColumnName($shortname)
	   {
			if($shortname == "student") return $this->studentColumnName;
			if($shortname == "email") return $this->emailColumnName;
			return "";
	   }
	
	   private function get_dategraded($gradeItem) {
        if (is_null($gradeItem->finalgrade)) {
            return null; // no grade == no date
        } else if ($gradeItem->overridden) {
            return date(DATE_ATOM, $gradeItem->overridden);
        } else {
            return date(DATE_ATOM, $gradeItem->timemodified);
        }
    }
	
	   private function format_grade_maxgrade($grade) {
	   	if (is_null($grade->finalgrade)) {
	   		$grade->rawgrademax = null;
	   	}
	   	return grade_format_gradevalue($grade->rawgrademax, $this->grade_items[$grade->itemid], false, $this->displaytype, $this->decimalpoints);
    }
}

