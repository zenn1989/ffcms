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


}