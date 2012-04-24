<?php

class Dase_File_Image extends Dase_File
{
	protected $metadata = array();

	function __construct($file,$mime='')
	{
		parent::__construct($file,$mime);
	}

	function addToCollection($item,$check_for_dups,$path_to_media)
	{
		$media_file = parent::addToCollection($item,$check_for_dups,$path_to_media);
		$rotate = 0;
		if (isset($this->metadata['exif_orientation'])) {
			if (6 == $this->metadata['exif_orientation']) {
				$rotate = 90;
			}
			if (8 == $this->metadata['exif_orientation']) {
				$rotate = 270;
			}
		}
		$this->makeThumbnail($item,$path_to_media,$rotate);
		$this->makeViewitem($item,$path_to_media,$rotate);
		$this->makeSizes($item,$path_to_media,$rotate);
		return $media_file;
	}

	function getIptc()
	{   
		$iptc_metadata = array();
		$iptc_table['2#005'] = 'iptc_object_name';
		$iptc_table['2#015'] = 'iptc_category';
		$iptc_table['2#020'] = 'iptc_supplemental_category';
		$iptc_table['2#025'] = 'iptc_keywords';
		$iptc_table['2#055'] = 'iptc_date_created';
		$iptc_table['2#060'] = 'iptc_time_created';
		$iptc_table['2#062'] = 'iptc_digital_creation_date';
		$iptc_table['2#063'] = 'iptc_digital_creation_time';
		$iptc_table['2#065'] = 'iptc_originating_program';
		$iptc_table['2#070'] = 'iptc_program_version';
		$iptc_table['2#080'] = 'iptc_by_line';
		$iptc_table['2#085'] = 'iptc_by_line_title';
		$iptc_table['2#090'] = 'iptc_city';
		$iptc_table['2#092'] = 'iptc_sub_location';
		$iptc_table['2#095'] = 'iptc_province_state';
		$iptc_table['2#100'] = 'iptc_country_primary_location_code';
		$iptc_table['2#101'] = 'iptc_country_primary_location_name';
		$iptc_table['2#105'] = 'iptc_headline';
		$iptc_table['2#110'] = 'iptc_credit';
		$iptc_table['2#115'] = 'iptc_source';
		$iptc_table['2#116'] = 'iptc_copyright_notice';
		$iptc_table['2#118'] = 'iptc_contact';
		$iptc_table['2#120'] = 'iptc_caption_abstract';
		$iptc_table['2#122'] = 'iptc_caption_writer';
		$iptc_table['2#131'] = 'iptc_image_orientation';
		$size = getimagesize ( $this->filepath, $info);       
		if(is_array($info) && isset($info["APP13"])) {   
			$iptc = iptcparse($info["APP13"]);
			if (is_array($iptc)) {
				foreach (array_keys($iptc) as $k) {             
					foreach($iptc[$k] as $val) {
						if (isset($iptc_table[$k]) && $val) {
							//NOTE THAT REPEAT FIELDS ARE OK!!!!!!!!!!!
							$iptc_metadata[$iptc_table[$k]][]  = $val;
						}
					}
				}                 
			}
		}            
		foreach($iptc_metadata as $k => $v) {

			//collapse multiples into a csv
			$this->metadata[$k] = join(',',$v);

				//keep having copyright symbol crashes
				if (strpos($this->metadata[$k]," \xA9")) {
						$this->metadata[$k] = str_replace( " \xA9", ' copyright', $this->metadata[$k]); 
				}

		}
		return $iptc_metadata;
	}

	function getMetadata()
	{
		$this->metadata = parent::getMetadata();
		$this->getIptc();
		$this->getExif();
		$size = getimagesize($this->filepath);
		$this->metadata['width'] =  $size[0];
		$this->metadata['height'] = $size[1];
		return $this->metadata;
	}


	function getExif()
	{

		//exif_read_data only w/ jpg & tif
		if (strpos($this->mime_type,'jpg') ||
			strpos($this->mime_type, 'tif') ||
			strpos($this->mime_type, 'jpeg') ||
			strpos($this->mime_type, 'tiff'))
		{
			$exif_table['FileName'] = 'exif_filename';
			//$exif_table['FileDateTime'] = 'exif_filedatetime';
			$exif_table['FileSize'] = 'exif_filesize';
			$exif_table['FileType'] = 'exif_filetype';
			$exif_table['MimeType'] = 'exif_mimetype';
			$exif_table['Make'] = 'exif_make';
			$exif_table['Model'] = 'exif_model';
			$exif_table['Orientation'] = 'exif_orientation';
		//	$exif_table['XResolution'] = 'exif_xresolution';
		//	$exif_table['YResolution'] = 'exif_yresolution';
		//	$exif_table['ResolutionUnit'] = 'exif_resolutionunit';
			$exif_table['DateTime'] = 'exif_datetime';
		//	$exif_table['YCbCrPositioning'] = 'exif_ycbcrpositioning';
		//	$exif_table['Exif_IFD_Pointer'] = 'exif_ifd_pointer';
		//	$exif_table['ExposureTime'] = 'exif_exposuretime';
		//	$exif_table['FNumber'] = 'exif_fnumber';
		//	$exif_table['ExifVersion'] = 'exif_exifversion';
		//	$exif_table['DateTimeOriginal'] = 'exif_datetimeoriginal';
		//	$exif_table['DateTimeDigitized'] = 'exif_datetimedigitized';
		//	$exif_table['ComponentsConfiguration'] = 'exif_componentsconfiguration';
		//	$exif_table['CompressedBitsPerPixel'] = 'exif_compressedbitsperpixel';
		//	$exif_table['ShutterSpeedValue'] = 'exif_shutterspeedvalue';
		//	$exif_table['ApertureValue'] = 'exif_aperturevalue';
		//	$exif_table['ExposureBiasValue'] = 'exif_exposurebiasvalue';
		//	$exif_table['MaxApertureValue'] = 'exif_maxaperturevalue';
		//	$exif_table['MeteringMode'] = 'exif_meteringmode';
			$exif_table['ImageType'] = 'exif_imagetype';
		//	$exif_table['FirmwareVersion'] = 'exif_firmwareversion';
		//	$exif_table['ImageNumber'] = 'exif_imagenumber';
			$exif_table['OwnerName'] = 'exif_ownername';
			$exif_metadata = array();
			try {
				//suppressing warning here
				@$exif = exif_read_data($this->filepath);
			} catch(Exception $e) {
				$this->log->debug('exif error: '.$e->getMessage());
			}
			if (is_array($exif)) {
				foreach ($exif as $k => $val) {
					if (isset($exif_table[$k]) && $val) {
						$exif_metadata[$exif_table[$k]] = $val;
					}
				}
				foreach ($exif_metadata as $k => $v) {
					$this->metadata[$k] = $v;
				}
				return $exif_metadata;
			}
		}
	}

	function makeThumbnail($item,$path_to_media,$rotate)
	{
		$collection = $item->getCollection();
		$subdir = Dase_Util::getSubdir($item->serial_number);
		$thumbnail = $path_to_media.'/'.$collection->ascii_id.'/thumbnail/'.$subdir.'/'.$item->serial_number.'_100.jpg';  
		$subdir_path = $path_to_media.'/'.$collection->ascii_id.'/thumbnail/'.$subdir;  
		if (!file_exists($subdir_path)) {
			mkdir($subdir_path);
		}
		$command = CONVERT." \"$this->filepath\" -format jpeg -rotate $rotate -resize '100x100 >' -colorspace RGB $thumbnail";
		$exec_output = array();
		$results = exec($command,$exec_output);
		if (!file_exists($thumbnail)) {
			$this->log->info("failed to write $thumbnail");
		}
		$file_info = getimagesize($thumbnail);

		$media_file = new Dase_DBO_MediaFile($this->db);
		$media_file->item_id = $item->id;
		$media_file->filename = $item->serial_number.'_100.jpg';
		if ($file_info) {
			$media_file->width = $file_info[0];
			$media_file->height = $file_info[1];
		}
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = 'thumbnail';
		$media_file->md5 = md5_file($thumbnail);
		$media_file->updated = date(DATE_ATOM);
		$media_file->file_size = filesize($thumbnail);
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		$this->log->info("created $media_file->size $media_file->filename");
	}

	function makeViewitem($item,$path_to_media,$rotate)
	{
		$collection = $item->getCollection();
		$subdir = Dase_Util::getSubdir($item->serial_number);
		$viewitem = $path_to_media.'/'.$collection->ascii_id.'/viewitem/'.$subdir.'/'.$item->serial_number.'_400.jpg';  
		$subdir_path = $path_to_media.'/'.$collection->ascii_id.'/viewitem/'.$subdir;  
		if (!file_exists($subdir_path)) {
			mkdir($subdir_path);
		}
		$command = CONVERT." \"$this->filepath\" -format jpeg -rotate $rotate -resize '400x400 >' -colorspace RGB $viewitem";
		$exec_output = array();
		$results = exec($command,$exec_output);
		if (!file_exists($viewitem)) {
			$this->log->info("failed to write $viewitem");
		}
		$file_info = getimagesize($viewitem);

		$media_file = new Dase_DBO_MediaFile($this->db);
		$media_file->item_id = $item->id;
		$media_file->filename = $item->serial_number . '_400.jpg';
		if ($file_info) {
			$media_file->width = $file_info[0];
			$media_file->height = $file_info[1];
		}
		$media_file->mime_type = 'image/jpeg';
		$media_file->size = 'viewitem';
		$media_file->md5 = md5_file($viewitem);
		$media_file->updated = date(DATE_ATOM);
		$media_file->file_size = filesize($viewitem);
		$media_file->p_collection_ascii_id = $collection->ascii_id;
		$media_file->p_serial_number = $item->serial_number;
		$media_file->insert();
		$this->log->info("created $media_file->size $media_file->filename");
	}

	function makeSizes($item,$path_to_media,$rotate)
	{
		$collection = $item->getCollection();
		$image_properties = array(
			'small' => array(
				'geometry'        => '640x480',
				'max_height'      => '480',
				'size_tag'        => '_640'
			),
			'medium' => array(
				'geometry'        => '800x600',
				'max_height'      => '600',
				'size_tag'        => '_800'
			),
			'large' => array(
				'geometry'        => '1024x768',
				'max_height'      => '768',
				'size_tag'        => '_1024'
			),
			'full' => array(
				'geometry'        => '3600x2700',
				'max_height'      => '2700',
				'size_tag'        => '_3600'
			),
		);
		$last_width = '';
		$last_height = '';
		$subdir = Dase_Util::getSubdir($item->serial_number);
		foreach ($image_properties as $size => $size_info) {
			$newimage = $path_to_media.'/'.$collection->ascii_id.'/'.$size.'/'.$subdir.'/'.$item->serial_number.$size_info['size_tag'].'.jpg';  
			$subdir_path = $path_to_media.'/'.$collection->ascii_id.'/'.$size.'/'.$subdir;  
			if (!file_exists($subdir_path)) {
				mkdir($subdir_path);
			}
			$command = CONVERT." \"$this->filepath\" -format jpeg -rotate $rotate -resize '$size_info[geometry] >' -colorspace RGB $newimage";
			$exec_output = array();
			$results = exec($command,$exec_output);
			if (!file_exists($newimage)) {
				$this->log->debug("failed to write $size image");
				$this->log->debug("UNSUCCESSFUL: $command");
			}
			$file_info = getimagesize($newimage);

			//create the media_file entry
			$media_file = new Dase_DBO_MediaFile($this->db);
			$media_file->item_id = $item->id;
			$media_file->filename = $item->serial_number.$size_info['size_tag'].".jpg";
			if ($file_info) {
				$media_file->width = $file_info[0];
				$media_file->height = $file_info[1];
			}

			if (($media_file->width <= $last_width) && ($media_file->height <= $last_height)) {
				return;
			}

			$last_width = $media_file->width;
			$last_height = $media_file->height;
			$media_file->mime_type = 'image/jpeg';
			$media_file->size = $size;
			$media_file->md5 = md5_file($newimage);
			$media_file->updated = date(DATE_ATOM);
			$media_file->file_size = filesize($newimage);
			$media_file->p_collection_ascii_id = $collection->ascii_id;
			$media_file->p_serial_number = $item->serial_number;
			$media_file->insert();
			$this->log->info("created $media_file->size $media_file->filename");
		}
		return;
	}
}
