<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

class hook_bbtohtml_front implements hook_front
{
    private $parser = null;

    public function load()
    {
        global $engine;
        if ($this->parser == null) {
            require_once $engine->constant->root . '/resource/xbbcode/bbcode.lib.php';
            $this->parser = new bbcode;
        }
        return $this;
    }

    /**
     * Преобразование bbcode в html
     * @param $bbtext
     * @return mixed
     */
    public function bbcode2html($bbtext)
    {
        $this->parser->parse($bbtext);
        return $this->parser->get_html();
    }

    /**
     * Убрать все bbcode из $bbtext
     * @param $bbtext
     * @return mixed
     */
    public function nobbcode($bbtext)
    {
        return preg_replace('/\[.*?\]/s', '', $bbtext);
    }
}


?>