<?php

class Dase_Handler_File extends Dase_Handler
{
		public $resource_map = array(
				'{name}' => 'file',
				'thumb/{name}' => 'thumbnail',
		);

		public function getFile($r) 
		{ 
				$media_dir = $this->config->getMediaDir();
				$file_path = $media_dir.'/'.$r->get('name').'.'.$r->getFormat();
				$r->serveFile($file_path,$r->response_mime_type);
		}

		public function getThumbnail($r) 
		{ 
				$media_dir = $this->config->getMediaDir();
				$file_path = $media_dir.'/thumb/'.$r->get('name').'.'.$r->getFormat();
				$r->serveFile($file_path,$r->response_mime_type);
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

}
