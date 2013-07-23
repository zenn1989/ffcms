<?php

/**
 * Класс шаблонизатора системы
 */
class template
{
    private $position = array();
    private $content = null;
    private $debug_readcount = 0;

    private $precompile_tag = array();

    function template()
    {
        if (loader == 'front' || loader == 'back') {
            $this->content = $this->getCarcase();
        }
    }

    /**
     * Допустимые позиции в шаблоне. В дальнейшем - сделать конфигурабельно относительно шаблонов
     * @return array
     */
    public function allowedPositions()
    {
        global $constant, $system;
        $file = $constant->root . $constant->ds . $constant->tpl_dir . $constant->ds . $constant->tpl_name . $constant->ds . "position.list";
        if(!file_exists($file)) {
            return array('header', 'left', 'body', 'right', 'bottom', 'footer');
        }
        return $system->altexplode('|', file_get_contents($file));
    }

    /**
     * Инициация шаблонизатора. Загрузка стандартных блоков.
     * Данные по каждой позиции расположены в page.class.php
     */
    public function init()
    {
        global $page, $extension;
        if (loader == 'front') {
            // инициация пре-загружаемых модулей с возможностью $page::setContentPosition(pos, data, index)
            $extension->modules_before_load();
        }
        foreach($this->allowedPositions() as $position) {
            $this->position[$position] = $page->getContentPosition($position);
        }
    }

    /**
     * Сборка и отображение шаблона
     */
    public function compile()
    {
        global $extension, $constant, $cache, $user, $database;
        if($database->isDown())
        {
            if($cache->check(true)) {
                return $cache->get();
            } else {
                $this->overloadCarcase('database_down');
                $this->globalset('admin_email', $constant->mail['from_email']);
            }
        }
        if($user->get('id') < 1 && $cache->check()) {
            return $cache->get();
        }
        foreach($this->allowedPositions() as $position) {
            $this->fortpl($position);
        }
        if (loader == 'front') {
            // инициация пост-загружаемых модулей
            $extension->moduleAfterLoad();
        }
        $this->postcompile();
        $this->language();
        $this->ruleCheck();
        $this->htmlhead();
        $this->cleanvar();
        if ($constant->do_compress_html && loader == 'front') {
            $this->compress();
        }
        if(loader == 'front' && !$cache->used())
            $cache->save($this->content);
        return $this->content;
    }

    /**
     * Пост-компиляция массива заданных тегов
     */
    private function postcompile()
    {
        foreach ($this->precompile_tag as $tag => $value) {
            $this->set($tag, $value);
        }
    }

    /**
     * Пост компиляция тегов, после сборки шаблона
     * @param String $tag
     * @param String $value
     */
    public function globalset($tag, $value)
    {
        $this->precompile_tag[$tag] = $value;
    }

    /**
     * Установка всех значений 1 блока по имени блока.
     */
    private function fortpl($position_name)
    {
        $result = null;
        $sort_entery = $this->position[$position_name];
        if (count($sort_entery) > 0) {
            foreach ($sort_entery as $enteries) {
                $result .= $enteries;
            }
        }
        $this->set($position_name, $result);
    }

    /**
     * Установка языковых переменных
     */
    private function language()
    {
        global $language;
        $this->content = $language->set($this->content);
    }

    /**
     * Загрузка основного каркаса шаблона, main.tpl
     */
    private function getCarcase()
    {
        return $this->get('main');
    }

    /**
     * Перезагрузка супер-позиции шаблона на указанный
     * @param $theme
     */
    public function overloadCarcase($theme)
    {
        $this->content = null;
        $this->content = $this->get($theme);
    }

    /**
     * Назначение переменной в супер-позиции $content
     * @param unknown_type $var
     * @param unknown_type $value
     */
    public function set($var, $value)
    {
        if (is_array($var)) {
            for ($i = 0; $i <= sizeof($var); $i++) {
                $this->content = str_replace('{$' . $var[$i] . '}', $value[$i], $this->content);
            }
        } else {
            $this->content = str_replace('{$' . $var . '}', $value, $this->content);
        }
    }

    /**
     * Очистка от {$__?___} в результате.
     * Для контента, обязательно использовать эквивалент -> $ = &#36;
     */
    private function cleanvar()
    {
        $this->content = preg_replace('/{\$(.*?)}/s', '', $this->content);
    }

    /**
     * Сжатие страницы путем удаления излишних переносов строк. Алгоритм героинский, но работает исправно и не бьет юзерский JS в коде шаблонов.
     */
    private function compress()
    {
        $compressed = null;
        preg_match_all('/(.*?)\n/s', $this->content, $matches);
        foreach ($matches[0] as $string) {
            if (preg_match('/[^\s]/s', $string))
                $compressed .= $string;
        }
        $this->content = $compressed;
    }

    /**
     * Функция для обработки условий {$if условие}содержимое{/$if} в шаблонах
     */
    public function ruleCheck($content = null)
    {
        global $rule;
        if ($content == null) {
            preg_match_all('/{\$if (.+?)}(.*?){\$\/if}/s', $this->content, $matches);
            for ($i = 0; $i < sizeof($matches[1]); $i++) {
                $theme_result = null;
                if ($rule->check($matches[1][$i])) {
                    $theme_result = $matches[2][$i];
                }
                $this->content = str_replace($matches[0][$i], $theme_result, $this->content);
            }
        } else {
            preg_match_all('/{\$if (.+?)}(.*?){\$\/if}/s', $content, $matches);
            for ($i = 0; $i < sizeof($matches[1]); $i++) {
                $theme_result = null;
                if ($rule->check($matches[1][$i])) {
                    $theme_result = $matches[2][$i];
                }
                $content = str_replace($matches[0][$i], $theme_result, $content);
            }
            return $content;
        }
    }

    private function htmlhead()
    {
        global $constant, $system;
        $compiled_header = null;
        // сборка подключения файлов javascript в шапку, к примеру из тела компонента, когда непосредственного доступа к тегу <head></head> нет.
        // пр: {$jsfile lib/js/script.js} уйдет в <head><script src="url/tpl_dir/tpl_name/lib/js/script.js
        preg_match_all('/{\$jsfile (.*?)}/s', $this->content, $jsfile_matches);
        $jsfile_array = $system->nullArrayClean(array_unique($jsfile_matches[1]));
        foreach ($jsfile_array as $jsfile) {
            if (file_exists($constant->root . $constant->ds . $constant->tpl_dir . $constant->ds . $constant->tpl_name . $constant->ds . $jsfile))
                $compiled_header .= "<script type=\"text/javascript\" src=\"{$constant->url}/{$constant->tpl_dir}/{$constant->tpl_name}/{$jsfile}\"></script>\r\n";
        }
        // сборка подключения CSS файлов в шапку, аналогично JS
        preg_match_all('/{\$cssfile (.*?)}/s', $this->content, $cssfile_matches);
        $cssfile_array = $system->nullArrayClean($cssfile_matches[1]);
        foreach ($cssfile_array as $cssfile) {
            if (file_exists($constant->root . $constant->ds . $constant->tpl_dir . $constant->ds . $constant->tpl_name . $constant->ds . $cssfile))
                $compiled_header .= "<link href=\"{$constant->url}/{$constant->tpl_dir}/{$constant->tpl_name}/{$cssfile}\" rel=\"stylesheet\" />\r\n";
        }
        // сборка подключения JS на api.php, в которых используется преобразование переменных.
        preg_match_all('/{\$jsapi (.*?)}/s', $this->content, $jsapi_matches);
        $jsapi_array = $system->nullArrayClean(array_unique($jsapi_matches[1]));
        foreach ($jsapi_array as $jsapi) {
            $compiled_header .= "<script type=\"text/javascript\" src=\"{$constant->url}/{$jsapi}\"></script>\r\n";
        }
        // сборка JS URL включений
        preg_match_all('/{\$jsurl (.*?)}/s', $this->content, $jsurl_matches);
        $jsurl_array = $system->nullArrayClean(array_unique($jsurl_matches[1]));
        foreach ($jsurl_array as $jsurl) {
            $compiled_header .= "<script type=\"text/javascript\" src=\"$jsurl\"></script>\r\n";
        }
        // сборка CSS URL включений
        preg_match_all('/{\$cssurl (.*?)}/s', $this->content, $cssurl_matches);
        $cssurl_array = $system->nullArrayClean($cssurl_matches[1]);
        foreach ($cssurl_array as $cssurl) {
            $compiled_header .= "<link href=\"$cssurl\" rel=\"stylesheet\" />\r\n";
        }
        $compiled_header .= "</head>";
        $this->content = str_replace('</head>', $compiled_header, $this->content);
    }

    /**
     * Установка стандартных шаблоных переменных. Пример: {$url} => http://blabla
     */
    public function setDefaults($theme)
    {
        global $constant, $user;
        if (loader == 'back') {
            $template_path = $constant->tpl_dir . $constant->slash . $constant->admin_tpl;
        } elseif(loader == 'install') {
            $template_path = $constant->tpl_dir . $constant->slash . $constant->install_tpl;
        } else {
            $template_path = $constant->tpl_dir . $constant->slash . $constant->tpl_name;
        }
        return str_replace(array('{$url}', '{$tpl_dir}', '{$user_id}', '{$user_nick}', '{$ffcms_version}'),
            array($constant->url, $template_path, loader == 'install' ? null : $user->get('id'), loader == 'install' ? null : $user->get('nick'), version),
            $theme);
    }


    /**
     * Use function get(tpl_name, customdirectory)
     * @deprecated
     */
    public function tplget($tplname, $customdirectory = null, $isadmin = false)
    {
        global $constant;
        if ($isadmin) {
            $file = $constant->root . $constant->ds . $constant->tpl_dir . $constant->ds . $constant->admin_tpl . $constant->ds . $customdirectory . $tplname . ".tpl";
        } else {
            $file = $constant->root . $constant->ds . $constant->tpl_dir . $constant->ds . $constant->tpl_name . $constant->ds . $customdirectory . $tplname . ".tpl";
        }
        if (file_exists($file)) {
            $this->debug_readcount++;
            return $this->setDefaults(file_get_contents($file));
        }
        return $this->tplException($tplname);
    }

    public function get($tplname, $customdirectory = null)
    {
        global $constant;
        if(loader == 'back') {
            $file = $constant->root . $constant->ds . $constant->tpl_dir . $constant->ds . $constant->admin_tpl . $constant->ds . $customdirectory . $tplname . ".tpl";
        } elseif(loader == 'install') {
            $file = $constant->root . $constant->ds . $constant->tpl_dir . $constant->ds . $constant->install_tpl . $constant->ds . $customdirectory . $tplname . ".tpl";
        } else {
            $file = $constant->root . $constant->ds . $constant->tpl_dir . $constant->ds . $constant->tpl_name . $constant->ds . $customdirectory . $tplname . ".tpl";
        }
        if (file_exists($file)) {
            $this->debug_readcount++;
            return $this->setDefaults(file_get_contents($file));
        }
        return $this->tplException($tplname);
    }

    /**
     * Назначение тегу значения (краткий аналог str_replace)
     * @param unknown_type $tag
     * @param unknown_type $data
     * @param unknown_type $where
     * @return mixed
     */
    public function assign($tag, $data, $where)
    {
        if (is_array($tag)) {
            $copy = array();
            foreach ($tag as $entery) {
                $copy[] = '{$' . $entery . '}';
            }
            return str_replace($copy, $data, $where);
        }
        return str_replace('{$' . $tag . '}', $data, $where);
    }

    /**
     * Выход при отсутствии файлов шаблона
     */
    private function tplException($tpl)
    {
        exit("Template file not founded: " . $tpl);
    }

    /**
     * Возвращает блок уведомлений
     * @param ENUM('error', 'info', 'success') $type
     * @param String $message
     * @param Boolean $isadmin
     * @return mixed
     */
    public function stringNotify($type, $content, $isadmin = false)
    {
        $theme = $this->get("notify_string_{$type}", null);
        return $this->assign('content', $content, $theme);
    }

    /**
     * Ошибка 404 для пользователей
     */
    public function compile404()
    {
        global $cache;
        $cache->setNoExist(true);
        return $this->get('404');
    }

    public function compileBan()
    {
        global $cache;
        $cache->setNoExist(true);
        return $this->get('ban');
    }

    /**
     * USE template::stringNotify()
     * @deprecated
     */
    public function compileNotify($type, $text, $isadmin = false)
    {
        return $this->stringNotify($type, $text, $isadmin);
    }

    /**
     * Отладочная информация о кол-ве считанных шаблонов
     */
    public function getReadCount()
    {
        return $this->debug_readcount;
    }

    /**
     * Очистка всех позиций. Возможна повторная выгрузка другого шаблона.
     */
    public function cleanafterprint()
    {
        unset($this->content);
        unset($this->position);
    }

    public function drowNumericPagination($index, $count, $total, $link)
    {
        $theme_head = $this->get('pagination_head');
        $theme_active = $this->get('pagination_active_item');
        $theme_inactive = $this->get('pagination_inactive_item');
        $theme_spliter = $this->get('pagination_split_item');

        // если все записи вмещены на 1 странице - пагинация не нужна.
        if ($total <= $count) {
            return;
        }

        $compiled_items = null;
        $last_page = (int)$total / $count;
        // если всего планируется более 10 страничек с итемами
        if ($total / $count > 10) {
            // если это начало списка
            if ($index < 5) {
                for ($i = 0; $i <= 8; $i++) {
                    if ($i == $index) {
                        $di = ($i == 0) ? null : $i;
                        $compiled_items .= $this->assign(array('item_link', 'item_name'), array($link . $di, $i + 1), $theme_active);
                    } else {
                        $di = ($i == 0) ? null : $i;
                        $compiled_items .= $this->assign(array('item_link', 'item_name'), array($link . $di, $i + 1), $theme_inactive);
                    }
                }
                $compiled_items .= $theme_spliter;
                $compiled_items .= $this->assign(array('item_link', 'item_name'), array($link . $last_page, $last_page + 1), $theme_inactive);
            } // это конец списка
            elseif ($last_page - $index < 5) {
                $compiled_items .= $this->assign(array('item_link', 'item_name'), array($link . '0', 1), $theme_inactive);
                $compiled_items .= $theme_spliter;
                for ($i = $last_page - 8; $i <= $last_page; $i++) {
                    if ($i == $index) {
                        $compiled_items .= $this->assign(array('item_link', 'item_name'), array($link . $i, $i + 1), $theme_active);
                    } else {
                        $compiled_items .= $this->assign(array('item_link', 'item_name'), array($link . $i, $i + 1), $theme_inactive);
                    }
                }
            } // это середина списка
            else {
                $compiled_items .= $this->assign(array('item_link', 'item_name'), array($link . '0', 1), $theme_inactive);
                $compiled_items .= $theme_spliter;
                for ($i = $index - 3; $i <= $index; $i++) {
                    if ($i == $index) {
                        $compiled_items .= $this->assign(array('item_link', 'item_name'), array($link . $i, $i + 1), $theme_active);
                    } else {
                        $compiled_items .= $this->assign(array('item_link', 'item_name'), array($link . $i, $i + 1), $theme_inactive);
                    }
                }
                for ($i = $index + 1; $i <= $index + 3; $i++) {
                    $compiled_items .= $this->assign(array('item_link', 'item_name'), array($link . $i, $i + 1), $theme_inactive);
                }
                $compiled_items .= $theme_spliter;
                $compiled_items .= $this->assign(array('item_link', 'item_name'), array($link . $last_page, $last_page + 1), $theme_inactive);
            }
        } // иначе все просто, генерируем до предела
        else {
            // от 0 до прогнозируемого кол-ва страниц
            for ($i = 0; $i < $total / $count; $i++) {
                if ($i == $index) {
                    if ($i == 0) $i = null;
                    $compiled_items .= $this->assign(array('item_link', 'item_name'), array($link . $i, $i + 1), $theme_active);
                } else {
                    if ($i == 0) $i = null;
                    $compiled_items .= $this->assign(array('item_link', 'item_name'), array($link . $i, $i + 1), $theme_inactive);
                }
            }
        }
        return $this->assign('li_items', $compiled_items, $theme_head);
    }

    /**
     * Возвращает количество вхождений тега $tag в глобальном шаблоне $content.
     * @param $tag
     */
    public function tagRepeatCount($tag)
    {
        return substr_count($this->content, '{$' . $tag . '}');
    }
}

?>