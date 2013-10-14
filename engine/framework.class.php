<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

/**
 * Надстройка для динамического использования класса system
 * Class framework
 */
class framework
{
    private $object = null;

    public function set($object)
    {
        $this->object = $object;
        return $this;
    }

    public function fromPost($post_key_name)
    {
        global $engine;
        $this->object = $engine->system->post($post_key_name);
        return $this;
    }

    public function get()
    {
        $result = $this->object;
        $this->object = null;
        return $result;
    }

    public function nohtml()
    {
        global $engine;
        $this->object = $engine->system->nohtml($this->object);
        return $this;
    }

    public function altexplode($decimal)
    {
        global $engine;
        $this->object = $engine->system->altexplode($decimal, $this->object);
        return $this;
    }

    public function altsubstr($start, $end)
    {
        global $engine;
        $this->object = $engine->system->altsubstr($this->object, $start, $end);
        return $this;
    }

    public function length()
    {
        global $engine;
        return $engine->system->length($this->object);
    }

    public function toInt()
    {
        global $engine;
        $this->object = $engine->system->toInt($this->object);
        return $this;
    }


}