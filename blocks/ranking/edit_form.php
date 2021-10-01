<?php

class block_ranking_edit_form extends block_edit_form {
        
    protected function specific_definition($mform) {
        
        // Section header title according to language file.
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));

        // A sample string variable with a default value.
        $mform->addElement('selectyesno', 'config_showall', get_string('showallranked', 'block_ranking'));
        $mform->setDefault('config_showall', 'no');
        $mform->setType('config_showall', PARAM_RAW);        


        if (! empty($this->config->config_showall)) {
            $this->content->config_showall = $this->config->config_showall;
        }


        $mform->addElement('text', 'config_title', get_string('blocktitle', 'block_ranking'));
        $mform->setDefault('config_title', 'Ranking');
        $mform->setType('config_title', PARAM_TEXT);
    }
    
}