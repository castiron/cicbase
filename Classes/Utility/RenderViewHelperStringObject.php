<?php namespace CIC\Cicbase\Utility;

/**
* If we simply pass a string for the value of a view variable, then it would
* try to escape that string. So passing an object with a __toString() implementation
* will prevent the string from being escaped.
*
*/
class RenderViewHelperStringObject {
    /**
    * @var string
    */
    protected $content = '';

    public function __construct($content = '') {
        $this->content = $content;
    }

    public function __toString() {
        return $this->content;
    }
}