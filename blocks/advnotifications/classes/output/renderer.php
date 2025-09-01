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
 * Advanced Notifications renderer - what gets displayed
 *
 * @package    block_advnotifications
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Zander Potgieter <zander.potgieter@learningworks.co.nz>
 */

namespace block_advnotifications\output;

use plugin_renderer_base;
use renderable;

/**
 * Renders notifications.
 *
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base
{
    /**
     * Renders notification on page.
     *
     * @param   array $notifications Attributes about notifications to render.
     * @return  string Returns HTML to render notification.
     */
    public function render_notification($notifications, $carousel = null) {
        $html = '';
        // Render all the appropriate notifications.

        // If no data.
        if (count($notifications) == 0) {
            return $html;
        }

        if (!$carousel) {
            foreach ($notifications as $notification) {
                // Open notification block.
                $html .= '<div class="notification-block-wrapper' . $notification['extraclasses'] . ' mb-3 d-flex p-0 alert alert-' . $notification['alerttype'] .
                '" data-dismiss="' . $notification['notifid'] .
                /* '"><div class="d-flex p-0 alert alert-' . $notification['alerttype'] . */
                '"><div class="alert-colorblock"></div><div class="alert-inner d-flex flex-no-wrap align-items-start">';

                // TODO: temporary hidden. WE have not Title on mockup
                /*  if (!empty($notification['title'])) {
                $html .= '<strong>' . $notification['title'] . '</strong> ';
                } */

                // If icon, add icon.
                if (!empty($notification['aiconflag']) && $notification['aiconflag'] == 1) {
                    $alerttype = $notification['aicon'];
                    if ($alerttype == 'warning') {
                        $html .= '<div class="icon-wrapper"><i class=" fal fa-exclamation-triangle mt-1"></i></div>';
                    }
                    if ($alerttype == 'danger') {
                        $html .= '<div class="icon-wrapper"><i class="aicon fal fa-exclamation-circle mt-1"></i></div>';
                    }
                    if ($alerttype == 'success') {
                        $html .= '<div class="icon-wrapper"><i class="aicon fal fa-check-circle mt-1"></i></div>';
                    }
                    if ($alerttype == 'info') {
                        $html .= '<div class="icon-wrapper"><i class="aicon fal fa-exclamation-circle mt-1"></i></div>';
                    }

                    /*   $html .= '<img class="notification_aicon" src="' .
                $this->image_url($notification['aicon'], 'block_advnotifications') . '"/>'; */
                }
                $html .= '<div class="alert-wrapper"><h5 class="alert-title"></h5>';
                if (!empty($notification['message'])) {
                    if (get_config('block_advnotifications', 'html') == 1) {
                        $html .= '<div class="m-0 alert-text">' . format_text($notification['message']) . '</div>';
                    } else {
                        $html .= '<p class="m-0 alert-text">' . $notification['message'] . '</p>';
                    }
                }
                $html .= '</div>';
                // If dismissible, add close button.
                if ($notification['dismissible'] == 1) {
                    $html .= '<div class="notification-block-close ml-auto mt-1"><i class="fal fa-times-circle"></i></div>';
                }

                // Close notification block.
                $html .= '</div></div>';
            }
        } else {
            $uniqid = uniqid();
            $html .= '<div id="advnotifcarousel'.$uniqid.'" class="carousel mt-4 slide" data-ride="carousel">
                        <div class="carousel-inner">';
            $active = 'active';
            foreach ($notifications as $notification) {
                // Open notification block.
                $html .= '<div class="carousel-item '.$active.'" data-interval='. ($carousel->duration * 1000) . '">';
                $active = '';

                $html .= '<div class="notification-block-wrapper' . $notification['extraclasses'] . ' mb-3 d-flex p-0 alert alert-' . $notification['alerttype'] .
                '" data-dismiss="' . $notification['notifid'] .
                /* '"><div class="d-flex p-0 alert alert-' . $notification['alerttype'] . */
                '"><div class="alert-colorblock"></div><div class="alert-inner d-flex flex-no-wrap align-items-start">';

                // TODO: temporary hidden. WE have not Title on mockup
                /*  if (!empty($notification['title'])) {
                $html .= '<strong>' . $notification['title'] . '</strong> ';
                } */

                // If icon, add icon.
                if (!empty($notification['aiconflag']) && $notification['aiconflag'] == 1) {
                    $alerttype = $notification['aicon'];
                    if ($alerttype == 'warning') {
                        $html .= '<div class="icon-wrapper"><i class=" fal fa-exclamation-triangle mt-1"></i></div>';
                    }
                    if ($alerttype == 'danger') {
                        $html .= '<div class="icon-wrapper"><i class="aicon fal fa-exclamation-circle mt-1"></i></div>';
                    }
                    if ($alerttype == 'success') {
                        $html .= '<div class="icon-wrapper"><i class="aicon fal fa-check-circle mt-1"></i></div>';
                    }
                    if ($alerttype == 'info') {
                        $html .= '<div class="icon-wrapper"><i class="aicon fal fa-exclamation-circle mt-1"></i></div>';
                    }

                    /*   $html .= '<img class="notification_aicon" src="' .
                $this->image_url($notification['aicon'], 'block_advnotifications') . '"/>'; */
                }
                $html .= '<div class="alert-wrapper"><h5 class="alert-title"></h5>';
                if (!empty($notification['message'])) {
                    if (get_config('block_advnotifications', 'html') == 1) {
                        $html .= '<div class="m-0 alert-text">' . format_text($notification['message']) . '</div>';
                    } else {
                        $html .= '<p class="m-0 alert-text">' . $notification['message'] . '</p>';
                    }
                }
                $html .= '</div>';
                // If dismissible, add close button.
                if ($notification['dismissible'] == 1) {
                    // $html .= '<div class="notification-block-close ml-auto mt-1"><i class="fal fa-times-circle"></i></div>';
                }

                // Close notification block.
                $html .= '</div></div>';
                $html .= '</div>';
            }

            if (count($notifications) > 1) {
                $html .= '  </div>
                        <button class="carousel-control-prev btn btn-link mb-3" type="button" data-target="#advnotifcarousel'.$uniqid.'" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </button>
                        <button class="carousel-control-next btn btn-link mb-3" type="button" data-target="#advnotifcarousel'.$uniqid.'" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </button>
                    </div>';
            }
        }

        return $html;
    }

    /**
     * Render interface to add a notification.
     *
     * @param   array $params - passes information such whether notification is new or the block's instance id.
     * @return  string - returns HTML to render (add notification form HTML).
     * @throws  coding_exception
     */
    public function add_notification($params, $cohorts) {
        global $CFG;
        $html = '';

        $cho = '';
        foreach($cohorts as $key => $value) {
            $cho .= '<option value="' . $key . '">' . $value . '</option>'; //close your tags!!
        }

        // New Notification Form.
        $html .= '<div id="add_notification_wrapper_id" class="add_notification_wrapper">
                    <div class="add_notification_header"><h2>' .
                        get_string('advnotifications_add_heading', 'block_advnotifications') .
                        '</h2>
                    </div>
                    <div class="add_notification_form_wrapper">
                        <form id="add_notification_form" action="' . $CFG->wwwroot .
                            '/blocks/advnotifications/pages/process.php" method="POST">
                            <div class="form-check">
                                <input type="checkbox" id="add_notification_enabled" class="form-check-input" name="enabled"/>
                                <label for="add_notification_enabled" class="form-check-label">' .
                                    get_string('advnotifications_enabled', 'block_advnotifications') .
                                '</label>
                            </div>' .
                            ((array_key_exists('blockid', $params) &&
                                array_key_exists('global', $params) &&
                                $params['global'] === true) ?
                            '<div class="form-check">
                                <input type="checkbox" id="add_notification_global" class="form-check-input" name="global"/>
                                <label for="add_notification_global" class="form-check-label">' .
                                    get_string('advnotifications_global', 'block_advnotifications') .
                                '</label>
                                <input type="hidden" id="add_notification_blockid" name="blockid" value="' . $params['blockid'] .
                                    '"/>
                            </div>' :
                                ((array_key_exists('global', $params) &&
                                    $params['global'] === true) ?
                                    '<div class="form-group">
                                        <strong>
                                            <em>' . get_string('add_notification_global_notice', 'block_advnotifications') . '</em>
                                        </strong>
                                        <input type="hidden" id="add_notification_global" name="global" value="1"/>
                                    </div>' :
                                    '<div class="form-group">
                                        <strong>' . get_string('add_notif_local_notice', 'block_advnotifications') . '</strong>
                                        <input type="hidden" id="add_notification_global" name="global" value="0"/>
                                    </div>')) .
                            '<div class="form-group row">
                                <input type="text" id="add_notification_title" class="form-control" name="title" placeholder="' .
                                    get_string('advnotifications_title', 'block_advnotifications') . '"/>
                                <textarea id="add_notification_message" class="form-control" name="message" placeholder="' .
                                    get_string('advnotifications_message', 'block_advnotifications') . '"></textarea>
                            </div>
    
                            <div class="form-group row">
                                <select id="add_notification_cohort" class="form-control col-7" name="cohort" required>
                                    <option selected disabled>' .
                                        get_string('cohort', 'core_cohort').' 
                                    <option value="0">'. get_string('profilevisibleall', 'core_admin').'</option>'.$cho.'
                                        
                                  </select>
                                <label for="add_notification_cohort" class="col">
                                    <strong class="required">*</strong>
                                </label>
                            </div>


                            <div class="form-group row">
                                <select id="add_notification_type" class="form-control col-7" name="type" required>
                                    <option selected disabled>' .
                                        get_string('advnotifications_type', 'block_advnotifications') .
                                    '</option>
                                    <option value="info">' .
                                        get_string('advnotifications_add_option_info', 'block_advnotifications') .
                                    '</option>
                                    <option value="success">' .
                                        get_string('advnotifications_add_option_success', 'block_advnotifications') .
                                    '</option>
                                    <option value="warning">' .
                                        get_string('advnotifications_add_option_warning', 'block_advnotifications') .
                                    '</option>
                                    <option value="danger">' .
                                        get_string('advnotifications_add_option_danger', 'block_advnotifications') .
                                    '</option>
                                    <option value="announcement">' .
                                        get_string('advnotifications_add_option_announcement', 'block_advnotifications') .
                                    '</option>
                                </select>
                                <label for="add_notification_type" class="col">
                                    <strong class="required">*</strong>
                                </label>
                            </div>
                            <div class="form-group row">
                                <select id="add_notification_times" class="form-control col-7" name="times" required>
                                    <option selected disabled>' .
                                        get_string('advnotifications_times', 'block_advnotifications') . '</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                                <label for="add_notification_times" class="col">
                                    <strong class="required col">*</strong>
                                </label>
                                <small class="form-text text-muted">' .
                                    get_string('advnotifications_times_label', 'block_advnotifications') . '</small>
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" id="add_notification_aicon" class="form-check-input" name="aicon"/>
                                    <label for="add_notification_aicon" class="form-check-label">' .
                                        get_string('advnotifications_aicon', 'block_advnotifications') . '</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox"
                                        id="add_notification_dismissible"
                                        class="form-check-input"
                                        name="dismissible"/>
                                    <label for="add_notification_dismissible" class="form-check-label">' .
                                        get_string('advnotifications_dismissible', 'block_advnotifications') . '</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="add_notification_date_from" class="form-text">' .
                                    get_string('advnotifications_date_from', 'block_advnotifications') . '</label>
                                <input type="date"
                                    id="add_notification_date_from"
                                    class="form-control"
                                    name="date_from"
                                    placeholder="yyyy-mm-dd"/>
                                <label for="add_notification_date_to" class="form-text">' .
                                    get_string('advnotifications_date_to', 'block_advnotifications') . '</label>
                                <input type="date"
                                    id="add_notification_date_to"
                                    class="form-control"
                                    name="date_to"
                                    placeholder="yyyy-mm-dd"/>
                                <small class="form-text text-muted">' .
                                    get_string('advnotifications_date_info', 'block_advnotifications') . '</small>
                            </div>
                            <input type="hidden" id="add_notification_sesskey" name="sesskey" value="' . sesskey() . '"/>
                            <input type="hidden" id="add_notification_purpose" name="purpose" value="add"/>
                            <input type="hidden" id="add_notification_blockid" name="blockid" value="' .
                            (array_key_exists('blockid', $params) ?
                                $params['blockid'] :
                                '-1') . '"/>
                            <div class="form-group">
                                <input type="submit"
                                    id="add_notification_save"
                                    class="btn btn-primary"
                                    role="button"
                                    name="save"
                                    value="' . get_string('advnotifications_save', 'block_advnotifications') . '"/>
                                <a href="' . $CFG->wwwroot . '/blocks/advnotifications/pages/notifications.php"
                                    id="add_notification_cancel" class="btn btn-danger">' .
                                get_string('advnotifications_cancel', 'block_advnotifications') . '</a>
                            </div>
                            <div id="add_notification_status">
                                <div class="signal"></div>
                                <div class="saving">' .
                                    get_string('advnotifications_add_saving', 'block_advnotifications') .
                                '</div>
                                <div class="done" style="display: none;">' .
                                    get_string('advnotifications_add_done', 'block_advnotifications') .
                                '</div>
                            </div>
                        </form>
                    </div>
                </div>';

        return $html;
    }

    public function manage_order() {
        global $CFG;
        $html = '';

        // Get actual IDS and DURARTION.
        $idsraw = get_config('block_advnotifications', 'carousel_ids');
        $duration = get_config('block_advnotifications', 'carousel_duration') ?? 5;

        // New Notification Form.
        $html .= '<div id="manage_carousel_wrapper_id" class="manage_carousel_wrapper">
                    <div class="manage_carousel_header"><h2>' .
                        get_string('manage_carousel', 'block_advnotifications') .
                        '</h2>
                    </div>
                    <div class="manage_carousel_form_wrapper">
                        <form id="manage_carousel_form" action="' . $CFG->wwwroot .
                            '/blocks/advnotifications/pages/process.php" method="POST">
                            <div class="form-group">
                                <label for="manage_carousel_ids" class="form-check-label">' .
                                    get_string('advnotifications_ids', 'block_advnotifications') . '</label>
                                <input type="text" id="manage_carousel_ids" class="form-control w-25" name="ids" placeholder="' .
                                    get_string('advnotifications_ids_desc', 'block_advnotifications') . '" value="'.$idsraw.'"/>
                                <label for="manage_carousel_duration" class="form-check-label">' .
                                    get_string('advnotifications_duration', 'block_advnotifications') . '</label>
                                <input type="text" id="manage_carousel_duration" class="form-control w-25" name="duration" placeholder="' .
                                    get_string('advnotifications_duration_desc', 'block_advnotifications') . '"  value="'.$duration.'"/>
                            </div>

                            <input type="hidden" id="manage_carousel_sesskey" name="sesskey" value="' . sesskey() . '"/>
                            <input type="hidden" id="manage_carousel_purpose" name="purpose" value="carousel"/>
                            <div class="form-group">
                                <input type="submit"
                                    id="manage_carousel_save"
                                    class="btn btn-primary"
                                    role="button"
                                    name="save"
                                    value="' . get_string('advnotifications_save', 'block_advnotifications') . '"/>
                            </div>
                        </form>
                    </div>
                </div>';

        return $html;
    }
}