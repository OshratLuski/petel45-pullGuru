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
 * The chooser_item renderable.
 *
 * @package    core
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core\output;

use core\exception\coding_exception;
use core\context;
use stdClass;

/**
 * The chooser_item renderable class.
 *
 * @package    core
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class chooser_item implements renderable, templatable {
    /** @var string An identifier for the item. */
    public $id;
    /** @var string The label of this item. */
    public $label;
    /** @var string The value this item represents. */
    public $value;
    /** @var pix_icon The icon for this item. */
    public $icon;
    /** @var string The item description. */
    public $description;
    /** @var context The relevant context. */
    public $context;
    /** @var boolean The item favourite. */
    public $favourite;
    /** @var boolean The item recommend. */
    public $recommend;
    /** string the recommendation prefix itemtype in the favourites table. */
    public const RECOMMENDATION_PREFIX = 'recommend_';

    /**
     * Constructor.
     */
    public function __construct($id, $label, $value, pix_icon $icon, $description = null, ?context $context = null) {
        $this->id = $id;
        $this->label = $label;
        $this->value = $value;
        $this->icon = $icon;
        $this->description = $description;

        if (!empty($description) && empty($context)) {
            throw new coding_exception('The context must be passed when there is a description.');
        }
        $this->context = $context;
    }

    /**
     * Export for template.
     *
     * @param renderer_base  The renderer.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $USER;

        $itemtype = self::RECOMMENDATION_PREFIX . $this->id;
        $data = new stdClass();
        $data->id = $this->id;
        $data->label = $this->label;
        $data->value = $this->value;
        $data->icon = $this->icon->export_for_template($output);

        $options = new stdClass();
        $options->trusted = false;
        $options->noclean = false;
        $options->filter = false;
        $options->para = true;
        $options->newlines = false;
        $options->overflowdiv = false;

        $usercontext = \context_user::instance($USER->id);
        $ufservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);
        $favorite = $ufservice->count_favourites_by_type('core_question', $this->id);

        $admin = get_admin();
        $usercontextrecomended = \context_user::instance($admin->id);
        $ufservicerecomended = \core_favourites\service_factory::get_service_for_user_context($usercontextrecomended);
        $recommended = $ufservicerecomended->count_favourites_by_type('core_question', $itemtype);

        if($favorite > 0) {
            $data->favourite = true;
        } else {
            $data->favourite = false;
        }

        if($recommended > 0) {
            $data->recommended = true;
        } else {
            $data->recommended = false;
        }

        $data->description = '';
        if (!empty($this->description)) {
            [$data->description] = \core_external\util::format_text(
                (string) $this->description,
                FORMAT_MARKDOWN,
                $this->context->id,
                null,
                null,
                null,
                $options
            );
        }

        return $data;
    }
}
