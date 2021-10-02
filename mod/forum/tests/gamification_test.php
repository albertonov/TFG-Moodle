<?php


class mod_forum_gamification_testcase extends advanced_testcase {

    
    public function test_subjects() {
        global $DB;

        $this->resetAfterTest(true);


        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($user2->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($user3->id, $course->id, 'student');


        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');

        $this->assertEquals( $userexperience, 0);
        $this->assertEquals( $courseexperience, 0);

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user2->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user2->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');

        $this->assertEquals( $userexperience, 0);
        $this->assertEquals( $courseexperience, 0);

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user3->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user3->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');

        $this->assertEquals( $userexperience, 0);
        $this->assertEquals( $courseexperience, 0);



        // Add a discussion.
        $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        // Add a post.
        $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $record['courseid'] = $course->id;

        $this->setUser($user);
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');

        $this->assertEquals( $userexperience, 5);
        $this->assertEquals( $courseexperience, 5);


        $this->setUser($user2);
        $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user2->id;
        $record['courseid'] = $course->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user2->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user2->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');

        $this->assertEquals( $userexperience, 5);
        $this->assertEquals( $courseexperience, 5);



        $this->setUser($user3);
        $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user3->id;
        $record['courseid'] = $course->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $userexperience = $DB->get_field_sql('SELECT totalexperience FROM {user} WHERE id  ='.$user3->id);
        $courseexperience = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user3->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course->id.')');

        $this->assertEquals( $userexperience, 5);
        $this->assertEquals( $courseexperience, 5);



    }
    
}
