<?php
class block_ranking extends block_base {
    public function init() {
        $this->title = get_string('ranking', 'block_ranking');
        $this->showall = FALSE;
    }



    public function get_content() {
        if ($this->content !== null) {
          return $this->content;
        }
    
        $this->content         =  new stdClass;
        $this->content->text   = '<a href="https://www.w3schools.com">Visit W3Schools.com!</a>        ';
        #$this->content->text = $this->page->course->id;

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
                $this->config->text = 'no';
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