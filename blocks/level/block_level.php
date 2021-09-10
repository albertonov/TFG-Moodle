<?php
class block_level extends block_base {
    public function init() {
        $this->title = get_string('level', 'block_level');
    }






    public function get_content() {
        global $course;
        global $USER;


        $this->content->text =  'holaaaaaaaaaaaaaaaaa';
        


        return $this->content;
    }



/*
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
*/
    public function instance_allow_multiple() {
        return false;
       }
}