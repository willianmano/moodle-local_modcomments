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
    public function save($context, $course, $userid, $cm, $modname, $comment) {
        global $CFG;

        list($finalcomment, $userstonotify) = $this->get_final_comment_and_users_to_notify($context, $courseid, $comment);

        require_once("$CFG->dirroot/comment/lib.php");

        $args = new \stdClass();
        $args->context   = $context;
        $args->course    = $course;
        $args->cm        = $cm;
        $args->component = 'local_modcomments';
        $args->itemid    = $cm->id;
        $args->area      = 'local_modcomments';

        $manager = new \comment($args);
        $newcomment = $manager->add($finalcomment, FORMAT_HTML);

        $notification = new commentadded($context, $course->id, $cm->id, $modname);
        $notification->send();

        if ($userstonotify) {
            $notification = new mention($context, $course->id, $cm->id, $modname);
            $notification->send_mentions_notifications($userstonotify);
        }

        return $newcomment;
    }

    public function get_comments($context, $course, $cm) {
        global $CFG;

        require_once("$CFG->dirroot/comment/lib.php");

        $args = new \stdClass();
        $args->context   = $context;
        $args->course    = $course;
        $args->cm        = $cm;
        $args->component = 'local_modcomments';
        $args->itemid    = $cm->id;
        $args->area      = 'local_modcomments';

        $manager = new \comment($args);

        $comments = $manager->get_comments();

        if (!$comments) {
            return false;
        }

        if ($course->groupmode == 1) {
            $grouputil = new group();

            $userids = $grouputil->get_user_groups_members_ids($course->id);

            foreach ($comments as $key => $comment) {
                if (!in_array($comment->userid, $userids)) {
                    unset($comments[$key]);
                }
            }
        }

        return array_values($comments);
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
