<?php

/**
 * Стандартный класс по управлению константами и переменными
 * обозначеными при загрузке той или иной страницы
 */
class constant
{
	public $ds = DIRECTORY_SEPARATOR;
    public $slash = "/";
	public $root = null;
	public $tpl_dir = null;
	public $tpl_name = null;
	public $url = null;

	public $db = array();

	public $cache_interval = 60;
	public $debug_no_cache = true;
	
	public $seo_meta = array();

	// время жизни токена авторизации, дефолт = 1 сутки
	public $token_time = 86400;

	public $mail = array();

	public $admin_tpl = 'admin';

	// язык
	public $lang = 'ru';
	// временная зона
	public $time_zone = "Europe/Kiev";

	// максимальный размер изображения (kb)
	public $upload_img_max_size = 500;

	public $do_compress_html = true;
	
	public $password_salt = "TjE3#81A@j9^am1@";

	function __construct()
	{
		global $config;
		$this->root = root;
		$this->tpl_dir = $config['tpl_dir'];
		$this->tpl_name = $config['tpl_name'];
		$this->url = $config['url'];

		$this->db['host'] = $config['db_host'];
		$this->db['user'] = $config['db_user'];
		$this->db['pass'] = $config['db_pass'];
		$this->db['db'] = $config['db_name'];
		$this->db['prefix'] = $config['db_prefix'];
		
		$this->seo_meta['title'] = $config['seo_title'];
		$this->seo_meta['keywords'] = $config['seo_keywords'];
		$this->seo_meta['description'] = $config['seo_description'];
        $this->seo_meta['multi_title'] = $config['multi_title'];

		$this->cache_interval = $config['cache_interval'];
		$this->token_time = $config['token_time'];

		$this->mail['from_email'] = $config['mail_from'];
		$this->mail['ownername'] = $config['mail_ownername'];
		$this->mail['smtp_enabled'] = $config['mail_smtp_use'];
		$this->mail['smtp_host'] = $config['mail_smtp_host'];
		$this->mail['smtp_auth'] = $config['mail_smtp_auth'];
		$this->mail['smtp_port'] = $config['mail_smtp_port'];
		$this->mail['smtp_user'] = $config['mail_smtp_login'];
		$this->mail['smtp_password'] = $config['mail_smtp_password'];
	}


}
?>