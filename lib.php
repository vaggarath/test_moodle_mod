<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants.
 *
 * @package     mod_vagodel
 * @copyright   2022 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function vagodel_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_vagodel into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_vagodel_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function vagodel_add_instance($moduleinstance, $mform) {
    global $DB, $USER, $CFG;
    require_once("$CFG->libdir/resourcelib.php");
    require_once("$CFG->dirroot/mod/vagodel/locallib.php");

    $moduleinstance->timecreated = time();
    $moduleinstance->teacher = $USER->lastname." ".$USER->firstname;

    $id = $DB->insert_record('vagodel', $moduleinstance);
    
    vagodel_set_mainfile($moduleinstance);

    $completiontimeexpected = !empty($moduleinstance->completionexpected) ? $moduleinstance->completionexpected : null;
    \core_completion\api::update_completion_date_event($cmid, 'resource', $moduleinstance->id, $completiontimeexpected);

    return $id;
}

/**
 * Updates an instance of the mod_vagodel in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_vagodel_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function vagodel_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('vagodel', $moduleinstance);
}

/**
 * Removes an instance of the mod_vagodel from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function vagodel_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('vagodel', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('vagodel', array('id' => $id));

    return true;
}

/**
 * Returns the lists of all browsable file areas within the given module context.
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@see file_browser::get_file_info_context_module()}.
 *
 * @package     mod_vagodel
 * @category    files
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return string[].
 */
function vagodel_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for mod_vagodel file areas.
 *
 * @package     mod_vagodel
 * @category    files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info Instance or null if not found.
 */
function vagodel_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {

    $out = array();
        
    $fs = get_file_storage();
    $files = $fs->get_area_files($contextid, 'mod_assignment', 'submission', $submission->id);
    if($files){//uniquement s'il y a des fichiers
        foreach ($files as $file) {
            $filename = $file->get_filename();
            $url = moodle_url::make_file_url('/pluginfile.php', array($file->get_contextid(), 'mod_assignment', 'submission',
                    $file->get_itemid(), $file->get_filepath(), $filename));
            $out[] = html_writer::link($url, $filename);
        }
        $br = html_writer::empty_tag('br');
                
        return implode($br, $out);
    }

    return null; //si pas de fichiers
}

/**
 * Serves the files from the mod_vagodel file areas.
 *
 * @package     mod_vagodel
 * @category    files
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param stdClass $context The mod_vagodel's context.
 * @param string $filearea The name of the file area.
 * @param array $args Extra arguments (itemid, path).
 * @param bool $forcedownload Whether or not force download.
 * @param array $options Additional options affecting the file serving.
 */
function vagodel_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = array()) {
    global $DB, $CFG;

    // if ($context->contextlevel != CONTEXT_SYSTEM) {
    //     return false;
    // }

    require_login();

    if ($filearea != 'content') {
        return false;
    }

    $itemid = (int)array_shift($args);

    if ($itemid != 0) {
        return false;
    }

    $fs = get_file_storage();

    $filename = array_pop($args);
    if (empty($args)) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }

    $file = $fs->get_file($context->id, 'mod_vagodel', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }

    // finally send the file
    send_stored_file($file, 0, 0, true, $options); // download MUST be forced - security!
}


// function getmodel($files){
//     if (class_exists('mod_vagodel_getmodel')) {
//         $test = new mod_vagodel_getmodel();
//         return $test->do_foo(); 
//     }else{
//         return "oups";
//     }
// }

function getmodel($model, $texture){
    if($model){
        $environment = $texture ? $texture : "";
        return '<model-viewer alt="" src="'.$model.'" ar environment-image="'.$environment.'" poster="" shadow-intensity="1" camera-controls touch-action="pan-y"></model-viewer>';
    }else{
        return "Aucun modele disponible";
    }
}