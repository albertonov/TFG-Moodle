<?php

require_once(__DIR__. '/../../config.php');
require_once($CFG->dirroot . '/local/stats/lib.php');

$PAGE->set_url(new moodle_url('/local/stats/view.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Tus estadisticas');
require_login(0, true, null, false);


echo $OUTPUT->header();

$stats = new stats();
$stats->set_data();

$data = $stats->get_data();


echo $OUTPUT->render_from_template('local_stats/stats',$data );
echo $OUTPUT->footer();