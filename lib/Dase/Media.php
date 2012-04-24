<?php
Class Dase_Media 
{
	public static $media_types = array(
		'application/pdf',
		'application/json',
		'application/msword',
		'application/atom+xml',
		'application/vnd.google-earth.kml+xml',
		'audio/*',
		'image/*',
		'text/*',
		'video/*',
	);

	public static $sizes = array(
		'aiff' => 2,
		'archive' => 1,
		'atom' => 0,
		'css' => 1,
		'deleted' => 1,
		'doc' => 1,
		'full' => 1,
		'gif' => 1,
		'html' => 1,
		'jpeg' => 1,
		'kml' => 1,
		'large' => 1,
		'medium' => 1,
		'mp3' => 2,
		'mp4' => 2,
		'oga' => 2,
		'ogv' => 2,
		'pdf' => 1,
		'png' => 1,
		'quicktime' => 2,
		'quicktime_stream' => 2,
		'raw' => 2,
		'small' => 1,
		'text' => 1,
		'thumbnail' => 0,
		'tiff' => 2,
		'uploaded_files' => 2,
		'viewitem' => 1,
		'wav' => 2,
		'xml' => 1,
		'xsl' => 1,
	);

	function __construct() {}

	/** 
	 * from php port of Mimeparse
	 * Python code (http://code.google.com/p/mimeparse/)
	 * @author Joe Gregario, Andrew "Venom" K.
	 *
	 * patched (changed split to explode) by Patrick Hochstenbach
	 */
	public static function parseMimeType($mime_type)
	{
		$parts = explode(";", $mime_type);
		$params = array();
		foreach ($parts as $i=>$param) {
			if (strpos($param, '=') !== false) {
				list ($k, $v) = explode('=', trim($param));
				$params[$k] = $v;
			}
		}
		list ($type, $subtype) = explode('/', $parts[0]);
		if (!$subtype) throw new Exception("malformed mime type");
		return array(trim($type), trim($subtype), $params);
	}

	public static function getAcceptedTypes()
	{
		return self::$media_types;
	}

	/** returns type on success, false on failure */
	public static function isAcceptable($content_type)
	{
		$ok_type = false;
		try {
			list($type,$subtype) = Dase_Media::parseMimeType($content_type);
		} catch (Exception $e) {
			return false;
		}
		foreach(Dase_Media::getAcceptedTypes() as $t) {
			list($acceptedType,$acceptedSubtype) = explode('/',$t);
			if($acceptedType == '*' || $acceptedType == $type) {
				if($acceptedSubtype == '*' || $acceptedSubtype == $subtype)
					$ok_type = $type . "/" . $subtype;
			}
		}
		return $ok_type;
	}
}
