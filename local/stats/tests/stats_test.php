<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/mod/assign/tests/generator.php');


class local_stats_test_testcase extends advanced_testcase {




    public function test_true() {
        global $DB;


        $this->resetAfterTest(true);

        $this->assertNotEquals( true, false);

    }



    #simulate conexions and comprove, comporvoe

    
    
}
