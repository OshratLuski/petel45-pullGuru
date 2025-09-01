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
 * @package    qtype_formulas
 * @copyright  2013 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // Use tooltip or not to display correct answer.
    $settings->add(new admin_setting_configcheckbox('qtype_formulas/usepopup',
            new lang_string('settingusepopup', 'qtype_formulas'),
            new lang_string('settingusepopup_desc', 'qtype_formulas'), 0));
    // Default answer type.
    $settings->add(new admin_setting_configselect('qtype_formulas/defaultanswertype',
            new lang_string('defaultanswertype', 'qtype_formulas'),
            new lang_string('defaultanswertype_desc', 'qtype_formulas'), 0,
            array(0 => new lang_string('number', 'qtype_formulas'),
                    10 => new lang_string('numeric', 'qtype_formulas'),
                        100 => new lang_string('numerical_formula', 'qtype_formulas'),
                        1000 => new lang_string('algebraic_formula', 'qtype_formulas'))));
    // Default correctness.
    $settings->add(new admin_setting_configtext('qtype_formulas/defaultcorrectness',
        get_string('defaultcorrectness', 'qtype_formulas'),
        get_string('defaultcorrectness_desc', 'qtype_formulas'), '_relerr < 0.01'));
    // Default answermark.
    $settings->add(new admin_setting_configtext('qtype_formulas/defaultanswermark',
        get_string('defaultanswermark', 'qtype_formulas'),
        get_string('defaultanswermark_desc', 'qtype_formulas'), 1));
    // Default unit penalty.
    $settings->add(new admin_setting_configtext('qtype_formulas/defaultunitpenalty',
        get_string('defaultunitpenalty', 'qtype_formulas'),
        get_string('defaultunitpenalty_desc', 'qtype_formulas'), 1));


    // Units.
    $default = "
1kg=1000gr=1000000mg=0.001Ton
10000000000000Angstrom=1km=1000m=100000cm=1000000mm=1000000000micrometer=1000000000000nm
1hr=60min=3600sec
1N=1kg*m/sec^2
1Hz=1/sec
1A=1A
1V=1V
1kW=1000W
1km/sec=1000m/sec=100000cm/sec
3.6km/hr=1m/s
1C=1C
1°C=1°C
1K=1K
1mol = 1000milimol
1gr/mol = 1gr/mol
1.0M=0.001mol/ml = 0.001gr/cm^3 = 0.001gr/cc = 1mol/L
1L/mol=1000ml/mol
1000ml = 1L
1gr/ml = 1000gr/L
1atm=760mmHg=760Torr
1cal=4.18J = 4.18N*m
1000J=1kJ
1J/K*mol = 1J/K*mol
1J/K = 1J/K
1atoms=1atoms
1molecules=1molecules
1particles=1particles
1dyn/m=1dyn/m
1J/m^2=1J/m^2
1dyn=1g·cm/sec^2=0.00001kg·m/sec^2=0.00001N
1J=kg*m^2/sec^2=1N*m
        ";

    $settings->add(new admin_setting_configtextarea('qtype_formulas_units',
            get_string('setting_units', 'qtype_formulas'), '', $default, PARAM_TEXT));

    // Wrong value penalty.
    $settings->add(new admin_setting_configtext('qtype_formulas_wrongvaluepenalty',
            get_string('setting_wrong_value_penalty', 'qtype_formulas'), '', '0.1', PARAM_FLOAT));

    // Wrong unit penalty.
    $settings->add(new admin_setting_configtext('qtype_formulas_wrongunitpenalty',
            get_string('setting_wrong_unit_penalty', 'qtype_formulas'), '', '0.9', PARAM_FLOAT));
}
