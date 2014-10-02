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
                // allow multi-url support
                if($key == 'url') {
                    self::$cfg['source_url'] = $value;
                    if(system::getInstance()->contains(';', $value)) { // contains ; spliter in urls
                        $adr_array = system::getInstance()->altexplode(';', $value);
                        $user_address = system::getInstance()->getProtocol() . '://';
                        $user_address .= $_SERVER['HTTP_HOST'];
                        foreach($adr_array as $address) {
                            if(system::getInstance()->prefixEquals($address, $user_address)) {
                                self::$cfg['url'] = $address;
                                break;
                            }
                        }
                        if(self::$cfg['url'] == null) // if url still null - set first of know
                            self::$cfg['url'] = $adr_array[0];
                    } else
                        self::$cfg[$key] = $value;
                } else
                    self::$cfg[$key] = $value;
            }
        }
        self::$cfg['yandex_translate_key'] = 'trnsl.1.1.20140923T120415Z.11ea02784e7b7447.158c20fac47143a5ccda5fc8a8ca81182669c80f';
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
        self::$cfg['protocol'] = system::getInstance()->getProtocol();
    }

}