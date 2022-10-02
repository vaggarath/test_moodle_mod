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
 * Prints an instance of mod_vagodel.
 *
 * @package     mod_vagodel
 * @copyright   2022 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

global $PAGE;
// $PAGE->requires->js('/mod/vagodel/amd/js/model-viewer.min.js', true);
// $PAGE->requires->js_call_amd('mod/vagodel/amd/js/model-viewer.min.js');
// echo '<script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>'; //bon à priori ultra mauvaise pratique mais y'a un peu marre^^
echo '<script type="module" src="amd/js/model-viewer.min.js"></script>'; //mouais...
echo '<link href="styles.css" rel="stylesheet">';

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$v = optional_param('v', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('vagodel', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('vagodel', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('vagodel', array('id' => $v), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('vagodel', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/vagodel/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();

// foreach ($fs_files as $f)
// {
//     // $f is an instance of stored_file
//     $pathname = $f->get_filepath();
//     $filename = $f->get_filename();
//     $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION)); //pour savoir si glb ou img ou l'enfer sur terre
//     // $file = reset($fs_files);
//     html_writer::div(var_dump(moodle_url::make_pluginfile_url($context->id,'mod_vagodel', 'content', $model->id, $pathname,$filename)), 'frog');

// }

$fs = get_file_storage();

$model = null;
$texture = null;

if ($files = $fs->get_area_files($context->id, 'mod_vagodel', 'content', '0', 'sortorder', false)) {
    // Look through each file being managed
    //texture isn't texture anymore but poster. Too lazy to change functions name for now^^
    foreach ($files as $file) {
        $ext = strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION));

        if($ext === "glb"){

        }
        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
        $download_url = $fileurl->get_port() ? $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path() . ':' . $fileurl->get_port() : $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path();

        if($ext === "glb"){
            $model = $download_url;
        }elseif($ext === 'jpg' || $ext === 'png' || $ext === 'gif'){
            $texture = $download_url;
        }

        // var_dump($ext);
        // echo "<img src='".$download_url."'>" ; 
    }

    echo getmodel($model, $texture);
} else {
	echo '<p>Aucun modèle disponible</p>';
}  

echo $OUTPUT->footer();
