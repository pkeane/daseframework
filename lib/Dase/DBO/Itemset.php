<?php

require_once 'Dase/DBO/Autogen/Itemset.php';

class Dase_DBO_Itemset extends Dase_DBO_Autogen_Itemset 
{
		public $items = array();

		public static function getList($db)
		{
				$sets = new Dase_DBO_Itemset($db);
				$sets->orderBy('title');
				return $sets->findAll(1);
		}

		public static function get($r,$name)
		{
				$url = $r->app_root.'/set/'.$name.'.json';
				return Dase_Json::toPhp(file_get_contents($url));
		}

		public static function getByName($db,$r,$name)
		{
				$set = new Dase_DBO_Itemset($db);
				$set->name = $name;
				$set->findOne();
				return $set->asArray($r);
		}

		public function getItemIds()
		{
				$set_items = new Dase_DBO_ItemsetItem($this->db);
				$set_items->itemset_id = $this->id;
				$set_items->orderBy('sort_order, created');
				$item_ids_array = array();
				foreach ($set_items->findAll(1) as $si) {
						$item_ids_array[] = $si->item_id;
				}
				return $item_ids_array;
		}

		public function getItems() 
		{
				foreach ($this->getItemIds() as $item_id) {
						$item = new Dase_DBO_Item($this->db);
						$item->load($item_id);
						$this->items[] = $item;
				}
				return $this->items;
		}

		public function asArray($r)
		{
				$result =array();
				$result['id'] = $r->app_root.'/set/'.$this->name;
				$result['title'] = $this->title;
				$result['items'] = array();
				foreach ($this->getItems() as $item) {
						$result['items'][$item->name] = $item->asArray($r);
				}
				return $result;
		}

		public function asJson($r)
		{
				return Dase_Json::get($this->asArray($r));
		}

		public function removeItems()
		{
				$isi = new Dase_DBO_ItemsetItem($this->db);
				$isi->itemset_id = $this->id;
				foreach ($isi->findAll(1) as $doomed) {
						$doomed->delete();
				}
		}

}
