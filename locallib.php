<?php

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/vagodel/lib.php");

function vagodel_set_mainfile($data) {
    global $DB;
    $fs = get_file_storage();
    $cmid = $data->coursemodule;
    $draftitemid = $data->files;

    $context = context_module::instance($cmid);
    if ($draftitemid) {
        $options = array('subdirs' => true, 'embed' => false);
        // if ($data->display == RESOURCELIB_DISPLAY_EMBED) {
        //     $options['embed'] = true;
        // }
        file_save_draft_area_files($draftitemid, $context->id, 'mod_vagodel', 'content', 0, $options);
    }
    $files = $fs->get_area_files($context->id, 'mod_vagodel', 'content', 0, 'sortorder', false);

    if (count($files) == 1) {
        // only one file attached, set it as main file automatically
        $file = reset($files);
        file_set_sortorder($context->id, 'mod_vagodel', 'content', 0, $file->get_filepath(), $file->get_filename(), 1);
    }
}

class vagodel_content_file_info extends file_info_stored {
    public function get_parent() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->browser->get_file_info($this->context);
        }
        return parent::get_parent();
    }
    public function get_visible_name() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->topvisiblename;
        }
        return parent::get_visible_name();
    }
}