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
 * class block_recent_activity
 *
 * @package    block_recent_activity
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/course/lib.php');

/**
 * class block_recent_activity
 *
 * @package    block_recent_activity
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_challenger_question extends block_base {

    /**
     * Use {@link block_recent_activity::get_timestart()} to access
     *
     * @var int stores the time since when we want to show recent activity
     */
    protected $timestart = null;
    protected $hascategory = true;

    /**
     * Initialises the block
     */
    function init() {
        $this->title = 'Reto del dia';
    }
    function set_first_question() {
        
        global $DB, $COURSE;
            $exists = $DB->record_exists_sql('SELECT id FROM {challenger_question} WHERE course ='.$COURSE->id);
            if(!$exists) {
                #print_r('no existe primera tarea');
    
                $context = context_course::instance($COURSE->id);
    
                $existscategory = $DB->record_exists_sql("SELECT * FROM {question_categories} WHERE contextid =".$context->id."and name = 'CHALLENGER'");
                if($existscategory) {
                    #print_r(' existe la categoria');
                    $this->hascategory =true;
                    $category = $DB->get_field_sql("SELECT id FROM {question_categories} WHERE contextid =".$context->id."and name = 'CHALLENGER'");
                    $bank = $DB->get_records_sql("SELECT id FROM {question} WHERE qtype='truefalse' and category =".$category);
                    #print_r($bank);
                    $idarray = array_rand( $bank, $num = 1);
                    $challenger_question = new \stdClass();
                    $challenger_question->course = $COURSE->id;
                    $challenger_question->idquestion = $bank[$idarray]->id;
    
                    $DB->insert_record('challenger_question', $challenger_question);
                }
                else{
                   # print_r('NO existe la categoria');
                    $this->hascategory =false;
                }
            }




    }


    /**
     * Returns the content object
     *
     * @return stdObject
     */

    public function get_content() {
        global $course;
        global $USER;

        $this-> set_first_question();  

        if($this->hascategory  == false) {
    
            $this->content = new stdClass();
            $this->content->text = 'Añade preguntas en el banco';
            $this->content->footer = '';
        }
        else{
            $renderable = new \block_challenger_question\output\challenger_question($this->config);
            $renderer = $this->page->get_renderer('block_challenger_question');
    
            $this->content = new stdClass();
            $this->content->text = $renderer->render($renderable);
            $this->content->footer = '';
        }

        return $this->content;
    }

    
}

