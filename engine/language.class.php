<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

/**
 * класс управляющий языками отображения сайта
 */
class language
{
    private $lang = array();
    private $use_lang = null;
    private $load_add_front = false;
    private $load_add_back = false;
    private $available = array();

    function language()
    {
        global $constant, $system;
        $this->loadAvailableLanguages();
        $this->use_lang = $constant->lang;
        if($_COOKIE['ffcms_lang'] != null && $this->canUseLanguage($_COOKIE['ffcms_lang'])) {
            $this->use_lang = $_COOKIE['ffcms_lang'];
        } elseif($_SERVER['HTTP_ACCEPT_LANGUAGE'] != null && $this->canUseLanguage(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2))) {
            $this->use_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        }
        $file = null;
        if (loader == "back") {
            $file = $constant->root . '/language/' . $this->use_lang . '.back.lang';
        } elseif(loader == "install") {
            $file = $constant->root . '/language/' . $this->use_lang . '.install.lang';
        } else {
            $file = $constant->root . '/language/' . $this->use_lang . '.front.lang';
        }

        if (file_exists($file)) {
            $con = file_get_contents($file);
            $lang_array = explode("\n", $con);
            foreach ($lang_array as $line) {
                // a<=>b is a min. example
                if (strlen($line) > 4) {
                    list($tag, $value) = explode("<=>", $line);
                    if(!$system->prefixEquals($tag, '#'))
                        $this->lang[$tag] = $value;
                }
            }
        }
        $this->additionalLoad();
        $this->themeLanguageLoad();
    }

    public function getCustom()
    {
        return $this->use_lang;
    }

    public function canUseLanguage($lang)
    {
        return in_array($lang, $this->available) ? true : false;
    }

    public function getAvailable()
    {
        return $this->available;
    }

    private function loadAvailableLanguages()
    {
        global $constant, $system;
        $language_files = scandir($constant->root . '/language/');
        $found_lang = array();
        foreach($language_files as $file) {
            if(!$system->prefixEquals('.', $file) && $system->suffixEquals($file, '.lang'))
                $found_lang = $system->arrayAdd(strstr($file, '.', true), $found_lang);
        }
        foreach($found_lang as $found) {
            if(file_exists($constant->root . '/language/' . $found . '.front.lang') && file_exists($constant->root . '/language/' . $found . '.back.lang') && file_exists($constant->root . '/language/' . $found . '.install.lang')) {
                $this->available[] = $found;
            }
        }
    }

    public function set($data)
    {
        foreach ($this->lang as $tag => $value) {
            $data = str_replace('{$lang::' . $tag . '}', $value, $data);
        }
        return $data;
    }

    private function additionalLoad()
    {
        global $constant, $system;
        $file = null;
        if(loader == "back") {
            $file = $constant->root . '/language/'. $this->use_lang . '.back.addition.lang';
            $this->load_add_back = true;
        } else {
            $file = $constant->root . '/language/'. $this->use_lang . '.front.addition.lang';
            $this->load_add_front = true;
        }
        if($file != null && file_exists($file)) {
            $con = file_get_contents($file);
            $add_array = explode("\n", $con);
            foreach($add_array as $line) {
                if(strlen($line) > 4) {
                    list($tag, $value) = explode("<=>", $line);
                    if(!$system->prefixEquals($tag, '#'))
                        $this->lang[$tag] = $value;
                }
            }
        }
    }

    private function themeLanguageLoad()
    {
        global $constant, $system;
        $file = null;
        if(loader == "back") {
            $file = $constant->root . $constant->ds . $constant->tpl_dir . "/admin/" . $this->use_lang . '.language.lang';
        } else {
            $file = $constant->root . $constant->ds . $constant->tpl_dir .$constant->ds . $constant->tpl_name . $constant->ds . $this->use_lang . '.language.lang';
        }
        if($file != null && file_exists($file)) {
            $con = file_get_contents($file);
            $add_array = explode("\n", $con);
            foreach($add_array as $line) {
                if(strlen($line) > 4) {
                    list($tag, $value) = explode("<=>", $line);
                    if(!$system->prefixEquals($tag, '#'))
                        $this->lang[$tag] = $value;
                }
            }
        }
    }

    public function get($data)
    {
        return $this->lang[$data];
    }

    /**
     * Функция добавляет в lang.type.addition.lang значения из массива $line_array
     * array(ru => array('add.some-string' => 'value this string));
     * @param $line_array
     */
    public function addLinesLanguage($line_array, $isback = false)
    {
        global $engine;
        foreach($line_array as $lang=>$lines) {
            $toWriteString = "\r\n";
            foreach($lines as $param=>$value) {
                $toWriteString .= $param . "<=>" . $value . "\n";
            }
            $file = null;
            if($isback) {
                $file = $engine->constant->root . "/language/" . $lang . ".back.addition.lang";
            } else {
                $file = $engine->constant->root . "/language/" . $lang . ".front.addition.lang";
            }
            file_put_contents($file, $toWriteString, FILE_APPEND | LOCK_EX);
        }
    }

}

?>