<?php
global $CFG;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/mod/assign/mod_form.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/grade/grading/lib.php');
require_once($CFG->dirroot . '/mod/assign/feedbackplugin.php');
require_once($CFG->dirroot . '/mod/assign/submissionplugin.php');
require_once($CFG->dirroot . '/mod/assign/renderable.php');
require_once($CFG->dirroot . '/mod/assign/gradingtable.php');
require_once($CFG->libdir . '/portfolio/caller.php');

use \mod_assign\output\grading_app;
use PHPUnit\Util\Printer;

class mod_assign_time_estimated_testcase extends advanced_testcase {

/**
 * Convenience function to create an instance of an assignment.
 *
 * @param array $params Array of parameters to pass to the generator
 * @return assign The assign class.
 */
protected function create_instance($course, $params = [], $options = []) {
    global $DB;
    $params['course'] = $course->id;
    $generator = $this->getDataGenerator()->get_plugin_generator('mod_assign');
    $instance = $generator->create_instance($params, $options);
    $cm = get_coursemodule_from_instance('assign', $instance->id);
    $context = context_module::instance($cm->id);
    $instance->timeestimated = $params['timeestimated'];
    $DB->update_record('assign',   $instance);
    return new mod_assign_testable_assign($context, $cm, $course);
}


    public function test_timeestimated_appear_in_summary_studient() {
        global $DB, $PAGE, $OUTPUT;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'teacher');
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        $this->setUser($teacher);
        $assign = $this->create_instance($course, [
                'assignsubmission_onlinetext_enabled' => 1,
                'timeestimated'=> 7
            ]);

        $this->setUser($teacher);
        $data = new stdClass();
        $data->grade = '50.0';
        $assign->testable_apply_grade_to_user($data, $student->id, 0);

        $gradegrade = grade_grade::fetch([
                'userid' => $student->id,
                'itemid' => $assign->get_grade_item()->id,
            ]);

        $this->assertEquals(false, $gradegrade->is_overridden());

        $this->setUser($student);
        $submission = $assign->get_user_submission($student->id, true);

        $PAGE->set_url(new moodle_url('/mod/assign/view.php', ['id' => $assign->get_course_module()->id]));

        $gradegrade->set_overridden(true);
        $this->assertEquals(true, $gradegrade->is_overridden());


        $assignsubmissionstatus = $assign->get_assign_submission_status_renderable($student, true);
        $output = $assign->get_renderer()->render($assignsubmissionstatus);

        $this->assertStringContainsString('Tiempo estimado', $output);
        $this->assertStringContainsString('7 horas', $output);
        $this->assertStringNotContainsString('N/A', $output);


    }

    public function test_timeestimated_appear_in_summary_teacher() {
        global $DB, $PAGE, $OUTPUT;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'teacher');
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        $this->setUser($teacher);
        $assign = $this->create_instance($course, [
                'assignsubmission_onlinetext_enabled' => 1,
                'timeestimated'=> 7
            ]);

        $this->setUser($teacher);
        $data = new stdClass();
        $data->grade = '50.0';
        $assign->testable_apply_grade_to_user($data, $student->id, 0);

        $gradegrade = grade_grade::fetch([
                'userid' => $student->id,
                'itemid' => $assign->get_grade_item()->id,
            ]);

        $this->assertEquals(false, $gradegrade->is_overridden());

        $this->setUser($student);
        $submission = $assign->get_user_submission($student->id, true);

        $PAGE->set_url(new moodle_url('/mod/assign/view.php', ['id' => $assign->get_course_module()->id]));

        $gradegrade->set_overridden(true);
        $this->assertEquals(true, $gradegrade->is_overridden());


 

        $this->setUser($teacher);
        $summary = $assign->get_assign_grading_summary_renderable();
        $output = $assign->get_renderer()->render($summary);
        
        $this->assertStringContainsString('Tiempo estimado', $output);
        $this->assertStringContainsString('7 horas', $output);
        $this->assertStringNotContainsString('N/A', $output);
    }

    public function test_timeestimated_null_appears_as_NA() {
        global $DB, $PAGE, $OUTPUT;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'teacher');
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        $this->setUser($teacher);
        $assign = $this->create_instance($course, [
                'assignsubmission_onlinetext_enabled' => 1,
                'timeestimated'=> 0
            ]);

        $this->setUser($teacher);
        $data = new stdClass();
        $data->grade = '50.0';
        $assign->testable_apply_grade_to_user($data, $student->id, 0);

        $gradegrade = grade_grade::fetch([
                'userid' => $student->id,
                'itemid' => $assign->get_grade_item()->id,
            ]);

        $this->assertEquals(false, $gradegrade->is_overridden());

        $this->setUser($student);
        $submission = $assign->get_user_submission($student->id, true);

        $PAGE->set_url(new moodle_url('/mod/assign/view.php', ['id' => $assign->get_course_module()->id]));

        $gradegrade->set_overridden(true);
        $this->assertEquals(true, $gradegrade->is_overridden());



        $this->setUser($student);
        $submission = $assign->get_user_submission($student->id, true);

        $PAGE->set_url(new moodle_url('/mod/assign/view.php', ['id' => $assign->get_course_module()->id]));

        $gradegrade->set_overridden(true);
        $this->assertEquals(true, $gradegrade->is_overridden());


        $assignsubmissionstatus = $assign->get_assign_submission_status_renderable($student, true);
        $output = $assign->get_renderer()->render($assignsubmissionstatus);
        
        $this->assertStringContainsString('Tiempo estimado', $output);
        $this->assertStringNotContainsString('0 horas', $output);
        $this->assertStringContainsString('N/A', $output);


         

        $this->setUser($teacher);
        $summary = $assign->get_assign_grading_summary_renderable();
        $output = $assign->get_renderer()->render($summary);
        
        $this->assertStringContainsString('Tiempo estimado', $output);
        $this->assertStringNotContainsString('0 horas', $output);
        $this->assertStringContainsString('N/A', $output);

    }
    




}