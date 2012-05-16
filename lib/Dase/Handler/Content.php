<?php

class Dase_Handler_Content extends Dase_Handler
{
    public $resource_map = array(
        '/' => 'items',
        'create' => 'content_form',
        'csv/form' => 'csv_form',
        'items' => 'items',
        'items/thumbnails' => 'items_thumbnails',
        'items/metadata' => 'items_metadata',
        'items/{id}/files' => 'item_files',
        'items/{id}/metadata' => 'item_metadata',
        'items/{id}/metadata/{value_id}' => 'item_metadata_value',
        'items/{id}/metadata/{value_id}/form' => 'item_metadata_value_form',
        'file/{serial_number}' => 'file',
        'file/thumb/{serial_number}' => 'thumbnail',
        'file/view/{serial_number}' => 'view',
        'item/{serial_number}' => 'item_by_serial_number',
        'items/{id}' => 'item',
        'items/{id}/edit' => 'item_edit_form',
        'items/{id}/edit/metadata' => 'item_edit_metadata_form',
        'items/{id}/swap' => 'item_swap',
        'items/{id}/map' => 'item_map',
        'items/{id}/location' => 'item_location',
        'attribute/{id}' => 'attribute',
        'attribute/{id}/edit' => 'attribute_edit_form',
        'attribute/{id}/input_form' => 'attribute_input_form',
        'attributes' => 'attributes',
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

    public function deleteItemFiles($r) 
    {
        $item = new Dase_DBO_Item($this->db);
        if (!$item->load($r->get('id'))) {
            $r->renderError(404);
        }
        $item->deleteFiles();
        $r->renderResponse('deleted');
    }

    public function getCsvForm($r)
    {
        $r->renderTemplate('framework/content_csv_form.tpl');
    }


    public function postToCsvForm($r)
    {

        $file = $r->getFile('uploaded_file');
        if ($file && $file->isValid() && ('text' == substr($file->getMimeType(),0,4))) {
            $count = Dase_DBO_Item::processCsv($this->db,$file,$this->user);
        } else {
            $type = $file->getMimeType();
            $r->renderError(400,"invalid $type file");
        }
        $r->renderRedirect('content/items');
    }

    public function putItemMetadataValue($r)
    {
        $item = new Dase_DBO_Item($this->db);
        if (!$item->load($r->get('id'))) {
            $r->renderError(404);
        }
        $v = new Dase_DBO_Value($this->db);
        $v->load($r->get('value_id'));;
        if ($v->item_id == $item->id) {
            $v->text = $r->getBody();
            $v->update();
            $r->renderOk();
        }
    }

    public function postToItemMetadataValue($r)
    {
        $item = new Dase_DBO_Item($this->db);
        if (!$item->load($r->get('id'))) {
            $r->renderError(404);
        }
        $v = new Dase_DBO_Value($this->db);
        $v->load($r->get('value_id'));;
        if ($v->item_id == $item->id) {
            $v->text = $r->get('value_text');
            $v->update();
        }
        $r->renderRedirect('content/items/'.$item->id);
    }

    public function getItemMetadataValue($r)
    {
        $item = new Dase_DBO_Item($this->db);
        if (!$item->load($r->get('id'))) {
            $r->renderError(404);
        }
        $v = new Dase_DBO_Value($this->db);
        $v->load($r->get('value_id'));;
        if ($v->item_id == $item->id) {
            $r->renderResponse($v->text);
        }
    }

    public function deleteItemMetadataValue($r)
    {
        $item = new Dase_DBO_Item($this->db);
        if (!$item->load($r->get('id'))) {
            $r->renderError(404);
        }
        $v = new Dase_DBO_Value($this->db);
        $v->load($r->get('value_id'));;
        if ($v->item_id == $item->id) {
            $v->delete();
            $r->renderResponse('deleted');
        }
    }

    public function getItemMetadataValueForm($r) 
    {
        $item = new Dase_DBO_Item($this->db);
        if (!$item->load($r->get('id'))) {
            $r->renderError(404);
        }
        $r->assign('item',$item);
        $v = new Dase_DBO_Value($this->db);
        $v->load($r->get('value_id'));;
        if ($v->item_id != $item->id) {
            $r->renderError(404);
        }
        $att = new Dase_DBO_Attribute($this->db);
        if (!$att->load($v->attribute_id)) {
            $r->renderError(404);
        }
        if ($att->values_json) {
            $att->values = json_decode($att->values_json,1);
        }
        if ($att->values_item_type) {
            $values = array();
            $items = new Dase_DBO_Item($this->db);
            $items->type = $att->values_item_type;
            foreach ($items->find() as $item) {
                $item = clone($item);
                $values[] = $item->title;
            }
            sort($values);
            $att->values = $values;
        }
        $r->assign('value',$v);
        $r->assign('att',$att);
        $r->renderTemplate('framework/content_metadata_value_edit_form.tpl');
    }

    public function postToItemsMetadata($r)
    {
        if ($r->get('items') && $r->get('attribute_id')) {
            $item_ids = explode('|',$r->get('items'));
            foreach ($item_ids as $item_id) {
                $v = new Dase_DBO_Value($this->db);
                $v->item_id = $item_id;
                $v->attribute_id = $r->get('attribute_id');
                $v->text = $r->get('value_text');
                $v->insert();
            }
        }
        $r->renderRedirect('content');
    }

    public function postToItemMetadata($r)
    {
        $item = new Dase_DBO_Item($this->db);
        if (!$item->load($r->get('id'))) {
            $r->renderError(404);
        }
        if ($r->get('attribute_id')) {
            $v = new Dase_DBO_Value($this->db);
            $v->item_id = $item->id;
            $v->attribute_id = $r->get('attribute_id');
            $v->text = $r->get('value_text');
            $v->insert();
        }
        $r->renderRedirect('content/items/'.$item->id);
    }

    public function getAttributeInputForm($r) 
    {
        $att = new Dase_DBO_Attribute($this->db);
        if (!$att->load($r->get('id'))) {
            $r->renderError(404);
        }
        if ($att->values_json) {
            $att->values = json_decode($att->values_json,1);
        }
        if ($att->values_item_type) {
            $values = array();
            $items = new Dase_DBO_Item($this->db);
            $items->type = $att->values_item_type;
            foreach ($items->find() as $item) {
                $item = clone($item);
                $values[] = $item->title;
            }
            sort($values);
            $att->values = $values;
        }
        $r->assign('att',$att);
        $r->renderTemplate('framework/content_attribute_input_form.tpl');
    }

    public function getAttributeEditForm($r) 
    {
        $att = new Dase_DBO_Attribute($this->db);
        if (!$att->load($r->get('id'))) {
            $r->renderRedirect('content/attributes');
        }
        if ($att->values_json) {
            $att->values = json_decode($att->values_json,1);
            $r->assign('valstring',join("\n",$att->values));
        }
        $r->assign('att',$att);
        $r->renderTemplate('framework/content_attribute_edit.tpl');
    }

    public function postToAttributeEditForm($r)
    {
        $att = new Dase_DBO_Attribute($this->db);
        if (!$att->load($r->get('id'))) {
            $r->renderError(404);
        }
        if (!$this->user->is_admin) {
            $r->renderError(401);
        }
        $att->name = $r->get('name');
        $att->values_item_type = $r->get('values_item_type');
        $att->applies_to_type = $r->get('applies_to_type');
        $att->input_type = $r->get('input_type');
        $pattern = '/[\n;]/';
        $prepared_string = preg_replace($pattern,'%',$r->get('values'));
        $values_array = array();
        foreach (explode('%',$prepared_string) as $v) {
            $values_array[] = trim($v);
        }
        $att->values_json = json_encode($values_array);
        $att->update();
        $r->renderRedirect('content/attribute/'.$att->id.'/edit');
    }

    public function deleteAttribute($r)
    {
        $att = new Dase_DBO_Attribute($this->db);
        if (!$att->load($r->get('id'))) {
            $r->renderError(404);
        }
        if (!$this->user->is_admin) {
            $r->renderError(401);
        }
        $vals = new Dase_DBO_Value($this->db);
        $vals->attribute_id = $att->id;
        if (!$vals->findOne()) {
            $att->delete();
            $r->renderResponse('deleted attribute');
        } else {
            $r->renderError(400);
        }
    }

    public function postToAttributes($r)
    {
        $att = new Dase_DBO_Attribute($this->db);
        $att->name = $r->get('name');
        if (!$att->findOne()) {
            $att->ascii_id = Dase_DBO_Attribute::findUniqueAsciiId($this->db,$att->name);
            $att->input_type = 'text';
            $att->created = date(DATE_ATOM);
            $att->created_by = $this->user->eid;
            $att->insert();
        }
        $r->renderRedirect('content/attributes');
    }

    public function postToItemLocation($r)
    {
        $item = new Dase_DBO_Item($this->db);
        if (!$item->load($r->get('id'))) {
            $r->renderRedirect('content');
        }
        $item->lat = $r->get('lat');
        $item->lng = $r->get('lng');
        $item->update();
        $r->renderRedirect('content/items/'.$item->id.'/map');
    }

    public function getAttributes($r)
    {
        $atts = new Dase_DBO_Attribute($this->db);
        $r->assign('atts',$atts->findAll(1));
        $r->renderTemplate('framework/content_attributes.tpl');
    }

    public function getItemMap($r)
    {
        $item = new Dase_DBO_Item($this->db);
        if (!$item->load($r->get('id'))) {
            $r->renderError(404);
        }
        $r->assign('item',$item);
        $r->renderTemplate('framework/content_item_map.tpl');
    }

    public function getItemEditForm($r) 
    {
        $item = new Dase_DBO_Item($this->db);
        if (!$item->load($r->get('id'))) {
            $r->renderRedirect('content/items');
        }
        $r->assign('not_set',$r->get('not_set'));
        $r->assign('page',$r->get('page'));
        $r->assign('q',$r->get('q'));
        $r->assign('att',$r->get('att'));
        $r->assign('val',$r->get('val'));
        $r->assign('type',$r->get('type'));
        $r->assign('max',$r->get('max'));
        $r->assign('num',$r->get('num'));
        $r->assign('display',$r->get('display'));
        $r->assign('item',$item);
        $types = new Dase_DBO_Item($this->db);
        $types->type = 'type';
        $r->assign('types',$types->findAll(1));
        $r->assign('item',$item);
        $r->renderTemplate('framework/content_item_edit.tpl');
    }

    public function getItemEditMetadataForm($r) 
    {
        $item = new Dase_DBO_Item($this->db);
        if (!$item->load($r->get('id'))) {
            $r->renderRedirect('content/items');
        }
        $r->assign('not_set',$r->get('not_set'));
        $r->assign('page',$r->get('page'));
        $r->assign('q',$r->get('q'));
        $r->assign('att',$r->get('att'));
        $r->assign('val',$r->get('val'));
        $r->assign('type',$r->get('type'));
        $r->assign('max',$r->get('max'));
        $r->assign('num',$r->get('num'));
        $r->assign('display',$r->get('display'));
        $types = new Dase_DBO_Item($this->db);
        $types->type = 'type';
        $r->assign('types',$types->findAll(1));
        $atts = new Dase_DBO_Attribute($this->db);
        $atts->orderBy('name');
        $atts = $atts->findAll(1);
        $item->getMetadata($r);
        $r->assign('atts',$atts);
        $r->assign('item',$item);
        $r->renderTemplate('framework/content_item_edit_metadata.tpl');
    }

    public function getItemBySerialNumberJson($r) 
    {
        $item = new Dase_DBO_Item($this->db);
        $item->serial_number = $r->get('serial_number');
        if ( $item->findOne()) {
            $r->renderResponse($item->getAsJson($r));
        } else {
            $r->renderError(404);
        }
    }

    public function getItemBySerialNumber($r) 
    {
        $item = new Dase_DBO_Item($this->db);
        $item->serial_number = $r->get('serial_number');
        if (!$item->findOne()) {
            $r->renderError(404);
        }
        $item->getMetadata($r);
        $r->assign('item',$item);
        $r->renderTemplate('framework/content_item.tpl');
    }

    public function getItem($r) 
    {
        //typically NOT used to get item -- see getItems w/ num
        $item = new Dase_DBO_Item($this->db);
        if (!$item->load($r->get('id')))  {
            $r->renderError(404);
        }
        $item->getMetadata($r);
        $r->assign('item',$item);
        $r->renderTemplate('framework/content_item.tpl');
    }

    public function deleteItem($r)
    {
        $item = new Dase_DBO_Item($this->db);
        if (!$item->load($r->get('id'))) {
            $r->renderError(404);
        }
        if (($this->user->eid != $item->created_by) && !$this->user->is_admin) {
            $r->renderError(401);
        }
        if ($item->expunge()) {
            $r->renderResponse('deleted item');
        } else {
            $r->renderError(400);
        }
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
        $item->type = $r->get('item_type');
        if ($r->has('lat')) {
            $item->lat = $r->get('lat');
        }
        if ($r->has('lng')) {
            $item->lng = $r->get('lng');
        }
        $item->updated_by = $this->user->eid;
        $item->updated = date(DATE_ATOM);
        $item->update();
        if ($r->get('not_set')) {
            $r->renderRedirect('content/items/'.$item->id);
        }
        $params['page'] = $r->get('page');
        $params['q'] = $r->get('q');
        $params['att'] = $r->get('att');
        $params['val'] = $r->get('val');
        $params['type'] = $r->get('type');
        $params['max'] = $r->get('max');
        $params['num'] = $r->get('num');
        $params['display'] = $r->get('display');
        $r->renderRedirect('content/items',$params);
    }

    public function getItemSwap($r) 
    {
        $item = new Dase_DBO_Item($this->db);
        if (!$item->load($r->get('id'))) {
            $r->renderRedirect('content/items');
        }
        $r->assign('not_set',$r->get('not_set'));
        $r->assign('page',$r->get('page'));
        $r->assign('q',$r->get('q'));
        $r->assign('att',$r->get('att'));
        $r->assign('val',$r->get('val'));
        $r->assign('type',$r->get('type'));
        $r->assign('max',$r->get('max'));
        $r->assign('num',$r->get('num'));
        $r->assign('display',$r->get('display'));
        $r->assign('item',$item);
        $r->renderTemplate('framework/content_item_swap.tpl');
    }

    public function postToItemSwap($r)
    {
        $item = new Dase_DBO_Item($this->db);
        if (!$item->load($r->get('id'))) {
            $r->renderError(404);
        }
        if (($this->user->eid != $item->created_by) && !$this->user->is_admin) {
            $r->renderError(401);
        }
        $file = $r->getFile('uploaded_file');
        if ($file && $file->isValid()) {
            $item->deleteMedia();
            $item->processUploadedFile($file);
        } else {
            $r->renderError(400);
        }
        $item->url = 'content/item/'.$item->serial_number;
        $item->updated_by = $this->user->eid;
        $item->updated = date(DATE_ATOM);
        $item->update();
        if ($r->get('not_set')) {
            $r->renderRedirect('content/items/'.$item->id);
        }
        $params['page'] = $r->get('page');
        $params['q'] = $r->get('q');
        $params['att'] = $r->get('att');
        $params['val'] = $r->get('val');
        $params['type'] = $r->get('type');
        $params['max'] = $r->get('max');
        $params['num'] = $r->get('num');
        $params['display'] = $r->get('display');
        $r->renderRedirect('content/items',$params);
    }

    public function getFile($r) 
    { 
        $media_dir = $this->config->getMediaDir();
        $file_path = $media_dir.'/'.$r->get('serial_number').'.'.$r->ext;
        $r->serveFile($file_path,$r->mime);
    }

    public function getThumbnail($r) 
    { 
        $media_dir = $this->config->getMediaDir();
        $file_path = $media_dir.'/thumb/'.$r->get('serial_number').'.'.$r->ext;
        $r->serveFile($file_path,$r->mime);
    }

    public function getView($r) 
    { 
        $media_dir = $this->config->getMediaDir();
        $file_path = $media_dir.'/view/'.$r->get('serial_number').'.'.$r->ext;
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

        public function getThumbnailJpg($r) { 
            $media_dir = $this->config->getMediaDir();
            $file_path = $media_dir.'/thumb/'.$r->get('serial_number').'.'.$r->ext;
            if (!is_file($file_path)) {
                $file_path = BASE_PATH.'/www/img/missing.jpg';
            }
            $r->serveFile($file_path,$r->mime);
        }
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

        public function getViewJpg($r) { return $this->getView($r); }
        public function getViewGif($r) { return $this->getView($r); }
        public function getViewPng($r) { return $this->getView($r); }
        public function getViewTxt($r) { return $this->getView($r); }
        public function getViewPdf($r) { return $this->getView($r); }
        public function getViewDoc($r) { return $this->getView($r); }
        public function getViewMp3($r) { return $this->getView($r); }
        public function getViewMp4($r) { return $this->getView($r); }
        public function getViewMov($r) { return $this->getView($r); }
        public function getViewOgv($r) { return $this->getView($r); }
        public function getViewOga($r) { return $this->getView($r); }

        public function getContentForm($r) 
        {
            $types = new Dase_DBO_Item($this->db);
            $types->type = 'type';
            $r->assign('types',$types->findAll(1));
            $r->renderTemplate('framework/content_form.tpl');
        }

    public function postToContentForm($r)
    {
        $item = new Dase_DBO_Item($this->db);
        $item->body = $r->get('body');
        $item->title = $r->get('title');
        $item->type = $r->get('type');
        $item->serial_number = Dase_DBO_Item::getUniqueSerialNumber($this->db,$r->get('preferred_sernum'));
        if (!$item->title) {
            $item->title = $item->serial_number;
        }

        $file = $r->getFile('uploaded_file');
        if ($file && $file->isValid()) {
            $item->processUploadedFile($file);
        } else {
            $item->thumbnail_url = 	'www/img/mime_icons/content.png';
        }
        $item->created_by = $this->user->eid;
        $item->created = date(DATE_ATOM);
        $item->updated_by = $this->user->eid;
        $item->updated = date(DATE_ATOM);
        $item->url = 'content/item/'.$item->serial_number;
        $item->insert();
        $r->renderRedirect('content/items');
    }

    public function getItems($r) 
    {
        $items = Dase_DBO_Item::retrieveSet($this->db,$r);

        //total == how many items total (unpaged) this search returns
        $total = count($items);

        if (0 == $total) {
            // $r->renderTemplate('framework/content_items_thumbs.tpl');
        }

        //max == mximun # of items shown on a page
        $max = $r->get('max') ? $r->get('max') : 60;

        //page == which page we are on
        if ($r->get('page')) {
            $page = $r->get('page');
        } else {
            $page = 1;
        }
        $r->assign('page',$page);

        //start = number (w/in total result) of first item on page
        if (1 == $page) {
            $start = 1;
        } else {
            $start = (($page-1) * $max)+1;
        }

        //$start = $r->get('start') ? $r->get('start') : 1;

        //end = number (w/in total result) of last item on page 
        $end = $start + $max - 1;

        if ($total < $end) {
            $end = $total;
        }

        $total_pages = ceil(($total/$max)-.001);

        if ($total_pages < 11) {
            $paginated = $total_pages;
        } else {
            $paginated = 10;
        }

        $r->assign('paginated',$paginated);
        $r->assign('total_pages',$total_pages);
        $r->assign('end',$end);
        $r->assign('start',$start);
        $r->assign('max',$max);
        $r->assign('total',$total);

        if ($r->get('curr')) {
            $r->assign('curr',$r->get('curr'));
        }


        if ($r->get('num')) {
            $num = $r->get('num');
            $r->assign('num',$num);
            $r->assign('display',$r->get('display'));
            //because items is indexed w/ item id
            $slice = array_slice($items,$num-1,1);
            $item = array_pop($slice);
            $item->getMetadata($r);
            $r->assign('item',$item);
            $r->assign('is_set',1);
            $r->renderTemplate('framework/content_item.tpl');
        }

        $items = array_slice($items,$start-1,$max);
        $r->assign('items',$items);

        if ('table' == $r->get('display')) {
            $r->assign('display','table');
            $r->renderTemplate('framework/content_items_table.tpl');
        } else {
            $r->renderTemplate('framework/content_items_thumbs.tpl');
        }
    }

    public function getItemsJson($r) 
    {
        $items = new Dase_DBO_Item($this->db);
        $items->orderBy('updated DESC');
        $set = array();
        foreach ($items->find() as $item) {
            $item = clone($item);
            $set[] = $item->getAsArray($r);
        }
        $r->renderResponse(Dase_Json::get($set));
    }

    private function _processFile($r)
    {
        //create new item
        $item = new Dase_DBO_Item($this->db);

        $mime_type = $r->getContentType();
        if (!Dase_Media::isAcceptable($mime_type)) {
            $r->renderError(415,'cannot accept '.$mime_type);
        }
        $bits = $r->getBody();
        $file_meta = Dase_File::$types_map[$mime_type];
        $ext = $file_meta['ext'];
        /*
        if ( $r->http_title ) {
            $item->title = $r->http_title;
        } elseif ( $r->slug ) {
            $item->title = $r->slug;
        } 
         */
        $item->serial_number = Dase_DBO_Item::getUniqueSerialNumber($this->db,$item->title);
        if (!$item->title) {
            $item->title = $item->serial_number;
        } 
        $media_dir = $this->config->getMediaDir();
        $file_path = $media_dir.'/'.$item->serial_number.'.'.$ext;
        $ifp = @ fopen( $file_path, 'wb' );
        if (!$ifp) {
            $r->log->debug('cannot write file '.$file_path);
            $r->renderError(500,'cannot write file '.$file_path);
        }

        @fwrite( $ifp, $bits );
        fclose( $ifp );
        @ chmod( $file_path,0775);

        $size = @getimagesize($file_path);
        if (isset($size[0]) && $size[0]) { $item->width = $size[0]; }
        if (isset($size[1]) && $size[1]) { $item->height = $size[1]; }

            $item->file_ext = $ext;
        $item->file_path = $file_path;
        $item->file_url = 'content/file/'.$item->serial_number.'.'.$ext;
        $item->filesize = filesize($file_path);
        $this->file_original_name = $item->title;
        $item->mime = $mime_type;
        $item->makeDerivatives($media_dir);

        $item->created_by = $this->user->eid;
        $item->created = date(DATE_ATOM);
        $item->updated_by = $this->user->eid;
        $item->updated = date(DATE_ATOM);
        $item->url = 'content/item/'.$item->serial_number;
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
            $json_data['title'] = $json_data['id'];
            //    $r->renderError(415,'incorrect json format');
        }
        //create new item
        $item = new Dase_DBO_Item($this->db);
        $item->serial_number = Dase_DBO_Item::getUniqueSerialNumber($this->db);
        $item->title = $json_data['title'];
        if (isset($json_data['body'])) {
            $item->body = $json_data['body'];
        }
        if (isset($json_data['enclosure']) && isset($json_data['enclosure']['href'])) {
            $json_data['links']['file'] = $json_data['app_root'].$json_data['enclosure']['href'];
        }
        if (isset($json_data['links']['file'])) {
            $file_url = $json_data['links']['file'];
            $ext = strtolower(pathinfo($file_url, PATHINFO_EXTENSION));
            $mime_type = Dase_Request::$types[$ext];
            $media_dir = $this->config->getMediaDir();
            $basename = Dase_Util::dirify(pathinfo($file_url,PATHINFO_FILENAME));
            $file_path = $media_dir.'/'.$item->serial_number.'.'.$ext;
            //move file to new home
            file_put_contents($file_path,file_get_contents($file_url));
            chmod($file_path,0775);
            $size = @getimagesize($file_path);
            if (isset($size[0]) && $size[0]) { $item->width = $size[0]; }
            if (isset($size[1]) && $size[1]) { $item->height = $size[1]; }
                if (!$item->title) {
                    $item->title = $item->serial_number;
                }
            $item->file_ext = $ext;
            $item->file_path = $file_path;
            $item->file_url = 'content/file/'.$item->serial_number.'.'.$ext;
            $item->filesize = filesize($file_path);
            $item->file_original_name = $basename.'.'.$ext;
            $item->mime = $mime_type;
            $item->makeDerivatives($media_dir);
        } else { //meaning no file
            if (!$item->title) {
                $item->title = $item->serial_number;
            }
            $item->thumbnail_url = 	'www/img/mime_icons/content.png';
        }
        $item->created_by = $this->user->eid;
        $item->created = date(DATE_ATOM);
        $item->updated_by = $this->user->eid;
        $item->updated = date(DATE_ATOM);
        $item->url = 'content/item/'.$item->serial_number;
        if ($item->insert()) {
            //prob should happen earlier
            if (isset($json_data['metadata'])) {
                foreach ($json_data['metadata'] as $att_ascii => $vals) {
                    foreach ($vals as $val) {
                        $item->setValue($r,$att_ascii,$val);
                    }
                }
            }
            $r->renderOk('added item');
        } else {
            $r->renderError(400);
        }
    }
}

