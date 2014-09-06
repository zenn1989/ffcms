<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

namespace engine;

class property extends singleton {
    protected static $cfg = array();
    protected static $instance = null;

    /**
     * @return property
     */
    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new self();
            self::defaultsInit();
        }
        return self::$instance;
    }

    /**
     * @param $param
     * @return string|null|array
     */
    public function get($param) {
        return self::$cfg[$param];
    }

    /**
     * @param string $param
     * @param string $value
     */
    public function set($param, $value) {
        self::$cfg[$param] = $value;
    }

    /**
     * @return array
     */
    public function getAll() {
        return self::$cfg;
    }

    protected static function defaultsInit() {
        global $config;
        self::$cfg['ds'] = '/'; // directory separator, but now in all O.S. supported "/" win,nix
        self::$cfg['slash'] = '/'; // web slash, mb someone making amazing ;D
        self::$cfg['admin_tpl'] = 'admin';
        self::$cfg['install_tpl'] = 'install';
        self::$cfg['collect_statistic'] = true;
        self::$cfg['upload_img_max_size'] = 500;
        self::$cfg['tpl_dir'] = 'templates';
        self::$cfg['user_friendly_url'] = true;
        self::$cfg['use_multi_language'] = true;
        // upd
        self::$cfg['upload_other_max_size'] = 3000;
        self::$cfg['upload_allowed_ext'] = '.doc;.docx;.rtf;.pdf;.txt;';
        if(is_array($config)) {
            foreach($config as $key=>$value) {
                self::$cfg[$key] = $value;
            }
        }
    }

    public function dymanicPrepares() {
        self::$cfg['script_url'] = self::$cfg['url'];
        if(!self::$cfg['user_friendly_url']) {
            self::$cfg['url'] .= '/index.php';
        }
        self::$cfg['nolang_url'] = self::$cfg['url'];
        if(self::$cfg['use_multi_language']) {
            if(loader === 'front')
                self::$cfg['url'] .= '/' . router::getInstance()->getPathLanguage();
            elseif(loader === 'back')
                self::$cfg['url'] .= '/' . property::getInstance()->get('lang');
        }
    }

}