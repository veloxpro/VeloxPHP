<?php
namespace Velox\Framework\Http;

class UploadedFile {
    protected $name;
    protected $fileName;
    protected $type;
    protected $tmpPath;
    protected $error;
    protected $size;

    public function __construct($name, $params) {
        $this->name = $name;
        $this->fileName = $params['name'];
        $this->type = $params['type'];
        $this->tmpPath = $params['tmp_name'];
        $this->error = $params['error'];
        $this->size = $params['size'];
    }

    public function moveTo($destination) {
        if (!file_exists($this->tmpPath))
            return false;
        return move_uploaded_file($this->tmpPath, $destination);
    }

    public function getFileName() {
        return $this->fileName;
    }

    public function getType() {
        return $this->type;
    }
}
