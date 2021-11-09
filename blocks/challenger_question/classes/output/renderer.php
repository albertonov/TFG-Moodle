<?php


namespace block_challenger_question\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;

/**
 * myprofile block renderer
 *

 */
class renderer extends plugin_renderer_base {

    /**
     * Return the main content for the block level.
     *
     * @param challenger_question $level The level renderable
     * @return string HTML string
     */
    public function render_challeng(challenger_question $challenger_question) {
        return $this->render_from_template('block_challenger_question/challenger_question', $challenger_question->export_for_template($this));
    }
}
