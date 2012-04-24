<?php

class Dase_Handler_Item extends Dase_Handler
{
		public $resource_map = array(
				'list' => 'list',
				'{name}' => 'item',
				'{id}/edit' => 'edit_form',
				'{id}/swap' => 'swap_file',
				'{id}/sets' => 'sets',
		);

		protected function setup($r)
		{
				$this->user = $r->getUser();
		}

		public function postToSets($r)
		{
				$item = new Dase_DBO_Item($this->db);
				if (!$item->load($r->get('id'))) {
						$r->renderError(404);
				}
				$isi = new Dase_DBO_ItemsetItem($this->db);
				$isi->item_id = $item->id;
				$isi->itemset_id = $r->get('set_id');
				if (!$isi->findOne()) {
						$isi->created = date(DATE_ATOM);
						$isi->insert();
				}
				$r->renderRedirect('item/'.$item->id);
		}

		public function getEditForm($r) 
		{
				$t = new Dase_Template($r);
				$item = new Dase_DBO_Item($this->db);
				if (!$item->load($r->get('id'))) {
						$r->renderRedirect('items');
						//$r->renderError(404);
				}
				$t->assign('item',$item);
				$r->renderResponse($t->fetch('framework/admin_item_edit.tpl'));
		}

		public function getListJson($r) 
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

		public function getItem($r) 
		{
				$t = new Dase_Template($r);
				//if no format, assume name is ID
				$item = new Dase_DBO_Item($this->db);
				if ($item->load($r->get('name'))) {
						$item->getSets();
						$t->assign('item',$item);
						$sets = Dase_DBO_Itemset::getList($this->db);
						$t->assign('sets',$sets);
						$r->renderResponse($t->fetch('framework/item.tpl'));
				} else {
						$r->renderRedirect('items');
						//$r->renderError(404);
				}
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

		public function postToEditForm($r)
		{
				$item = new Dase_DBO_Item($this->db);
				if (!$item->load($r->get('id'))) {
						$r->renderError(404);
				}
				if (($this->user->eid != $item->created_by) && !$this->user->is_admin) {
						$r->renderError(401);
				}
				$item->title = $r->get('title');
				$item->meta1 = $r->get('meta1');
				$item->meta2 = $r->get('meta2');
				$item->meta3 = $r->get('meta3');
				$item->body = $r->get('body');
				$item->updated_by = $this->user->eid;
				$item->updated = date(DATE_ATOM);
				$item->update();
				$r->renderRedirect('item/'.$item->id);
		}

		public function postToSwapFile($r)
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
				$r->renderRedirect('item/'.$item->id);
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

