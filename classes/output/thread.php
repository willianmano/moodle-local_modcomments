<?php

namespace local_modcomments\output;

defined('MOODLE_INTERNAL') || die();

use local_modcomments\models\comment;
use renderable;
use templatable;
use renderer_base;

/**
 * Module comments renderable class.
 *
 * @package     local_modcomments
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class thread implements renderable, templatable {
    protected $course;
    protected $cmid;
    protected $modname;

    public function __construct($course, $cmid, $modname) {
        $this->course = $course;
        $this->cmid = $cmid;
        $this->modname = $modname;
    }

    public function export_for_template(renderer_base $output) {
        global $USER, $PAGE;

        $userpicture = new \user_picture($USER);
        $userpicture->size = 35;

        $commentmodel = new comment();

        return [
            'courseid' => $this->course->id,
            'cmid' => $this->cmid,
            'modname' => $this->modname,
            'comments' => $commentmodel->get_comments($this->course, $this->cmid),
            'userfullname' => fullname($USER),
            'userpicture' => $userpicture->get_url($PAGE),
        ];
    }
}
