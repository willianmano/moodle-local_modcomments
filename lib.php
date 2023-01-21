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

    if (isguestuser() || !isloggedin() || !$PAGE->cm) {
        return false;
    }

    if (in_array($PAGE->cm->modname, $disabledmodules)) {
        return false;
    }

    $renderer = $PAGE->get_renderer('local_modcomments');

    $contentrenderable = new \local_modcomments\output\thread($PAGE->course->id, $PAGE->cm->id, $PAGE->cm->modname);

    return $renderer->render($contentrenderable);
}

/**
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 * https://docs.moodle.org/dev/Callbacks
 */
function local_modcomments_coursemodule_standard_elements($formwrapper, $mform) {
    global $DB;

    $options = [
        1 => get_string('yes'),
        0 => get_string('no')
    ];

    $mform->addElement('header', 'modcommentsheader', get_string('modcommentsheader', 'local_modcomments'));
    $mform->addElement('select', 'enablemodcomments', get_string('enablemodcomments', 'local_modcomments'), $options);
    $mform->setType('enablemodcomments', PARAM_INT);

    if ($formwrapper->get_coursemodule()) {
        $settings = new \local_modcomments\util\settings();
        $mform->setDefault('enablemodcomments', $settings->are_comments_enabled($formwrapper->get_coursemodule()->id));
    }
}

/**
 * Saves the data of custom fields elements of all moodle module settings forms.
 *
 * @param object $moduleinfo the module info
 * @param object $course the course of the module
 */
function local_modcomments_coursemodule_edit_post_actions($moduleinfo, $course) {
    $settings = new \local_modcomments\util\settings();
    $settings->update_comments_setting($moduleinfo->coursemodule, $moduleinfo->enablemodcomments);

    return $moduleinfo;
}
