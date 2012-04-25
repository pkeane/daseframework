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
        if ($this->file_url) {
            $base_dir = $this->config->getMediaDir();
            $file_path = $base_dir.'/'.$this->name;
            @unlink($file_path);
        }
        $this->deleteMetadata();
        if ($this->delete()) {
            return true;
        }
    }

    public function deleteMetadata()
    {
        $v = new Dase_DBO_Value($this->db);
        $v->item_id = $this->id;
        foreach ($v->findAll(1) as $doomed) {
            $doomed->delete();
        }
    }

    public function processUploadedFile(Symfony\Component\HttpFoundation\File\UploadedFile $file) 
    {
        $name = $file->getClientOriginalName();
        $path = $file->getPathName();
        $type = $file->getMimeType();
        if (!is_uploaded_file($path)) {
            $r->renderError(400,'no go upload');
        }
        if (!isset(Dase_File::$types_map[$type])) {
            $r->renderError(415,'unsupported media type: '.$type);
        }

        $base_dir = $this->config->getMediaDir();
        $thumb_dir = $this->config->getMediaDir().'/thumb';
        $view_dir = $this->config->getMediaDir().'/view';

        if (!file_exists($base_dir) || !is_writeable($base_dir)) {
            $r->renderError(403,'media directory not writeable: '.$base_dir);
        }

        if (!file_exists($thumb_dir) || !is_writeable($thumb_dir)) {
            $r->renderError(403,'thumbnail directory not writeable: '.$thumb_dir);
        }

        if (!file_exists($view_dir) || !is_writeable($view_dir)) {
            $r->renderError(403,'view directory not writeable: '.$view_dir);
        }

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $basename = Dase_Util::dirify(pathinfo($name,PATHINFO_FILENAME));

        if ('application/pdf' == $type) {
            $ext = 'pdf';
        }
        if ('application/msword' == $type) {
            $ext = 'doc';
        }
        if ('application/vnd.openxmlformats-officedocument.wordprocessingml.document' == $type) {
            $ext = 'docx';
        }

        $newname = Dase_File::findNextUnique($base_dir,$basename,$ext);
        $new_path = $base_dir.'/'.$newname;
        //move file to new home
        rename($path,$new_path);
        chmod($new_path,0775);
        $size = @getimagesize($new_path);

        $this->name = $newname;
        if (!$this->title) {
            $this->title = $item->name;
        }
        $this->file_url = 'content/file/'.$item->name;
        $this->filesize = filesize($new_path);
        $this->mime = $type;

        $parts = explode('/',$type);
        if (isset($parts[0]) && 'image' == $parts[0]) {

            /****  make thumbnail  ****/

            $thumb_path = $thumb_dir.'/'.$newname;
            $thumb_path = str_replace('.'.$ext,'.jpg',$thumb_path);
            $command = CONVERT." \"$new_path\" -format jpeg -resize '100x100 >' -colorspace RGB $thumb_path";
            $exec_output = array();
            $results = exec($command,$exec_output);
            if (!file_exists($thumb_path)) {
                $this->log->info("failed to write $thumb_path");
            }
            chmod($thumb_path,0775);
            $newname = str_replace('.'.$ext,'.jpg',$newname);
            $this->thumbnail_url = 'content/file/thumb/'.$newname;

            /****  make view  ****/

            $view_path = $view_dir.'/'.$newname;
            $view_path = str_replace('.'.$ext,'.jpg',$view_path);
            $command = CONVERT." \"$new_path\" -format jpeg -resize '640x480 >' -colorspace RGB $view_path";
            $exec_output = array();
            $results = exec($command,$exec_output);
            if (!file_exists($view_path)) {
                $this->log->info("failed to write $view_path");
            }
            chmod($view_path,0775);
            $newname = str_replace('.'.$ext,'.jpg',$newname);
            $this->view_url = 'content/file/view/'.$newname;

        } else {
            $this->thumbnail_url = 	'www/img/mime_icons/'.Dase_File::$types_map[$type]['size'].'.png';
            $this->view_url = 	'www/img/mime_icons/'.Dase_File::$types_map[$type]['size'].'.png';
        }
        if (isset($size[0]) && $size[0]) {
            $this->width = $size[0];
        }
        if (isset($size[1]) && $size[1]) {
            $this->height = $size[1];
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
