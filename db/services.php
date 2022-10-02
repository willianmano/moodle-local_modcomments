<?php

/**
 * Module comments services definition
 *
 * @package     local_modcomments
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_modcomments_addcomment' => [
        'classname' => 'local_modcomments\external\comment',
        'classpath' => 'local/modcomments/classes/external/comment.php',
        'methodname' => 'add',
        'description' => 'Add a new comment',
        'type' => 'write',
        'ajax' => true
    ],
];
