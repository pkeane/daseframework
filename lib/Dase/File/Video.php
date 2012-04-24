<?php

include_once 'getid3/getid3.php';

class Dase_File_Video extends Dase_File
{
	protected $metadata = array();

	function __construct($file,$mime='')
	{
		parent::__construct($file,$mime);
	}

	public function addToCollection($item,$check_for_dups,$path_to_media) 
	{
		$media_file = parent::addToCollection($item,$check_for_dups,$path_to_media);
		$this->makeThumbnail($item,$path_to_media);
		$this->makeViewitem($item,$path_to_media);
		return $media_file;
	}

	function getMetadata()
	{
		$this->metadata = parent::getMetadata();
		$getid3 = new getid3;
		$getid3->encoding = 'UTF-8';
		try {
			$getid3->Analyze($this->filepath);
			$id3 = $getid3->info;
		}
		catch (Exception $e) {
			echo 'An error occured: ' .  $e->message;
		}
		if (is_array($id3)) {
			if ( isset($id3['video']) ) {
				$v = $id3['video'];
				if ($v['dataformat']) {
				$this->metadata['dataformat'] = $v['dataformat'];
				}
				if ($v['frame_rate']) {
				$this->metadata['frame_rate'] = $v['frame_rate'];
				}
				if ($v['resolution_x']) {
				$this->metadata['width'] = $v['resolution_x'];
				}
				if ($v['resolution_y']) {
				$this->metadata['height'] = $v['resolution_y'];
				}
			}
		}
		return $this->metadata;
	}
}
