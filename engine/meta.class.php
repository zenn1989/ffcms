<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

/**
 *
 * @author zenn
 * Класс управляющий мета-данными CMS (title,description,keywords)
 */
class meta
{
    private $title = array();
    private $description = array();
    private $keywords = array();
    private $generator = array();

    /**
     * Назначение содержимого мета-тега по имени тега
     * @param String $data - присваиваемое содержимое
     * @param ENUM $metaname - имя мета-тега
     */
    public function set($metaname, $data)
    {
        $this->{$metaname} = null;
        $this->{$metaname}[] = $data;
    }

    /**
     * Получение мета-тега по имени
     * @param ENUM $metaname - имя мета-тега
     */
    public function get($metaname)
    {
        return $this->{$metaname};
    }

    /**
     * Добавление к мета-тегу содержимого по имени
     * @param String $data
     * @param ENUM $metaname
     */
    public function add($metaname, $data)
    {
        $this->{$metaname}[] = $data;
    }

    /**
     * Сбор мета-тегов и выставление их в суперпозицию
     */
    public function compile()
    {
        global $engine;
        $engine->template->globalset('keywords', $engine->system->altimplode(", ", $this->keywords));
        $engine->template->globalset('description', $engine->system->altimplode(". ", $this->description));
        if ($engine->constant->seo_meta['multi_title'])
            $engine->template->globalset('title', $engine->system->altimplode(" - ", array_reverse($this->title)));
        else
            $engine->template->globalset('title', array_pop($this->title));
        $engine->template->globalset('generator', $engine->system->altimplode(' ', $this->generator));
    }

}


?>