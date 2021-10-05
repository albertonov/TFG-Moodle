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

    $factory = new piechart_factory($arrayseries, $arraylabels, 'Experiencia');
    return  $factory->create_chart('doughnut');
}

function create_attendance_chart($records)
{
  print_r($records);
    $end=date_create(array_key_last( $records));
    $begin =$end->sub(new DateInterval('P6D'));
    $end=date_create(array_key_last( $records));

    $interval = new DateInterval('P1D');
    $period=new DatePeriod($begin,$interval,$end);

    $arraylabels = array();
    $arrayseries = array();

    foreach ($period as $day){
      $usercount= isset($records[$day->format('m/d/Y')]) ? $records[$day->format('m/d/Y')] :0;
      array_push($arraylabels, $day->format('D'));
      array_push($arrayseries,$usercount);
    }
    $key=array_key_last( $records);
    array_push($arraylabels,  date('D', strtotime($key)));
    array_push($arrayseries, $records[$key]);
    

    $factory = new linechart_factory($arrayseries, $arraylabels, 'Conexiones');

    return  $factory->create_chart('smooth');
}


function get_last_five_grades_qualificated($userid)
{

    #recibir id y obtener los ultimos 5 quizs o tareas calificadas
    global $DB;


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
            $lastattemptid =    $DB->get_field_sql(
                'SELECT id FROM {quiz_attempts} WHERE quiz = ? AND userid = ? ORDER BY attempt DESC limit 1',
                array($record->quiz,$userid)
            );
            $record->url = '../../../moodle/mod/quiz/view.php?id=' . $idurl;
            $record->urlreview = '../../../moodle/mod/quiz/review.php?attempt='.$lastattemptid.'&cmid='.$idurl;

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
            FROM {logstore_standard_log} l
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
            FROM {assign_submission} s
            INNER JOIN {assign} a
            ON a.id = s.assignment 
            WHERE userid = ? and status = 'submitted'",
        array($userid)
    );
    return seconds_to_days($timemean);

}


function get_qualifications_from_posts($userid){
    global $DB;
    $qualification = $DB->get_records_sql(
        "   SELECT p.qual, count(p.qual) as totalcount,   
            count(p.qual)/(select count(*) from mdl_post_qualifications sp where id_user =  ?)::float as percentage
        
            FROM mdl_post_qualifications p
            WHERE id_user = ?
            GROUP BY qual
        
        ",
        array($userid,$userid )
    );

    return array(
        "percentage_positive" => $qualification["positive"]->percentage * 100,
        "percentage_negative" => $qualification["negative"]->percentage * 100,
        "percentage_like" => $qualification["like"]->percentage * 100,

        "total_positive" => $qualification["positive"]->totalcount,
        "total_negative" => $qualification["negative"]->totalcount,
        "total_like" => $qualification["like"]->totalcount,

    );

}
