<?php
namespace engine;

class template extends singleton {

    protected static $twig_file = null;
    protected static $twig_string = null;
    protected static $instance = null;
    protected static $variables = array();

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
        if(is_null(self::$instance)) {
            self::$instance = new self();
            self::twigLoader();
        }
        return self::$instance;
    }

    protected static function twigLoader() {
        $twig_cache = root . '/cache/';
        $tpl_name = self::getIfaceTemplate();
        switch(loader) {
            case 'front':
            case 'api':
                $twig_cache .= user::getInstance()->get('id') < 1 ? 'guest' : 'uid'.user::getInstance()->get('id');
                break;
            case 'back':
                $twig_cache .= 'admintmp';
                break;
            case 'install':
                $twig_cache .= 'installtmp';
                break;
        }
        require_once(root . "/resource/Twig/Autoloader.php");
        \Twig_Autoloader::register();
        self::$twig_file = new \Twig_Environment(
            new \Twig_Loader_Filesystem(root . '/' . property::getInstance()->get('tpl_dir') . '/' . $tpl_name),
            array(
                'cache' => $twig_cache,
                'auto_reload' => true,
                'charset' => 'utf-8',
                'autoescape' => false
            )
        );
        self::$twig_string = new \Twig_Environment(new \Twig_Loader_String());
    }

    protected static function getIfaceTemplate() {
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

    public function twigDefaultVariables() {
        self::$variables[self::TYPE_SYSTEM]['url'] = property::getInstance()->get('url');
        self::$variables[self::TYPE_SYSTEM]['script_url'] = property::getInstance()->get('script_url');
        self::$variables[self::TYPE_SYSTEM]['nolang_url'] = property::getInstance()->get('nolang_url');
        self::$variables[self::TYPE_SYSTEM]['theme'] = property::getInstance()->get('script_url') . '/' . property::getInstance()->get('tpl_dir') . '/' . self::getIfaceTemplate();
        self::$variables[self::TYPE_SYSTEM]['lang'] = language::getInstance()->getUseLanguage();
        self::$variables[self::TYPE_SYSTEM]['languages'] = language::getInstance()->getAvailable();
        self::$variables[self::TYPE_SYSTEM]['self_url'] = property::getInstance()->get('url').router::getInstance()->getUriString();
        self::$variables[self::TYPE_SYSTEM]['title'] = property::getInstance()->get('seo_title');
        self::$variables[self::TYPE_SYSTEM]['file_name'] = basename($_SERVER['PHP_SELF']);
        self::$variables[self::TYPE_SYSTEM]['version'] = version;
    }

    public function twigUserVariables() {
        self::$variables[self::TYPE_USER]['id'] = user::getInstance()->get('id');
        self::$variables[self::TYPE_USER]['name'] = user::getInstance()->get('nick');
        self::$variables[self::TYPE_USER]['admin'] = permission::getInstance()->have('global/owner');
    }

    /**
     * @return \Twig_Environment
     */
    public function twig() {
        return self::$twig_file;
    }

    /**
     * @return \Twig_Environment
     */
    public function twigString() {
        return self::$twig_string;
    }

    /**
     * Add to rendering variable with value. If add is true value not be replaced, added.
     * @param $type ['content', 'language', 'system']
     * @param $variable
     * @param $value
     * @param bool $add
     */
    public function set($type, $variable, $value, $add = false) {
        if(is_array($variable)) {
            foreach($variable as $single_var) {
                if($add || is_null(self::$variables[$type][$single_var])) {
                    if($add)
                        self::$variables[$type][$single_var] .= $value[$single_var];
                    else
                        self::$variables[$type][$single_var] = $value[$single_var];
                }
            }
        } else {
            if($add || is_null(self::$variables[$type][$variable])) {
                if($add)
                    self::$variables[$type][$variable] .= $value;
                else
                    self::$variables[$type][$variable] = $value;
            }
        }
    }

    /**
     * Get template variable by type and name
     * @param $type
     * @param $variable
     * @return string
     */
    public function get($type, $variable) {
        return self::$variables[$type][$variable];
    }

    /**
     * Render function for extensions.
     * @param $tpl
     * @param $variables array
     * @return string
     */
    public function twigRender($tpl, $variables) {
        $renderArray = array_merge(self::$variables, $variables);
        return $this->twig()->render($tpl, $renderArray);
    }

    public function make() {
        if($this->get(self::TYPE_CONTENT, 'body') == null) { // set 404 code for browser and search engines
            header("HTTP/1.0 404 Not Found");
        }
        return $this->twig()->render('main.tpl', self::$variables);
    }

    /**
     * Print content without compiling main template.
     * @param $content
     */
    public function justPrint($content) {
        $render = $this->twigString()->render($content, self::$variables);
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
