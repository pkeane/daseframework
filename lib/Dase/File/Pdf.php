<?php

class Dase_File_Pdf extends Dase_File
{
	protected $metadata = array();

	function __construct($file,$mime='')
	{
		parent::__construct($file,$mime);
	}

	function getMetadata()
	{
		$this->metadata = parent::getMetadata();
		return $this->metadata;
	}

	public function addToCollection($item,$check_for_dups,$path_to_media) 
	{
		$media_file = parent::addToCollection($item,$check_for_dups,$path_to_media);
		$this->makeThumbnail($item,$path_to_media);
		$this->makeViewitem($item,$path_to_media);
		return $media_file;
	}
}

