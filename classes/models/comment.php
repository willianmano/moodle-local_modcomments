<?php

namespace local_modcomments\models;

/**
 * Comment model class.
 *
 * @package     local_modcomments
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class comment {
    public function save($courseid, $userid, $cmid, $modname, $comment) {
        global $DB;

        $usercomment = new \stdClass();
        $usercomment->courseid = $courseid;
        $usercomment->userid = $userid;
        $usercomment->cmid = $cmid;
        $usercomment->module = $modname;
        $usercomment->comment = $comment;
        $usercomment->timecreated = time();
        $usercomment->timemodified = time();

        $id = $DB->insert_record('modcomments_comments', $usercomment);

        $usercomment->id = $id;

        return $usercomment;
    }

    public function get_comments($cmid) {
        global $DB, $PAGE;

        $sql = 'SELECT
                    c.id, c.timecreated, c.comment,
                    u.id as userid, u.picture, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                    u.middlename, u.alternatename, u.imagealt, u.email  
                FROM {modcomments_comments} c
                INNER JOIN {user} u ON u.id = c.userid
                WHERE c.cmid = :cmid
                ORDER BY c.id DESC';

        $comments = $DB->get_records_sql($sql, ['cmid' => $cmid]);

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
}