<?php

// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

/**
 * Класс кеширования страниц
 */
class cache
{

    private $noexist = false;
    private $static_always_loaded = false;

    public function check($site_down = false)
    {
        global $engine;
        if ($engine->constant->debug_no_cache) {
            return false;
        }
        $way = $engine->page->getPathway();
        // анализируем базовые нулевые пачвеи на список игнора кеша
        if (in_array($way[0], $engine->page->getNoCache())) {
            return false;
        }
        // получаем путь к файлу кеша
        $file = $this->pathHash();
        if (file_exists($file)) {
            $filetime = filemtime($file);
            // если файл пролежал меньше, чем указано в конфиге или база данных недоступна в текущий момент
            if ((time() - $filetime) < $engine->constant->cache_interval || $site_down) {
                $this->static_always_loaded = true;
                return true;
            }
        }
        return false;
    }

    /**
     * Получение кеша. Обязательна проверка ДО использования данного параметра!
     */
    public function get()
    {
        // обязательна проверка cache::check до того, как будет выполнена функция get()!!!!
        return file_get_contents($this->pathHash());
    }

    /**
     * Сохранение страницы в кеш
     */

    public function save($data)
    {
        global $engine;
        // не сохраняем для авторизованных пользователей
        if ($engine->user->get('id') != NULL) {
            return;
        }
        // Не сохраняем 404 ошибки как кеш
        if ($this->noexist) {
            return;
        }
        $way = $engine->page->getPathway();
        // если кеш запрещен - не сохраняем
        if (in_array($way[0], $engine->page->getNoCache())) {
            return;
        }
        $place = $this->pathHash();
        file_put_contents($place, $data, LOCK_EX);
    }

    /**
     * Хеш-функция, указывает на полный адрес файла в папке cache
     * Возможны коллизии, если $engine->page->getStrPathway() > 32 символов, однако
     * данные колизии не критичны в данном случае.
     */
    private function pathHash()
    {
        global $engine;
        return $engine->constant->root . "/cache/" . $engine->language->getCustom() . "_" . md5($engine->page->getStrPathway());
    }

    public function setNoExist($boolean)
    {
        $this->noexist = $boolean;
    }

    public function used()
    {
        return $this->static_always_loaded;
    }

    /**
     * Сохранение блочного объекта в кеш
     * @param $name
     * @param $data
     */
    public function saveBlock($name, $data)
    {
        global $engine;
        if(!file_exists($engine->constant->root . "/cache/block/")) {
            mkdir($engine->constant->root . "/cache/block/");
        }
        $fname = $engine->constant->root . "/cache/block/" . $name . ".cache";
        @file_put_contents($fname, $data, LOCK_EX);
    }

    /**
     * Получение блочного объекта из кеша
     * @param $name
     * @param int $time
     * @return null|string
     */
    public function getBlock($name, $time = 120)
    {
        global $engine;
        $fname = $engine->constant->root . "/cache/block/" . $name . ".cache";
        if(!file_exists($fname))
            return null;
        $ftime = filemtime($fname);
        if(time() - $ftime < $time)
            return file_get_contents($fname);
        return null;
    }


}

?>