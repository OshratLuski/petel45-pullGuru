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

namespace metadatacontext_section\output;

defined('MOODLE_INTERNAL') || die;

class renderer extends \local_metadata\output\renderer {

    /**
     * Category table renderer.
     *
     * @param \local_metadata\output\category_table $categorytable renderable object.
     */
    public function render_category_table(\local_metadata\output\category_table $categorytable) {
        $output = parent::render_category_table($categorytable);
        if (get_config('metadatacontext_section', 'metadataenabled') == 0) {
            $output = $this->notification(get_string('metadatadisabled', 'metadatacontext_section')) . $output;
        }
        return $output;
    }

    /**
     * Question settings renderer.
     *
     * @param manage_data $sectionsettings renderable object.
     */
    public function render_manage_data(manage_data $sectionsettings) {
        global $PAGE;

        $PAGE->set_title(get_string('metadatatitle', 'metadatacontext_section'));
        $output = '';
        $output .= $this->header();
        $output .= $this->heading(get_string('metadatatitle', 'metadatacontext_section'));
        if ($sectionsettings->saved) {
            $output .= $this->notification(get_string('metadatasaved', 'local_metadata'), 'success');
        }
        $output .= $sectionsettings->form->render();
        $output .= $this->footer();

        return $output;
    }
}