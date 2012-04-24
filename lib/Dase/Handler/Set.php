<?php

class Dase_Handler_Set extends Dase_Handler
{
		public $resource_map = array(
				'list' => 'list', //list all sets
				'form' => 'form', //create a set
				'{name}' => 'set', //add/delete items (html) or view (json)
				'{id}/edit' => 'edit_form', 
				'{id}/item/{item_id}' => 'set_item', 
				'{id}/order' => 'order', 
		);

		protected function setup($r)
		{
            $this->t = new Dase_Template($r);
            $this->user = $r->getUser();
            if ($this->user->is_admin) {
                //ok
            } else {
                $r->renderError(401);
            }
		}

		public function postToOrder($r)
		{
				$set = new Dase_DBO_Itemset($this->db);
				if (!$set->load($r->get('id'))) {
						$r->renderError(404);
				}
				$order_array = explode('|',$r->getBody());
				$i = 0;
				foreach ($order_array as $item_id) {
						if ($item_id) {
								$i++;
								$isi = new Dase_DBO_ItemsetItem($this->db);
								$isi->item_id = $item_id;
								$isi->itemset_id = $set->id;
								if ($isi->findOne()) {
										$isi->sort_order = $i;
										$isi->update();
								}
						}
				}
				$r->renderResponse('ordered!');
		}

		public function getForm($r) 
		{
				$r->renderResponse($this->t->fetch('framework/set_form.tpl'));
		}

		public function postToEditForm($r) 
		{
				$set = new Dase_DBO_Itemset($this->db);
				if (!$set->load($r->get('id'))) {
						$r->renderError(404);
				}
				$set->title = $r->get('title');
				if (!$set->title) {
						$set->title = dechex(time());
				}
				$set->name = $this->_findUniqueName(Dase_Util::dirify($set->title));
				$set->update();
				$r->renderRedirect('set/'.$set->name);
		}

		public function getEditForm($r) 
		{
				$set = new Dase_DBO_Itemset($this->db);
				if (!$set->load($r->get('id'))) {
						$r->renderRedirect('set/list');
						//$r->renderError(404);
				}
				$this->t->assign('set',$set);
				$r->renderResponse($this->t->fetch('framework/set_edit.tpl'));
		}

		public function deleteSet($r)
		{
				$this->user = $r->getUser();
				$set = new Dase_DBO_Itemset($this->db);
				if (!$set->load($r->get('name'))) {
						$r->renderError(404);
				}
				if (!$this->user->is_admin) {
						$r->renderError(401);
				}

				$set->removeItems();
				$set->delete();
				$r->renderResponse('deleted set');
		}
		public function deleteSetItem($r)
		{
				$is_item = new Dase_DBO_ItemsetItem($this->db);
				$is_item->itemset_id = $r->get('id');
				$is_item->item_id = $r->get('item_id');
				if ($is_item->findOne()) {
						$is_item->delete();
				}
				$r->renderResponse('removed item');
		}

		public function getList($r) 
		{
				$sets = new Dase_DBO_Itemset($this->db);
				$sets->orderBy('created DESC');
				$this->t->assign('sets',$sets->findAll(1));
				$r->renderResponse($this->t->fetch('framework/sets.tpl'));
		}

		public function getSet($r) 
		{
				$set = new Dase_DBO_Itemset($this->db);
				$set->name = $r->get('name');
				if ($set->findOne()) {
						$set->getItems();
						$this->t->assign('set',$set);
						$r->renderResponse($this->t->fetch('framework/set.tpl'));
				} else {
						$r->renderError(404);
				}
		}

		public function postToSet($r) 
		{
				$set = new Dase_DBO_Itemset($this->db);
				if ($set->load($r->get('name'))) {
						$is_item = new Dase_DBO_ItemsetItem($this->db);
						$is_item->itemset_id = $set->id;
						$is_item->item_id = $r->get('item_id');
						$is_item->created = date(DATE_ATOM);
						$is_item->insert();
				}
				$r->renderRedirect('set/'.$set->name);
		}

		public function getSetJson($r) 
		{
				//$r->checkCache(60);
				$set = new Dase_DBO_Itemset($this->db);
				$set->name = $r->get('name');
				if ( $set->findOne()) {
						$r->renderResponse($set->asJson($r));
				} else {
						$r->renderError(404);
				}
		}

		public function postToForm($r)
		{
				$this->user = $r->getUser();
				$set = new Dase_DBO_Itemset($this->db);
				$set->title = $r->get('title');
				if (!$set->title) {
						$set->title = dechex(time());
				}
				$set->name = $this->_findUniqueName(Dase_Util::dirify($set->title));
				$set->created_by = $this->user->eid;
				$set->created = date(DATE_ATOM);
				$set->insert();
				$r->renderRedirect('set/'.$set->name);
		}

		private function _findUniqueName($name,$iter=0)
		{
				if ($iter) {
						$checkname = $name.'_'.$iter;
				} else {
						$checkname = $name;
				}
				$set = new Dase_DBO_Itemset($this->db);
				$set->name = $checkname;
				if (!$set->findOne()) {
						return $checkname;
				} else {
						$iter++;
						return $this->_findUniqueName($name,$iter);
				}
		}
}

