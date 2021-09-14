<?php

require_once(__DIR__. '/../../config.php');
$PAGE->set_url(new moodle_url('/local/stats/view.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Tus estadisticas');


echo $OUTPUT->header();
$serie1 = new core\chart_series('My series title', [400, 460, 1120, 540]);
$serie2 = new core\chart_series('My series 222', [333, 888, 1333, 600]);

$chart1example = new \core\chart_pie();
$chart1example->set_doughnut(true); // Calling set_doughnut(true) we display the chart as a doughnut.
$chart1example->add_series($serie1);
$chart1example->set_labels(['2004', '2005', '2006', '2007']);

$chart2example = new core\chart_bar();
$chart2example->set_stacked(true);
$chart2example->add_series($serie2);
$chart2example->set_labels(['2004', '2005', '2006', '2007']);


$data = new \stdClass();
$data->chart1 = $OUTPUT->render($chart1example);
$data->chart2 = $OUTPUT->render($chart2example);

#$data->chart = ['chartdata' => json_encode($chart), 'withtable' => true];

echo $OUTPUT->render_from_template('local_stats/stats',$data );

echo $OUTPUT->footer();