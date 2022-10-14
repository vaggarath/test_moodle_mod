<?php

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/vagodel/lib.php");

function vagodel_set_mainfile($data) { //creation
    global $DB;
    $fs = get_file_storage();
    $cmid = $data->coursemodule;
    $draftitemid = $data->files;

    $context = context_module::instance($cmid);
    if ($draftitemid) {
        $options = array('subdirs' => true, 'embed' => false);
        file_save_draft_area_files($draftitemid, $context->id, 'mod_vagodel', 'content', 0, $options);
    }
    $files = $fs->get_area_files($context->id, 'mod_vagodel', 'content', 0, 'sortorder', false);
}

function vagodel_replace_mainfile($data) { //replace
    global $DB;
    $fs = get_file_storage();
    $cmid = $data->coursemodule;
    $draftitemid = $data->files;

    $context = context_module::instance($cmid);

    if ($draftitemid) {
        //first look for files, if they exist : delete them

        if ($files = $fs->get_area_files($context->id, 'mod_vagodel', 'content', '0', 'sortorder', false)) {
            foreach ($files as $file) { //TODO : Vérifier pour chaque fichier. Là on supprime tout au lieu de check si les trois fichiers sont remplacés ou non
                //let's delete... So... How ?:D
                $file->delete(); //si c'est aussi con j'me tire une balle
                //fonctionne mais : Ne supprime pas draft, (logique) mais pas non plus les instances/component user
            }
        }

        $options = array('subdirs' => true, 'embed' => false);
        file_save_draft_area_files($draftitemid, $context->id, 'mod_vagodel', 'content', 0, $options);
    }

    $files = $fs->get_area_files($context->id, 'mod_vagodel', 'content', 0, 'sortorder', false);
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