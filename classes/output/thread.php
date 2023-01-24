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

namespace local_modcomments\output;

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
    protected $context;

    public function __construct($course, $cm, $context) {
        $this->course = $course;
        $this->cm = $cm;
        $this->context = $cm->context;
    }

    public function export_for_template(renderer_base $output) {
        global $USER, $PAGE;

        $userpicture = new \user_picture($USER);
        $userpicture->size = 35;

        $commentmodel = new comment();

        return [
            'courseid' => $this->course->id,
            'cmid' => $this->cm->id,
            'modname' => $this->cm->modname,
            'comments' => $commentmodel->get_comments($this->context, $this->course, $this->cm),
            'userfullname' => fullname($USER),
            'userpicture' => $userpicture->get_url($PAGE),
        ];
    }
}
