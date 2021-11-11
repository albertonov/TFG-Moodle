<?php


namespace block_level\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;

/**
 * level block renderer
 *

 */
class renderer extends plugin_renderer_base {

    /**
     * Return the main content for the block level.
     *
     * @param level $level The level renderable
     * @return string HTML string
     */
    public function render_level(level $level) {
        return $this->render_from_template('block_level/progressbar', $level->export_for_template($this));
    }
}
