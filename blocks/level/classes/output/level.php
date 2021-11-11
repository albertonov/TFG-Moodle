<?php

/**
 * Class containing data for level block.
 *
 * @package    block_level
 */

namespace block_level\output;

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
class level implements renderable, templatable {

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

    public function get_current_level_and_experience($USER) {

        global $DB;

        $data = new \stdClass();

        $data->experience = $DB->get_field_sql('SELECT totalexperience FROM {user} u WHERE u.id = ?', array($USER->id));


        $setlevel = array(
            1   => array("min" => 0, "max" => 25),
            2   => array("min" => 25, "max" => 50),
            3   => array("min" => 50, "max" => 100),
            4   => array("min" => 100, "max" => 200),
            5   => array("min" => 200, "max" => 500)
        );

        switch ($data->experience){
            case $data->experience >= 0  &&   $data->experience < 25:
                $data->level = 1;
                $data->min = $setlevel[1]["min"];
                $data->max = $setlevel[1]["max"];

                break;

            case $data->experience >= 25  &&   $data->experience < 50:
                $data->level = 2;
                $data->min = $setlevel[2]["min"];
                $data->max = $setlevel[2]["max"];


                break;

            case $data->experience >= 50   &&   $data->experience < 100:
                $data->level = 3;
                $data->min = $setlevel[3]["min"];
                $data->max = $setlevel[3]["max"];

                break;

            case $data->experience >= 100   &&   $data->experience < 200:
                $data->level = 4;
                $data->min = $setlevel[4]["min"];
                $data->max = $setlevel[4]["max"];

                break;

            case $data->experience >= 200   &&   $data->experience < 500:
                $data->level = 5;
                $data->min = $setlevel[5]["min"];
                $data->max = $setlevel[5]["max"];

                break;

            case $data->experience >= 500:
                $data->level = 6;
                $data->min = 500;
                $data->max = -1;

                break;
            }
        return $data;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $USER, $OUTPUT;

        $data = new \stdClass();
        $userdata = $this->get_current_level_and_experience($USER);

        $data->level = $userdata->level;
        
        $data->experience = $userdata->experience;
        $data->istoplevel = $userdata->level < 6 ? false : true;
        $data->nextlevel = $data->istoplevel ? $userdata->level : $userdata->level + 1;
        $data->top = $userdata->max;
        $data->less = $userdata->min;
        $data->percentage = (($userdata->experience - $userdata->min )  / ($userdata->max - $userdata->min) ) * 100;
        $data->showcompletemessage = $data->percentage >= 7 ? true : false;
        $data->showpercentageonbar = $data->percentage < 7  && $data->percentage >= 0 ? true : false;

        $data->info = get_string('info', 'block_level');


        
        return $data;
    }
}
