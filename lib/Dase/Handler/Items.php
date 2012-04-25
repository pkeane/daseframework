<?php

class Dase_Handler_Items extends Dase_Handler
{
		public $resource_map = array(
				'/' => 'items',
		);

		protected function setup($r)
		{
            $this->user = $r->getUser();
            if ($this->user->is_admin) {
                //ok
            } else {
                $r->renderError(401);
            }
		}

		public function getItems($r) 
		{
				$items = new Dase_DBO_Item($this->db);
				$items->orderBy('updated DESC');
				$items = $items->findAll(1);
				$r->assign('items',$items);
				$r->renderTemplate('framework/items.tpl');
		}

		public function getItemsJson($r) 
		{
				$items = new Dase_DBO_Item($this->db);
				$items->orderBy('updated DESC');
				$set = array();
				foreach ($items->find() as $item) {
						$item = clone($item);
						$set[] = $item->asArray($r);
				}
				$r->renderResponse(Dase_Json::get($set));
		}

		/** lots of duplicated code here -- need to refactor
		 */
		private function _processFile($r)
		{
				$content_type = $r->getContentType();
				if (!Dase_Media::isAcceptable($content_type)) {
						$r->renderError(415,'cannot accept '.$content_type);
				}

				$bits = $r->getBody();

				$file_meta = Dase_File::$types_map[$content_type];
				$ext = $file_meta['ext'];

				$title = '';
				if ( $r->http_title ) {
						$title = $r->http_title;
				} elseif ( $r->slug ) {
						$title = $r->slug;
				} else {
						$title = dechex(time());
				}
				$base_dir = $this->config->getMediaDir();
				$basename = $this->_findUniqueName(Dase_Util::dirify($title));
				$newname = $this->_findNextUnique($base_dir,$basename,$ext);
				$new_path = $base_dir.'/'.$newname;

				$ifp = @ fopen( $new_path, 'wb' );
				if (!$ifp) {
						Dase_Log::debug(LOG_FILE,'cannot write file '.$new_path);
						$r->renderError(500,'cannot write file '.$new_path);
				}

				@fwrite( $ifp, $bits );
				fclose( $ifp );
				@ chmod( $new_file,0775);

				//create new item
				$item = new Dase_DBO_Item($this->db);
				$item->title = $title;

				$size = @getimagesize($new_path);
				$item->name = $newname;
				$item->file_url = 'file/'.$item->name;
				$item->filesize = filesize($new_path);
				$item->mime = $content_type;

				$mime_type = $item->mime;
				$parts = explode('/',$mime_type);
				if (isset($parts[0]) && 'image' == $parts[0]) {
						$thumb_path = $base_dir.'/thumb/'.$newname;
						$thumb_path = str_replace('.'.$ext,'.jpg',$thumb_path);
						$command = CONVERT." \"$new_path\" -format jpeg -resize '100x100 >' -colorspace RGB $thumb_path";
						$exec_output = array();
						$results = exec($command,$exec_output);
						if (!file_exists($thumb_path)) {
								//Dase_Log::info(LOG_FILE,"failed to write $thumb_path");
						}
						chmod($thumb_path,0775);
						$newname = str_replace('.'.$ext,'.jpg',$newname);
						$item->thumbnail_url = 'file/thumb/'.$newname;
				} else {
						$item->thumbnail_url = 	'www/img/mime_icons/'.Dase_File::$types_map[$mime_type]['size'].'.png';
				}
				if (isset($size[0]) && $size[0]) {
						$item->width = $size[0];
				}
				if (isset($size[1]) && $size[1]) {
						$item->height = $size[1];
				}
				$item->created_by = $this->user->eid;
				$item->created = date(DATE_ATOM);
				$item->updated_by = $this->user->eid;
				$item->updated = date(DATE_ATOM);
				$item->url = 'item/'.$item->name;
				if ($item->insert()) {
						$r->renderOk('added item');
				} else {
						$r->renderError(400);
				}
		}

		/**
		 * will ingest file if there is one
		 */
		public function postToItems($r) 
		{
				$this->user = $r->getUser('http');
				if (!$this->user->is_admin) {
						$r->renderError(401,'no go unauthorized');
				}
				$content_type = $r->getContentType();
				if ('application/json' != $content_type ) {
						//$r->renderError(415,'cannot accept '.$content_type);
						return $this->_processFile($r);
				}
				$json_data = Dase_Json::toPhp($r->getBody());
				if (!isset($json_data['title'])) {
						$r->renderError(415,'incorrect json format');
				}

				//create new item
				$item = new Dase_DBO_Item($this->db);
				$item->title = $json_data['title'];
				if (isset($json_data['body'])) {
						$item->body = $json_data['body'];
				}

				if (isset($json_data['links']['file'])) {
						$file_url = $json_data['links']['file'];
						$ext = strtolower(pathinfo($file_url, PATHINFO_EXTENSION));
						$mime_type = Dase_Request::$types[$ext];
						$base_dir = $this->config->getMediaDir();
						$basename = Dase_Util::dirify(pathinfo($file_url,PATHINFO_FILENAME));
						$newname = $this->_findNextUnique($base_dir,$basename,$ext);
						$new_path = $base_dir.'/'.$newname;
						//move file to new home
						file_put_contents($new_path,file_get_contents($file_url));
						chmod($new_path,0775);
						$size = @getimagesize($new_path);
						$item->name = $newname;
						if (!$item->title) {
								$item->title = $item->name;
						}
						$item->file_url = 'file/'.$item->name;
						$item->filesize = filesize($new_path);
						$item->mime = $mime_type;

						$parts = explode('/',$mime_type);
						if (isset($parts[0]) && 'image' == $parts[0]) {
								$thumb_path = $base_dir.'/thumb/'.$newname;
								$thumb_path = str_replace('.'.$ext,'.jpg',$thumb_path);
								$command = CONVERT." \"$new_path\" -format jpeg -resize '100x100 >' -colorspace RGB $thumb_path";
								$exec_output = array();
								$results = exec($command,$exec_output);
								if (!file_exists($thumb_path)) {
										//Dase_Log::info(LOG_FILE,"failed to write $thumb_path");
								}
								chmod($thumb_path,0775);
								$newname = str_replace('.'.$ext,'.jpg',$newname);
								$item->thumbnail_url = 'file/thumb/'.$newname;
						} else {
								$item->thumbnail_url = 	'www/img/mime_icons/'.Dase_File::$types_map[$mime_type]['size'].'.png';
						}
						if (isset($size[0]) && $size[0]) {
								$item->width = $size[0];
						}
						if (isset($size[1]) && $size[1]) {
								$item->height = $size[1];
						}
				} else { //meaning no file
						if (!$item->title) {
								$item->title = substr($item->body,0,20);
						}
						$item->name = $this->_findUniqueName(Dase_Util::dirify($item->title));
						$item->thumbnail_url = 	'www/img/mime_icons/content.png';
				}
				$item->created_by = $this->user->eid;
				$item->created = date(DATE_ATOM);
				$item->updated_by = $this->user->eid;
				$item->updated = date(DATE_ATOM);
				$item->url = 'item/'.$item->name;
				if ($item->insert()) {
						$r->renderOk('added item');
				} else {
						$r->renderError(400);
				}
		}

		private function _findNextUnique($base_dir,$basename,$ext,$iter=0)
		{
				if ($iter) {
						$checkname = $basename.'_'.$iter.'.'.$ext;
				} else {
						$checkname = $basename.'.'.$ext;
				}
				if (!file_exists($base_dir.'/'.$checkname)) {
						return $checkname;
				} else {
						$iter++;
						return $this->_findNextUnique($base_dir,$basename,$ext,$iter);
				}

		}

		private function _findUniqueName($name,$iter=0)
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
						return $this->_findUniqueName($name,$iter);
				}
		}
}

