<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

/**
 * Tests for the quiz_attempt class.
 *
 * @copyright 2014 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_quiz_gamification_testcase extends advanced_testcase {

    protected function create_instance($params = array()) {
        global $DB;
        #print_r($params);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $instance = $generator->create_instance($params);
        $instance->isgamebased = $params['isgamebased'];
        $instance->multiplicadorgb = $params['multiplicadorgb'];

        $DB->update_record('quiz',   $instance);
        $cm = get_coursemodule_from_instance('quiz', $instance->id);
        $context = \context_module::instance($cm->id);
      #  $quiz = new \quiz($context, $cm, $params['course']);
        return $instance;

    }


    protected function init_and_complete_quiz($quiz,$user ) {
        #based on create_quiz_with_questions function

        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        
        $cat = $questiongenerator->create_question_category();
        $saq = $questiongenerator->create_question('shortanswer', null, array('category' => $cat->id));
        $numq = $questiongenerator->create_question('numerical', null, array('category' => $cat->id));
        
        quiz_add_quiz_question($saq->id, $quiz);
        quiz_add_quiz_question($numq->id, $quiz);
        
        $quizobj = quiz::create($quiz->id, $user->id);


        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
        $timenow = time();
        $attempt = quiz_create_attempt($quizobj, 1, false, $timenow, false, $user->id);


        #start one attempt
             
        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);
        #$this->assertEquals('1,2,0', $attempt->layout);
        
        quiz_attempt_save_started($quizobj, $quba, $attempt);


        $attemptobj = quiz_attempt::create($attempt->id);
        $this->assertFalse($attemptobj->has_response_to_at_least_one_graded_question());
        
        $prefix1 = $quba->get_field_prefix(1);
        $prefix2 = $quba->get_field_prefix(2);
        
        $tosubmit = array(1 => array('answer' => 'frog'),
            2 => array('answer' => '3.14'));
            $attemptobj->process_submitted_actions($timenow, false, $tosubmit);
        
        // Finish the attempt.
        $attemptobj = quiz_attempt::create($attempt->id);
        $this->assertTrue($attemptobj->has_response_to_at_least_one_graded_question());
        $attemptobj->process_finish($timenow, false);
        return  $quizobj;
    }



    public function test_attempt_with_gamebased_enabled() {
        global $DB;
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->create_instance(
            array(
            'course' => $course->id, 
            'questionsperpage' => 2, 
            'grade' => 100.0,
            'sumgrades' => 4,
            'isgamebased' => 1,
            'multiplicadorgb' => 1.750


        ));

        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        $this->setUser($user);

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');

        $this->assertEquals( $userexperience, 0);
        $this->assertEquals( $courseexperience, 0);


        
        $this->init_and_complete_quiz($quiz, $user);
        
                


        
        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');

        $this->assertEquals( $userexperience, 18);
        $this->assertEquals( $courseexperience, 18);
        

    }


    public function test_attempt_with_gamebased_not_enabled() {
        global $DB;
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->create_instance(
            array(
            'course' => $course->id, 
            'questionsperpage' => 2, 
            'grade' => 100.0,
            'sumgrades' => 4,
            'isgamebased' => 0,
            'multiplicadorgb' => 1.750


        ));

        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        $this->setUser($user);

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');

        $this->assertEquals( $userexperience, 0);
        $this->assertEquals( $courseexperience, 0);
        
        $this->init_and_complete_quiz($quiz, $user);
        
        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');

        $this->assertEquals( $userexperience, 0);
        $this->assertEquals( $courseexperience, 0);
        

    }



    public function test_try_multiply_attempts() {
        global $DB;
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->create_instance(
            array(
            'course' => $course->id, 
            'questionsperpage' => 2, 
            'grade' => 100.0,
            'sumgrades' => 4,
            'isgamebased' => 1,
            'multiplicadorgb' => 1.750


        ));

        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        $this->setUser($user);

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');

        $this->assertEquals( $userexperience, 0);
        $this->assertEquals( $courseexperience, 0);
        
        $quizobj = $this->init_and_complete_quiz($quiz, $user);


        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');

        $this->assertEquals( $userexperience, 18);
        $this->assertEquals( $courseexperience, 18);



        #simulate 10 attempts
        for ($i = 2; $i <= 10; $i++) {
            $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
            $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
            $timenow = time();
            $attempt = quiz_create_attempt($quizobj, $i, false, $timenow, false, $user->id);
            quiz_start_new_attempt($quizobj, $quba, $attempt, $i, $timenow);
            quiz_attempt_save_started($quizobj, $quba, $attempt);



            $attemptobj = quiz_attempt::create($attempt->id);
            $this->assertFalse($attemptobj->has_response_to_at_least_one_graded_question());
            
            $prefix1 = $quba->get_field_prefix(1);
            $prefix2 = $quba->get_field_prefix(2);
            
            $tosubmit = array(1 => array('answer' => 'frog'),
                2 => array('answer' => '3.14'));
                $attemptobj->process_submitted_actions($timenow, false, $tosubmit);
            
            // Finish the attempt.
            $attemptobj = quiz_attempt::create($attempt->id);
            $this->assertTrue($attemptobj->has_response_to_at_least_one_graded_question());
            $attemptobj->process_finish($timenow, false);

            $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
            $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');
    
            $this->assertEquals( $userexperience, 18);
            $this->assertEquals( $courseexperience, 18);
            
        }
        $this->assertEquals( $courseexperience, 18);


        $recordsattempts = $DB->get_records_sql('SELECT * FROM {quiz_attempts} WHERE userid  ='.$user->id.' and quiz  ='.$quiz->id);

        $this->assertEquals(count($recordsattempts), 10);

        foreach ($recordsattempts as &$record) {
            $this->assertEquals($record->state, 'finished');
        }
    }

}