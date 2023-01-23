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

/**
 * Groups utility class helper
 *
 * @package     local_modcomments
 * @copyright   2023 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class group {
    public function get_user_groups_members_ids($courseid, $userid = null) {
        global $USER;

        if (!$userid) {
            $userid = $USER->id;
        }

        $groupmembers = $this->get_user_groups_members($courseid, $userid);

        if (!$groupmembers) {
            return [$USER->id];
        }

        $ids = [];
        foreach ($groupmembers as $groupmember) {
            $ids[] = $groupmember->id;
        }

        return $ids;
    }

    public function get_user_groups_members($courseid, $userid = null) {
        global $USER;

        if (!$userid) {
            $userid = $USER->id;
        }

        $groups = $this->get_user_groups($courseid, $userid);

        // If no groups, return only current user id.
        if (!$groups) {
            return false;
        }

        $groupsmembers = $this->get_groups_members($groups);

        if (!$groupsmembers) {
            return false;
        }

        return array_values($groupsmembers);
    }

    public function get_user_groups($courseid, $userid = null) {
        global $DB, $USER;

        if (!$userid) {
            $userid = $USER->id;
        }

        $sql = "SELECT g.id, g.name, g.picture
                FROM {groups} g
                JOIN {groups_members} gm ON gm.groupid = g.id
                WHERE gm.userid = :userid AND g.courseid = :courseid";

        $groups = $DB->get_records_sql($sql, ['courseid' => $courseid, 'userid' => $userid]);

        if (!$groups) {
            return false;
        }

        return array_values($groups);
    }

    public function get_groups_members($groups) {
        global $DB;

        $ids = [];
        foreach ($groups as $group) {
            $ids[] = $group->id;
        }

        list($groupsids, $groupsparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'group');

        $sql = "SELECT u.*
                FROM {groups_members} gm
                INNER JOIN {user} u ON u.id = gm.userid
                WHERE gm.groupid " . $groupsids;

        $groupsmembers = $DB->get_records_sql($sql, $groupsparams);

        if (!$groupsmembers) {
            return false;
        }

        return array_values($groupsmembers);
    }
}
