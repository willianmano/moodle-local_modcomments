<?php

/**
 * Plugin lib.
 *
 * @package     local_modcomments
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */


defined('MOODLE_INTERNAL') || die();

function local_modcomments_moove_module_footer() {
    global $PAGE;

    $disabledmodules = ['forum'];

    if (isguestuser() || !isloggedin()) {
        return false;
    }

    if (in_array($PAGE->cm->modname, $disabledmodules)) {
        return false;
    }

    $renderer = $PAGE->get_renderer('local_modcomments');

    $contentrenderable = new \local_modcomments\output\thread($PAGE->course->id, $PAGE->cm->id, $PAGE->cm->modname);

    return $renderer->render($contentrenderable);
}
