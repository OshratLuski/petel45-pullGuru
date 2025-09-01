<?php
$capabilities = array(
        'local/metadata:mdmanager' => array(
                'riskbitmask' => RISK_PERSONAL,
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'legacy' => array(
                        'guest' => CAP_PREVENT,
                        'student' => CAP_PREVENT,
                        'teacher' => CAP_PREVENT,
                        'editingteacher' => CAP_PREVENT,
                        'coursecreator' => CAP_PREVENT,
                        'manager' => CAP_ALLOW
                )
        ),
);

