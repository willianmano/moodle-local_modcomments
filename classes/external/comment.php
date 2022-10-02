<?php

namespace local_modcomments\external;

use external_api;
use external_value;
use external_single_structure;
use external_function_parameters;
use local_modcomments\notification\commentadded;
use moodle_url;
use html_writer;
use context_course;
use context_module;

/**
 * Section external api class.
 *
 * @package     local_modcomments
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class comment extends external_api {
    /**
     * Create comment parameters
     *
     * @return external_function_parameters
     */
    public static function add_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'The course id', VALUE_REQUIRED),
            'cmid' => new external_value(PARAM_INT, 'The course module id', VALUE_REQUIRED),
            'modname' => new external_value(PARAM_TEXT, 'The course module name', VALUE_REQUIRED),
            'comment' => new external_value(PARAM_TEXT, 'The comment text', VALUE_REQUIRED)
        ]);
    }

    /**
     * Create comment method
     *
     * @param int $courseid
     * @param int $cmid
     * @param string $modname
     * @param string $comment
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function add($courseid, $cmid, $modname, $comment) {
        global $USER;

        self::validate_parameters(self::add_parameters(), ['courseid' => $courseid, 'cmid' => $cmid, 'modname' => $modname, 'comment' => $comment]);

        $commentmodel = new \local_modcomments\models\comment();

        $usercomment = $commentmodel->save($courseid, $USER->id, $cmid, $modname, $comment);

        $context = context_course::instance($courseid);

        $notification = new commentadded($context, $cmid, $modname);
        $notification->send();

        return [
            'comment' => $comment,
            'humantimecreated' => userdate($usercomment->timecreated)
        ];
    }

    /**
     * Create comment return fields
     *
     * @return external_single_structure
     */
    public static function add_returns() {
        return new external_single_structure([
            'comment' => new external_value(PARAM_RAW, 'Comment message', VALUE_REQUIRED),
            'humantimecreated' => new external_value(PARAM_TEXT, 'Human readable time created', VALUE_REQUIRED)
        ]);
    }
}
