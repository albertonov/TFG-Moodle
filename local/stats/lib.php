<?php
require_once(__DIR__.'/factory/factory.php');

function get_user_experience_from_courses($userid)
{

    #recibir id y devolver experiencia y nombre de los cursos
    global $DB;


    $records = $DB->get_records_sql(
        'SELECT co.fullname, ue.courseexperience
    FROM {user_enrolments} ue 
    INNER JOIN {enrol} en ON en.id = ue.enrolid
    INNER JOIN {course} co ON en.courseid = co.id
    WHERE ue.userid = ?',
        array($userid)
    );

    return $records;
}

function create_experience_chart($records)
{

    $arrayseries = array();
    $arraylabels = array();
    foreach ($records as &$record) {
        array_push($arrayseries, $record->courseexperience);
        array_push($arraylabels, $record->fullname);
    }

    $serie = new core\chart_series('Experiencia', $arrayseries);


    $factory = new factory;
    return $factory-> create_doughnut_chart(array($serie), array($arraylabels));
}

function create_attendance_chart($records)
{
    $arraylabels = array();
    $arrayseries = array();


    foreach($records as $key => $record) {
        array_push($arraylabels,  date('D', strtotime($key)));
        array_push($arrayseries, $records[$key]);

    }

    $serie = new core\chart_series('Conexiones', $arrayseries);
    $factory = new factory;
    return $factory-> create_line_chart(array($serie), array($arraylabels));
}


function get_last_five_grades_qualificated($userid)
{

    #recibir id y obtener los ultimos 5 quizs o tareas calificadas
    global $DB;

    /*
    $recordsassigns = $DB->get_records_sql("SELECT ag.timemodified as timemodified, ag.grade, ag.assignment, ag.attemptnumber, 'assign' as tipo, a.course, a.name
    FROM {assign_grades} ag 
    INNER JOIN {assign} a
    ON a.id = ag.assignment
    WHERE ag.userid = ?
    ORDER BY ag.timemodified DESC limit 5"
    , array($userid));


    $recordsquizs = $DB->get_records_sql("SELECT qg.timemodified as timemodified, qg.grade, qg.quiz, 'quiz' as tipo, q.course, q.name
    FROM {quiz_grades} qg
    INNER JOIN {quiz} q
    ON q.id = qg.quiz
    WHERE qg.userid = ?
    ORDER BY qg.timemodified DESC limit 5"
    , array($userid));
*/

    $recordsassigns = $DB->get_records_sql(
        "    SELECT ag.timemodified as timemodified, ag.grade, ag.assignment, ag.attemptnumber, 'assign' as tipo, a.course, a.name, asub.media
                                                FROM {assign_grades} ag 

                                                INNER JOIN {assign} a
                                                ON a.id = ag.assignment


                                                INNER JOIN (
                                                    SELECT  assignment, AVG(grade) as media
                                                    FROM {assign_grades}  group by assignment
                                                ) asub
                                                ON asub.assignment = ag.assignment

                                                WHERE ag.userid = ?
                                                ORDER BY ag.timemodified DESC limit 5",
        array($userid)
    );



    $recordsquizs = $DB->get_records_sql(
        "  SELECT qg.timemodified as timemodified, qg.grade, qg.quiz, 'quiz' as tipo, q.course, q.name, qsub.media
                                            FROM {quiz_grades} qg
    
                                            INNER JOIN {quiz} q
                                            ON q.id = qg.quiz

                                            INNER JOIN (
                                                    SELECT  quiz, AVG(grade) as media
                                                    FROM {quiz_grades}  group by quiz
                                                ) qsub
                                            ON qsub.quiz = qg.quiz
                                            
                                            WHERE qg.userid = ?
                                            ORDER BY qg.timemodified DESC limit 5",
        array($userid)
    );



    $records = array_merge($recordsassigns, $recordsquizs);

    usort($records, function ($a, $b) {
        return $a->timemodified < $b->timemodified ? 1 : -1;
    });



    $records =  array_slice($records, 0, 5, true);

    foreach ($records as &$record) {
        if ($record->tipo == "quiz") {
            $idurl =    $DB->get_field_sql(
                'SELECT id FROM {course_modules} WHERE module = 17 AND course = ? AND instance = ?',
                array($record->course, $record->quiz)
            );
            $record->url = '../../../moodle/mod/quiz/view.php?id=' . $idurl;
            $record->isquiz = true;
        } else {
            $idurl =    $DB->get_field_sql(
                'SELECT id FROM {course_modules} WHERE module = 1 AND course = ? AND instance = ?',
                array($record->course, $record->assignment)
            );
            $record->url = '../../../moodle/mod/assign/view.php?id=' . $idurl;
            $record->isquiz = false;
        }
        $record->coursename =  $DB->get_field('course', 'shortname', array('id' => $record->course));
        $record->grade = round($record->grade, 2);
        $record->date = date('H:m d/m/Y', $record->timemodified);

    }

    return $records;
}


function get_user_attendance($userid, $fromtime){
    global $DB;

    $today = strtotime("today 23:59");

    $attendacerecords = $DB->get_records_sql(
        "   SELECT timecreated 
            FROM mdl_logstore_standard_log l
            WHERE userid = ? and action = 'loggedin' and timecreated  > (?)",
        array($userid, $today - $fromtime)
    );
    $dates = array();
    foreach ($attendacerecords as &$record) {
        array_push($dates, date('m/d/Y', $record->timecreated));
    }
    return  array_count_values($dates);
}



function seconds_to_days($ss) {
    $h = floor(($ss%86400)/3600);
    $d = floor(($ss%2592000)/86400);
    
    return "$d dias, $h horas";
    }

function get_mean_time_assigns($userid){
    global $DB;
    $timemean = $DB->get_field_sql(
        "   SELECT avg(a.duedate  - s.timemodified) 
            FROM mdl_assign_submission s
            INNER JOIN mdl_assign a
            ON a.id = s.assignment 
            WHERE userid = ? and status = 'submitted'",
        array($userid)
    );
    return seconds_to_days($timemean);

}
