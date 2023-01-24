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
 * Coment added message class.
 *
 * @package     local_modcomments
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_modcomments\notification;

use core\message\message;
use moodle_url;

/**
 * Comment mention notification class
 *
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class mention {
    /** @var \context Course context. */
    public $context;
    /** @var \context Course ID. */
    public $courseid;
    /** @var int The course module ID. */
    public $cmid;
    /** @var string The course name. */
    public $modname;

    /**
     * Constructor.
     *
     * @param \context $context
     * @param int $courseid
     * @param int $cmid
     * @param string $modname
     */
    public function __construct($context, $courseid, $cmid, $modname) {
        $this->context = $context;
        $this->courseid = $courseid;
        $this->cmid = $cmid;
        $this->modname = $modname;
    }

    /**
     * Send the message
     *
     * @param array $users A list of users ids to be notifiable
     *
     * @return bool
     *
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function send_mentions_notifications(array $users) {
        $messagedata = $this->get_mention_message_data();

        foreach ($users as $user) {
            $messagedata->userto = $user;

            message_send($messagedata);
        }

        return true;
    }

    /**
     * Get the notification message data
     *
     * @return message
     *
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    protected function get_mention_message_data() {
        global $USER;

        $youwerementioned = get_string('message_mentioned', 'local_modcomments');
        $youwerementionedinanactivity = get_string('message_mentionedinanactivity', 'local_modcomments', $this->modname);
        $clicktoaccessportfolio = get_string('message_clicktoaccessactivity', 'local_modcomments');

        $url = new moodle_url("/mod/{$this->modname}/view.php", ['id' => $this->cmid]);

        $message = new message();
        $message->component = 'local_modcomments';
        $message->name = 'mention';
        $message->userfrom = $USER;
        $message->subject = $youwerementioned;
        $message->fullmessage = "{$youwerementioned}: {$this->modname}";
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = '<p>'.$youwerementionedinanactivity.'</p>';
        $message->fullmessagehtml .= '<p><a class="btn btn-primary" href="'.$url.'">'.$clicktoaccessportfolio.'</a></p>';
        $message->smallmessage = $youwerementioned;
        $message->contexturl = $url;
        $message->contexturlname = get_string('message_mentioncontextname', 'local_modcomments');
        $message->courseid = $this->courseid;
        $message->notification = 1;

        return $message;
    }
}
