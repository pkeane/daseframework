<?php

include_once 'getid3/getid3.php';

class Dase_File_Audio extends Dase_File
{
	protected $metadata = array();

	function __construct($file,$mime='')
	{
		parent::__construct($file,$mime);
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
			if (isset($id3['comments']) && 
				isset($id3['playtime_seconds']) &&
				isset($id3['bitrate']) &&
				isset($id3['audio'])) {
					$this->metadata['duration'] =  $id3['playtime_seconds'];
					$this->metadata['bitrate'] =  $id3['bitrate'];
					$this->metadata['channels'] = $id3['audio']['channels'];
					$this->metadata['samplingrate'] = $id3['audio']['sample_rate'];
					$this->metadata['audio_title'] = $id3['comments']['title'][0]; 
					$this->metadata['audio_artist'] = $id3['comments']['artist'][0];
					if (isset($id3['comments']['comment'])) {
						$this->metadata['audio_comment'] = $id3['comments']['comment'][0];
					}
					if (isset($id3['comments']['album'])) {
						$this->metadata['audio_album'] = $id3['comments']['album'][0];
					}
					if (isset($id3['comments']['year'])) {
						$this->metadata['audio_year'] = $id3['comments']['year'][0];
					}
					if (isset($id3['comments']['encoded_by'])) {
						$this->metadata['audio_encoded_by'] = $id3['comments']['encoded_by'][0];
					}
					if (isset($id3['comments']['track'])) {
						$this->metadata['audio_track'] = $id3['comments']['track'][0];
					}
					if (isset($id3['comments']['genre'])) {
						$this->metadata['audio_genre'] = $id3['comments']['genre'][0];
					}
					if (isset($id3['comments']['totaltracks'])) {
						$this->metadata['audio_totaltracks'] = $id3['comments']['totaltracks'][0];
					}
				}
		}
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

