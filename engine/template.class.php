<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

namespace engine;

class template extends singleton {

    protected static $instance = null;

    protected $twig_file = null;
    protected $twig_string = null;
    protected $variables = array();

    // twig variables type
    const TYPE_CONTENT = 'content';
    const TYPE_LANGUAGE = 'language';
    const TYPE_SYSTEM = 'system';
    const TYPE_USER = 'user';
    const TYPE_MODULE = 'module';
    const TYPE_META = 'meta';

    /**
     * @return template
     */
    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    protected function twigLoader() {
        $twig_cache = root . '/cache/';
        $tpl_name = $this->getIfaceTemplate();
        switch(loader) {
            case 'front':
            case 'api':
                $twig_cache .= user::getInstance()->get('id') < 1 ? 'guest' : 'uid' . user::getInstance()->get('id');
                break;
            case 'back':
                $twig_cache .= 'admintmp';
                break;
            case 'install':
                $twig_cache .= 'installtmp';
                break;
        }
        $template_path_root = root . '/' . property::getInstance()->get('tpl_dir') . '/' . $tpl_name;
        if(!file_exists($template_path_root)) {
            // mb default template is available ?
            if(file_exists(root . '/' . property::getInstance()->get('tpl_dir') . '/default') && in_array(loader, array('front', 'api'))) {
                property::getInstance()->set('tpl_name', 'default');
                $template_path_root = root . '/' . property::getInstance()->get('tpl_dir') . '/default';
            } else
                exit("Template " . $tpl_name . " is not founded! Exit");
            logger::getInstance()->log(logger::LEVEL_ERR, 'Template ' . $tpl_name . ' is not founded. Use default template.');
        }
        require_once(root . "/resource/Twig/Autoloader.php");
        \Twig_Autoloader::register();
        $this->twig_file = new \Twig_Environment(
            new \Twig_Loader_Filesystem($template_path_root),
            array(
                'cache' => $twig_cache,
                'charset' => 'utf-8',
                'autoescape' => false,
                'strict_variables' => false,
            )
        );
        if(loader == 'install' || permission::getInstance()->have('global/owner')) // auto rebuild cache for owner
            $this->twig_file->enableAutoReload();
        $this->twig_string = new \Twig_Environment(new \Twig_Loader_String());
    }

    protected function getIfaceTemplate() {
        $tpl_dir = null;
        switch(loader) {
            case 'front':
            case 'api':
                $tpl_dir = property::getInstance()->get('tpl_name');
                break;
            case 'back':
                $tpl_dir = property::getInstance()->get('admin_tpl');
                break;
            case 'install':
                $tpl_dir = property::getInstance()->get('install_tpl');
                break;
        }
        return $tpl_dir;
    }

    public function init() {
        $this->twigLoader();
        $this->set(self::TYPE_SYSTEM, 'url', property::getInstance()->get('url'));
        $this->set(self::TYPE_SYSTEM, 'protocol', property::getInstance()->get('protocol'));
        $this->set(self::TYPE_SYSTEM, 'script_url', property::getInstance()->get('script_url'));
        $this->set(self::TYPE_SYSTEM, 'nolang_url', property::getInstance()->get('nolang_url'));
        $this->set(self::TYPE_SYSTEM, 'uri', $this->nolang_uri());
        $this->set(self::TYPE_SYSTEM, 'theme', property::getInstance()->get('script_url') . '/' . property::getInstance()->get('tpl_dir') . '/' . $this->getIfaceTemplate());
        $this->set(self::TYPE_SYSTEM, 'lang', language::getInstance()->getUseLanguage());
        $this->set(self::TYPE_SYSTEM, 'languages', language::getInstance()->getAvailable());
        $this->set(self::TYPE_SYSTEM, 'self_url', property::getInstance()->get('script_url').router::getInstance()->getUriString());
        $serial_title = property::getInstance()->get('seo_title');
        $this->set(self::TYPE_SYSTEM, 'title', $serial_title[language::getInstance()->getUseLanguage()]);
        $this->set(self::TYPE_SYSTEM, 'file_name', basename($_SERVER['PHP_SELF']));
        $this->set(self::TYPE_SYSTEM, 'version', version);
        $this->set(self::TYPE_SYSTEM, 'admin_tpl', property::getInstance()->get('script_url') . '/' . property::getInstance()->get('tpl_dir') . '/' . property::getInstance()->get('admin_tpl')); // for script usage
        $this->set(self::TYPE_SYSTEM, 'loader', loader);
        $this->set(self::TYPE_SYSTEM, 'is_main', router::getInstance()->isMain());
        $this->set(self::TYPE_SYSTEM, 'get_data', system::getInstance()->get(null));
        $this->set(self::TYPE_SYSTEM, 'yandex_translate_key', property::getInstance()->get('yandex_translate_key'));
    }

    public function nolang_uri() {
        $uri = system::getInstance()->altexplode('/', router::getInstance()->getUriString());
        if(!property::getInstance()->get('user_friendly_url')) // remove /index.php if non friendy urls
            array_shift($uri);
        if(property::getInstance()->get('use_multi_language')) // remove /ru /en from uri
            array_shift($uri);
        return system::getInstance()->altimplode('/', $uri);
    }

    /**
     * @return \Twig_Environment
     */
    public function twig() {
        return $this->twig_file;
    }

    /**
     * @return \Twig_Environment
     */
    public function twigString() {
        return $this->twig_string;
    }

    /**
     * Add to rendering variable with value. If add is true value not be replaced, added.
     * @param $type ['content', 'language', 'system']
     * @param string $variable
     * @param string|array $value
     * @param bool $add
     */
    public function set($type, $variable, $value, $add = false) {
        if(system::getInstance()->length($variable) < 1 || (!is_array($value) && system::getInstance()->length($value) < 1) || (is_array($value) && $add))
            return;
        $this->variables[$type][$variable] = $add ? $this->variables[$type][$variable] . $value : $value;
    }

    /**
     * Get template variable by type and name
     * @param $type ['content', 'language', 'system']
     * @param string $variable
     * @return string|null
     */
    public function get($type, $variable) {
        return $this->variables[$type][$variable];
    }

    /**
     * Render function for extensions.
     * @param string $tpl
     * @param array $variables
     * @return string
     */
    public function twigRender($tpl, $variables) {
        $renderArray = array_merge($this->variables, $variables);
        return $this->twig()->render($tpl, $renderArray);
    }

    public function make() {
        if($this->get(self::TYPE_CONTENT, 'body') == null) { // set 404 code for browser and search engines
            header("HTTP/1.0 404 Not Found");
        }
        return $this->twig()->render('main.tpl', $this->variables);
    }

    /**
     * Print content without compiling main template.
     * @param $content
     * @param array|null $params
     */
    public function justPrint($content, $params = null) {
        $renderParams = array();
        if(!is_null($params)) {
            $renderParams = array_merge($this->variables, $params);
        } else {
            $renderParams = $this->variables;
        }
        $render = $this->twigString()->render($content, $renderParams);
        exit($render);
    }

    /**
     * Display fast pagination based on input data like index, item count, total count and prepend link
     * @param $index
     * @param $count
     * @param $total
     * @param $link
     * @return null|string
     */
    public function showFastPagination($index, $count, $total, $link)
    {
        if ($total <= $count) {
            return null;
        }
        $compiled_items = null;
        $last_page = (int)(($total-1) / $count); // -1 for int collision, ex: 20/10 = 2 (0..2) or 21/10 ~= 2 (0..2) - cleanup useless pag. items
        $params = array(
            'index' => $index,
            'count' => $count,
            'total' => $total,
            'link' => $link,
            'lastpage' => $last_page
        );
        return $this->twigRender('pagination.tpl', array('local' => $params));
    }

}
