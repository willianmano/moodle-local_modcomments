<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin lib.
 *
 * @package     local_modcomments
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

function local_modcomments_moove_module_footer() {
    global $PAGE;

    $disabledmodules = ['forum', 'label'];

    if (isguestuser() || !isloggedin() || !$PAGE->cm) {
        return false;
    }

    if (in_array($PAGE->cm->modname, $disabledmodules)) {
        return false;
    }

    $settings = new \local_modcomments\util\settings();
    if (!$settings->are_comments_enabled($PAGE->cm->id)) {
        return false;
    }

    $renderer = $PAGE->get_renderer('local_modcomments');

    $contentrenderable = new \local_modcomments\output\thread($PAGE->course, $PAGE->cm, $PAGE->context);

    return $renderer->render($contentrenderable);
}

function local_modcomments_dom_module_footer() {
    return local_modcomments_moove_module_footer();
}

function local_modcomments_before_footer() {
    global $CFG;

    if ($CFG->theme == 'moove' || $CFG->theme == 'dom') {
        return '';
    }

    return local_modcomments_moove_module_footer();
}

/**
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 * https://docs.moodle.org/dev/Callbacks
 */
function local_modcomments_coursemodule_standard_elements($formwrapper, $mform) {
    $disabledmodules = ['forum', 'label'];

    if (in_array($formwrapper->get_coursemodule()->modname, $disabledmodules)) {
        return;
    }

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
    $disabledmodules = ['forum', 'label'];

    if (in_array($moduleinfo->modulename, $disabledmodules)) {
        return $moduleinfo;
    }

    $settings = new \local_modcomments\util\settings();
    $settings->update_comments_setting($moduleinfo->coursemodule, $moduleinfo->enablemodcomments);

    return $moduleinfo;
}

function local_modcomments_comment_permissions($args) {
    return ['post' => true, 'view' => true];
}

function local_modcomments_comment_validate($args) {
    $settings = new \local_modcomments\util\settings();

    if (!$settings->are_comments_enabled($args->cm->id)) {
        throw new comment_exception('commentsdisabled');
    }

    return true;
}
