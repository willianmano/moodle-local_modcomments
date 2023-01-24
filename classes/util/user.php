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

namespace local_modcomments\util;

use context;
use user_picture;

/**
 * User utility class helper
 *
 * @package     local_modcomments
 * @copyright   2023 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class user {
    /**
     * Get all users enrolled in a course by id
     *
     * @param int $userid
     * @param context $context
     *
     * @return \stdClass
     * @throws \dml_exception
     */
    public function get_by_id($userid, context $context) {
        global $DB;

        $ufields = user_picture::fields('u');

        list($esql, $enrolledparams) = get_enrolled_sql($context);

        $sql = "SELECT $ufields
                FROM {user} u
                JOIN ($esql) je ON je.id = u.id
                WHERE u.id = :userid";

        $params = array_merge($enrolledparams, ['userid' => $userid]);

        return $DB->get_record_sql($sql, $params, MUST_EXIST);
    }
}
