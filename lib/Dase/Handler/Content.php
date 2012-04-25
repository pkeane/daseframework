<?php

class Dase_Handler_Content extends Dase_Handler
{
    public $resource_map = array(
        '/' => 'items',
        'create' => 'content_form',
        'items' => 'items',
        'file/{name}' => 'file',
        'file/thumb/{name}' => 'thumbnail',
        'file/view/{name}' => 'view',
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
            $r->renderRedirect('content/items');
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
        $file = $r->getFile('uploaded_file');
        if ($file && $file->isValid()) {
            $item->deleteMedia();
            $item->processUploadedFile($file);
        } else {
            $r->renderError(400);
        }
        $item->url = 'content/item/'.$item->name;
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

    public function getView($r) 
    { 
        $media_dir = $this->config->getMediaDir();
        $file_path = $media_dir.'/view/'.$r->get('name').'.'.$r->ext;
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
            $r->renderTemplate('framework/content_form.tpl');
        }

    public function postToContentForm($r)
    {
        $item = new Dase_DBO_Item($this->db);
        $item->body = $r->get('body');
        $item->title = $r->get('title');

        $file = $r->getFile('uploaded_file');
        if ($file && $file->isValid()) {
            $item->name = $item->processUploadedFile($file);
            if (!$item->title) {
                $item->title = $item->name;
            }
        } else {
            if (!$item->title) {
                $item->title = substr($item->body,0,20);
            }
            if (!$item->title) {
                $params['msg'] = "title or body is required if no file is uploaded";
                $r->renderRedirect('content',$params);
            }
            $item->name = Dase_DBO_Item::findUniqueName(Dase_Util::dirify($item->title));
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
        $title = '';
        if ( $r->http_title ) {
            $title = $r->http_title;
        } elseif ( $r->slug ) {
            $title = $r->slug;
        } else {
            $title = dechex(time());
        }
        $item->title = $title;
        $media_dir = $this->config->getMediaDir();
        $basename = Dase_DBO_Item::findUniqueName(Dase_Util::dirify($title));
        $name = Dase_File::findNextUnique($media_dir,$basename,$ext);
        $file_path = $media_dir.'/'.$name;
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

            $item->name = $name;
        if (!$item->title) {
            $item->title = $item->name;
        }
        $item->file_ext = $ext;
        $item->file_path = $file_path;
        $item->file_url = 'content/file/'.$item->name;
        $item->filesize = filesize($file_path);
        $item->mime = $mime_type;
        $item->makeDerivatives($media_dir);

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
            $media_dir = $this->config->getMediaDir();
            $basename = Dase_Util::dirify(pathinfo($file_url,PATHINFO_FILENAME));
            $name = Dase_File::findNextUnique($media_dir,$basename,$ext);
            $file_path = $media_dir.'/'.$name;
            //move file to new home
            file_put_contents($file_path,file_get_contents($file_url));
            chmod($file_path,0775);
            $size = @getimagesize($file_path);
            if (isset($size[0]) && $size[0]) { $item->width = $size[0]; }
            if (isset($size[1]) && $size[1]) { $item->height = $size[1]; }
                $item->name = $name;
            if (!$item->title) {
                $item->title = $item->name;
            }
            $item->file_ext = $ext;
            $item->file_path = $file_path;
            $item->file_url = 'content/file/'.$item->name;
            $item->filesize = filesize($file_path);
            $item->mime = $mime_type;
            $item->makeDerivatives($media_dir);
        } else { //meaning no file
            if (!$item->title) {
                $item->title = substr($item->body,0,20);
            }
            $item->name = Dase_DBO_Item::findUniqueName(Dase_Util::dirify($item->title));
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
}

