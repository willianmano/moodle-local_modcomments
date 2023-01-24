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

namespace local_modcomments\models;

use local_modcomments\notification\commentadded;
use local_modcomments\notification\mention;
use local_modcomments\util\group;
use local_modcomments\util\user;

/**
 * Comment model class.
 *
 * @package     local_modcomments
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class comment {
    public function save($context, $courseid, $userid, $cmid, $modname, $comment) {
        global $DB;

        list($finalcomment, $userstonotify) = $this->get_final_comment_and_users_to_notify($context, $courseid, $comment);

        $usercomment = new \stdClass();
        $usercomment->courseid = $courseid;
        $usercomment->userid = $userid;
        $usercomment->cmid = $cmid;
        $usercomment->module = $modname;
        $usercomment->comment = $finalcomment;
        $usercomment->timecreated = time();
        $usercomment->timemodified = time();

        $id = $DB->insert_record('modcomments_comments', $usercomment);

        $usercomment->id = $id;

        $notification = new commentadded($context, $courseid, $cmid, $modname);
        $notification->send();

        if ($userstonotify) {
            $notification = new mention($context, $courseid, $cmid, $modname);
            $notification->send_mentions_notifications($userstonotify);
        }

        return $usercomment;
    }

    public function get_comments($course, $cmid) {
        global $DB, $PAGE;

        $sql = 'SELECT
                    c.id, c.timecreated, c.comment,
                    u.id as userid, u.picture, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                    u.middlename, u.alternatename, u.imagealt, u.email
                FROM {modcomments_comments} c
                INNER JOIN {user} u ON u.id = c.userid
                WHERE c.cmid = :cmid ';

        $params = ['cmid' => $cmid];

        if ($course->groupmode == 1) {
            $grouputil = new group();

            $ids = $grouputil->get_user_groups_members_ids($course->id);

            list($insql, $inparams) = $DB->get_in_or_equal($ids,  SQL_PARAMS_NAMED);

            $sql .= ' AND u.id ' . $insql;
            $params = array_merge($params, $inparams);
        }

        $sql .= ' ORDER BY c.id DESC';

        $comments = $DB->get_records_sql($sql, $params);

        $data = [];
        if (!$comments) {
            return $data;
        }

        foreach ($comments as $comment) {
            $user = clone($comment);
            $user->id = $user->userid;

            $userpicture = new \user_picture($user);
            $userpicture->size = 35;

            $data[] = [
                'userfullname' => fullname($user),
                'userpicture' => $userpicture->get_url($PAGE),
                'comment' => $comment->comment,
                'humantimecreated' => userdate($comment->timecreated)
            ];
        }

        return $data;
    }

    private function get_final_comment_and_users_to_notify($context, $courseid, $comment) {
        // Handle the mentions.
        $matches = [];
        preg_match_all('/<span(.*?)<\/span>/s', $comment, $matches);
        $replaces = [];
        $userstonotifymention = [];
        if (!empty($matches[0])) {
            $userutil = new user();

            for ($i = 0; $i < count($matches[0]); $i++) {
                $mention = $matches[0][$i];

                $useridmatches = null;
                preg_match( '@data-uid="([^"]+)"@' , $mention, $useridmatches);
                $userid = array_pop($useridmatches);

                if (!$userid) {
                    continue;
                }

                $user = $userutil->get_by_id($userid, $context);

                if (!$user) {
                    continue;
                }

                $userprofilelink = new \moodle_url('/user/view.php',  ['id' => $user->id, 'course' => $courseid]);
                $userprofilelink = \html_writer::link($userprofilelink->out(false), fullname($user));

                $replaces['replace' . $i] = $userprofilelink;

                $userstonotifymention[] = $user->id;
            }
        }

        $outputtext = $comment;

        foreach ($replaces as $key => $replace) {
            $outputtext = str_replace("[$key]", $replace, $outputtext);
        }

        return [$outputtext, $userstonotifymention];
    }
}
