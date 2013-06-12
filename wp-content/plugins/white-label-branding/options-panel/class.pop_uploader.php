<?php

/**
 * Handle file uploads via XMLHttpRequest
 */
class pop_qqUploadedFileXhr {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {    
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
    }
    function getName() {
        return $_GET['qqfile'];
    }
    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new Exception(__('Getting content length is not supported.','pop'));
        }      
    }   
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class pop_qqUploadedFileForm {  
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
            return false;
        }
        return true;
    }
    function getName() {
        return $_FILES['qqfile']['name'];
    }
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
}

class pop_qqFileUploader {
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760){        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        //$this->checkServerSettings();       

        if (isset($_GET['qqfile'])) {
            $this->file = new pop_qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new pop_qqUploadedFileForm();
        } else {
            $this->file = false; 
        }
    }
    
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
			die(json_encode((object)array('error'=>sprintf( __('increase post_max_size and upload_max_filesize to %s','pop') ,$size))));
			//die("{'error':'".sprintf('increase post_max_size and upload_max_filesize to %s',$size)."'}");    
        }        
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload( $replaceOldFile = FALSE){
		$upload_dir = wp_upload_dir();
		$uploadDirectory = $upload_dir['path'].'/';
		$uploadUrl = $upload_dir['url'].'/';
        if (!is_writable($uploadDirectory)){
            return array('error' => __("Server error. Upload directory isn't writable.",'pop') );
        }
        sleep(3);
        if (!$this->file){
            return array('error' => __('No files were uploaded.','pop') );
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => __('File is empty','pop') );
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => __('File is too large','pop') );
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' =>  sprintf( __('File has an invalid extension, it should be one of %s','pop') , $these));
        }
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }
        
        if ($this->file->save($uploadDirectory . $filename . '.' . $ext)){
            return array(
				'success'=>true,
				'url'=>	$uploadUrl . $filename . '.' . $ext,
				'id'=> $_REQUEST['id']
			);
        } else {
            return array('error'=> __('Could not save uploaded file.','pop') .  __('The upload was cancelled, or server error encountered','pop') );
        }
        
    }    
}