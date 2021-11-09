<?php

namespace block_challenger_question\task;

defined('MOODLE_INTERNAL') || die();


class cleanup extends \core\task\scheduled_task {

    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name() {
        return 'challenger_question';
    }

    /**
     * Remove old entries from table block_recent_activity
     */
    public function execute() {
        global $CFG, $DB;
        ##
        $DB->execute('DELETE FROM {challenger_user_questions}');

        #extraer todos los records
        #borrar todos los records de la tabla
        #cambiarles las preguntas
        #insertar todos los records

        $allrecords= $DB->get_records_sql('SELECT * FROM {challenger_question}');
        $DB->execute('DELETE FROM {challenger_question}');

        foreach ($allrecords as &$record) {
            $context = \context_course::instance($record->course);
            $existscategory = $DB->record_exists_sql("SELECT * FROM {question_categories} WHERE contextid =".$context->id."and name = 'CHALLENGER'");
            if($existscategory) {
                $category = $DB->get_field_sql("SELECT id FROM {question_categories} WHERE contextid =".$context->id."and name = 'CHALLENGER'");
                $bank = $DB->get_records_sql("SELECT id FROM {question} WHERE qtype='truefalse' and category =".$category);
                
                $idarray = array_rand( $bank, $num = 1);
                $challenger_question = new \stdClass();
                $challenger_question->course = $record->course;
                $challenger_question->idquestion = $bank[$idarray]->id;

                $DB->insert_record('challenger_question', $challenger_question);
            }
        }
    }
}
