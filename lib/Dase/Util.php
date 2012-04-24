<?php
Class Dase_Util 
{
		function __construct() {}

		public static function getVersion()
		{
				$ver = explode( '.', PHP_VERSION );
				return $ver[0] . $ver[1] . $ver[2];
		}

		//from http://us3.php.net/readfile
		public static function readfileChunked ($filename) {
				$chunksize = 1*(1024*1024); // how many bytes per chunk
				$buffer = '';
				$handle = fopen($filename, 'rb');
				if ($handle === false) {
						return false;
				}
				while (!feof($handle)) {
						$buffer = fread($handle, $chunksize);
						print $buffer;
						//added:
						ob_flush();
						flush();
				}
				return fclose($handle);
		} 

		/**
		 * linear regression function
		 * @param $x array x-coords
		 * @param $y array y-coords
		 * @returns array() m=>slope, b=>intercept
		 */
		public static function linReg($x, $y) {

				// calculate number points
				$n = count($x);

				// ensure both arrays of points are the same size
				if ($n != count($y)) {
						trigger_error("linear_regression(): Number of elements in coordinate arrays do not match.", E_USER_ERROR);
				}

				// calculate sums
				$x_sum = array_sum($x);
				$y_sum = array_sum($y);

				$xx_sum = 0;
				$xy_sum = 0;

				for($i = 0; $i < $n; $i++) {
						$xy_sum+=($x[$i]*$y[$i]);
						$xx_sum+=($x[$i]*$x[$i]);
				}

				// calculate slope
				$m = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));

				// calculate intercept
				$b = ($y_sum - ($m * $x_sum)) / $n;

				// return result
				return array("m"=>$m, "b"=>$b);
		}

		//from http://mobiforge.com/developing/story/content-delivery-mobile-devices
		public static function rangeDownload($file) {
				$fp = @fopen($file, 'rb');
				if (!$fp) {
						echo "no such file";
						return;
				}
				$size   = filesize($file); // File size
				$length = $size;           // Content length
				$start  = 0;               // Start byte
				$end    = $size - 1;       // End byte
				// Now that we've gotten so far without errors we send the accept range header
				/* At the moment we only support single ranges.
				 * Multiple ranges requires some more work to ensure it works correctly
				 * and comply with the spesifications: http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
				 *
				 * Multirange support annouces itself with:
				 * header('Accept-Ranges: bytes');
				 *
				 * Multirange content must be sent with multipart/byteranges mediatype,
				 * (mediatype = mimetype)
				 * as well as a boundry header to indicate the various chunks of data.
				 */
				header("Accept-Ranges: 0-$length");
				// header('Accept-Ranges: bytes');
				// multipart/byteranges
				// http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
				if (isset($_SERVER['HTTP_RANGE'])) {

						$c_start = $start;
						$c_end   = $end;
						// Extract the range string
						list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
						// Make sure the client hasn't sent us a multibyte range
						if (strpos($range, ',') !== false) {

								// (?) Shoud this be issued here, or should the first
								// range be used? Or should the header be ignored and
								// we output the whole content?
								header('HTTP/1.1 416 Requested Range Not Satisfiable');
								header("Content-Range: bytes $start-$end/$size");
								// (?) Echo some info to the client?
								exit;
						}
						// If the range starts with an '-' we start from the beginning
						// If not, we forward the file pointer
						// And make sure to get the end byte if spesified
						if ($range0 == '-') {

								// The n-number of the last bytes is requested
								$c_start = $size - substr($range, 1);
						}
						else {

								$range  = explode('-', $range);
								$c_start = $range[0];
								$c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
						}
						/* Check the range and make sure it's treated according to the specs.
						 * http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
						 */
						// End bytes can not be larger than $end.
						$c_end = ($c_end > $end) ? $end : $c_end;
						// Validate the requested range and return an error if it's not correct.
						if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {

								header('HTTP/1.1 416 Requested Range Not Satisfiable');
								header("Content-Range: bytes $start-$end/$size");
								// (?) Echo some info to the client?
								exit;
						}
						$start  = $c_start;
						$end    = $c_end;
						$length = $end - $start + 1; // Calculate new content length
						fseek($fp, $start);
						header('HTTP/1.1 206 Partial Content');
				}
				// Notify the client the byte range we'll be outputting
				header("Content-Range: bytes $start-$end/$size");
				header("Content-Length: $length");

				// Start buffered download
				$buffer = 1024 * 8;
				while(!feof($fp) && ($p = ftell($fp)) <= $end) {

						if ($p + $buffer > $end) {

								// In case we're only outputtin a chunk, make sure we don't
								// read past the length
								$buffer = $end - $p + 1;
						}
						set_time_limit(0); // Reset time limit for big files
						echo fread($fp, $buffer);
						flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
				}
				fclose($fp);
		}



		//todo: work on this:
		public static function isUrl($str)
		{
				if ('http://' == substr($str,0,7) || 'https://' == substr($str,0,8)) {
						return true;
				} else {
						return false;
				}
		}

		public static function unhtmlspecialchars( $string )
		{
				$string = str_replace ( '&#039;', '\'', $string );
				$string = str_replace ( '&quot;', '"', $string );
				$string = str_replace ( '&lt;', '<', $string );
				$string = str_replace ( '&gt;', '>', $string );
				//this needs to be last!!
				$string = str_replace ( '&amp;', '&', $string );
				return $string;
		}

		public static function stripInvalidXmlChars( $in ) {
				$out = "";
				$length = strlen($in);
				for ( $i = 0; $i < $length; $i++) {
						$current = ord($in{$i});
						if (($current == 0x9) ||
								($current == 0xA) || 
								($current == 0xD) || 
								($current >= 0x20 && $current <= 0xD7FF) || 
								($current >= 0xE000 && $current <= 0xFFFD) || 
								($current >= 0x10000 && $current <= 0x10FFFF)
						){
								$out .= chr($current);
						} else{
								$out .= " ";
						}
				}
				return $out;
		}

		public static function getTime()
		{
				list($usec, $sec) = explode(" ", microtime());
				return ((float)$usec + (float)$sec);
		}

		/** from http://www.weberdev.com/get_example-3543.html */
		public static function getUniqueName()
		{
				// explode the IP of the remote client into four parts
				if (isset($_SERVER["REMOTE_ADDR"])) {
						$ip = $_SERVER["REMOTE_ADDR"];
				} else {
						$ip = '123.456.7.8';
				}
				$ipbits = explode(".", $ip);
				// Get both seconds and microseconds parts of the time
				list($usec, $sec) = explode(" ",microtime());

				// Fudge the time we just got to create two 16 bit words
				$usec = (integer) ($usec * 65536);
				$sec = ((integer) $sec) & 0xFFFF;

				// Fun bit - convert the remote client's IP into a 32 bit
				// hex number then tag on the time.
				// Result of this operation looks like this xxxxxxxx-xxxx-xxxx
				$uid = sprintf("%08x-%04x-%04x",($ipbits[0] << 24)
						| ($ipbits[1] << 16)
								| ($ipbits[2] << 8)
										| $ipbits[3], $sec, $usec);

				return $uid;
		} 

		public static function getSubdir($serial_number)
		{
				return substr(md5($serial_number),0,2);
		}

		public static function camelize($str)
		{
				$str = trim($str,'_');
				if (false === strpos($str,'_')) {
						return ucfirst($str);
				} else {
						return str_replace(' ','',ucwords(str_replace('_',' ',$str)));
						//too clever:
						//$set = explode('_',$str);
						//array_walk($set, create_function('&$v,$k', '$v = ucfirst($v);'));
						//return join('',$set);
				}
		}

		public static function truncate($string,$max)
		{
				if (strlen($string) <= $max) {
						return $string;
				}
				return substr($string,0,$max);
		}

		public static function dirify($str)
		{
				$str = strtolower(preg_replace('/[^a-zA-Z0-9_-]/','_',trim($str)));
				$str = preg_replace('/__*/','_',$str);
				$str = preg_replace('/^_/','',$str);
				$str = preg_replace('/_$/','',$str);
				return $str;
		}

		public static function undirify($str)
		{
				return ucwords(preg_replace('/_/',' ',$str));
		}

		public static function makeSerialNumber($str)
		{
				if ($str) {
						//get just the last segment if it includes directory path
						$str = array_pop(explode('/',$str));
						$str = preg_replace('/[^a-zA-Z0-9_-]/','_',trim($str));
						$str = trim(preg_replace('/__*/','_',$str),'_');
						return Dase_Util::truncate($str,50);
				} else {
						return null;
				}
		}

		public static function sortByLastUpdateSortable($b,$a)
		{
				if ($a->lastUpdateSortable == $b->lastUpdateSortable) {
						return 0;
				}
				return ($a->lastUpdateSortable < $b->lastUpdateSortable) ? -1 : 1;
		}
		public static function sortByCount($b,$a)
		{
				if (count($a) == count($b)) {
						return 0;
				}
				return (count($a) < count($b)) ? -1 : 1;
		}
		public static function sortByItemCount($b,$a)
		{
				if ($a->item_count == $b->item_count) {
						return 0;
				}
				return ($a->item_count < $b->item_count) ? -1 : 1;
		}
		public static function sortBySubLevel($a,$b)
		{
				if ($a->sub_level == $b->sub_level) {
						return 0;
				}
				return ($a->sub_level < $b->sub_level) ? -1 : 1;
		}
		public static function sortByAttributeName($a,$b)
		{
				$a_str = strtolower($a->attribute_name);
				$b_str = strtolower($b->attribute_name);
				if ($a_str == $b_str) {
						return 0;
				}
				return ($a_str < $b_str) ? -1 : 1;
		}
		public static function sortBySortOrder($a,$b)
		{
				$a_str = $a->sort_order;
				$b_str = $b->sort_order;
				if ($a_str == $b_str) {
						return 0;
				}
				return ($a_str < $b_str) ? -1 : 1;
		}
		public static function sortByCollectionName($a,$b)
		{
				$a_str = strtolower($a->collection_name);
				$b_str = strtolower($b->collection_name);
				if ($a_str == $b_str) {
						return 0;
				}
				return ($a_str < $b_str) ? -1 : 1;
		}
		public static function sortByTitle($a,$b)
		{
				$a_str = strtolower($a->title);
				$b_str = strtolower($b->title);
				if ($a_str == $b_str) {
						return 0;
				}
				return ($a_str < $b_str) ? -1 : 1;
		}
		public static function sortByValueText($a,$b)
		{
				$a_str = strtolower($a->value_text);
				$b_str = strtolower($b->value_text);
				if ($a_str == $b_str) {
						return 0;
				}
				return ($a_str < $b_str) ? -1 : 1;
		}
		/** like above but w/ arrays */
		public static function sortByAttName($a,$b)
		{
				$a_str = strtolower($a['attribute_name']);
				$b_str = strtolower($b['attribute_name']);
				if ($a_str == $b_str) {
						return 0;
				}
				return ($a_str < $b_str) ? -1 : 1;
		}
		public static function sortValuesByAttributeSortOrder($a,$b)
		{
				$a_so = $a->attribute->sort_order;
				$b_so = $b->attribute->sort_order;
				if ($a_so == $b_so) {
						return 0;
				}
				return ($a_so < $b_so) ? -1 : 1;
		}
}


