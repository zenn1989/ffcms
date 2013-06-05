<?php

class hook_bbtohtml_front implements hook_front
{
    private $parser = null;

    public function load()
    {
        global $constant;
        if($this->parser == null)
        {
            require_once $constant->root.'/resource/xbbcode/bbcode.lib.php';
            $this->parser = new bbcode;
        }
        return $this;
    }

    public function bbcode2html($bbcode)
    {
        $this->parser->parse($bbcode);
        return $this->parser->get_html();
    }
}







?>