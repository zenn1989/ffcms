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
        global $template, $system, $constant;
        $template->globalset('keywords', $system->altimplode(", ", $this->keywords));
        $template->globalset('description', $system->altimplode(". ", $this->description));
        if ($constant->seo_meta['multi_title'])
            $template->globalset('title', $system->altimplode(" - ", array_reverse($this->title)));
        else
            $template->globalset('title', array_pop($this->title));
        $template->globalset('generator', $system->altimplode(' ', $this->generator));
    }

}


?>