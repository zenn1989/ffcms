<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

namespace engine;

class csrf extends singleton {
    protected static $instance = null;

    const SESSION_TIME = 600; // session lifetime 10min

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Check current form usage is safe for CSRF attack. Form must have <input type="hidden" name="csrf_token" value="{{system.csrf_token}}" />
     * @return bool
     */
    public function check() {
        $p_token = null;
        $c_token = $_SESSION['csrf_token']['data'];
        $referer = $_SERVER['HTTP_REFERER'];
        // raw prevent - analys referer header
        if($referer != null && system::getInstance()->length($referer) > 0) {
            if(!system::getInstance()->prefixEquals($referer, property::getInstance()->get('script_url')))
                return false;
        }
        if(system::getInstance()->length(system::getInstance()->post('csrf_token')) >= 32 && system::getInstance()->length(system::getInstance()->post('csrf_token')) <= 128) {
            $p_token = system::getInstance()->post('csrf_token');
        } elseif(system::getInstance()->length(system::getInstance()->get('csrf_token')) >= 32 && system::getInstance()->length(system::getInstance()->get('csrf_token')) <= 128) {
            $p_token = system::getInstance()->get('csrf_token');
        }
        if($p_token == null)
            return false;
        if($c_token == null)
            return false;
        if($c_token != $p_token)
            return false;
        return true;
    }

    /**
     * Get token for csrf prevention. Token is 32...128 chars. Token automatic add in cookie as 'csrf_token' and in template as {{ system.csrf_token }}
     * @return string
     */
    public function buildToken() {
        $now = time();
        if(!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token']['time'] == null || $_SESSION['csrf_token']['data'] == null
            || $now - $_SESSION['csrf_token']['time'] > self::SESSION_TIME) {
            $_SESSION['csrf_token'] = array(
                'time' => $now,
                'data' => system::getInstance()->randomSecureString128()
            );
        }
        template::getInstance()->set(template::TYPE_SYSTEM, 'csrf_token', $_SESSION['csrf_token']['data']);
    }
}