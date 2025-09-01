<?php

namespace aiplacement_petel\persistent;

class log extends \core\persistent {


    const TABLE = 'petel_placement_log';


    /**
     * Define properties.
     *
     * @return array
     */
    protected static function define_properties() {

        return [
            'contextid' => [
                'type' => PARAM_INT,
            ],
            'instanceid' => [
                'type' => PARAM_INT,
            ],
            'action' => [
                'type' => PARAM_ALPHANUMEXT,
            ],
            'type' => [
                'type' => PARAM_ALPHANUMEXT,
            ],
            'request' => [
                'type' => PARAM_RAW,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'response' => [
                'type' => PARAM_RAW,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'errorcode' => [
                'type' => PARAM_INT,
            ],
            'customdata' => [
                'type' => PARAM_RAW,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'responsedata' => [
                'type' => PARAM_RAW,
                'null' => NULL_ALLOWED,
                'default' => null,
            ]
        ];
    }

}