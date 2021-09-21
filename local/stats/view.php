<?php
global $USER;

require_once(__DIR__. '/../../config.php');
require_once($CFG->dirroot . '/local/stats/lib.php');

$PAGE->set_url(new moodle_url('/local/stats/view.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Tus estadisticas');
require_login(0, true, null, false);


echo $OUTPUT->header();
$serie2 = new core\chart_series('My series 222', [333, 888, 1333, 600]);


$recordsUE = get_user_experience_from_courses($USER->id);
$doughnotchart = create_experience_chart($recordsUE);

$recordsattendence = get_user_attendance($USER->id, 604800);
$attendacechart = create_attendance_chart($recordsattendence);


$data = new \stdClass();
$data->chart1 = $OUTPUT->render($doughnotchart);
$data->chart2 = $OUTPUT->render($attendacechart);


$data->qualified_task = get_last_five_grades_qualificated($USER->id);
$data->mean = get_mean_time_assigns($USER->id);

echo $OUTPUT->render_from_template('local_stats/stats',$data );
echo $OUTPUT->footer();