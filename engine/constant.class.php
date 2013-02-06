<?php

/**
 * Стандартный класс по управлению константами и переменными
 * обозначеными при загрузке той или иной страницы
 */
class constant
{
    public $root = null;
    public $tpl_dir = null;
	public $tpl_name = null;
    public $url = null;
    
    function __construct() 
    {
        global $config;
		$this->root = root;
		$this->tpl_dir = $config['tpl_dir'];
		$this->tpl_name = $config['tpl_name'];
		$this->url = $config['url'];
        
    }
    
}
?>