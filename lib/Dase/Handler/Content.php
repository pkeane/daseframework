<?php

class Dase_Handler_Content extends Dase_Handler
{
		public $resource_map = array(
				'/' => 'items',
				'create' => 'content_form',
				'items' => 'items',
				'file/{name}' => 'file',
				'file/thumb/{name}' => 'thumbnail',
				'item/{name}' => 'item',
				'item/{id}/edit' => 'item_edit_form',
				'item/{id}/swap' => 'item_swap_file',
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

		public function getItemEditForm($r) 
		{
				$item = new Dase_DBO_Item($this->db);
				if (!$item->load($r->get('id'))) {
						$r->renderRedirect('items');
						//$r->renderError(404);
				}
				$r->assign('item',$item);
				$r->renderTemplate('framework/content_item_edit.tpl');
		}

		public function getItemJson($r) 
		{
				$item = new Dase_DBO_Item($this->db);
				$item->name = $r->get('name');
				if ( $item->findOne()) {
						$r->renderResponse($item->asJson($r));
				} else {
						$r->renderError(404);
				}
		}

		public function getItem($r) 
		{
				$item = new Dase_DBO_Item($this->db);
				if (!$item->load($r->get('name'))) {
						$r->renderError(404);
				}
				$r->assign('item',$item);
				$r->renderTemplate('framework/content_item.tpl');
		}

		public function deleteItem($r)
		{
				$item = new Dase_DBO_Item($this->db);
				if (!$item->load($r->get('name'))) {
						$r->renderError(404);
				}
				if (($this->user->eid != $item->created_by) && !$this->user->is_admin) {
						$r->renderError(401);
				}

				if ($item->file_url) {
						$base_dir = $this->config->getMediaDir();
						$file_path = $base_dir.'/'.$item->name;
						@unlink($file_path);
				}

				$item->removeFromSets();
				$item->delete();
				$r->renderResponse('deleted item');
		}

		public function postToItemEditForm($r)
		{
				$item = new Dase_DBO_Item($this->db);
				if (!$item->load($r->get('id'))) {
						$r->renderError(404);
				}
				if (($this->user->eid != $item->created_by) && !$this->user->is_admin) {
						$r->renderError(401);
				}
				$item->title = $r->get('title');
				$item->body = $r->get('body');
				$item->updated_by = $this->user->eid;
				$item->updated = date(DATE_ATOM);
				$item->update();
				$r->renderRedirect('content/item/'.$item->id);
		}

		public function postToItemSwapFile($r)
		{
				$item = new Dase_DBO_Item($this->db);
				if (!$item->load($r->get('id'))) {
						$r->renderError(404);
				}
				if (($this->user->eid != $item->created_by) && !$this->user->is_admin) {
						$r->renderError(401);
				}
				//@unlink($old_path);
				$file = $r->_files['uploaded_file'];
				if ($file && is_file($file['tmp_name'])) {
						$name = $file['name'];
						$path = $file['tmp_name'];
						$type = $file['type'];
						if (!is_uploaded_file($path)) {
								$r->renderError(400,'no go upload');
						}
						if (!isset(Dase_File::$types_map[$type])) {
								$r->renderError(415,'unsupported media type: '.$type);
						}

						$base_dir = $this->config->getMediaDir();

						$old_path = $base_dir.'/'.$item->name;
						@unlink($old_path);
						//we won't worry about deleting old thumbnail

						if (!file_exists($base_dir) || !is_writeable($base_dir)) {
								$r->renderError(403,'not allowed');
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

						$newname = $this->_findNextUnique($base_dir,$basename,$ext);
						$new_path = $base_dir.'/'.$newname;
						//move file to new home
						rename($path,$new_path);
						chmod($new_path,0775);
						$size = @getimagesize($new_path);

						//ONLY update name if item had file already
						//adding file to text item shouldn't change name
						if ($item->file_url) {
								$item->name = $newname;
						}
						if (!$item->title) {
								$item->title = $item->name;
						}
						$item->file_url = 'file/'.$newname;
						$item->filesize = filesize($new_path);
						$item->mime = $type;

						$parts = explode('/',$type);
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
								$item->thumbnail_url = 	'www/img/mime_icons/'.Dase_File::$types_map[$type]['size'].'.png';
						}
						if (isset($size[0]) && $size[0]) {
								$item->width = $size[0];
						}
						if (isset($size[1]) && $size[1]) {
								$item->height = $size[1];
						}
				}
				$item->updated_by = $this->user->eid;
				$item->updated = date(DATE_ATOM);
				$item->update();
				$r->renderRedirect('content/item/'.$item->id);
		}

		public function getFile($r) 
		{ 
				$media_dir = $this->config->getMediaDir();
				$file_path = $media_dir.'/'.$r->get('name').'.'.$r->ext;
				$r->serveFile($file_path,$r->mime);
		}

		public function getThumbnail($r) 
		{ 
				$media_dir = $this->config->getMediaDir();
				$file_path = $media_dir.'/thumb/'.$r->get('name').'.'.$r->ext;
				$r->serveFile($file_path,$r->mime);
		}

		public function getFileJpg($r) { return $this->getFile($r); }
		public function getFileGif($r) { return $this->getFile($r); }
		public function getFilePng($r) { return $this->getFile($r); }
		public function getFileTxt($r) { return $this->getFile($r); }
		public function getFilePdf($r) { return $this->getFile($r); }
		public function getFileDoc($r) { return $this->getFile($r); }
		public function getFileMp3($r) { return $this->getFile($r); }
		public function getFileMp4($r) { return $this->getFile($r); }
		public function getFileMov($r) { return $this->getFile($r); }
		public function getFileOgv($r) { return $this->getFile($r); }
		public function getFileOga($r) { return $this->getFile($r); }

		public function getThumbnailJpg($r) { return $this->getThumbnail($r); }
		public function getThumbnailGif($r) { return $this->getThumbnail($r); }
		public function getThumbnailPng($r) { return $this->getThumbnail($r); }
		public function getThumbnailTxt($r) { return $this->getThumbnail($r); }
		public function getThumbnailPdf($r) { return $this->getThumbnail($r); }
		public function getThumbnailDoc($r) { return $this->getThumbnail($r); }
		public function getThumbnailMp3($r) { return $this->getThumbnail($r); }
		public function getThumbnailMp4($r) { return $this->getThumbnail($r); }
		public function getThumbnailMov($r) { return $this->getThumbnail($r); }
		public function getThumbnailOgv($r) { return $this->getThumbnail($r); }
		public function getThumbnailOga($r) { return $this->getThumbnail($r); }

		public function getContentForm($r) 
		{
				$r->renderTemplate('framework/content_form.tpl');
		}

		public function postToContentForm($r)
		{
				$item = new Dase_DBO_Item($this->db);
				$item->body = $r->get('body');
				$item->title = $r->get('title');

				$file = $r->getFile('uploaded_file');
				if ($file && $file->isValid()) {
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

						if (!file_exists($base_dir) || !is_writeable($base_dir)) {
								$r->renderError(403,'media directory not writeable: '.$base_dir);
						}

						if (!file_exists($thumb_dir) || !is_writeable($thumb_dir)) {
								$r->renderError(403,'thumbnail directory not writeable: '.$thumb_dir);
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

						$newname = $this->_findNextUnique($base_dir,$basename,$ext);
						$new_path = $base_dir.'/'.$newname;
						//move file to new home
						rename($path,$new_path);
						chmod($new_path,0775);
						$size = @getimagesize($new_path);

						$item->name = $newname;
						if (!$item->title) {
								$item->title = $item->name;
						}
						$item->file_url = 'content/file/'.$item->name;
						$item->filesize = filesize($new_path);
						$item->mime = $type;

						$parts = explode('/',$type);
						if (isset($parts[0]) && 'image' == $parts[0]) {
								$thumb_path = $thumb_dir.'/'.$newname;
								$thumb_path = str_replace('.'.$ext,'.jpg',$thumb_path);
								$command = CONVERT." \"$new_path\" -format jpeg -resize '100x100 >' -colorspace RGB $thumb_path";
								$exec_output = array();
								$results = exec($command,$exec_output);
								if (!file_exists($thumb_path)) {
										//Dase_Log::info(LOG_FILE,"failed to write $thumb_path");
								}
								chmod($thumb_path,0775);
								$newname = str_replace('.'.$ext,'.jpg',$newname);
								$item->thumbnail_url = 'content/file/thumb/'.$newname;
						} else {
								$item->thumbnail_url = 	'www/img/mime_icons/'.Dase_File::$types_map[$type]['size'].'.png';
						}
						if (isset($size[0]) && $size[0]) {
								$item->width = $size[0];
						}
						if (isset($size[1]) && $size[1]) {
								$item->height = $size[1];
						}
				} else {
						if (!$item->title) {
								$item->title = substr($item->body,0,20);
						}
						if (!$item->title) {
								$params['msg'] = "title or body is required is no file is uploaded";
								$r->renderRedirect('content',$params);
						}
						$item->name = $this->_findUniqueName(Dase_Util::dirify($item->title));
						$item->thumbnail_url = 	'www/img/mime_icons/content.png';
				}
				$item->created_by = $this->user->eid;
				$item->created = date(DATE_ATOM);
				$item->updated_by = $this->user->eid;
				$item->updated = date(DATE_ATOM);
				$item->url = 'content/item/'.$item->name;
				$item->insert();

				$r->renderRedirect('content/items');
		}

		public function getItems($r) 
		{
				$items = new Dase_DBO_Item($this->db);
				$items->orderBy('updated DESC');
				$items = $items->findAll(1);
				$r->assign('items',$items);
				$r->renderTemplate('framework/content_items.tpl');
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
				$item->file_url = 'content/file/'.$item->name;
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
						$item->thumbnail_url = 'content/file/thumb/'.$newname;
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
				$item->url = 'content/item/'.$item->name;
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
						$item->file_url = 'content/file/'.$item->name;
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
								$item->thumbnail_url = 'content/file/thumb/'.$newname;
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

