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
 * Atto text editor integration version file.
 *
 * @package atto_molstructure
 * @copyright  2022 Unistra  {@link http://unistra.fr}
 * @author Louis Plyer <louis.plyer@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Initialise this plugin
 *
 */
function atto_molstructure_strings_for_js() {
    global $PAGE;
    $PAGE->requires->strings_for_js(array(
        'iframeprb',
        'insert',
        'dialogtitle',
        'resize',
        'height',
        'width',
        'instructions',
    ), 'atto_molstructure');
}

function atto_molstructure_params_for_js($elementid, $options, $fpoptions) {
    global $USER, $COURSE;

    $coursecontext           = context_course::instance($COURSE->id);
    $usercontextid           = context_user::instance($USER->id)->id;
    $disabled                = false;
    $params                  = array();
    $params['usercontextid'] = $usercontextid;

    if (!has_capability('atto/molstructure:visible', $coursecontext)) {
        $disabled = true;
    }
    $params['disabled'] = $disabled;
    return $params;
}
