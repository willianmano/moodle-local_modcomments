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

use core\context\course as context_course;
use user_picture;

/**
 * Course utility class helper
 *
 * @package     local_modcomments
 * @copyright   2023 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class course {
    /**
     * Get all users enrolled in a course by name
     *
     * @param string $name
     * @param context_course $context
     * @param \stdClass $course
     *
     * @return array
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public function get_enrolled_users_by_name($name, context_course $context, $course) {
        global $DB;

        list($ufields, $searchparams, $wherecondition) = $this->get_basic_search_conditions($name, $context);

        list($esql, $enrolledparams) = get_enrolled_sql($context);

        $sql = "SELECT $ufields
                FROM {user} u
                JOIN ($esql) je ON je.id = u.id
                WHERE $wherecondition";

        if ($course->groupmode == 1) {
            $grouputil = new group();

            $ids = $grouputil->get_user_groups_members_ids($course->id);

            list($insql, $inparams) = $DB->get_in_or_equal($ids,  SQL_PARAMS_NAMED);

            $sql .= ' AND u.id ' . $insql;

            $searchparams = array_merge($searchparams, $inparams);
        }

        list($sort, $sortparams) = users_order_by_sql('u');
        $sql = "$sql ORDER BY $sort";

        $params = array_merge($searchparams, $enrolledparams, $sortparams);

        $users = $DB->get_records_sql($sql, $params, 0, 10);

        if (!$users) {
            return [];
        }

        return array_values($users);
    }

    /**
     * Helper method used by get_enrolled_users_by_name().
     *
     * @param string $search the search term, if any.
     * @param context_course $context course context
     *
     * @return array with three elements:
     *     string list of fields to SELECT,
     *     array query params. Note that the SQL snippets use named parameters,
     *     string contents of SQL WHERE clause.
     */
    protected function get_basic_search_conditions($search, context_course $context) {
        global $DB, $CFG, $USER;

        // Add some additional sensible conditions.
        $tests = ["u.id <> :guestid", "u.deleted = 0", "u.confirmed = 1", "u.id <> :loggedinuser"];
        $params = [
            'guestid' => $CFG->siteguest,
            'loggedinuser' => $USER->id
        ];

        if (!empty($search)) {
            $conditions = get_extra_user_fields($context);
            foreach (get_all_user_name_fields() as $field) {
                $conditions[] = 'u.'.$field;
            }

            $conditions[] = $DB->sql_fullname('u.firstname', 'u.lastname');

            $searchparam = '%' . $search . '%';

            $i = 0;
            foreach ($conditions as $key => $condition) {
                $conditions[$key] = $DB->sql_like($condition, ":con{$i}00", false);
                $params["con{$i}00"] = $searchparam;
                $i++;
            }

            $tests[] = '(' . implode(' OR ', $conditions) . ')';
        }

        $wherecondition = implode(' AND ', $tests);

        $fields = \core_user\fields::for_identity($context, false)->excluding('username', 'lastaccess');

        $extrafields = $fields->get_required_fields();
        $extrafields[] = 'username';
        $extrafields[] = 'lastaccess';
        $extrafields[] = 'maildisplay';

        $ufields = user_picture::fields('u', $extrafields);

        return [$ufields, $params, $wherecondition];
    }
}
