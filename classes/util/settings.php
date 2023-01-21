<?php

/**
 * Module settings
 *
 * @package     local_modcomments
 * @copyright   2023 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_modcomments\util;

defined('MOODLE_INTERNAL') || die;

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