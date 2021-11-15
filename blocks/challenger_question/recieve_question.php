<?php

require_once('../../config.php');
global $DB, $USER;
$answer   = required_param('answer', PARAM_TEXT);
$idcourse   = required_param('courseid', PARAM_INT);
$idquestion   = required_param('idquestion', PARAM_INT);
$idchallenger   = required_param('idchallenger', PARAM_INT);

$PAGE->set_url('/blocks/challenger_question/recieve_question.php', array(
    'answer' => $answer,
    'course' => $idcourse,
    'idquestion' => $idquestion,
    'idchallenger' => $idchallenger

));
if($answer == 'Verdadero' || $answer == 'Falso'   ){

    $exists = $DB->record_exists_sql('SELECT id FROM {challenger_user_questions} WHERE userid ='.$USER->id.'and id_challenger ='.$idchallenger);
    if(!$exists) {
        $correctanswer = $DB->get_record_sql('SELECT * FROM {question_answers} WHERE fraction=1 and question = '.$idquestion);

        
        if($correctanswer->answer == 'Verdadero'){
            $escorrecta  = true;
        }
        elseif($correctanswer->answer == 'True'){
            $escorrecta  = true;
        }
        elseif($correctanswer->answer == 'Falso'){
            $escorrecta  = false;
        }
        else{
            $escorrecta  = false;
        }

        if($answer == 'Verdadero'){
            $answer  = true;
        }
        else{
            $answer  = false;
        }


        $challenger_user_questions = new \stdClass();
        $challenger_user_questions->course = $idcourse;
        $challenger_user_questions->id_challenger = $idchallenger;
        $challenger_user_questions->userid = $USER->id;
    
        
        if ($answer ==  $escorrecta){
            #respuesta correcta
            $challenger_user_questions->iscorrect = 1;
            \core_user::user_add_experience_to_total_and_course($USER->id, 20, $idcourse ); 
    
        }
        else{
            #respuesta falsa
            $challenger_user_questions->iscorrect = 0;
            \core_user::user_add_experience_to_total_and_course($USER->id, 5, $idcourse ); 
    
        }
    
    
        #$challenger_user_questions->idquestion = $;
    
        $DB->insert_record('challenger_user_questions', $challenger_user_questions);
    
        redirect( new moodle_url('/course/view.php', array('id' => $idcourse)));
    }
    else{
        print_error('ESte usuario ya ha votado');

    }


}
else{
    print_error('Error al pasar un parametro');
}



