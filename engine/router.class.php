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

    protected $patharray = array();
    protected $pathstring = null;
    protected $ismain = false;

    protected $path_language = null;

    /**
     * Init router worker
     */
    public function init() {
        $this->prepareURI();
        $this->preparePropertys();
        $this->prepareLanguages();
    }

    /**
     * Prepare URI path as string and array
     * */
    private function prepareURI() {
        $raw_uri = urldecode($_SERVER['REQUEST_URI']);
        if($get_pos = strpos($raw_uri, '?'))
            $raw_uri = substr($raw_uri, 0, $get_pos);
        $this->pathstring = $raw_uri;
        $this->patharray = explode("/", $raw_uri);
        // remove 1st null element
        array_shift($this->patharray);
        // no user-friendy url ? remove index.php from path
        if(!property::getInstance()->get('user_friendly_url')) {
            array_shift($this->patharray);
            $this->pathstring = substr($this->pathstring, 10); // remove /index.php from path string
        }
        if(property::getInstance()->get('use_multi_language')) { // remove /lang/ from path and notify language of this action
            $this->path_language = array_shift($this->patharray);
            if(!language::getInstance()->canUse($this->path_language) && loader == 'front') { // language is not founded?
                $redirect_to = null;
                if(property::getInstance()->get('user_friendly_url'))
                    $redirect_to .= '/';
                else
                    $redirect_to .= '/index.php/';

                $redirect_to .= property::getInstance()->get('lang') . '/';
                system::getInstance()->redirect($redirect_to);
            } elseif($_COOKIE['ffcms_lang'] !== $this->path_language) // set language for api and ajax scripts
                system::getInstance()->setCookie('ffcms_lang', $this->path_language);
        }
        // its a main?
        if($this->patharray[0] == null || system::getInstance()->prefixEquals($this->patharray[0], 'index.'))
            $this->ismain = true;
    }

    /**
     * Overload default system property's: set urls like script_url, full_url, nolang_url depend of routing changes
     */
    private function preparePropertys() {
        property::getInstance()->set('script_url', property::getInstance()->get('url'));
        if(!property::getInstance()->get('user_friendly_url')) {
            property::getInstance()->set('url', property::getInstance()->get('url') . '/index.php');
        }
        property::getInstance()->set('nolang_url', property::getInstance()->get('url'));
        if(property::getInstance()->get('use_multi_language')) {
            if(loader === 'front')
                property::getInstance()->set('url', property::getInstance()->get('url') . '/' . $this->getPathLanguage());
            elseif(loader === 'back')
                property::getInstance()->set('url', property::getInstance()->get('url') . '/' . property::getInstance()->get('lang'));
        }
        property::getInstance()->set('protocol', system::getInstance()->getProtocol());
    }

    /**
     * Prepare language info from input data.
     */
    private function prepareLanguages() {
        $lang = null;
        if(loader === 'front' && router::getInstance()->getPathLanguage() != null && language::getInstance()->canUse($this->getPathLanguage())) // did we have language in path for front iface?
            $lang = router::getInstance()->getPathLanguage();
        elseif((loader === 'api' || loader === 'install') && language::getInstance()->canUse($_COOKIE['ffcms_lang'])) // did language defined for API scripts?
            $lang = $_COOKIE['ffcms_lang'];
        elseif($_SERVER['HTTP_ACCEPT_LANGUAGE'] != null && language::getInstance()->canUse(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2)) && loader !== 'back') // did we have lang mark in browser?
            $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        else // no ? then use default language
            $lang = property::getInstance()->get('lang');
        language::getInstance()->setUseLanguage($lang);
    }

    /**
     * Get split'd by "/" pathway array of current website page
     * @return array
     */
    public function getUriArray() {
        return $this->patharray;
    }

    /**
     * Get split'd by "/" pathway array without first element
     * @return array
     */
    public function shiftUriArray() {
        $path = $this->patharray;
        array_shift($path);
        return $path;
    }

    /**
     * Get current URI of user request
     * @return string
     */
    public function getUriString() {
        return $this->pathstring;
    }

    /**
     * Init current component or model and prepare data to display
     */
    public function makeRoute() {
        $way = $this->getUriArray();
        // its can be a main blank page ?
        if(extension::getInstance()->foundRoute($way[0])) { // its a component?
            $object = extension::getInstance()->call(extension::TYPE_COMPONENT, $way[0]);
            if(is_object($object) && method_exists($object, 'make'))
                $object->make();
        }
    }

    /**
     * Get language obtained from URI pathway
     * @return string|null
     */
    public function getPathLanguage() {
        return $this->path_language;
    }

    /**
     * Check this page is main?
     * @return bool
     */
    public function isMain() {
        return $this->ismain;
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
                if ($rule_split[$i] == $this->patharray[$i]) {
                    // if its last level of pathway
                    if (system::getInstance()->contains('.html', $this->patharray[$i])) {
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
            $array_object[] = $this->patharray[0];
            // next way from add
            foreach ($additional as $values) {
                $array_object[] = $values;
            }
        } else {
            $array_object = $this->patharray;
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
