<?php

App::uses('Component', 'Controller');

class FileUploaderComponent extends Component {

    protected $file;
    protected $dir = false;
    protected $maxFileSize = true;
    protected $allowedExtensions = true;
    protected $newFileName = false;
    protected $ext = false;

    public function setFile(array $file) {
        $this->file = $file;
        return $this;
    }

    public function setUploadDir($dir) {
        $this->dir = $dir;
        return $this;
    }

    public function setMaxFileSize($size) {
        $this->maxFileSize = $size;
        return $this;
    }

    public function setAllowedExtensions(array $ext) {
        $this->allowedExtensions = $ext;
        return $this;
    }

    public function setExt($ext) {
        $this->ext = $ext;
    }

    public function setNewFileName($name) {
        $this->newFileName = $name;
    }

    public function getNewFileName() {
        return $this->newFileName;
    }

    public function upload() {

        try {
            $this->checkForErrors();
        } catch (Exception $e) {
            return __($e->getMessage());
        }
        try {
            $this->checkMaxFileSize();
        } catch (Exception $e) {
            return __($e->getMessage());
        }
        try {
            $this->checkAllowedExtensions();
        } catch (Exception $e) {
            return __($e->getMessage());
        }

        if (is_uploaded_file($this->file['tmp_name'])) {
            $ext = $this->ext;
            if ($this->dir === false) {
                $dirname = WWW_ROOT . 'img' . DS;
            } else {
                rtrim($dirname = $this->dir, DS) . DS;
            }
            if (!file_exists($dirname)) {
                mkdir($dirname, 0777, true);
            }
            $nfn = $this->filename ? $dirname . $this->filename . '.' . $ext : $dirname . $this->randString(6) . '.' . $ext;
            $this->setNewFileName($nfn);
            $filePath = $nfn;
            move_uploaded_file($this->file['tmp_name'], $filePath);
            return true;
        }
    }

    public function deleteIfExists($path) {
       // var_dump($path);echo '<br>';
        //var_dump(file_exists($path));die;
        if (file_exists($path)) {
            unlink($path);
        }
        return $this;
    }

    protected function checkMaxFileSize() {
        $file = $this->file;
        $maxFileSize = $this->maxFileSize;
        if ($file['size'] > $maxFileSize && $this->maxFileSize !== false)
            throw new Exception('Filesize is too large');

        return true;
    }

    protected function checkAllowedExtensions() {
        $file = $this->file;
        $ext = explode('/', $file['type']);
        $ext = $ext[1];
        $this->setExt($ext);
        if (is_array($this->allowedExtensions)) {
            if (in_array($ext, $this->allowedExtensions)) {
                throw new Exception('Incorrect extension');
            }
        }
    }

    protected function slugify($str) {
        $search = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        $replace = array('s', 't', 's', 't', 's', 't', 's', 't', 'i', 'a', 'a', 'i', 'a', 'a', 'e', 'E');
        $str = str_ireplace($search, $replace, strtolower(trim($str)));
        $str = preg_replace('/[^\w\d\-\ ]/', '', $str);
        $str = str_replace(' ', '-', $str);
        return preg_replace('/\-{2,}', '-', $str);
    }

    protected function checkForErrors() {
        switch ($this->file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('No file sent.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('Exceeded filesize limit.');
            default:
                throw new RuntimeException('Unknown errors.');
        }
    }

    protected function randString($length, $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789') {
        $str = '';
        $count = strlen($charset);
        while ($length--) {
            $str .= $charset[mt_rand(0, $count - 1)];
        }
        return $str;
    }

}
