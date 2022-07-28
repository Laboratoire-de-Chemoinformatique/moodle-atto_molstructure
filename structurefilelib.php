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
 * structure settings.
 *
 * @package atto_molstructure
 * @copyright  2022 Unistra  {@link http://unistra.fr}
 * @author Louis Plyer <louis.plyer@unistra.fr>
 * inspired by atto_structure from Carl LeBlond
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../../../../config.php");
require_login();
require_sesskey();

global $CFG;

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/lib/editor/atto/plugins/molstructure/lib.php');

$datatype   = optional_param('datatype', "", PARAM_TEXT);
$filedata   = optional_param('filedata', "", PARAM_TEXT); // Nature of value depends on datatype, maybe path.
$requestid  = optional_param('requestid', "", PARAM_TEXT); // File or dir hash.
$itemid     = optional_param('itemid', 0, PARAM_INT); // The id of the module.

header("Content-type: text/xml");
echo "<?xml version=\"1.0\"?>\n";

global $CFG, $USER;
// Setup our return object.
$return = atto_molstructure_fetch_return_array(true);

$fs       = get_file_storage();
// Assume a root level filepath.
$filepath = "/";
$farea = "draft";
$comp = "user";
$mediatype = "image";
$fileextension = "png";
$contextiduser = context_user::instance($USER->id)->id;

// Make our filerecord.
$record            = new stdClass();
$record->filearea  = $farea;
$record->component = $comp;
$record->filepath  = $filepath;
$record->itemid    = $itemid;
$record->license   = $CFG->sitedefaultlicense;
$record->author    = fullname($USER);
$record->contextid = $contextiduser;
$record->userid    = $USER->id;
$record->source    = '';

$filenamebase = "upfile_" . $requestid . ".";

$filename         = $filenamebase . $fileextension;
$record->filename = $filename;

if ($fs->file_exists($contextiduser, $comp, $farea, $itemid, $filepath, $filename)) {
    // Delete any existing draft files!
    $file = $fs->get_file($contextiduser, $comp, $farea, $itemid, $filepath, $filename);
    $file->delete();
    // Check there is no metadata prefixed to the base 64. From OL widgets, none, from JS yes!
    array_push($return['messages'], "File already existed, it has been rewritten. " );
}
$xfiledata = atto_molstructure_prepare_base64($filedata);
// Create the file!
$storedfile = $fs->create_file_from_string($record, $xfiledata);
// If successful return filename.
if ($storedfile) {
    array_push($return['messages'], "Stored the file with filename: " . $filename);
} else {
    $return['success'] = false;
    array_push($return['messages'], "Unable to save file with filename:" . $filename); // If unsuccessful, return error.
}
// We process the result for return to browser.
$xmloutput = atto_molstructure_prepare_xml_return($return, $requestid);

// Return to widget/client the result of our file operation.
echo $xmloutput;
return;
