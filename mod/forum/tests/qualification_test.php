<?php


defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/forum/lib.php');

class mod_forum_qualification_testcase extends externallib_advanced_testcase {

    /**
     * Tests set up
     */
    protected function setUp(): void {
        global $CFG;

        // We must clear the subscription caches. This has to be done both before each test, and after in case of other
        // tests using these functions.
        \mod_forum\subscriptions::reset_forum_cache();

        require_once($CFG->dirroot . '/mod/forum/externallib.php');
    }

    public function tearDown(): void {
        // We must clear the subscription caches. This has to be done both before each test, and after in case of other
        // tests using these functions.
        \mod_forum\subscriptions::reset_forum_cache();
    }


    public function test_mod_forum_check_qual_urls() {
        // Based on test_mod_forum_get_discussion_posts_by_userid in externallib
        global $CFG;

        $this->resetAfterTest(true);

        // Set the CFG variable to allow track forums.
        $CFG->forum_trackreadposts = true;


        // Create course to add the module.
        $course1 = self::getDataGenerator()->create_course();

        // Create a user who can track forums.
        $record = new stdClass();
        $record->trackforums = true;
        $user1 = self::getDataGenerator()->create_user($record);
        $user2 = self::getDataGenerator()->create_user();

        $forumgenerator = self::getDataGenerator()->get_plugin_generator('mod_forum');

        // Set the first created user to the test user.
        self::setUser($user1);

        // Forum with tracking off.
        $record = new stdClass();
        $record->course = $course1->id;
        $record->trackingtype = FORUM_TRACKING_OFF;
        // Display word count. Otherwise, word and char counts will be set to null by the forum post exporter.
        $record->displaywordcount = true;
        $forum1 = self::getDataGenerator()->create_module('forum', $record);
        $forum1context = context_module::instance($forum1->cmid);


        // Add the discussion
        $record = new stdClass();
        $record->course = $course1->id;
        $record->userid = $user1->id;
        $record->forum = $forum1->id;
        $discussion1 = $forumgenerator->create_discussion($record);


        // Add 1 reply
        $record = new stdClass();
        $record->discussion = $discussion1->id;
        $record->parent = $discussion1->firstpost;
        $record->userid = $user2->id;
        $post =$forumgenerator->create_post($record);

        $expectedcalificatepositiveurl = 'https://www.example.com/moodle/mod/forum/post.php?idpost='. $post->id.'&amp;calificate=positive';
        $expectedcalificatelikeurl = 'https://www.example.com/moodle/mod/forum/post.php?idpost='. $post->id.'&amp;calificate=like';
        $expectedcalificatenegativeurl = 'https://www.example.com/moodle/mod/forum/post.php?idpost='. $post->id.'&amp;calificate=negative';


        $urlfactory = mod_forum\local\container::get_url_factory();
        $entityfactory = mod_forum\local\container::get_entity_factory();
        $postentity = $entityfactory->get_post_from_stdclass($post);


        $calificatepositiveurl = $urlfactory->get_calificate_url_from_post($postentity, 'positive');
        $calificatelikeurl = $urlfactory->get_calificate_url_from_post($postentity, 'like');
        $calificatenegativeurl = $urlfactory->get_calificate_url_from_post($postentity, 'negative');

        $this->assertEquals( $expectedcalificatepositiveurl, $calificatepositiveurl->__toString());
        $this->assertEquals( $expectedcalificatelikeurl, $calificatelikeurl->__toString());
        $this->assertEquals( $expectedcalificatenegativeurl, $calificatenegativeurl->__toString());


    }

    public function test_mod_forum_basic_qualification() {
        // Based on test_mod_forum_get_discussion_posts_by_userid in externallib
        global $CFG;

        $this->resetAfterTest(true);

        // Set the CFG variable to allow track forums.
        $CFG->forum_trackreadposts = true;

        $urlfactory = mod_forum\local\container::get_url_factory();
        $legacyfactory = mod_forum\local\container::get_legacy_data_mapper_factory();
        $entityfactory = mod_forum\local\container::get_entity_factory();

        // Create course to add the module.
        $course1 = self::getDataGenerator()->create_course();

        // Create a user who can track forums.
        $record = new stdClass();
        $record->trackforums = true;
        $user1 = self::getDataGenerator()->create_user($record);
        // Create a bunch of other users to post.
        $user2 = self::getDataGenerator()->create_user();
        $user2entity = $entityfactory->get_author_from_stdclass($user2);
        $exporteduser2 = [
            'id' => (int) $user2->id,
            'fullname' => fullname($user2),
            'isdeleted' => false,
            'groups' => [],
            'urls' => [
                'profile' => $urlfactory->get_author_profile_url($user2entity, $course1->id)->out(false),
                'profileimage' => $urlfactory->get_author_profile_image_url($user2entity),
            ]
        ];
        $user2->fullname = $exporteduser2['fullname'];


        $forumgenerator = self::getDataGenerator()->get_plugin_generator('mod_forum');

        // Set the first created user to the test user.
        self::setUser($user1);

        // Forum with tracking off.
        $record = new stdClass();
        $record->course = $course1->id;
        $record->trackingtype = FORUM_TRACKING_OFF;
        // Display word count. Otherwise, word and char counts will be set to null by the forum post exporter.
        $record->displaywordcount = true;
        $forum1 = self::getDataGenerator()->create_module('forum', $record);
        $forum1context = context_module::instance($forum1->cmid);


        // Add the discussion
        $record = new stdClass();
        $record->course = $course1->id;
        $record->userid = $user1->id;
        $record->forum = $forum1->id;
        $discussion1 = $forumgenerator->create_discussion($record);


        // Add 1 reply
        $record = new stdClass();
        $record->discussion = $discussion1->id;
        $record->parent = $discussion1->firstpost;
        $record->userid = $user2->id;
        $discussion1reply1 = $forumgenerator->create_post($record);
        $filename = 'shouldbeanimage.jpg';
        $filerecordinline = array(
            'contextid' => $forum1context->id,
            'component' => 'mod_forum',
            'filearea'  => 'post',
            'itemid'    => $discussion1reply1->id,
            'filepath'  => '/',
            'filename'  => $filename,
        );
        $fs = get_file_storage();
        $timepost = time();
        $fs->create_file_from_string($filerecordinline, 'image contents (not really)');





        // Enrol the user in the  course.
        $enrol = enrol_get_plugin('manual');
        // Following line enrol and assign default role id to the user.
        // So the user automatically gets mod/forum:viewdiscussion on all forums of the course.
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id);



        // Create the qualification
        $record = new stdClass();
        $record->id_post = $discussion1reply1->id;
        $record->id_user = $user1->id;
        $record->qual = 'positive';
        $record->discussion = $discussion1->id;
        $record->courseid = $course1->id;
        $record->userwhoqualificate = $user1->id;
        $record->userqualificated = $user2->id;

        $qual = $forumgenerator->create_qual($record);


        // Create what we expect to be returned when querying the discussion.
        $expectedposts = array(
            'posts' => array(),
            'courseid' => $course1->id,
            'forumid' => $forum1->id,
            'ratinginfo' => array(
                'contextid' => $forum1context->id,
                'component' => 'mod_forum',
                'ratingarea' => 'post',
                'canviewall' => null,
                'canviewany' => null,
                'scales' => array(),
                'ratings' => array(),
            ),
            'warnings' => array(),
        );



        $isolatedurl = $urlfactory->get_discussion_view_url_from_discussion_id($discussion1reply1->discussion);
        $isolatedurl->params(['parent' => $discussion1reply1->id]);
        $message = file_rewrite_pluginfile_urls($discussion1reply1->message, 'pluginfile.php',
            $forum1context->id, 'mod_forum', 'post', $discussion1reply1->id);
        $expectedposts['posts'][] = array(
            'id' => $discussion1reply1->id,
            'discussionid' => $discussion1reply1->discussion,
            'parentid' => $discussion1reply1->parent,
            'hasparent' => true,
            'timecreated' => $discussion1reply1->created,
            'subject' => $discussion1reply1->subject,
            'replysubject' => get_string('re', 'mod_forum') . " {$discussion1reply1->subject}",
            'message' => $message,
            'messageformat' => 1,   // This value is usually changed by external_format_text() function.
            'unread' => null,
            'isdeleted' => false,
            'isprivatereply' => false,
            'haswordcount' => true,
            'wordcount' => count_words($message),
            'charcount' => count_letters($message),
            'author'=> $exporteduser2,
            'attachments' => [],
            'tags' => [],
            'html' => [
                'rating' => null,
                'taglist' => null,
                'authorsubheading' => $forumgenerator->get_author_subheading_html((object)$exporteduser2, $discussion1reply1->created)
            ],
            'capabilities' => [
                'view' => 1,
                'edit' => 0,
                'delete' => 0,
                'split' => 0,
                'reply' => 1,
                'export' => 0,
                'controlreadstatus' => 0,
                'canreplyprivately' => 0,
                'selfenrol' => 0
            ],
            'urls' => [
                'view' => $urlfactory->get_view_post_url_from_post_id($discussion1reply1->discussion, $discussion1reply1->id),
                'viewisolated' => $isolatedurl->out(false),
                'viewparent' => $urlfactory->get_view_post_url_from_post_id($discussion1reply1->discussion, $discussion1reply1->parent),
                'edit' => null,
                'delete' =>null,
                'split' => null,
                'reply' => (new moodle_url('/mod/forum/post.php#mformforum', [
                    'reply' => $discussion1reply1->id
                ]))->out(false),
                'export' => null,
                'markasread' => null,
                'markasunread' => null,
                'discuss' => $urlfactory->get_discussion_view_url_from_discussion_id($discussion1reply1->discussion),
            ],
            'hasnegativequal' => false,
            'haspositivequal' => true,
            'haslikequal' => false,
            'hasemptyqual' => false,
            'numberqual' => 1,
            'user1' => Array
            (
                'id' =>  $user1->id,
                'name' =>  $user1->firstname.' '. $user1->lastname,
                'show' => true,
            ),
            'user2' => Array
            (
                'id' => null,
                'name' => null,
                'show' => null,
            ),
            'restofuser' => Array
            (
                'lista' => null,
                'numero' => null,
                'show' => null,
            ),
            'course'=> strval($course1->id),
            'isgoodquestion' => false,
            'isanomreply' => false


        );

        $posts = mod_forum_external::get_discussion_posts($discussion1->id, 'modified', 'DESC');
        $posts = external_api::clean_returnvalue(mod_forum_external::get_discussion_posts_returns(), $posts);
        $this->assertEquals(2, count($posts['posts']));

        // Unset the initial discussion post.
        array_pop($posts['posts']);
        $this->assertEquals($expectedposts, $posts);

    }



    public function test_mod_forum_experience_qualification() {
        // Based on test_mod_forum_get_discussion_posts_by_userid in externallib
        global $CFG, $DB;

        $this->resetAfterTest(true);

        // Set the CFG variable to allow track forums.
        $CFG->forum_trackreadposts = true;

        // Create course to add the module.
        $course1 = self::getDataGenerator()->create_course();

        // Create a user who can track forums.
        $record = new stdClass();
        $record->trackforums = true;
        $user1 = self::getDataGenerator()->create_user($record);
        $user2 = self::getDataGenerator()->create_user();

        $forumgenerator = self::getDataGenerator()->get_plugin_generator('mod_forum');

        // Set the first created user to the test user.
        self::setUser($user1);

        // Forum with tracking off.
        $record = new stdClass();
        $record->course = $course1->id;
        $record->trackingtype = FORUM_TRACKING_OFF;
        // Display word count. Otherwise, word and char counts will be set to null by the forum post exporter.
        $record->displaywordcount = true;
        $forum1 = self::getDataGenerator()->create_module('forum', $record);
        $forum1context = context_module::instance($forum1->cmid);


        // Add the discussion
        $record = new stdClass();
        $record->course = $course1->id;
        $record->userid = $user1->id;
        $record->forum = $forum1->id;
        $discussion1 = $forumgenerator->create_discussion($record);


        // Add 1 reply
        $record = new stdClass();
        $record->discussion = $discussion1->id;
        $record->parent = $discussion1->firstpost;
        $record->userid = $user2->id;
        $discussion1reply1 = $forumgenerator->create_post($record);




        // Enrol the user in the  course.
        $enrol = enrol_get_plugin('manual');
        // Following line enrol and assign default role id to the user.
        // So the user automatically gets mod/forum:viewdiscussion on all forums of the course.
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id);



        // Create the qualification
        $record = new stdClass();
        $record->id_post = $discussion1reply1->id;
        $record->id_user = $user1->id;
        $record->qual = 'positive';
        $record->discussion = $discussion1->id;
        $record->courseid = $course1->id;
        $record->userwhoqualificate = $user1->id;
        $record->userqualificated = $user2->id;

        $qual = $forumgenerator->create_qual($record);


        // Create what we expect to be returned when querying the discussion.
        $expectedposts = array(
            'posts' => array(),
            'courseid' => $course1->id,
            'forumid' => $forum1->id,
            'ratinginfo' => array(
                'contextid' => $forum1context->id,
                'component' => 'mod_forum',
                'ratingarea' => 'post',
                'canviewall' => null,
                'canviewany' => null,
                'scales' => array(),
                'ratings' => array(),
            ),
            'warnings' => array(),
        );



        $posts = mod_forum_external::get_discussion_posts($discussion1->id, 'modified', 'DESC');
        $posts = external_api::clean_returnvalue(mod_forum_external::get_discussion_posts_returns(), $posts);
        $this->assertEquals(2, count($posts['posts']));

        // Unset the initial discussion post.
        array_pop($posts['posts']);
        $user1 = core_user::get_user( $user1->id);
        $courseexperience1 = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user1->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course1->id.')');

        $user2 = core_user::get_user( $user2->id);
        $courseexperience2 = $DB->get_field_sql('SELECT courseexperience FROM {user_enrolments}  where userid = '.$user2->id.' and enrolid in ( SELECT id from {enrol} where courseid = '.$course1->id.')');

        $this->assertEquals($user1->totalexperience, 15);
        $this->assertEquals($user2->totalexperience, 5);
        $this->assertEquals($courseexperience1, 15);
        $this->assertEquals($courseexperience2, 5);
    }


    public function test_mod_forum_is_good_question() {
        // Based on test_mod_forum_get_discussion_posts_by_userid in externallib
        global $CFG;

        $this->resetAfterTest(true);

        // Set the CFG variable to allow track forums.
        $CFG->forum_trackreadposts = true;


        // Create course to add the module.
        $course1 = self::getDataGenerator()->create_course();

        // Create a user who can track forums.
        $record = new stdClass();
        $record->trackforums = true;
        $user1 = self::getDataGenerator()->create_user($record);
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();

        $forumgenerator = self::getDataGenerator()->get_plugin_generator('mod_forum');

        // Set the first created user to the test user.
        self::setUser($user1);

        // Forum with tracking off.
        $record = new stdClass();
        $record->course = $course1->id;
        $record->trackingtype = FORUM_TRACKING_OFF;
        // Display word count. Otherwise, word and char counts will be set to null by the forum post exporter.
        $record->displaywordcount = true;
        $forum1 = self::getDataGenerator()->create_module('forum', $record);
        $forum1context = context_module::instance($forum1->cmid);


        // Add the discussion
        $record = new stdClass();
        $record->course = $course1->id;
        $record->userid = $user1->id;
        $record->forum = $forum1->id;
        $discussion1 = $forumgenerator->create_discussion($record);


        // Add 1 reply
        $record = new stdClass();
        $record->discussion = $discussion1->id;
        $record->parent = $discussion1->firstpost;
        $record->userid = $user2->id;
        $discussion1reply1 = $forumgenerator->create_post($record);




        // Enrol the user in the  course.
        $enrol = enrol_get_plugin('manual');
        // Following line enrol and assign default role id to the user.
        // So the user automatically gets mod/forum:viewdiscussion on all forums of the course.
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id);
        $this->getDataGenerator()->enrol_user($user3->id, $course1->id);



        // Create the qualification
        $record = new stdClass();
        $record->id_post = $discussion1reply1->id;
        $record->id_user = $user1->id;
        $record->qual = 'positive';
        $record->discussion = $discussion1->id;
        $record->courseid = $course1->id;
        $record->userwhoqualificate = $user1->id;
        $record->userqualificated = $user2->id;

        $qual = $forumgenerator->create_qual($record);



        $posts = mod_forum_external::get_discussion_posts($discussion1->id, 'modified', 'DESC');
        $posts = external_api::clean_returnvalue(mod_forum_external::get_discussion_posts_returns(), $posts);
        $this->assertEquals(2, count($posts['posts']));

        
        // check still is not a good question
        array_pop($posts['posts']);
        $this->assertEquals(null, $posts['posts'][0]['isgoodquestion']);


        // Create another qualification
        $record = new stdClass();
        $record->id_post = $discussion1reply1->id;
        $record->id_user = $user3->id;
        $record->qual = 'like';
        $record->discussion = $discussion1->id;
        $record->courseid = $course1->id;
        $record->userwhoqualificate = $user3->id;
        $record->userqualificated = $user2->id;

        $qual = $forumgenerator->create_qual($record);

        $posts = mod_forum_external::get_discussion_posts($discussion1->id, 'modified', 'DESC');
        $posts = external_api::clean_returnvalue(mod_forum_external::get_discussion_posts_returns(), $posts);
        $this->assertEquals(2, count($posts['posts']));

        
        // check still is not a good question
        array_pop($posts['posts']);
        $this->assertEquals(true, $posts['posts'][0]['isgoodquestion']);

    }


    public function test_mod_forum_check_user_qual_view() {
        // Based on test_mod_forum_get_discussion_posts_by_userid in externallib
        global $CFG;

        $this->resetAfterTest(true);

        // Set the CFG variable to allow track forums.
        $CFG->forum_trackreadposts = true;


        // Create course to add the module.
        $course1 = self::getDataGenerator()->create_course();

        // Create a user who can track forums.
        $record = new stdClass();
        $record->trackforums = true;
        $user1 = self::getDataGenerator()->create_user($record);
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();
        $user4 = self::getDataGenerator()->create_user();
        $user5 = self::getDataGenerator()->create_user();

        $forumgenerator = self::getDataGenerator()->get_plugin_generator('mod_forum');

        // Set the first created user to the test user.
        self::setUser($user1);

        // Forum with tracking off.
        $record = new stdClass();
        $record->course = $course1->id;
        $record->trackingtype = FORUM_TRACKING_OFF;
        // Display word count. Otherwise, word and char counts will be set to null by the forum post exporter.
        $record->displaywordcount = true;
        $forum1 = self::getDataGenerator()->create_module('forum', $record);
        $forum1context = context_module::instance($forum1->cmid);


        // Add the discussion
        $record = new stdClass();
        $record->course = $course1->id;
        $record->userid = $user1->id;
        $record->forum = $forum1->id;
        $discussion1 = $forumgenerator->create_discussion($record);


        // Add 1 reply
        $record = new stdClass();
        $record->discussion = $discussion1->id;
        $record->parent = $discussion1->firstpost;
        $record->userid = $user2->id;
        $discussion1reply1 = $forumgenerator->create_post($record);


        $this->getDataGenerator()->enrol_user($user1->id, $course1->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id);
        $this->getDataGenerator()->enrol_user($user3->id, $course1->id);
        $this->getDataGenerator()->enrol_user($user4->id, $course1->id);
        $this->getDataGenerator()->enrol_user($user5->id, $course1->id);





        // Create the qualifications
        $record = new stdClass();
        $record->id_post = $discussion1reply1->id;
        $record->id_user = $user1->id;
        $record->qual = 'positive';
        $record->discussion = $discussion1->id;
        $record->courseid = $course1->id;
        $record->userwhoqualificate = $user1->id;
        $record->userqualificated = $user2->id;

        $qual = $forumgenerator->create_qual($record);

        // Create the qualification
        $record = new stdClass();
        $record->id_post = $discussion1reply1->id;
        $record->id_user = $user3->id;
        $record->qual = 'positive';
        $record->discussion = $discussion1->id;
        $record->courseid = $course1->id;
        $record->userwhoqualificate = $user3->id;
        $record->userqualificated = $user2->id;

        $qual = $forumgenerator->create_qual($record);

        // Create the qualification
        $record = new stdClass();
        $record->id_post = $discussion1reply1->id;
        $record->id_user = $user4->id;
        $record->qual = 'positive';
        $record->discussion = $discussion1->id;
        $record->courseid = $course1->id;
        $record->userwhoqualificate = $user4->id;
        $record->userqualificated = $user2->id;

        $qual = $forumgenerator->create_qual($record);
        
        // Create the qualification
        $record = new stdClass();
        $record->id_post = $discussion1reply1->id;
        $record->id_user = $user5->id;
        $record->qual = 'negative';
        $record->discussion = $discussion1->id;
        $record->courseid = $course1->id;
        $record->userwhoqualificate = $user5->id;
        $record->userqualificated = $user2->id;

        $qual = $forumgenerator->create_qual($record);
        

        $posts = mod_forum_external::get_discussion_posts($discussion1->id, 'modified', 'DESC');
        $posts = external_api::clean_returnvalue(mod_forum_external::get_discussion_posts_returns(), $posts);
        $this->assertEquals(2, count($posts['posts']));

        
        // check number of qualifications coincides
        array_pop($posts['posts']);
        $this->assertEquals(4, $posts['posts'][0]['numberqual']);

        $this->assertEquals(1, $posts['posts'][0]['restofuser']['numero']);

        // check user5 who voted negative does not appear
        $this->assertNotEquals($posts['posts'][0]['user1']['name'], $user5->firstname.' '.$user4->lastname);
        $this->assertNotEquals($posts['posts'][0]['user2']['name'], $user5->firstname.' '.$user4->lastname);
        $this->assertNotEquals($posts['posts'][0]['restofuser']['lista'], $user5->firstname.' '.$user4->lastname);


    }

}
