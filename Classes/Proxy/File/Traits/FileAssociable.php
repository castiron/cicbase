<?php namespace CIC\Cicbase\Proxy\File\Traits;

trait FileAssociable {
    /**
     * The absolute path to the file
     * @var string
     */
    protected $file = '';

    /**
     * @param string $file
     */
    public function setFile($file = '') {
        $this->file = $file;
    }
}
