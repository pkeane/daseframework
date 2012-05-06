<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

abstract class Dase_File
{
	public static $types_map = array(
		'application/msword' => array('size' => 'doc', 'ext' => 'doc','class'=>'Dase_File_Doc'),
		'application/pdf' => array('size' => 'pdf', 'ext' => 'pdf','class'=>'Dase_File_Pdf'),
		'application/vnd.google-earth.kml+xml' => array('size' => 'kml', 'ext' => 'kml','class'=>'Dase_File_Text'),
		'application/xml' => array('size' => 'xml', 'ext' => 'xml','class'=>'Dase_File_Text'),
		'application/xslt+xml' => array('size' => 'xslt', 'ext' => 'xsl','class'=>'Dase_File_Image'),
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => array('size' => 'doc','ext' => 'docx','class' => 'Dase_File_Doc'),
		'audio/mpeg' => array('size' => 'mp3', 'ext' => 'mp3','class'=>'Dase_File_Audio'),
		'audio/x-mpeg' => array('size' => 'mp3', 'ext' => 'mp3','class'=>'Dase_File_Audio'),
		'audio/wav' => array('size' => 'wav', 'ext' => 'wav','class'=>'Dase_File_Audio'),
		'audio/x-wav' => array('size' => 'wav', 'ext' => 'wav','class'=>'Dase_File_Audio'),
		'audio/mpg' => array('size' => 'mp3', 'ext' => 'mp3','class'=>'Dase_File_Audio'),
		'audio/mp3' => array('size' => 'mp3', 'ext' => 'mp3','class'=>'Dase_File_Audio'),
		'audio/ogg' => array('size' => 'oga', 'ext' => 'oga','class'=>'Dase_File_Audio'),
		'image/gif' => array('size' => 'gif', 'ext' => 'gif','class'=>'Dase_File_Image'),
		'image/jpeg' => array('size' => 'jpeg', 'ext' => 'jpg','class'=>'Dase_File_Image'),
		'image/png' => array('size' => 'png', 'ext' => 'png','class'=>'Dase_File_Image'),
		'image/tiff' => array('size' => 'tiff', 'ext' => 'tif','class'=>'Dase_File_Image'),
	//	'image/tiff' => array('size' => 'tiff', 'ext' => 'tiff','class'=>'Dase_File_Image'),
		'text/css' => array('size' => 'css', 'ext' => 'css','class'=>'Dase_File_Text'),
		'text/html' => array('size' => 'html', 'ext' => 'html','class'=>'Dase_File_Text'),
		'text/plain' => array('size' => 'text', 'ext' => 'txt','class'=>'Dase_File_Text'),
		'text/xml' => array('size' => 'xml', 'ext' => 'xml','class'=>'Dase_File_Text'),
		'video/mp4' => array('size' => 'mp4', 'ext' => 'mp4','class'=>'Dase_File_Video'),
		'video/ogg' => array('size' => 'ogv', 'ext' => 'ogv','class'=>'Dase_File_Video'),
		'video/quicktime' => array('size' => 'quicktime', 'ext' => 'mov','class'=>'Dase_File_Video'),
	);

	protected $metadata = array();
	protected $filepath;
	protected $file_size;
	protected $extension;
	protected $basename; //this INCLUDES the extension
	protected $filename;  //this is the basename minus the extension!!
	protected $mime_type;
	protected $orig_name;
	protected $path_to_media;
	protected $db;
    protected $log;

	protected function __construct($file,$mime='')
	{  //can ONLY be called by subclass
		$this->filepath = $file;
		$this->file_size = filesize($file);
		$path_parts = pathinfo($file);
		if ($mime) {
			$this->mime_type = $mime;
			$this->extension = self::$types_map[$mime]['ext'];
		} else {
			//we need an extension if no mime type passed in
			//todo: will be a problem if no extention is returned in path_parts
			$this->extension = $path_parts['extension'];
		}
		$this->basename = $path_parts['basename'];
		if (isset($path_parts['filename'])) {
			$this->filename = $path_parts['filename']; // since PHP 5.2.0
		} else {
			$this->filename = str_replace("." . $this->extension,'',$path_parts['basename']);
		}

        $this->log = new Logger('file');
        $this->log->pushHandler(new StreamHandler(LOG_FILE,LOG_LEVEL));
	}

	static function getExtension($mime_type)
	{
		if (isset(Dase_File::$types_map[$mime_type])) {
			return Dase_File::$types_map[$mime_type]['ext'];
		} else {
			return false;
		}
	}

	static function newFile($db,$file,$mime='',$orig_name='',$base_path)
	{
		if (!$mime) {
			$mime = Dase_File::getMimeType($file);
		}
		if ($mime) {
			if (!isset(self::$types_map[$mime])) {
				throw new Exception("DASe does not handle $mime mime type ($orig_name)");
			}
			$orig_name = $orig_name ? $orig_name : $file;
			//creates proper subclass
			$dasefile = new self::$types_map[$mime]['class']($file,$mime);
			$dasefile->size = self::$types_map[$mime]['size'];
			$dasefile->ext = self::$types_map[$mime]['ext'];
			$dasefile->mime_type = $mime;
			$dasefile->base_path = $base_path;
			$dasefile->orig_name = $orig_name;
			$dasefile->db = $db;
			return $dasefile;
		} else {
			throw new Exception("cannot determin mime type for $file");
		}
	}

	function getFilepath()
	{
		return $this->filepath;
	}

	function getFilename()
	{
		return $this->filename;
	}

	function getFiletype()
	{
		return $this->mime_type;
	}

	function getFileSize()
	{
		return $this->file_size;
	}

	function getBasename()
	{
		return $this->basename;
	}

	function getOrigName()
	{
		return $this->orig_name;
	}

	public function addToCollection($item,$check_for_dups,$path_to_media)
	{
		$c = $item->getCollection();
		$metadata = $this->getMetadata();

		//prevents 2 files in same collection w/ same md5
		if ($check_for_dups) {
			$prefix = $this->db->table_prefix;
			$sql = "
				SELECT v.value_text
				FROM {$prefix}value v, {$prefix}item i, {$prefix}attribute a
				WHERE i.collection_id = ?
				AND a.ascii_id = ?
				AND v.attribute_id = a.id
				AND i.id = v.item_id
				AND v.value_text = ?
				LIMIT 1
				";
			$hash = $metadata['md5'];
			$dbh = $this->db->getDbh();
			$sth = $dbh->prepare($sql);
			$sth->execute(array($c->id,'admin_checksum',$hash));
			$row = $sth->fetch();
			if ($row && $row['value_text']) {
				throw new Exception('duplicate file');
			}
		}

		$subdir =  Dase_Util::getSubdir($item->serial_number);
		$subdir_path = $path_to_media.'/'.$c->ascii_id.'/'.$this->size.'/'.$subdir;  
		if (!file_exists($subdir_path)) {
			mkdir($subdir_path);
		}

		$target = $path_to_media.'/'.$c->ascii_id.'/'.$this->size.'/'.$subdir.'/'.$item->serial_number.'.'.$this->ext;
		if (file_exists($target)) {
			//make a timestamped backup
			copy($target,$target.'.bak.'.time());
		}
		//should this be try-catch?
		if ($this->copyTo($target)) {
			$media_file = new Dase_DBO_MediaFile($this->db);
			$mediafile_meta = array(
				'file_size','height','width','mime_type','updated','md5'
			);
			foreach ($mediafile_meta as $term) {
				if (isset($metadata[$term])) {
					$media_file->$term = $metadata[$term];
				}
			}
			$media_file->item_id = $item->id;
			$media_file->filename = $item->serial_number.'.'.$this->ext;
			$media_file->size = $this->size;
			$media_file->p_serial_number = $item->serial_number;
			$media_file->p_collection_ascii_id = $c->ascii_id;
			$media_file->insert();
			//will only insert item metadata when attribute name matches 'admin_'+att_name
			foreach ($metadata as $term => $text) {
				//catches UTF8 errors in exif/iptc data
					//actually, no it doesn't :(
				try {
					$item->setValue('admin_'.$term,$text);
				} catch (Exception $e) {
					$this->log->debug("could not write admin $term: $text ERROR: ".$e->getMessage());
				}
			}
		}
		return $media_file;
	}

	function getMetadata()
	{
		$this->metadata['md5'] = md5_file($this->filepath);
		$this->metadata['file_size'] = $this->file_size;
		$this->metadata['filename'] = $this->orig_name;
		$this->metadata['updated'] = date(DATE_ATOM,filemtime($this->filepath)); 
		$this->metadata['mime_type'] = $this->mime_type;
		//for admin_ attributes:
		$this->metadata['upload_date_time'] = date(DATE_ATOM);
		$this->metadata['checksum'] = $this->metadata['md5'];
		return $this->metadata;
	}	

	static function getMimeType($file,$is_url = false)
	{
		if ($is_url) {
			$headers = get_headers($file);
			foreach ($headers as $hdr) {
				$matches = array();
				if (preg_match('@content-type:? ([a-zA-z0-9/]*)@i',$hdr,$matches)) {
					return $matches[1];
				}
			}
		} else {
			$output = array();
			exec("file -i -b \"$file\"",$output);
			$matches = array();
			if (preg_match('@([a-zA-z0-9/]*);?@i',$output[0],$matches)) {
				return $matches[1];
			}
		}
	}

	static function getMTime($file)
	{
		$stat = @stat($file);
		if($stat[9]) {
			return $stat[9];
		} else {
			return false;
		}
	}

	function copyTo($location)
	{
		if (copy($this->filepath,$location)) {
			return true;
		} else {
			throw new Exception("could not copy $this->filepath to $location");
		}
	}

	function moveTo($location)
	{
		if (rename($this->filepath,$location)) {
			return true;
		} else {
			throw new Exception("could not move $this->filepath to $location");
		}
	}

	/** will be invoked by subclass */
	function makeThumbnail($item,$path_to_media)
	{
		$size = $this->size;
		$collection = $item->getCollection();
		$target = $path_to_media.'/'.$collection->ascii_id . '/thumbnail/'.$size.'.jpg';
		if (!file_exists($target)) {
			copy($this->base_path.'/www/images/thumb_icons/'.$size.'.jpg',$target);
		}
		$media_file = new Dase_DBO_MediaFile($this->db);
		$media_file->item_id = $item->id;
		$media_file->filename = $size.'.jpg';
		$media_file->width = 80;
		$media_file->height = 80;
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = 'thumbnail';
		$media_file->file_size = filesize($target);
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		$this->log->info("created $media_file->size $media_file->filename");
	}

	/** will be invoked by subclass */
	function makeViewitem($item,$path_to_media)
	{
		$size = $this->size;
		$collection = $item->getCollection();
		$target = $path_to_media.'/'.$collection->ascii_id . '/viewitem/'.$size.'.jpg';
		if (!file_exists($target)) {
			copy($this->base_path.'/www/images/thumb_icons/'.$size.'.jpg',$target);
		}
		$media_file = new Dase_DBO_MediaFile($this->db);
		$media_file->item_id = $item->id;
		$media_file->filename = $size.'.jpg';
		$media_file->width = 80;
		$media_file->height = 80;
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = 'viewitem';
		$media_file->file_size = filesize($target);
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		$this->log->info("created $media_file->size $media_file->filename");
	}

    public static function findNextUnique($base_dir,$basename,$ext,$iter=0)
    {
        if ($iter) {
            $new_basename = $basename.'_'.$iter;
        } else {
            $new_basename = $basename;
        }
        $checkname = $new_basename.'.'.$ext;
        if (!file_exists($base_dir.'/'.$checkname)) {
            return $new_basename;
        } else {
            $iter++;
            return Dase_File::findNextUnique($base_dir,$basename,$ext,$iter);
        }
    }

}
