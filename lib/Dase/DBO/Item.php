<?php

require_once 'Dase/DBO/Autogen/Item.php';

class Dase_DBO_Item extends Dase_DBO_Autogen_Item 
{

		public $sets = array();

		public static function getByTitle($db,$r,$title)
		{
				$item = new Dase_DBO_Item($db);
				$item->title = $title;
				if ($item->findOne()) {
						return $item->asArray($r);
				} else {
						return false;
				}
		}

		public function getSets()
		{
				$isis = new Dase_DBO_ItemsetItem($this->db);
				$isis->item_id = $this->id;
				foreach ($isis->findAll(1) as $isi) {
						$set = new Dase_DBO_Itemset($this->db);
						$set->load($isi->itemset_id);
						$this->sets[] = $set;
				}
				return $this->sets;
		}

		public function removeFromSets()
		{
				$isi = new Dase_DBO_ItemsetItem($this->db);
				$isi->item_id = $this->id;
				foreach ($isi->findAll(1) as $doomed) {
						$doomed->delete();
				}
		}

		public function asArray($r)
		{
				$set = array();
				$set['id'] = $r->app_root.'/item/'.$this->name;
				$set['title'] = $this->title;
				$set['name'] = $this->name;
				$set['item_id'] = $this->id;
				if ($this->body) {
						$set['body'] = $this->body;
				}
				$set['created'] = $this->created;
				$set['updated'] = $this->updated;
				$set['links'] = array();
				$set['links']['self'] = $r->app_root.'/'.$this->url.'.json';
				if ($this->file_url) {
						$set['links']['file'] = $r->app_root.'/'.$this->file_url;
				}
				if ($this->thumbnail_url) {
						$set['links']['thumbnail'] = $r->app_root.'/'.$this->thumbnail_url;
				}
				if ($this->filesize) {
						$set['filesize'] = $this->filesize;
				}
				if ($this->mime) {
						$set['mime'] = $this->mime;
				}
				if ($this->width) {
						$set['width'] = $this->width;
				}
				if ($this->height) {
						$set['height'] = $this->height;
				}
				if ($this->meta1) {
						$set['meta1'] = $this->meta1;
				}
				if ($this->meta2) {
						$set['meta2'] = $this->meta2;
				}
				if ($this->meta3) {
						$set['meta3'] = $this->meta3;
				}
				return $set;
		}

		public function asJson($r)
		{
				return Dase_Json::get($this->asArray($r));
		}
}
