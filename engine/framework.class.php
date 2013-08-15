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
    private $system = null;

    public function framework()
    {
        global $system;
        $this->system = $system;
    }

    public function set($object)
    {
        $this->object = $object;
        return $this;
    }

    public function fromPost($post_key_name)
    {
        global $system;
        $this->object = $system->post($post_key_name);
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
        global $system;
        $this->object = $system->nohtml($this->object);
        return $this;
    }

    public function altexplode($decimal)
    {
        global $system;
        $this->object = $system->altexplode($decimal, $this->object);
        return $this;
    }

    public function altsubstr($start, $end)
    {
        global $system;
        $this->object = $system->altsubstr($this->object, $start, $end);
        return $this;
    }

    public function length()
    {
        global $system;
        return $system->length($this->object);
    }

    public function toInt()
    {
        global $system;
        $this->object = $system->toInt($this->object);
        return $this;
    }


}