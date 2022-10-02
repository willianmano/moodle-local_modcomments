<?php

/**
 * Coment added message class.
 *
 * @package     local_modcomments
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_modcomments\notification;

defined('MOODLE_INTERNAL') || die();

use core\message\message;
use moodle_url;

/**
 * Comment mention notification class
 *
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class commentadded {
    /** @var \context Course context. */
    public $context;
    /** @var int The course module ID. */
    public $cmid;
    /** @var string The course name. */
    public $modname;

    /**
     * Constructor.
     *
     * @param \context $context
     * @param int $cmid
     * @param string $modname
     */
    public function __construct($context, $cmid, $modname) {
        $this->context = $context;
        $this->cmid = $cmid;
        $this->modname = $modname;
    }

    protected function get_teachers(): array {
        global $DB;

        $fields = 'DISTINCT u.id, u.firstname, u.lastname, u.email';

        $capjoin = get_enrolled_with_capabilities_join($this->context, '', 'moodle/course:update');

        $from = ' {user} u ' . $capjoin->joins;

        $params = $capjoin->params;

        $sql = "SELECT {$fields} FROM {$from} WHERE {$capjoin->wheres}";

        $records = $DB->get_records_sql($sql, $params);

        if (!$records) {
            return [];
        }

        return array_values($records);
    }

    /**
     * Send the message
     *
     * @return bool
     *
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function send(): bool {
        $teachers = $this->get_teachers();

        if (!$teachers) {
            return true;
        }

        $messagedata = $this->get_mention_message_data();

        foreach ($teachers as $teacher) {
            $messagedata->userto = $teacher;

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

        $commentadded = get_string('notification:commentadded', 'local_modcomments');
        $commentaddedhtml = get_string('notification:commentaddedhtml', 'local_modcomments');
        $clicktoaccess = get_string('notification:clicktoaccess', 'local_modcomments');

        $url = new moodle_url("/mod/{$this->modname}/view.php", ['id' => $this->cmid]);

        $message = new message();
        $message->component = 'local_modcomments';
        $message->name = 'commentadded';
        $message->userfrom = $USER;
        $message->subject = $commentadded;
        $message->fullmessage = $commentadded;
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = "<p>{$commentaddedhtml}</p>";
        $message->fullmessagehtml .= "<p><a class='btn btn-primary' href='{$url}'>{$clicktoaccess}</a></p>";
        $message->smallmessage = $commentadded;
        $message->contexturl = $url;
        $message->contexturlname = get_string('message_mentioncontextname', 'local_modcomments');
        $message->courseid = $this->courseid;
        $message->notification = 1;

        return $message;
    }
}
