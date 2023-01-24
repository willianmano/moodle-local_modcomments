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

use external_api;
use external_value;
use external_single_structure;
use external_function_parameters;
use external_multiple_structure;

/**
 * Section external api class.
 *
 * @package     local_modcomments
 * @copyright   2023 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class course extends external_api {
    /**
     * Get enrolled users parameters
     *
     * @return external_function_parameters
     */
    public static function enrolledusers_parameters() {
        return new external_function_parameters([
            'search' => new external_single_structure([
                'courseid' => new external_value(PARAM_INT, 'The course id', VALUE_REQUIRED),
                'name' => new external_value(PARAM_TEXT, 'The user name', VALUE_REQUIRED)
            ])
        ]);
    }

    /**
     * Get the list of all course's users
     *
     * @param array $search
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function enrolledusers($search) {
        global $DB, $PAGE;

        self::validate_parameters(self::enrolledusers_parameters(), ['search' => $search]);

        $search = (object)$search;

        $course = $DB->get_record('course', ['id' => $search->courseid], '*', MUST_EXIST);
        $context = \context_course::instance($course->id);

        $PAGE->set_context($context);

        if (!is_enrolled($context) && !is_siteadmin()) {
            return [];
        }

        $courseutil = new \local_modcomments\util\course();

        $users = $courseutil->get_enrolled_users_by_name($search->name, $context, $course);

        $returndata = [];

        foreach ($users as $user) {
            $userpicture = new \user_picture($user);
            $returndata[] = [
                'id' => $user->id,
                'username' => $user->username,
                'fullname' => fullname($user),
                'picture' => $userpicture->get_url($PAGE)->out()
            ];
        }

        return [
            'users' => $returndata
        ];
    }

    /**
     * Get enrolled users return fields
     *
     * @return external_single_structure
     */
    public static function enrolledusers_returns() {
        return new external_function_parameters(
            array(
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'The user id'),
                            'username' => new external_value(PARAM_TEXT, "The user username"),
                            'fullname' => new external_value(PARAM_TEXT, "The user fullname"),
                            'picture' => new external_value(PARAM_TEXT, "The user picture url")
                        )
                    )
                )
            )
        );
    }
}
