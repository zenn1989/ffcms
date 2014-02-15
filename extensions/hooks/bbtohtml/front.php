<?php

class hooks_bbtohtml_front
{
    private $parser = null;
    protected static $instance = null;

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function make() {}

    /**
     * Parse bbcode to html syntax
     * @param $bbtext
     * @return mixed
     */
    public function bbcode2html($bbtext)
    {
        if(is_null($this->parser)) {
            require_once root . '/resource/xbbcode/bbcode.lib.php';
            $this->parser = new bbcode;
        }
        $this->parser->parse($bbtext);
        return $this->parser->get_html();
    }

    /**
     * Remove all bbcodes from $bbtext
     * @param $bbtext
     * @return mixed
     */
    public function nobbcode($bbtext)
    {
        return preg_replace('/\[.*?\]/s', '', $bbtext);
    }
}


?>