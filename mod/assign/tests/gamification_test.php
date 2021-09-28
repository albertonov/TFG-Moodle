<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/mod/assign/tests/generator.php');


class mod_assign_gamification_testcase extends advanced_testcase {

    /**
     * Convenience function to create an instance of an assignment.
     *
     * @param array $params Array of parameters to pass to the generator
     * @return assign The assign class.
     */
    protected function create_instance($params = array()) {
        global $DB;
        #print_r($params);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_assign');
        $instance = $generator->create_instance($params);
        $instance->isgamebased = $params['isgamebased'];
        $instance->multiplicadorgb = $params['multiplicadorgb'];

        $DB->update_record('assign',   $instance);
        $cm = get_coursemodule_from_instance('assign', $instance->id);
        $context = \context_module::instance($cm->id);
        $assign = new \assign($context, $cm, $params['course']);
        return $assign;

    }


    protected function create_default_submision($assign, $user ) {


        $submission = new \stdClass();
        $submission->assignment = $assign->get_instance()->id;
        $submission->userid = $user->id;
        $submission->timecreated = time();
        $submission->timemodified = time();
        $submission->onlinetext_editor = ['text' => 'Submission text',
            'format' => FORMAT_MOODLE];

        return  $submission;

    }

    public function test_when_gamebased_is_enabled() {
        global $DB;


        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();

        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $assign = $this->create_instance(
            [
                'course' => $course,
                'name' => 'Assign 1',
                'attemptreopenmethod' => ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL,
                'maxattempts' => 3,
                'assignsubmission_onlinetext_enabled' => true,
                'assignfeedback_comments_enabled' => true,
                'isgamebased' => 1,
                'multiplicadorgb' => 1.50,

            ]
        );


        $submission = new \stdClass();
        $submission->assignment = $assign->get_instance()->id;
        $submission->userid = $user->id;
        $submission->timecreated = time();
        $submission->timemodified = time();
        $submission->onlinetext_editor = ['text' => 'Submission text',
            'format' => FORMAT_MOODLE];

        $this->setUser($user);
        $notices = [];

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');
        $this->assertEquals( $userexperience, 0);
        $this->assertEquals( $courseexperience, 0);

        $assign->save_submission($submission, $notices);

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');

        $this->assertNotEquals( $userexperience, 0);
        $this->assertNotEquals( $courseexperience, 0);

    }



    public function test_when_gamebased_is_not_enabled() {
        global $DB;


        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();

        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $assign = $this->create_instance(
            [
                'course' => $course,
                'name' => 'Assign 1',
                'attemptreopenmethod' => ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL,
                'maxattempts' => 3,
                'assignsubmission_onlinetext_enabled' => true,
                'assignfeedback_comments_enabled' => true,
                'isgamebased' => 0,
                'multiplicadorgb' => 1.00,

            ]
        );


        $submission = new \stdClass();
        $submission->assignment = $assign->get_instance()->id;
        $submission->userid = $user->id;
        $submission->timecreated = time();
        $submission->timemodified = time();
        $submission->onlinetext_editor = ['text' => 'Submission text',
            'format' => FORMAT_MOODLE];

        $this->setUser($user);
        $notices = [];

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');
        $this->assertEquals( $userexperience, 0);
        $this->assertEquals( $courseexperience, 0);

        $assign->save_submission($submission, $notices);

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');
        $this->assertEquals( $userexperience, 0);
        $this->assertEquals( $courseexperience, 0);

    }


    public function test_difficult_levels() {
        global $DB;


        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();

        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $easyassign = $this->create_instance(
            [
                'course' => $course,
                'name' => ' Easy Assign 1',
                'attemptreopenmethod' => ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL,
                'maxattempts' => 3,
                'assignsubmission_onlinetext_enabled' => true,
                'assignfeedback_comments_enabled' => true,
                'isgamebased' => 1,
                'multiplicadorgb' => 0.75,

            ]
        );


        $normalassign = $this->create_instance(
            [
                'course' => $course,
                'name' => 'Medium Assign 1',
                'attemptreopenmethod' => ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL,
                'maxattempts' => 3,
                'assignsubmission_onlinetext_enabled' => true,
                'assignfeedback_comments_enabled' => true,
                'isgamebased' => 1,
                'multiplicadorgb' => 1.00,

            ]
        );


        $hardassign = $this->create_instance(
            [
                'course' => $course,
                'name' => 'Hard Assign 1',
                'attemptreopenmethod' => ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL,
                'maxattempts' => 3,
                'assignsubmission_onlinetext_enabled' => true,
                'assignfeedback_comments_enabled' => true,
                'isgamebased' => 1,
                'multiplicadorgb' => 1.50,

            ]
        );

        $hardestassig = $this->create_instance(
            [
                'course' => $course,
                'name' => 'Difficult Assign ',
                'attemptreopenmethod' => ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL,
                'maxattempts' => 3,
                'assignsubmission_onlinetext_enabled' => true,
                'assignfeedback_comments_enabled' => true,
                'isgamebased' => 1,
                'multiplicadorgb' => 1.75,

            ]
        );


        $submission1 =  $this->create_default_submision($easyassign, $user );
        $submission2 =  $this->create_default_submision($normalassign, $user );
        $submission3 =  $this->create_default_submision($hardassign, $user );
        $submission4 =  $this->create_default_submision($hardestassig, $user );


        $this->setUser($user);
        $notices = [];

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');
        $this->assertEquals( $userexperience, 0);
        $this->assertEquals( $courseexperience, 0);



        $easyassign->save_submission($submission1, $notices);

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');
        $this->assertEquals( $userexperience, 11);
        $this->assertEquals( $courseexperience, 11);



        $normalassign->save_submission($submission2, $notices);

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');
        $this->assertEquals( $userexperience, 11+15);
        $this->assertEquals( $courseexperience, 11+15);




        $hardassign->save_submission($submission3, $notices);

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');
        $this->assertEquals( $userexperience, 11+15+23);
        $this->assertEquals( $courseexperience, 11+15+23);


        $hardestassig->save_submission($submission4, $notices);

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');
        $this->assertEquals( $userexperience, 11+15+23+26);
        $this->assertEquals( $courseexperience, 11+15+23+26);


    }


    public function test_different_experience() {
        global $DB;


        $this->resetAfterTest(true);

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();

        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course1->id, 'student');
        $this->getDataGenerator()->enrol_user($user->id, $course2->id, 'student');

        $teacher1 = $this->getDataGenerator()->create_user();
        $teacher2 = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user($teacher1->id, $course1->id, 'editingteacher');
        $this->getDataGenerator()->enrol_user($teacher2->id, $course2->id, 'editingteacher');

        $assign1 = $this->create_instance(
            [
                'course' => $course1,
                'name' => 'Assign Course 1',
                'attemptreopenmethod' => ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL,
                'maxattempts' => 3,
                'assignsubmission_onlinetext_enabled' => true,
                'assignfeedback_comments_enabled' => true,
                'isgamebased' => 1,
                'multiplicadorgb' => 1.00,

            ]
        );


        $assign2 = $this->create_instance(
            [
                'course' => $course2,
                'name' => 'Assign Course 2',
                'attemptreopenmethod' => ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL,
                'maxattempts' => 3,
                'assignsubmission_onlinetext_enabled' => true,
                'assignfeedback_comments_enabled' => true,
                'isgamebased' => 1,
                'multiplicadorgb' => 1.00,

            ]
        );


        $submission1 =  $this->create_default_submision($assign1, $user );
        $submission2 =  $this->create_default_submision($assign2, $user );


        $this->setUser($user);
        $notices = [];

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience1 = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course1->id.')');
        $courseexperience2 = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course2->id.')');

        $this->assertEquals( $userexperience, 0);
        $this->assertEquals( $courseexperience1, 0);
        $this->assertEquals( $courseexperience2, 0);

        $assign1->save_submission($submission1, $notices);

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience1 = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course1->id.')');
        $courseexperience2 = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course2->id.')');

        $this->assertEquals( $userexperience, 15);
        $this->assertEquals( $courseexperience1, 15);
        $this->assertEquals( $courseexperience2, 0);


        $assign2->save_submission($submission2, $notices);

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience1 = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course1->id.')');
        $courseexperience2 = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course2->id.')');

        $this->assertEquals( $userexperience, 30);
        $this->assertEquals( $courseexperience1, 15);
        $this->assertEquals( $courseexperience2, 15);

    }


    public function test_team_gamebased_assign() {
        global $DB;


        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();

        $user1 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user1->id, $course->id, 'student');
        $user2 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user2->id, $course->id, 'student');
        $user3 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user3->id, $course->id, 'student');

        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $assign = $this->create_instance(
            [
                'course' => $course,
                'name' => 'Assign 1',
                'attemptreopenmethod' => ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL,
                'maxattempts' => 3,
                'assignsubmission_onlinetext_enabled' => true,
                'assignfeedback_comments_enabled' => true,
                'isgamebased' => 1,
                'multiplicadorgb' => 1.00,
                'teamsubmission' => 1,

            ]
        );


        $submission1 =  $this->create_default_submision($assign, $user1 );
        $submission2 =  $this->create_default_submision($assign, $user2 );
        $submission3 =  $this->create_default_submision($assign, $user3 );


        $this->setUser($user1);
        $notices = [];

        $userexperience1 = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user1->id);
        $courseexperience1 = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user1->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');

        $userexperience2 = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user2->id);
        $courseexperience2 = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user2->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');
        
        $userexperience3 = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user3->id);
        $courseexperience3 = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user3->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');
               
        
        $this->assertEquals( $userexperience1, 0);
        $this->assertEquals( $courseexperience1, 0);
        $this->assertEquals( $userexperience2, 0);
        $this->assertEquals( $courseexperience2, 0);
        $this->assertEquals( $userexperience3, 0);
        $this->assertEquals( $courseexperience3, 0);

        $assign->save_submission($submission1, $notices);

        $userexperience1 = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user1->id);
        $courseexperience1 = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user1->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');

        $userexperience2 = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user2->id);
        $courseexperience2 = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user2->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');
        
        $userexperience3 = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user3->id);
        $courseexperience3 = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user3->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');
               
        

        $this->assertEquals( $userexperience1, 15);
        $this->assertEquals( $courseexperience1, 15);

        $this->assertEquals( $userexperience2, 15);
        $this->assertEquals( $courseexperience2, 15);
        $this->assertEquals( $userexperience3, 15);
        $this->assertEquals( $courseexperience3, 15);
    }
    
    public function test_try_multiple_save_submission_assign() {
        global $DB;


        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();

        $user1 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user1->id, $course->id, 'student');

        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $assign = $this->create_instance(
            [
                'course' => $course,
                'name' => 'Assign 1',
                'attemptreopenmethod' => ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL,
                'maxattempts' => 3,
                'assignsubmission_onlinetext_enabled' => true,
                'assignfeedback_comments_enabled' => true,
                'isgamebased' => 1,
                'multiplicadorgb' => 1.00,
            ]
        );


        $submission1 =  $this->create_default_submision($assign, $user1 );


        $this->setUser($user1);
        $notices = [];

        $userexperience1 = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user1->id);
        $courseexperience1 = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user1->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');

        
        $this->assertEquals( $userexperience1, 0);
        $this->assertEquals( $courseexperience1, 0);

        $assign->save_submission($submission1, $notices);

        $userexperience1 = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user1->id);
        $courseexperience1 = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user1->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');

        $this->assertEquals( $userexperience1, 15);
        $this->assertEquals( $courseexperience1, 15);

        for ($i = 1; $i <= 10; $i++) {
            $assign->save_submission($submission1, $notices);
            $userexperience1 = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user1->id);
            $courseexperience1 = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user1->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');
    
            $this->assertEquals( $userexperience1, 15);
            $this->assertEquals( $courseexperience1, 15);
        }




    }



        public function get_assign_submission_status_renderable($user, $showlinks) {
        global $PAGE;

        $instance = $this->get_instance();
        $flags = $this->get_user_flags($user->id, false);
        $submission = $this->get_user_submission($user->id, false);

        $teamsubmission = null;
        $submissiongroup = null;
        $notsubmitted = array();
        if ($instance->teamsubmission) {
            $teamsubmission = $this->get_group_submission($user->id, 0, false);
            $submissiongroup = $this->get_submission_group($user->id);
            $groupid = 0;
            if ($submissiongroup) {
                $groupid = $submissiongroup->id;
            }
            $notsubmitted = $this->get_submission_group_members_who_have_not_submitted($groupid, false);
        }

        $showedit = $showlinks &&
                    ($this->is_any_submission_plugin_enabled()) &&
                    $this->can_edit_submission($user->id);

        $submissionlocked = ($flags && $flags->locked);

        // Grading criteria preview.
        $gradingmanager = get_grading_manager($this->context, 'mod_assign', 'submissions');
        $gradingcontrollerpreview = '';
        if ($gradingmethod = $gradingmanager->get_active_method()) {
            $controller = $gradingmanager->get_controller($gradingmethod);
            if ($controller->is_form_defined()) {
                $gradingcontrollerpreview = $controller->render_preview($PAGE);
            }
        }

        $showsubmit = ($showlinks && $this->submissions_open($user->id));
        $showsubmit = ($showsubmit && $this->show_submit_button($submission, $teamsubmission, $user->id));

        $extensionduedate = null;
        if ($flags) {
            $extensionduedate = $flags->extensionduedate;
        }
        $viewfullnames = has_capability('moodle/site:viewfullnames', $this->get_context());
        
        $gradingstatus = $this->get_grading_status($user->id);
        $usergroups = $this->get_all_groups($user->id);
        $submissionstatus = new assign_submission_status($instance->allowsubmissionsfromdate,
                                                          $instance->alwaysshowdescription,
                                                          $submission,
                                                          $instance->teamsubmission,
                                                          $teamsubmission,
                                                          $submissiongroup,
                                                          $notsubmitted,
                                                          $this->is_any_submission_plugin_enabled(),
                                                          $submissionlocked,
                                                          $this->is_graded($user->id),
                                                          $instance->duedate,
                                                          $instance->cutoffdate,
                                                          $this->get_submission_plugins(),
                                                          $this->get_return_action(),
                                                          $this->get_return_params(),
                                                          $this->get_course_module()->id,
                                                          $this->get_course()->id,
                                                          assign_submission_status::STUDENT_VIEW,
                                                          $showedit,
                                                          $showsubmit,
                                                          $viewfullnames,
                                                          $extensionduedate,
                                                          $this->get_context(),
                                                          $this->is_blind_marking(),
                                                          $gradingcontrollerpreview,
                                                          $instance->attemptreopenmethod,
                                                          $instance->maxattempts,
                                                          $gradingstatus,
                                                          $instance->preventsubmissionnotingroup,
                                                          $usergroups,
                                                          $instance->timeestimated);
        return $submissionstatus;
    }

}
