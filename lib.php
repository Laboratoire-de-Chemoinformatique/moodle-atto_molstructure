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

function atto_molstructure_prepare_xml_return($resultarray, $requestid) {
    // Set up xml to return.
    $xmloutput = "<result requestid='" . $requestid . "'>";

    if ($resultarray['success']) {
        $xmloutput .= 'success';
        // Not sure how this will impact attachment explorer .. (expects no messages here, but recorder expects..).
        foreach ($resultarray['messages'] as $message) {
            $xmloutput .= "<message>" . $message . "</message>";
        }
    } else {
        $xmloutput .= 'failure';
        foreach ($resultarray['messages'] as $message) {
            $xmloutput .= '<error>' . $message . '</error>';
        }
    }
    $xmloutput .= "</result>";
    return $xmloutput;
}

function atto_molstructure_fetch_return_array($initsuccess = false) {
    $return             = array();
    $return['messages'] = array();
    $return['success']  = $initsuccess;
    return $return;
}


function atto_molstructure_prepare_base64($filedata) {
    /*check there is no metadata prefixed to the base 64. From OL widgets, none, from JS yes
    if so it will look like this: data:image/png;base64,iVBORw0K
    we remove it, there must be a better way of course ...  */

    $metapos = strpos($filedata, ",");
    if ($metapos > 10 && $metapos < 30) {
        $filedata = substr($filedata, $metapos + 1);
    }
    // Decode the data.
    $xfiledata = base64_decode($filedata);
    return $xfiledata;
}