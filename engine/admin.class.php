<?php
/**
 |==========================================================|
 |========= @copyright Pyatinskii Mihail, 2013-2014 ========|
 |================= @website: www.ffcms.ru =================|
 |========= @license: GNU GPL V3, file: license.txt ========|
 |==========================================================|
 */

namespace engine;

class admin extends singleton {
    protected $get = array();
    protected $extension_link = array();
    const update_url = 'http://update.ffcms.ru/requestversion.php';

    protected static $default_admin_sections = array(
        'admin/main', 'admin/settings', 'admin/filemanager', 'admin/antivirus', 'admin/dump', 'admin/modules', 'admin/components', 'admin/hooks', 'admin/cleancache',
        'admin/cleanstats', 'admin/cleanlogs',
        'admin/imagebrowser', 'admin/flashbrowser', 'admin/filebrowser'
    );

    public function make() {
        $this->get = array(
            'object' => (string)system::getInstance()->get('object'),
            'action' => (string)system::getInstance()->get('action')
        );
        $access_suspend = false;
        if(!permission::getInstance()->have('global/owner')) { // if not a global admin
            if(!permission::getInstance()->haveAdmin($this->get['object'], $this->get['action'], (string)system::getInstance()->get('make'))) { // if dosnt have access to this part
                if(permission::getInstance()->have('admin/main')) { // user have access to admin main iface
                    $access_suspend = true;
                } else {
                    logger::getInstance()->log(logger::LEVEL_NOTIFY, 'Try to enter in admin without access from ip: '.system::getInstance()->getRealIp());
                    system::getInstance()->redirect();
                }
            }
        }
        language::getInstance()->setUseLanguage(property::getInstance()->get('lang'));
        template::getInstance()->set(template::TYPE_CONTENT, 'modmenu', $this->viewExtensionMenu());
        template::getInstance()->set(template::TYPE_CONTENT, 'head', $this->viewHeadElements());
        if($access_suspend)
            template::getInstance()->set(template::TYPE_CONTENT, 'body', template::getInstance()->twigRender('access_alert.tpl', array()));
        else
            template::getInstance()->set(template::TYPE_CONTENT, 'body', $this->loadAdminBody());
        return template::getInstance()->make();
    }

    private function loadAdminBody() {
        $content = null;
        switch($this->get['object']) {
            case 'settings':
                $content = $this->viewSettings();
                break;
            case 'filemanager':
                $content = $this->viewFileManager();
                break;
            case 'antivirus':
                $content = $this->viewAntiVirus();
                break;
            case 'dump':
                $content = $this->viewDumper();
                break;
            case 'updates':
                $content = $this->viewUpdates();
                break;
            case 'modules':
            case 'components':
            case 'hooks':
                $content = $this->viewExtension();
                break;
            case 'cleancache':
                $content = $this->viewCleanCache();
                break;
            case 'cleanstats':
                $content = $this->viewCleanStatistic();
                break;
            case 'cleanlogs':
                $content = $this->viewCleanLogs();
                break;
            default:
                $content = $this->loadMainpage();
                break;
        }
        return $content;
    }

    private function viewCleanLogs() {
        $params = array();

        if(system::getInstance()->post('submit')) {
            system::getInstance()->removeDirectory(root . '/log/');
            system::getInstance()->createPrivateDirectory(root . '/log/');
            $params['notify']['log_clear'] = true;
        }

        return template::getInstance()->twigRender('cleanlogs.tpl', $params);
    }

    private function viewCleanCache() {
        $params = array();

        if(system::getInstance()->post('submit')) {
            system::getInstance()->removeDirectory(root . '/cache/');
            system::getInstance()->createPrivateDirectory(root . '/cache/');
            $params['notify']['cache_clear'] = true;
        }

        $params['local']['cache_size'] = system::getInstance()->getDirSize(root . '/cache/');

        return template::getInstance()->twigRender('cleancache.tpl', $params);
    }

    private function viewCleanStatistic() {
        $params = array();

        $depricated_time = time() - (7*86400);

        if(system::getInstance()->post('submit')) {
            $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_statistic WHERE `time` < ?");
            $stmt->bindParam(1, $depricated_time, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            $params['notify']['stat_clear'] = true;
        }

        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_statistic WHERE `time` < ?");
        $stmt->bindParam(1, $depricated_time, \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();
        $depr_count = $res[0];

        $params['local']['stats_row'] = $depr_count;

        return template::getInstance()->twigRender('cleanstat.tpl', $params);
    }

    private function viewExtension() {
        // extension table
        if($this->get['action'] == null) {
            $params = array();
            $params['type'] = $this->get['object'];
            $ext_params = extension::getInstance()->getAllParams();
            foreach($ext_params as $type=>$data) {
                if($type == $this->get['object']) {
                    foreach($data as $cdir=>$cdata) {
                        $params['extension'][$cdir] = array(
                            'title' => language::getInstance()->get('admin_'.$type.'_'.$cdata['dir'].'.name') ?: $cdir,
                            'desc' => language::getInstance()->get('admin_'.$type.'_'.$cdata['dir'].'.desc') ?: $cdir,
                            'enabled' => $cdata['enabled'],
                            'type' => $cdata['type'],
                            'way' => $cdata['type'] == extension::TYPE_COMPONENT ? $cdir : null,
                            'path_choice' => $cdata['path_choice'],
                            'path_allow' => $cdata['path_allow'],
                            'path_deny' => $cdata['path_deny']
                        );
                    }
                }
            }
            $params['page']['title'] = language::getInstance()->get('admin_'.$this->get['object'].'_title');
            // not installed extensions scan
            $noinstall = scandir(root . '/extensions/'. $this->get['object'] . '/');
            foreach($noinstall as $item) {
                if(is_dir(root . '/extensions/'. $this->get['object'] . '/' . $item) && !system::getInstance()->prefixEquals($item, '.')
                && !array_key_exists($item, $params['extension'])) {
                    $params['noinstall'][] = $item;
                }
            }
            return template::getInstance()->twigRender('extensions.tpl', $params);
        } else { // single item
            if(in_array(system::getInstance()->get('sys'), array('enable', 'disable', 'install'))) {
                switch(system::getInstance()->get('sys')) {
                    case 'enable':
                        $this->enableExtension();
                        break;
                    case 'disable':
                        $this->disableExtension();
                        break;
                    case 'install':
                        $this->installExtension();
                        break;
                }
                system::getInstance()->redirect('?object='.$this->get['object']);
            }
            $ext_params = extension::getInstance()->getAllParams();
            if(array_key_exists($this->get['action'], $ext_params[$this->get['object']])) {
                if($ext_params[$this->get['object']][$this->get['action']] != null) {
                    $object = extension::getInstance()->call($this->get['object'], $this->get['action'], true);
                    if(!is_null($object) && is_object($object)) {
                        if(system::getInstance()->get('sys') == 'info') {
                            return $this->infoExtension($object, $ext_params[$this->get['object']][$this->get['action']]);
                        }
                        if(system::getInstance()->get('sys') == 'pathwork' && $this->get['object'] == 'modules') {
                            return $this->viewModulePathwork($this->get['action'], $ext_params[$this->get['object']][$this->get['action']]);
                        }
                        if(method_exists($object, '_compatable')) {
                            $script_compatable = $object->_compatable();
                            $cname = get_class($object);
                            if($script_compatable != version)
                                logger::getInstance()->log(logger::LEVEL_ERR, "Uncompatable extension class ".$cname.". System: ".version.", extension: ".$script_compatable);
                        }
                        if(method_exists($object, '_version')) {
                            $script_version = $object->_version();
                            $cname = get_class($object);
                            if($script_version != $ext_params[$this->get['object']][$this->get['action']]['version'])
                                logger::getInstance()->log(logger::LEVEL_WARN, "Extension class ".$cname." have new updates!");
                        }
                        if(method_exists($object, 'make'))
                            return @$object->make();
                    } else {
                        logger::getInstance()->log(logger::LEVEL_WARN, 'Extension '.$this->get['object'].' with type '.$this->get['type'].' is not founded');
                    }
                }
            }
            return template::getInstance()->twigRender('miss_settings.tpl', array());
        }
    }

    private function viewModulePathwork($module, $global_param) {
        csrf::getInstance()->buildToken();
        $params = array();

        if(system::getInstance()->post('submit') && csrf::getInstance()->check()) {
            $rule_path_choice = (int)system::getInstance()->post('mod_rule_type');
            if($rule_path_choice > 1) // only 1 and 0
                $rule_path_choice = 0;
            $rule_data = system::getInstance()->post('mod_rule_value');
            $stmt = null;
            if($rule_path_choice == 1) {
                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_extensions SET `path_allow` = ?, `path_choice` = ? WHERE `dir` = ? AND `type` = ?");
            } else {
                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_extensions SET `path_deny` = ?, `path_choice` = ? WHERE `dir` = ? AND `type` = ?");
            }
            $stmt->bindParam(1, $rule_data, \PDO::PARAM_STR);
            $stmt->bindParam(2, $rule_path_choice, \PDO::PARAM_INT);
            $stmt->bindParam(3, $this->get['action'], \PDO::PARAM_STR);
            $stmt->bindParam(4, $this->get['object'], \PDO::PARAM_STR);
            $stmt->execute();
            $stmt = null;
            $params['local']['saved'] = true;
            $global_param = extension::getInstance()->overloadExtension($this->get['object'], $this->get['action'], true);
        }

        $params['local']['mod_name'] = $module;
        $params['local']['path_choice'] = $global_param['path_choice'];
        $params['local']['path_allow'] = $global_param['path_allow'];
        $params['local']['path_deny'] = $global_param['path_deny'];

        return template::getInstance()->twigRender('extensionmodpath.tpl', $params);
    }

    private function infoExtension($object, $db_data) {
        $params = array();

        if(method_exists($object, '_version'))
            $params['extinfo']['script_version'] = $object->_version();

        if(method_exists($object, '_compatable'))
            $params['extinfo']['script_compatable'] = $object->_compatable();

        $params['extinfo']['sys_name'] = $this->get['action'];
        $params['extinfo']['sys_type'] = $this->get['object'];
        $params['extinfo']['db_version'] = $db_data['version'];
        $params['extinfo']['db_compatable'] = $db_data['compatable'];
        return template::getInstance()->twigRender('extensioninfo.tpl', $params);
    }

    private function installExtension() {
        $ext_params = extension::getInstance()->getAllParams();
        if(array_key_exists($this->get['action'], $ext_params[$this->get['object']])) // always installed, wtf this man try to do?
            return;
        $backend = root . '/extensions/' . $this->get['object'] . '/' . $this->get['action'] . '/back.php';
        if(file_exists($backend)) {
            require_once($backend);
            $cname = $this->get['object'] . '_' . $this->get['action'] . '_back';
            if(class_exists($cname)) {
                $class = @new $cname;
                $object = null;
                if(method_exists($cname, 'getInstance'))
                    $object = @$class::getInstance();

                if(is_object($object)) {
                    $ext_version = null;
                    $ext_compatable = null;
                    if(method_exists($object, '_version') && method_exists($object, '_compatable')) {
                        $ext_version = $object->_version();
                        $ext_compatable = $object->_compatable();
                    }

                    $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_extensions (`type`, `configs`, `dir`, `enabled`, `path_choice`, `path_allow`, `version`, `compatable`)
                    VALUES (?, '', ?, 0, 1, '*', ?, ?)");
                    $stmt->bindParam(1, $this->get['object'], \PDO::PARAM_STR);
                    $stmt->bindParam(2, $this->get['action'], \PDO::PARAM_STR);
                    $stmt->bindParam(3, $ext_version, \PDO::PARAM_STR|\PDO::PARAM_NULL);
                    $stmt->bindParam(4, $ext_compatable, \PDO::PARAM_STR|\PDO::PARAM_NULL);
                    $stmt->execute();
                    $stmt = null;

                    // @deprecated
                    if(method_exists($object, 'install'))
                        $object->install();
                    if(method_exists($object, '_install'))
                        $object->_install();
                }
            }
        }
    }

    private function disableExtension() {
        $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_extensions SET enabled = 0 WHERE `type` = ? AND `dir` = ?");
        $stmt->bindParam(1, $this->get['object'], \PDO::PARAM_STR);
        $stmt->bindParam(2, $this->get['action'], \PDO::PARAM_STR);
        $stmt->execute();
        $stmt = null;
    }

    private function enableExtension() {
        $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_extensions SET enabled = 1 WHERE `type` = ? AND `dir` = ?");
        $stmt->bindParam(1, $this->get['object'], \PDO::PARAM_STR);
        $stmt->bindParam(2, $this->get['action'], \PDO::PARAM_STR);
        $stmt->execute();
        $stmt = null;
    }

    private function viewUpdates() {
        csrf::getInstance()->buildToken();
        $action = system::getInstance()->get('action');
        $params = array();

        if($action == null) {
            $ffcms_jsondata = system::getInstance()->url_get_contents(self::update_url);

            if(system::getInstance()->length($ffcms_jsondata) < 1)
                $params['extinfo']['ffsite_down'] = true;

            $json_info = json_decode(base64_decode($ffcms_jsondata));
            $params['extinfo']['ff_lastversion'] = (string)$json_info->{'response'}->{'version'};

            if($params['extinfo']['ff_lastversion'] != null) {
                $intequal_local_version = system::getInstance()->ffVersionToCompare(version);
                $intequal_remote_version = system::getInstance()->ffVersionToCompare($params['extinfo']['ff_lastversion']);
                if($intequal_remote_version > $intequal_local_version)
                    $params['extinfo']['update_available'] = true;
            }
            $params['extinfo']['repos'] = $json_info->{'response'}->{'repo'};

            if(system::getInstance()->post('submit') && csrf::getInstance()->check() && $params['extinfo']['update_available'] === true) {
                $params['extinfo']['notifysubmit'] = true;
                $repo_get_name = system::getInstance()->post('repo_name');
                $repo_load_url = null;
                foreach($params['extinfo']['repos'] as $repo) {
                    if((string)$repo->{'name'} == $repo_get_name) {
                        $repo_load_url = (string)$repo->{'url'};
                        break;
                    }
                }
                $ffzip = system::getInstance()->url_get_contents($repo_load_url);
                $save_name = '/upload/dist/ffcms-'.$params['extinfo']['ff_lastversion'].'.zip';
                if(file_exists(root . $save_name))
                    unlink(root . $save_name);
                system::getInstance()->putFile($ffzip, '/upload/dist/ffcms-'.$params['extinfo']['ff_lastversion'].'.zip');
                $zip = new \ZipArchive();
                $zlink = $zip->open(root . $save_name);
                if($zlink === TRUE) {
                    $zip->extractTo(root);
                } else {
                    $params['extinfo']['notifyfail'] = true;
                }
                $zip->close();
            }

            return template::getInstance()->twigRender('updates_system.tpl', $params);
        } elseif($action == 'extensions') {
            $alldata = extension::getInstance()->getAllParams();
            foreach($alldata as $cdata) {
                foreach($cdata as $extension) {
                    $object = extension::getInstance()->call($extension['type'], $extension['dir'], true);
                    $script_version = null;
                    $script_compatable = null;
                    if(method_exists($object, '_version'))
                        $script_version = $object->_version();
                    if(method_exists($object, '_compatable'))
                        $script_compatable = $object->_compatable();
                    $params['extinfo']['data'][] = array(
                        'id' => $extension['id'],
                        'type' => $extension['type'],
                        'name' => $extension['dir'],
                        'db_version' => $extension['version'],
                        'db_compatable' => $extension['compatable'],
                        'script_version' => $script_version,
                        'script_compatable' => $script_compatable
                    );
                    if(system::getInstance()->post('submit')) {
                        if(method_exists($object, '_update') && $script_compatable == version)
                            @$object->_update($extension['version']);
                        $params['extinfo']['notify_updated'] = true;
                    }
                }
            }

            return template::getInstance()->twigRender('updates_extensions.tpl', $params);
        }
    }

    private function viewDumper() {
        $params = array();
        if(!file_exists(root . '/backup/'))
            system::getInstance()->createDirectory(root . '/backup/', 0755);
        if(!is_readable(root . "/backup/") || !is_writable(root . "/backup/"))
            $params['notify']['rw_error'] = true;
        if(system::getInstance()->post('submit'))
            dumper::getInstance()->make();
        $reader = scandir(root . "/backup/");
        $date_array = array();
        foreach($reader as $files) {
            if(!system::getInstance()->prefixEquals($files, '.')) {
                $file_date_array = system::getInstance()->altexplode('_', $files);
                $file_date = array_shift($file_date_array);
                $date_array = system::getInstance()->arrayAdd(system::getInstance()->toUnixTime($file_date), $date_array);
            }
        }
        arsort($date_array);
        $last_backup_date = null;
        if(!is_null($date_array[0]))
        {
            $params['backup']['last'] = system::getInstance()->toDate($date_array[0], 'd');
        }
        return template::getInstance()->twigRender('dumper.tpl', $params);
    }

    private function viewAntiVirus() {
        if($this->get['action'] === 'exclude') {
            $params = array();
            $exc_dir = system::getInstance()->get('directory');
            if(!is_null($exc_dir)) {
                antivirus::getInstance()->removeExcludedDir($exc_dir);
            }
            if(system::getInstance()->post('antivir_exclude') && system::getInstance()->isLatinOrNumeric(system::getInstance()->post('antivir_dir'))) {
                $params['notify']['folder_add'] = antivirus::getInstance()->addExcludedDir(system::getInstance()->post('antivir_dir'));
            }
            $params['antivirus']['excluded'] = antivirus::getInstance()->getExcludedDirs();
            return template::getInstance()->twigRender('avir_exclude.tpl', $params);
        } else {
            antivirus::getInstance()->doFullScan($this->get['action'] === 'rescan' ? true : false);
            $res = antivirus::getInstance()->getScanResult();
            return template::getInstance()->twigRender('avir_info.tpl', array('scan' => $res));
        }
    }

    private function viewFileManager() {
        return template::getInstance()->twigRender('filemanager.tpl', array());
    }

    private function viewSettings() {
        csrf::getInstance()->buildToken();
        $params = array();
        if (system::getInstance()->post('submit') && csrf::getInstance()->check()) {
            $save_data = "<?php\n";
            foreach (system::getInstance()->post(null) as $var_name => $var_value) {
                if (system::getInstance()->prefixEquals($var_name, 'cfgmain:')) {
                    $var_clear_name = system::getInstance()->nohtml(substr($var_name, 8));
                    $language_depended_params = array('seo_title', 'seo_description', 'seo_keywords');
                    if(in_array($var_clear_name, $language_depended_params)) {
                        foreach(language::getInstance()->getAvailable() as $clang) { // array of available languages. Search in post data
                            $save_data .= '$config[\'' . $var_clear_name . '\'][\'' . $clang . '\'] = "' . system::getInstance()->nohtml($var_value[$clang]) . '";' . "\n";
                        }
                    } elseif($var_value == "1" || $var_value == "0") { // boolean type
                        $boolean_var = $var_value == "1" ? "true" : "false";
                        $save_data .= '$config[\'' . $var_clear_name . '\'] = ' . $boolean_var . ';' . "\n";
                    } else {
                        $save_data .= '$config[\'' . $var_clear_name . '\'] = "' . system::getInstance()->nohtml($var_value) . '";' . "\n";
                    }
                }
            }
            $save_data .= "?>";
            if (is_readable(root . '/' . "config.php") && is_writable(root . '/' . "config.php")) {
                file_put_contents(root . '/' . "config.php", $save_data);
            }
            system::getInstance()->redirect("?object=settings&action=saved");
        }
        foreach(property::getInstance()->getAll() as $config=>$value) {
            $params['config'][$config] = $value;
        }
        $themeAvailable = array();
        $scan = scandir(root . '/' . property::getInstance()->get('tpl_dir'));
        foreach ($scan as $found_tpl) {
            if ($found_tpl != '.' && $found_tpl != '..' && !system::getInstance()->contains('admin', $found_tpl) && !system::getInstance()->contains('install', $found_tpl)
                && is_dir(root . '/' . property::getInstance()->get('tpl_dir') . '/' . $found_tpl)) {
                $themeAvailable[] = $found_tpl;
            }
        }
        $params['config']['addon'] = array(
            'availableThemes' => $themeAvailable,
            'availableLang' => language::getInstance()->getAvailable(),
            'availableZones' => timezone::getInstance()->getZoneUTC(),

        );
        if($this->get['action'] === 'saved')
            $params['notify']['saved'] = true;
        return template::getInstance()->twigRender('settings.tpl', $params);
    }

    private function viewHeadElements() {
        $params = array();

        list($month, $day, $year) = explode('-', date('m-d-y'));
        $day_start = mktime(0, 0, 0, $month, $day, $year);
        $day_end = mktime(0, 0, 0, $month, $day + 1, $year);

        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_feedback WHERE time >= ? AND time <= ?");
        $stmt->bindParam(1, $day_start, \PDO::PARAM_INT);
        $stmt->bindParam(2, $day_end, \PDO::PARAM_INT);
        $stmt->execute();

        $res_feed = $stmt->fetch();
        $stmt = null;

        $params['feedback_day'] = $res_feed[0];

        $stmt = database::getInstance()->con()->query("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE moderate = 1");

        $res_feed = $stmt->fetch();
        $stmt = null;

        $params['comments_day'] = $res_feed[0];

        return $params;
    }

    private function viewExtensionMenu() {
        $params = array();
        $ext = extension::getInstance()->getAllParams();
        foreach($ext as $type=>$cdata) {
            foreach($cdata as $single) {
                if($single['enabled'] == 1) {
                    $params[$type][] = array(
                        'dir' => $single['dir'],
                        'lang' => language::getInstance()->get('admin_'.$type.'_'.$single['dir'].'.name') ?: $single['dir']
                    );
                }
            }
        }
        return $params;
    }

    public function viewCurrentExtensionTitle() {
        $title = language::getInstance()->get('admin_' . $this->get['object'] . '_' . $this->get['action'] . '.name');
        if($title == null)
            $title = $this->get['action'];
        return $title;
    }

    public function saveExtensionConfigs() {
        $toSave = array();
        foreach(system::getInstance()->post() as $key=>$value) {
            if(system::getInstance()->prefixEquals($key, 'cfg:')) {
                list(,$cfgname) = system::getInstance()->altexplode(':', $key);
                $value = system::getInstance()->nohtml($value);
                $toSave[$cfgname] = $value;
            }
        }
        if(sizeof($toSave) > 0) {
            $stringcfg = serialize($toSave);
            $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_extensions SET configs = ? WHERE type = ? AND dir = ?");
            $stmt->bindParam(1, $stringcfg, \PDO::PARAM_STR);
            $stmt->bindParam(2, $this->get['object'], \PDO::PARAM_STR);
            $stmt->bindParam(3, $this->get['action'], \PDO::PARAM_STR);
            $stmt->execute();
            $stmt = null;
            extension::getInstance()->overloadConfigs();
            return true;
        }
        return false;
    }

    private function loadMainpage() {
        $params = array();
        list($month, $day, $year) = explode('-', date('m-d-y'));
        $start = mktime(0, 0, 0, $month, $day, $year);
        $end = mktime(0, 0, 0, $month, $day + 1, $year);
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_statistic WHERE time >= ? AND time <= ?");
        $stmt->bindParam(1, $start);
        $stmt->bindParam(2, $end);
        $stmt->execute();
        $res1 = $stmt->fetch();
        $params['stat']['view_count'] = $res1[0];
        $stmt = null;
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(DISTINCT ip,cookie) FROM ".property::getInstance()->get('db_prefix')."_statistic WHERE time >= ? AND time <= ?");
        $stmt->bindParam(1, $start);
        $stmt->bindParam(2, $end);
        $stmt->execute();
        $res2 = $stmt->fetch();
        $params['stat']['total_count'] = $res2[0];
        $stmt = null;
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(DISTINCT ip,cookie) FROM ".property::getInstance()->get('db_prefix')."_statistic WHERE time >= ? and time <= ? AND reg_id > 0");
        $stmt->bindParam(1, $start);
        $stmt->bindParam(2, $end);
        $stmt->execute();
        $res3 = $stmt->fetch();
        $params['stat']['registered_count'] = $res3[0];
        $stmt = null;
        $params['stat']['guest_count'] = $params['stat']['total_count'] - $params['stat']['registered_count'];
        $params['stat']['graph_data'] = $this->weekData();
        $params['stat']['server_os_type'] = php_uname('s');
        $params['stat']['server_php_ver'] = phpversion();
        $params['stat']['server_mysql_ver'] = database::getInstance()->con()->getAttribute(\PDO::ATTR_SERVER_VERSION);
        $params['stat']['server_load_avg'] = $this->getLoadAvarage();
        $params['stat']['folder_uploads_access'] = $this->analiseAccess("/upload/", "rw");
        $params['stat']['folder_language_access'] = $this->analiseAccess("/language/", "rw");
        $params['stat']['folder_cache_access'] = $this->analiseAccess("/cache/", "rw");
        $params['stat']['file_config_access'] = $this->analiseAccess("/config.php", "rw");
        $params['stat']['logs'] = $this->readLogs();

        return template::getInstance()->twigRender('general.tpl', array('local' => $params));
    }

    private function weekData() {
        $params = array();
        for ($i = 5; $i >= 0; $i--) {
            $totime = strtotime(date('Y-m-d', time() - ($i * 86400)));
            $fromtime = $totime - (60 * 60 * 24);
            $stmt = database::getInstance()->con()->prepare("SELECT COUNT(DISTINCT ip,cookie) FROM ".property::getInstance()->get('db_prefix')."_statistic WHERE time >= ? AND time <= ?");
            $stmt->bindParam(1, $fromtime);
            $stmt->bindParam(2, $totime);
            $stmt->execute();
            $res1 = $stmt->fetch();
            $unique_users = $res1[0];
            $stmt = null;
            $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_statistic WHERE time >= ? AND time <= ?");
            $stmt->bindParam(1, $fromtime);
            $stmt->bindParam(2, $totime);
            $stmt->execute();
            $res2 = $stmt->fetch();
            $view_users = $res2[0];
            $object_date = date('Y-m-d', $fromtime);
            $params[] = array('date' => $object_date, 'views' => $view_users, 'unique' => $unique_users);
        }
        return $params;
    }

    private function readLogs() {
        $output = array();
        if(file_exists(root . '/log/')) {
            $file_array = scandir(root . '/log/', 1);
            $log_array = array();
            foreach($file_array as $file) {
                if(system::getInstance()->suffixEquals($file, '.log')) {
                    list($d,$m,$y,$ext) = system::getInstance()->altexplode('.', $file);
                    if($m === date('m') && $y === date('Y'))
                        $log_array[] = $file;
                }
            }
            if(sizeof($log_array) > 0) {
                foreach($log_array as $log) {
                    $line_array = system::getInstance()->altexplode("\n", @file_get_contents(root . '/log/'.$log));
                    foreach($line_array as $line) {
                        if(sizeof($output) < 20) {
                            $output[] = $line;
                        } else {
                            continue;
                        }
                    }
                }
            }
        }
        return $output;
    }

    private function analiseAccess($data, $rule = 'rw')
    {
        $error = false;
        for ($i = 0; $i < strlen($rule); $i++) {
            if ($rule[$i] == "r") {
                if (!is_readable(root . $data))
                    $error = true;
            } elseif ($rule[$i] == "w") {
                if (!is_writable(root . $data))
                    $error = true;
            }
        }
        if ($error) {
            return "Error";
        } else {
            return "Ok";
        }
    }

    private function getLoadAvarage()
    {
        $load = 0;
        if (!stristr(PHP_OS, 'win')) {
            $load = system::getInstance()->altimplode(' ', sys_getloadavg());
        }
        return $load;
    }

    public function getDefaultAccessRights() {
        return self::$default_admin_sections;
    }
}