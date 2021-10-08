<?php

namespace block_challenger_question\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

/**
 * Class containing data for level block.
 *
 * @copyright  2018 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class challenger_question implements renderable, templatable {

    /**
     * @var object An object containing the configuration information for the current instance of this block.
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param object $config An object containing the configuration information for the current instance of this block.
     */
    public function __construct($config) {
        $this->config = $config;
    }


    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $COURSE, $DB,   $USER,  $OUTPUT, $CFG;
        $data = new \stdClass();


        $idchallenger = $DB->get_record_sql('SELECT * FROM {challenger_question} WHERE course ='.$COURSE->id);
        $question = $DB->get_record_sql('SELECT * FROM {question} WHERE id ='.$idchallenger->idquestion);
       # print_r($idchallenger->id);
        $data->question = $question;
        $data->courseid = $COURSE->id;
        $data->challengerid = $idchallenger;
        $data->questionid = $question->id;
        $data->trueurl = '../blocks/challenger_question/recieve_question.php?answer=Verdadero&courseid='.$COURSE->id.'&idquestion='.$question->id.'&idchallenger='.$idchallenger->id;
        $data->falseurl = '../blocks/challenger_question/recieve_question.php?answer=Falso&courseid='.$COURSE->id.'&idquestion='.$question->id.'&idchallenger='.$idchallenger->id;



        $exists = $DB->record_exists_sql('SELECT id FROM {challenger_user_questions} WHERE userid ='.$USER->id.'and id_challenger ='.$idchallenger->id);
        if( $exists) {
            $data->answered = true;
            $data->iscorrect = $DB->get_field_sql('SELECT iscorrect FROM {challenger_user_questions} WHERE userid ='.$USER->id.' and id_challenger ='.$idchallenger->id);
            $data->feedbackcorrect = $DB->get_field_sql("SELECT feedback FROM {question_answers} WHERE answer = 'Verdadero' and question =".$question->id);
            $data->feedbackincorrect = $DB->get_field_sql("SELECT feedback FROM {question_answers} WHERE  answer = 'Falso' and question =".$question->id);
            #print_r($question->id);
        }
        else{
            $data->answered = false;
            $data->iscorrect = $DB->get_field_sql('SELECT iscorrect FROM {challenger_user_questions} WHERE userid ='.$USER->id.' and id_challenger ='.$idchallenger->id);
        }

        return $data;
    }
}
