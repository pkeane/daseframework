<?php

class Dase_Handler_Admin extends Dase_Handler
{
		public $resource_map = array(
				'/' => 'admin',
				'set' => 'set',
				'users' => 'users',
				'add_user_form/{eid}' => 'add_user_form',
				'user/{id}/is_admin' => 'is_admin',
				'create' => 'content_form',
				'cats' => 'cats',
		);

		protected function setup($r)
		{
            $this->t = new Dase_Template($r);
            /*
				$this->user = $r->getUser();
				if ($this->user->is_admin) {
						//ok
				} else {
						$r->renderError(401);
				}
             */
		}

		/* 
		 * for adding multiple items to a set
		 */
		public function postToSet($r) 
		{
				$set = new Dase_DBO_Itemset($this->db);
				if (!$set->load($r->get('set_id'))) {
						$r->renderError(404);
				} 
				foreach ($r->get('item',true) as $item_id) {
						$isi = new Dase_DBO_ItemsetItem($this->db);
						$isi->item_id = $item_id;
						$isi->itemset_id = $set->id;
						$isi->created = date(DATE_ATOM);
						$isi->insert();
				}
				$r->renderRedirect('set/'.$set->name);
		}

		public function getCats($r)
		{
				$item = Dase_DBO_Item::getByTitle($this->db,$r,'Cats');
				if (!$item) {
						$user = $pass = '';
						foreach($r->getSuperusers() as $eid => $pass) {
								$user = $eid;
								$pass = $pass;
								break;
						}
						$image_url = $r->app_root.'/www/img/cats.jpg';
						$body = file_get_contents($image_url);
						$post_url = $r->app_root.'/items';
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, $post_url);
						curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
						curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
						curl_setopt($ch, CURLOPT_USERPWD,$user.':'.$pass);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
						$headers  = array("Content-Type: image/jpeg","Title: Cats");
						curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						$result = curl_exec($ch);
						$info = curl_getinfo($ch);
						curl_close($ch);  
						$r->renderRedirect('admin/cats');
				} else {
						$t->assign('item',$item);
						$r->renderResponse($this->t->fetch('framework/cats.tpl'));
				}
		}

		public function getContentForm($r) 
		{
				/*
				$items = new Dase_DBO_Item($this->db);
				$items->orderBy('updated DESC');
				$t->assign('items',$items->findAll(1));
				 */
				$r->renderResponse($this->t->fetch('framework/admin_create_content.tpl'));
		}

		public function postToContentForm($r)
		{
				$item = new Dase_DBO_Item($this->db);
				$item->body = $r->get('body');
				$item->title = $r->get('title');

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
						$item->file_url = 'file/'.$item->name;
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
				} else {
						if (!$item->title) {
								$item->title = substr($item->body,0,20);
						}
						if (!$item->title) {
								$params['msg'] = "title or body is required is no file is uploaded";
								$r->renderRedirect('admin/upload',$params);
						}
						$item->name = $this->_findUniqueName(Dase_Util::dirify($item->title));
						$item->thumbnail_url = 	'www/img/mime_icons/content.png';
				}
				$item->created_by = $this->user->eid;
				$item->created = date(DATE_ATOM);
				$item->updated_by = $this->user->eid;
				$item->updated = date(DATE_ATOM);
				$item->url = 'item/'.$item->name;
				$item->insert();

				$r->renderRedirect('items');
		}

		public function getAdmin($r) 
		{
            $r->renderResponse($this->t->fetch('framework/admin.tpl'));
		}

		public function getUsers($r) 
		{
				$users = new Dase_DBO_User($this->db);
				$users->orderBy('name');
				$t->assign('users', $users->findAll(1));
				$r->renderResponse($this->t->fetch('framework/admin_users.tpl'));
		}

		public function getAddUserForm($r) 
		{
				$record = Utlookup::getRecord($r->get('eid'));
				$u = new Dase_DBO_User($this->db);
				$u->eid = $r->get('eid');
				if ($u->findOne()) {
						$t->assign('user',$u);
				}
				$t->assign('record',$record);
				$r->renderResponse($this->t->fetch('framework/add_user_form.tpl'));
		}

		public function postToUsers($r)
		{
				$record = Utlookup::getRecord($r->get('eid'));
				$user = new Dase_DBO_User($this->db);
				$user->eid = $record['eid'];
				if (!$user->findOne()) {
						$user->name = $record['name'];
						$user->email = $record['email'];
						$user->insert();
				} else {
						//$user->update();
				}
				$r->renderRedirect('admin');

		}

		public function deleteIsAdmin($r) 
		{
				$user = new Dase_DBO_User($this->db);
				$user->load($r->get('id'));
				$user->is_admin = 0;
				$user->update();
				$r->renderResponse('deleted privileges');
		}

		public function putIsAdmin($r) 
		{
				$user = new Dase_DBO_User($this->db);
				$user->load($r->get('id'));
				$user->is_admin = 1;
				$user->update();
				$r->renderResponse('added privileges');
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

