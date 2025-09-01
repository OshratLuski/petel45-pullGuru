<?php

    $observers = array(
        array(
            'eventname'   => '\core\event\course_module_completion_updated',
            'includefile' => '/local/tutorials/locallib.php',
            'callback' => 'local_update_activity_completed_observer',
        ),
);