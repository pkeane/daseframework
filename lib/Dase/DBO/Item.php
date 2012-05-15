<?php

require_once 'Dase/DBO/Autogen/Item.php';

class Dase_DBO_Item extends Dase_DBO_Autogen_Item 
{

    public $sets = array();
    public $metadata_extended = array();
    public $metadata = array();

    public static function getBySernum($db,$serial_number)
    {
        $item = new Dase_DBO_Item($db);
        $item->serial_number = $serial_number;
        return $item->findOne();
    }

    public static function retrieveSet($db,$r) 
    {
        $type = $r->get('type');
        $att = $r->get('att');
        $val = $r->get('val');
        $q = $r->get('q');
        $sort = $r->get('sort');

        $r->assign('type',$type);
        $r->assign('att',$att);
        $r->assign('val',$val);
        $r->assign('q',$q);
        $r->assign('sort',$sort);

        $items = array();

        //ignore $q if $att & $val
        if ($att && $val) {
            $exec_array = array($att,$val);
            $sql = "
                SELECT item.* 
                FROM value, attribute,item
                WHERE attribute.ascii_id = ?
                AND value.text = ?
                AND value.attribute_id = attribute.id
                AND item.id = value.item_id
                ";
            if ($type) {
                $sql .= "AND item.type = ?";
                $exec_array[] = $type;
                   
            }
            if ($sort) {
                $sql .= "ORDER BY item.$sort"; 
            } else {
                $sql .= "ORDER BY item.id DESC"; 
            }
            //print_r($exec_array); exit;
            $item = new Dase_DBO_Item($db);
            $sth = $db->getDbh()->prepare($sql);
            $sth->setFetchMode(PDO::FETCH_INTO,$item);
            $sth->execute($exec_array);
            while ($item = $sth->fetch()) {
                $items[] = clone($item);
            }
            //$items = $sth->fetchAll();
        } elseif ($q) {
            $q = "%$q%";
            $exec_array = array($q,$q);
            $sql = "
                SELECT item.*
                FROM item 
                WHERE body like ? 
                OR title like ? 
                ";
            if ($type) {
                $sql .= "AND type = ?";
                $exec_array[] = $type;
                   
            }
            if ($sort) {
                $sql .= "ORDER BY item.$sort";
            } else {
                $sql .= "ORDER BY item.id DESC"; 
            }
            $item = new Dase_DBO_Item($db);
            $sth = $db->getDbh()->prepare($sql);
            $sth->setFetchMode(PDO::FETCH_INTO,$item);
            $sth->execute($exec_array);
            while ($item = $sth->fetch()) {
                $items[] = clone($item);
            }
            //$items = $sth->fetchAll();
        } else {
            //only deal w/ sort & type
            $items = new Dase_DBO_Item($db);
            if ($type) {
                $items->type = $type;
            }
            if ($sort) {
                $items->orderBy($sort);
            } else {
                $items->orderBy('id DESC');
            }
            $items = $items->findAll(1);
        }
        return $items;
    }


    public static function processCsv($db,$file,$user)
    {

        $path = $file->getPathName();
        $row = 0;
        if (($handle = fopen($path, "r")) !== FALSE) {
            $cols = fgetcsv($handle, 1000, ",");
            if (!in_array('title',$cols)) {
                return 0;
            }
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ( count($data) >= 3 ) {
                    $item = new Dase_DBO_Item($db);
                    $i = 0;
                    foreach ($data as $cell) {
                        $column = $cols[$i];
                        if ($item->hasMember($column)) {
                            $item->$column = $cell;
                        }
                        $i++;
                    }
                    $item->serial_number = Dase_DBO_Item::getUniqueSerialNumber($db);
                    $item->url = 'content/item/'.$item->serial_number;
                    $item->created_by = $user->eid;
                    $item->created = date(DATE_ATOM);
                    $item->updated_by = $user->eid;
                    $item->updated = date(DATE_ATOM);
                    $item->insert();
                    $row++;
                }
            }
            fclose($handle);
        }
        return $row;
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
        $this->deleteMedia();
        $this->file_path = '';
        $this->thumbnail_path = '';
        $this->view_path = '';
        $this->file_url = '';
        $this->thumbnail_url = '';
        $this->view_url = '';
        $this->filesize = 0;
        $this->file_ext = '';
        $this->file_original_name = '';
        $this->mime = '';
        $this->width = 0;
        $this->height = 0;
        $this->update();
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

        if ('application/pdf' == $mime) {
            $ext = 'pdf';
        }
        if ('application/msword' == $mime) {
            $ext = 'doc';
        }
        if ('application/vnd.openxmlformats-officedocument.wordprocessingml.document' == $mime) {
            $ext = 'docx';
        }

        $file_path = $media_dir.'/'.$this->serial_number.'.'.$ext;
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
        $this->file_ext = $ext;
        $this->file_path = $file_path;
        $this->file_url = 'content/file/'.$this->serial_number.'.'.$ext;
        $this->filesize = filesize($file_path);
        $this->file_original_name = $orig_name;
        $this->mime = $mime;

        $this->makeDerivatives($media_dir);
    }

    public function makeDerivatives($media_dir) 
    {
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

            $thumb_path = $thumb_dir.'/'.$this->serial_number.'.jpg';
            $command = CONVERT." \"$this->file_path\" -format jpeg -resize '100x100 >' -colorspace RGB $thumb_path";
            $exec_output = array();
            $results = exec($command,$exec_output);
            if (!file_exists($thumb_path)) {
                $this->log->info("failed to write $thumb_path");
            }
            chmod($thumb_path,0775);
            $this->thumbnail_url = 'content/file/thumb/'.$this->serial_number.'.jpg';
            $this->thumbnail_path = $thumb_path;

            /****  make view  ****/

            $view_path = $view_dir.'/'.$this->serial_number.'.jpg';
            $command = CONVERT." \"$this->file_path\" -format jpeg -resize '640x480 >' -colorspace RGB $view_path";
            $exec_output = array();
            $results = exec($command,$exec_output);
            if (!file_exists($view_path)) {
                $this->log->info("failed to write $view_path");
            }
            chmod($view_path,0775);
            $this->view_url = 'content/file/view/'.$this->serial_number.'.jpg';
            $this->view_path = $view_path;

        } else {
            $this->thumbnail_url = 	'www/img/mime_icons/'.Dase_File::$types_map[$this->mime]['size'].'.png';
            $this->view_url = 	'www/img/mime_icons/'.Dase_File::$types_map[$this->mime]['size'].'.png';
        }
    }

    public function getAsArray($r)
    {
        $set = array();
        $set['id'] = $r->app_root.'/content/item/'.$this->serial_number;
        $set['title'] = $this->title;
        $set['serial_number'] = $this->serial_number;
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
        if ($this->lat) {
            $set['lat'] = $this->lat;
        }
        if ($this->lng) {
            $set['lng'] = $this->lng;
        }
        $set['metadata'] = $this->getMetadata($r);
        $set['metadata_extended'] = $this->metadata_extended;
        return $set;
    }

    public function getMetadata($r)
    {
        $metadata = array();
        $metadata_extended = array();
        $sql = "
            SELECT attribute.ascii_id, attribute.name, value.id, value.text
            FROM attribute,value
            WHERE attribute.id = value.attribute_id
            AND value.item_id = ?
            ";
        $sth = $this->db->getDbh()->prepare($sql);
        $sth->execute(array($this->id));
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        while ($row = $sth->fetch()) {
            if (!isset($metadata[$row['ascii_id']])) {
                $metadata[$row['ascii_id']] = array();
            }
            $metadata[$row['ascii_id']][] = $row['text'];
            if (!isset($metadata_extended[$row['ascii_id']])) {
                $metadata_extended[$row['ascii_id']] = array();
                $metadata_extended[$row['ascii_id']]['label'] = $row['name'];
                $metadata_extended[$row['ascii_id']]['values'] = array();
            }
            $metadata_extended[$row['ascii_id']]['values'][] = array('text' => $row['text'],'edit' => $r->app_root.'/content/items/'.$this->id.'/metadata/'.$row['id']);

        }
        $this->metadata = $metadata;
        $this->metadata_extended = $metadata_extended;
        return $metadata;
    }

    public function getAsJson($r)
    {
        return Dase_Json::get($this->getAsArray($r));
    }

    public static function getUniqueSerialNumber($db,$serial_number=null)
    {
        if (!$serial_number) {
            $serial_number = dechex(mt_rand(1048576,16777215));
            return Dase_DBO_Item::getUniqueSerialNumber($db,$serial_number);
        }
        $serial_number = Dase_Util::dirify($serial_number);
        $item = new Dase_DBO_Item($db);
        $item->serial_number = $serial_number;
        if ($item->findOne()) {
            $serial_number = dechex(mt_rand(1048576,16777215));
            return Dase_DBO_Item::getUniqueSerialNumber($db,$serial_number);
        } else {
            return $serial_number;
        }
    }

}
