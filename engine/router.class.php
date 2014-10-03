<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

namespace engine;

class router extends singleton {

    protected static $patharray = array();
    protected static $pathstring = null;
    protected static $instance = null;

    protected static $ismain = false;

    protected static $path_language = null;

    /**
     * @return router
     */
    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function prepareRoutes() {
        $raw_uri = urldecode($_SERVER['REQUEST_URI']);
        if($get_pos = strpos($raw_uri, '?'))
            $raw_uri = substr($raw_uri, 0, $get_pos);
        self::$pathstring = $raw_uri;
        self::$patharray = explode("/", self::$pathstring);
        // remove 1st null element
        array_shift(self::$patharray);
        // no user-friendy url ? remove index.php from path
        if(!property::getInstance()->get('user_friendly_url')) {
            array_shift(self::$patharray);
            self::$pathstring = substr(self::$pathstring, 10); // remove /index.php from path string
        }
        if(property::getInstance()->get('use_multi_language')) { // remove /lang/ from path and notify language of this action
            self::$path_language = array_shift(self::$patharray);
            if(!language::getInstance()->canUse(self::$path_language)) { // language is not founded?
                $redirect_to = null;
                if(property::getInstance()->get('user_friendly_url'))
                    $redirect_to .= '/';
                else
                    $redirect_to .= '/index.php/';

                $redirect_to .= property::getInstance()->get('lang') . '/';
                system::getInstance()->redirect($redirect_to);
            } elseif($_COOKIE['ffcms_lang'] !== self::$path_language) // set language for api and ajax scripts
                setcookie('ffcms_lang', self::$path_language, null, '/', null, null, true);

        }
        // its a main?
        if(self::$patharray[0] == null || system::getInstance()->prefixEquals(self::$patharray[0], 'index.'))
            self::$ismain = true;
    }

    /**
     * Get split'd by "/" pathway array of current website page
     * @return array
     */
    public function getUriArray() {
        return self::$patharray;
    }

    /**
     * Get split'd by "/" pathway array without first element
     * @return array
     */
    public function shiftUriArray() {
        $path = self::$patharray;
        array_shift($path);
        return $path;
    }

    /**
     * Get current URI of user request
     * @return string
     */
    public function getUriString() {
        return self::$pathstring;
    }

    /**
     * Init current component or model and prepare data to display
     */
    public function makeRoute() {
        $way = self::getUriArray();
        // its can be a main blank page ?
        if(extension::getInstance()->foundRoute($way[0])) { // its a component?
            $object = extension::getInstance()->call(extension::TYPE_COMPONENT, $way[0]);
            if(is_object($object) && method_exists($object, 'make'))
                $object->make();
        }
    }

    /**
     * Get language obtained from URI pathway
     * @return null
     */
    public function getPathLanguage() {
        return self::$path_language;
    }

    /**
     * Check this page is main?
     * @return bool
     */
    public function isMain() {
        return self::$ismain;
    }

    /**
     * Check the rule is agree with current pathway
     * @param string $rule
     * @return bool
     */
    public function isRightWayRule($rule) {
        $rule_split = explode("/", $rule);
        for ($i = 0; $i <= sizeof($rule_split); $i++) {
            // if current level contains * we return the trust
            if ($rule_split[$i] == "*") {
                return true;
            } else {
                // maybe its a mainpage?
                if ($rule_split[$i] == "index" && $this->isMain()) {
                    return true;
                }
                // if pathway equals rule on current level
                if ($rule_split[$i] == self::$patharray[$i]) {
                    // if its last level of pathway
                    if (system::getInstance()->contains('.html', self::$patharray[$i])) {
                        return true;
                    }
                    // if not - continue cycle
                } else {
                    // if not equals - return false
                    return false;
                }
            }
        }
        // if equals not founded return false
        return false;
    }

    /**
     * Create fast hash from current URI without 1st element usage.
     * Can also create from $additional array way without usage current path
     * @param null $additional
     * @return null|string
     */
    public function hashUri($additional = null)
    {
        $array_object = array();
        if ($additional != null) {
            // nil element
            $array_object[] = self::$patharray[0];
            // next way from add
            foreach ($additional as $values) {
                $array_object[] = $values;
            }
        } else {
            $array_object = self::$patharray;
        }
        $string = null;
        for ($i = 1; $i <= sizeof($array_object); $i++) {
            if (system::getInstance()->suffixEquals($array_object[$i], '.html')) {
                $string .= $array_object[$i];
                continue;
            } elseif ($array_object[$i] != null) {
                $string .= $array_object[$i] . "/";
            }
        }
        return $string != null ? md5($string) : null;
    }
}
