<?php

require_once 'Dase/DBO/Autogen/Item.php';

class Dase_DBO_Item extends Dase_DBO_Autogen_Item 
{

    public $sets = array();

    public static function getByTitle($db,$r,$title)
    {
        $item = new Dase_DBO_Item($db);
        $item->title = $title;
        if ($item->findOne()) {
            return $item->asArray($r);
        } else {
            return false;
        }
    }

    public function expunge()
    {
        $this->deleteMedia();
        $this->deleteMetadata();
        if ($this->delete()) {
            return true;
        }
    }

    public function deleteMedia()
    {
        @unlink($this->file_path);
        @unlink($this->thumbnail_path);
        @unlink($this->view_path);
    }

    public function deleteMetadata()
    {
        $v = new Dase_DBO_Value($this->db);
        $v->item_id = $this->id;
        foreach ($v->findAll(1) as $doomed) {
            $doomed->delete();
        }
    }

    public function deleteFiles()
    {

    }

    public function processUploadedFile(Symfony\Component\HttpFoundation\File\UploadedFile $file) 
    {
        $orig_name = $file->getClientOriginalName();
        $path = $file->getPathName();
        $mime = $file->getMimeType();
        if (!is_uploaded_file($path)) {
            $r->renderError(400,'no go upload');
        }
        if (!isset(Dase_File::$types_map[$mime])) {
            $r->renderError(415,'unsupported media type: '.$mime);
        }
        $media_dir = $this->config->getMediaDir();
        if (!file_exists($media_dir) || !is_writeable($media_dir)) {
            $r->renderError(403,'media directory not writeable: '.$media_dir);
        }

        $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
        $basename = Dase_Util::dirify(pathinfo($orig_name,PATHINFO_FILENAME));

        if ('application/pdf' == $mime) {
            $ext = 'pdf';
        }
        if ('application/msword' == $mime) {
            $ext = 'doc';
        }
        if ('application/vnd.openxmlformats-officedocument.wordprocessingml.document' == $mime) {
            $ext = 'docx';
        }

        $name = Dase_File::findNextUnique($media_dir,$basename,$ext);
        $file_path = $media_dir.'/'.$name;
        //move file to new home
        rename($path,$file_path);
        chmod($file_path,0775);

        $size = @getimagesize($file_path);
        if (isset($size[0]) && $size[0]) { 
            $this->width = $size[0]; 
        }
        if (isset($size[1]) && $size[1]) { 
            $this->height = $size[1]; 
        }
        $this->name = $name;
        $this->file_ext = $ext;
        $this->file_path = $file_path;
        $this->file_url = 'content/file/'.$name;
        $this->filesize = filesize($file_path);
        $this->mime = $mime;

        $this->makeDerivatives($media_dir);
        return $name;
    }

    public function makeDerivatives($media_dir) 
    {
        if (!$this->name) {
            return;
        }
        $thumb_dir = $media_dir.'/thumb';
        if (!file_exists($thumb_dir) || !is_writeable($thumb_dir)) {
            $r->renderError(403,'thumbnail directory not writeable: '.$thumb_dir);
        }
        $view_dir = $media_dir.'/view';
        if (!file_exists($view_dir) || !is_writeable($view_dir)) {
            $r->renderError(403,'view directory not writeable: '.$view_dir);
        }
        $parts = explode('/',$this->mime);
        if (isset($parts[0]) && 'image' == $parts[0]) {

            /****  make thumbnail  ****/

            $thumb_name = str_replace('.'.$this->file_ext,'.jpg',$this->name);
            $thumb_path = $thumb_dir.'/'.$thumb_name;
            $command = CONVERT." \"$this->file_path\" -format jpeg -resize '100x100 >' -colorspace RGB $thumb_path";
            $exec_output = array();
            $results = exec($command,$exec_output);
            if (!file_exists($thumb_path)) {
                $this->log->info("failed to write $thumb_path");
            }
            chmod($thumb_path,0775);
            $this->thumbnail_url = 'content/file/thumb/'.$thumb_name;
            $this->thumb_path = $thumb_path;

            /****  make view  ****/

            $view_name = str_replace('.'.$this->file_ext,'.jpg',$this->name);
            $view_path = $view_dir.'/'.$view_name;
            $command = CONVERT." \"$this->file_path\" -format jpeg -resize '640x480 >' -colorspace RGB $view_path";
            $exec_output = array();
            $results = exec($command,$exec_output);
            if (!file_exists($view_path)) {
                $this->log->info("failed to write $view_path");
            }
            chmod($view_path,0775);
            $this->view_url = 'content/file/view/'.$view_name;
            $this->view_path = $view_path;

        } else {
            $this->thumbnail_url = 	'www/img/mime_icons/'.Dase_File::$types_map[$this->mime]['size'].'.png';
            $this->view_url = 	'www/img/mime_icons/'.Dase_File::$types_map[$this->mime]['size'].'.png';
        }
    }

    public function asArray($r)
    {
        $set = array();
        $set['id'] = $r->app_root.'/item/'.$this->name;
        $set['title'] = $this->title;
        $set['name'] = $this->name;
        $set['item_id'] = $this->id;
        if ($this->body) {
            $set['body'] = $this->body;
        }
        $set['created'] = $this->created;
        $set['updated'] = $this->updated;
        $set['links'] = array();
        $set['links']['self'] = $r->app_root.'/'.$this->url.'.json';
        if ($this->file_url) {
            $set['links']['file'] = $r->app_root.'/'.$this->file_url;
        }
        if ($this->thumbnail_url) {
            $set['links']['thumbnail'] = $r->app_root.'/'.$this->thumbnail_url;
        }
        if ($this->view_url) {
            $set['links']['view'] = $r->app_root.'/'.$this->view_url;
        }
        if ($this->filesize) {
            $set['filesize'] = $this->filesize;
        }
        if ($this->mime) {
            $set['mime'] = $this->mime;
        }
        if ($this->width) {
            $set['width'] = $this->width;
        }
        if ($this->height) {
            $set['height'] = $this->height;
        }
        return $set;
    }

    public function asJson($r)
    {
        return Dase_Json::get($this->asArray($r));
    }

    public static function findUniqueName($name,$iter=0)
    {
        if ($iter) {
            $checkname = $name.'_'.$iter;
        } else {
            $checkname = $name;
        }
        $item = new Dase_DBO_Item($this->db);
        $item->name = $checkname;
        if (!$item->findOne()) {
            return $checkname;
        } else {
            $iter++;
            return Dase_DBO_Item::findUniqueName($name,$iter);
        }
    }


}
