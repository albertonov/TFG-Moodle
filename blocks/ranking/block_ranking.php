<?php
class block_ranking extends block_base {
    public function init() {
        $this->title = get_string('ranking', 'block_ranking');
        $this->showall = FALSE;
    }

    public function es_profesor(){
        global $DB;
        global $course;
        global $USER;


        $context = context_course::instance($course->id);
        $roleid = $DB->get_field_sql('SELECT roleid FROM {role_assignments} WHERE userid = ? AND  contextid = ?', array($USER->id, $context->id));
        if ($roleid != 5 ){
            return true;
        }
        else{
            return false;
        }
    }



    public function get_individual_data($records){
        global $USER;

        #devolver;
            #datos personales
            #tu posicion en el ranking
            #la distancia que te falta para el primer puesto / siguiente puesto
            #total usuarios

        $data = new stdClass();
        $data->numusuarios = sizeof($records);


        $index  = 1;
        $previouskey  = 0;
        foreach($records as $key => $record) {
            if ($record->userid == $USER->id ) {
                #user found
                $data->firstname = $records[$key]->firstname;
                $data->lastname = $records[$key]->lastname;
                $data->courseexperience = $records[$key]->courseexperience;

                if ($key ==  array_key_first($records)){
                    #first position
                    $data->position = 1;
                    $data->difffirst = 0;
                    $data->diffnext = 0;

                }
                else{
                    $data->position =  $index;
                    $data->firspositionname = $records[array_key_first($records)]->firstname;
                    $data->nextpositionname = $records[$previouskey]->firstname;

                    $data->difffirst = $records[array_key_first($records)]->courseexperience - $records[$key]->courseexperience;
                    $data->diffnext = $records[$previouskey]->courseexperience - $records[$key]->courseexperience;;
                }
            }
            $index = $index + 1;
            $previouskey  = $key;

        }
        return $data;
    }

    public function get_users_data(){
        global $DB;
        global $course;
        $context = context_course::instance($course->id);
        $userroles = $DB->get_records('role_assignments', array('contextid' => $context->id));
        $rolenames = role_get_names($context, ROLENAME_ALIAS, true);


        $records = $DB->get_records_sql(' SELECT DISTINCT ue.userid, u.firstname, u.lastname, ue.courseexperience, ue.enrolid, ra.roleid
                                    FROM {user_enrolments} ue 
                                    INNER JOIN {user} u ON ue.userid=u.id
                                    INNER JOIN {role_assignments} ra ON ue.userid = ra.userid
                                    WHERE ue.enrolid IN (
                                        SELECT id 
                                        FROM {enrol} 
                                        WHERE courseid = ?
                                    )
                                    ORDER BY ue.courseexperience DESC'
                                    , array($course->id));
        print_r($course->id );
        foreach ($records as $key => $record) {
            if ($records[$key]->roleid != 5 ){
                unset($records[$key]);
            }
        }
        return $records;
    }

    public function create_table_html($records){
        $i = 1;
        $cadena = "
        <table style = 'width: 100%;'>
            <tr>
                <th>#</th>
                <th>Usuario</th>
                <th  style = 'width: 20%;'>Puntuacion</th>
            </tr>
            ";
        
        foreach ($records as $record){
            $cadena .= "
            <tr>
                <td> <b> {$i} </b></th>
                <td>{$record->firstname} {$record->lastname} </th>
                <td style = ' text-align: center;'>{$record->courseexperience}</th>
            </tr>
            ";
            $i = $i + 1;
        }
        $cadena .= "</table>";
        return  $cadena;
    }




    public function get_content() {
        global $course;
        global $USER;

        if ($this->content !== null) {
          return $this->content;
        }
    
        $this->content =  new stdClass;


        if ($this->showall ||$this->es_profesor() ) {
            $records = $this->get_users_data();
            $this->content->text =  $this->create_table_html($records);
        }
      
        else {
 
            $records = $this->get_users_data();
            $data = $this->get_individual_data($records);
            $this->content->text ="
                {$data->firstname} {$data->lastname}, estas en el puesto #{$data->position} de un total de {$data->numusuarios} compañeros.
            ";
            if ($data->position == 1) {
                $this->content->text .="<br>¡Sigue asi¡"; 
            } 
            elseif($data->position == 2){
                $this->content->text .="<br> Estas cerca de ser el mas trabajador de tus compañeros.<br>Te quedan {$data->difffirst} puntos para alcanzar el primer puesto"; 
            }
            else{
                $this->content->text .="<br>Te quedan {$data->diffnext} para alcanzar el siguiente puesto y {$data->difffirst} puntos para alcanzar el primer puesto, animo|"; 
            }
        }

        
        if (! empty($this->config->text)) {
            $this->content->text = $this->config->text;
        }
        return $this->content;
    }




    public function specialization() {
        if(isset($this->config)) {

            if (empty($this->config->title)) {
                $this->title = get_string('changetitle:default', 'block_ranking');
            }

            else {
                $this->title = $this->config->title;
            }


            if (empty($this->config->showall)) {
            }
            else {
                $this->showall = $this->config->showall;
            }
        }
    }

    public function instance_allow_multiple() {
        return false;
       }
}