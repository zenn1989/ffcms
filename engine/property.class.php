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

    protected $cfg = array();

    /**
     * Get configure param
     * @param $param
     * @return string|null|array
     */
    public function get($param) {
        return $this->cfg[$param];
    }

    /**
     * Set configure param
     * @param string $param
     * @param string $value
     */
    public function set($param, $value) {
        $this->cfg[$param] = $value;
    }

    /**
     * @return array
     */
    public function getAll() {
        return $this->cfg;
    }

    public function init() {
        global $config;
        $this->set('ds','/'); // directory separator, but now in all O.S. supported "/" win,nix
        $this->set('slash', '/'); // web slash, mb someone making amazing ;D
        $this->set('admin_tpl', 'admin');
        $this->set('install_tpl', 'install');
        $this->set('collect_statistic', true);
        $this->set('upload_img_max_size', 500);
        $this->set('tpl_dir', 'templates');
        $this->set('user_friendly_url', true);
        $this->set('use_multi_language', true);
        $this->set('maintenance', false);
        // upd
        $this->set('upload_other_max_size', 3000);
        $this->set('upload_allowed_ext', '.doc;.docx;.rtf;.pdf;.txt;');
        if(is_array($config)) {
            foreach($config as $key=>$value) {
                // allow multi-url support
                if($key == 'url') {
                    $this->set('source_url', $value);
                    if(system::getInstance()->contains(';', $value)) { // contains ; spliter in urls
                        $adr_array = system::getInstance()->altexplode(';', $value);
                        $user_address = system::getInstance()->getProtocol() . '://';
                        $user_address .= $_SERVER['HTTP_HOST'];
                        foreach($adr_array as $address) {
                            if(system::getInstance()->prefixEquals($address, $user_address)) {
                                $this->set('url', $address);
                                break;
                            }
                        }
                        if($this->get('url') == null) // if url still null - set first of know
                            $this->set('url', $adr_array[0]);
                    } else
                        $this->set($key, $value);
                } else
                    $this->set($key, $value);
            }
        }
        $this->set('yandex_translate_key', 'trnsl.1.1.20140923T120415Z.11ea02784e7b7447.158c20fac47143a5ccda5fc8a8ca81182669c80f');
    }

}