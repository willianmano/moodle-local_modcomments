<?php

/**
 * Module comments main renderer
 *
 * @package     local_modcomments
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_modcomments\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;
use renderable;

class renderer extends plugin_renderer_base {
    public function render_thread(renderable $page) {
        $data = $page->export_for_template($this);

        return parent::render_from_template('local_modcomments/thread', $data);
    }
}