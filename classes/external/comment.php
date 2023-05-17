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

namespace local_modcomments\external;

use core_external\external_api;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_function_parameters;
use local_modcomments\notification\commentadded;

/**
 * Section external api class.
 *
 * @package     local_modcomments
 * @copyright   2023 Willian Mano {@link https://conecti.me}
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
            'comment' => new external_value(PARAM_RAW, 'The comment text', VALUE_REQUIRED)
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
        global $USER, $PAGE;

        self::validate_parameters(self::add_parameters(), [
            'courseid' => $courseid,
            'cmid' => $cmid,
            'modname' => $modname,
            'comment' => $comment
        ]);

        $commentmodel = new \local_modcomments\models\comment();

        $context = \core\context\module::instance($cmid);
        $PAGE->set_context($context);

        list($course, $cm) = get_course_and_cm_from_cmid($cmid, $modname, $courseid);

        $usercomment = $commentmodel->save($context, $course, $USER->id, $cm, $modname, $comment);

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
