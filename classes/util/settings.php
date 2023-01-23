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
 * Module settings
 *
 * @package     local_modcomments
 * @copyright   2023 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_modcomments\util;

class settings {
    // Always try to return true, becase we almost all times want to have comments in activities.
    public function are_comments_enabled($moduleid) {
        $config = get_config('local_modcomments', 'activitiesenabled');

        if (!$config) {
            return true;
        }

        $config = json_decode($config);

        if (isset($config->$moduleid)) {
            return $config->$moduleid;
        }

        return true;
    }

    public function update_comments_setting($moduleid, $value = 0) {
        $config = get_config('local_modcomments', 'activitiesenabled');

        if (!$config) {
            $config = new \stdClass();
        } else {
            $config = json_decode($config);
        }

        $config->$moduleid = $value;

        return set_config('activitiesenabled', json_encode($config), 'local_modcomments');
    }
}
