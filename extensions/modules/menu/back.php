<?php

use engine\extension;
use engine\database;
use engine\property;
use engine\language;
use engine\template;
use engine\system;
use engine\admin;
use engine\csrf;

class modules_menu_back extends \engine\singleton {

    public function make() {
        $make = system::getInstance()->get('make');

        $content = null;
        switch($make) {
            case null:
                $content = $this->viewMenuList();
                break;
            case 'add':
                $content = $this->viewMenuAdd();
                break;
            case 'edit':
                $content = $this->viewMenuEdit();
                break;
            case 'delete':
                $content = $this->viewMenuDelete();
                break;
            case 'manage':
                $content = $this->viewMenuManage();
                break;
            case 'itemadd':
                $content = $this->viewMenuItemAdd();
                break;
            case 'itemedit':
                $content = $this->viewMenuItemEdit();
                break;
            case 'itemdelete':
                $content = $this->viewMenuItemDelete();
                break;
            case 'dependedit':
                $content = $this->viewMenuDependItemEdit();
                break;
            case 'dependdelete':
                $content = $this->viewMenuDependItemDelete();
                break;
        }
        return $content;
    }

    public function accessData() {
        return array(
            'admin/modules/menu',
            'admin/modules/menu/add',
            'admin/modules/menu/manage',
            'admin/modules/menu/edit',
            'admin/modules/menu/delete',
            'admin/modules/menu/itemadd',
            'admin/modules/menu/itemedit',
            'admin/modules/menu/itemdelete',
            'admin/modules/menu/dependedit',
            'admin/modules/menu/dependdelete',
        );
    }


    public function _version() {
        return '1.0.1';
    }

    public function _compatable() {
        return '2.0.3';
    }

    private function viewMenuDependItemDelete() {
        csrf::getInstance()->buildToken();
        $menu_id = (int)system::getInstance()->get('id');
        $menu_item_id = (int)system::getInstance()->get('did');
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_mod_menu_ditem WHERE d_id = ?");
        $stmt->bindParam(1, $menu_item_id, \PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() != 1)
            return null;

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt = null;

        $serial_name = unserialize($result['d_name']);
        $params['modmenu']['data'] = array(
            'name' => $serial_name[language::getInstance()->getUseLanguage()],
            'url' => $result['d_url'],
            'menu_id' => $menu_id,
            'is_depend' => true
        );

        if(system::getInstance()->post('submit') && csrf::getInstance()->check()) {
            $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_menu_ditem WHERE d_id = ?");
            $stmt->bindParam(1, $menu_item_id, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            system::getInstance()->redirect("?object=modules&action=menu&make=manage&id=" . $menu_id);
        }

        return template::getInstance()->twigRender('modules/menu/itemdel.tpl', $params);
    }

    private function viewMenuDependItemEdit() {
        csrf::getInstance()->buildToken();
        $menu_id = (int)system::getInstance()->get('id');
        $menu_item_id = (int)system::getInstance()->get('did');
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_mod_menu_header WHERE menu_id = ?");
        $stmt->bindParam(1, $menu_id, \PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() != 1)
            return null;

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt = null;

        $menu_serial_name = unserialize($result['menu_name']);
        $params['modmenu']['data'] = array(
            'name' => $menu_serial_name[language::getInstance()->getUseLanguage()],
            'tag' => $result['menu_tag'],
            'id' => $menu_id
        );

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM `".property::getInstance()->get('db_prefix')."_mod_menu_ditem` WHERE d_id = ?");
        $stmt->bindParam(1, $menu_item_id, \PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() != 1)
            return null;

        $itemRes = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt = null;

        $serial_name = unserialize($itemRes['d_name']);
        $params['modmenu']['general'] = array(
            'url' => $itemRes['d_url'],
            'name' => $serial_name,
            'priority' => $itemRes['d_priority']
        );

        $params['modmenu']['data']['owner_item'] = $itemRes['d_owner_gid'];

        $stmt = database::getInstance()->con()->query("SELECT g_id, g_url, g_name FROM ".property::getInstance()->get('db_prefix')."_mod_menu_gitem");
        $resList = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;

        foreach($resList as $item) {
            $serial_owner = unserialize($item['g_name']);
            $params['modmenu']['elements'][] = array(
                'id' => $item['g_id'],
                'name' => $serial_owner[language::getInstance()->getUseLanguage()],
                'url' => $item['g_url']
            );
        }

        if(system::getInstance()->post('submit') && csrf::getInstance()->check()) {
            $menu_name = system::getInstance()->nohtml(system::getInstance()->post('menu_name'));
            $menu_url = system::getInstance()->nohtml(system::getInstance()->post('menu_url'));
            $menu_owner = (int)system::getInstance()->post('menu_owner');
            $menu_priority = (int)system::getInstance()->post('menu_priority');

            if(system::getInstance()->length($menu_name[language::getInstance()->getUseLanguage()]) < 1)
                $params['notify']['name_empty'] = true;
            if(system::getInstance()->length($menu_url) < 1)
                $params['notify']['url_wrong'] = true;

            if(sizeof($params['notify']) < 1) {
                $menu_savename = serialize($menu_name);
                if($menu_owner == 0) {
                    // from depended to general
                    $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_mod_menu_gitem
                                                            (`g_menu_head_id` , `g_name`, `g_url`, `g_priority`) VALUES(?, ?, ?, ?)");
                    $stmt->bindParam(1, $menu_id, \PDO::PARAM_INT);
                    $stmt->bindParam(2, $menu_savename, \PDO::PARAM_STR);
                    $stmt->bindParam(3, $menu_url, \PDO::PARAM_STR);
                    $stmt->bindParam(4, $menu_priority, \PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;

                    $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_menu_ditem WHERE d_id = ?");
                    $stmt->bindParam(1, $menu_item_id, \PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                } else {
                    // update current data withIN change d_owner_gid
                    $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_mod_menu_ditem
                                                            SET d_name = ?, d_url = ?, d_priority = ?, d_owner_gid = ? WHERE d_id = ?");
                    $stmt->bindParam(1, $menu_savename, \PDO::PARAM_STR);
                    $stmt->bindParam(2, $menu_url, \PDO::PARAM_STR);
                    $stmt->bindParam(3, $menu_priority, \PDO::PARAM_INT);
                    $stmt->bindParam(4, $menu_owner, \PDO::PARAM_INT);
                    $stmt->bindParam(5, $menu_item_id, \PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                }
                system::getInstance()->redirect('?object=modules&action=menu&make=manage&id=' . $menu_id);
            }
        }


        return template::getInstance()->twigRender('modules/menu/itemadd.tpl', $params);
    }

    private function viewMenuItemDelete() {
        csrf::getInstance()->buildToken();
        $menu_id = (int)system::getInstance()->get('id');
        $menu_item_id = (int)system::getInstance()->get('owner');
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_mod_menu_gitem WHERE g_menu_head_id = ? AND g_id = ?");
        $stmt->bindParam(1, $menu_id, \PDO::PARAM_INT);
        $stmt->bindParam(2, $menu_item_id, \PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() != 1)
            return null;

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt = null;

        $serial_name = unserialize($result['g_name']);
        $params['modmenu']['data'] = array(
            'name' => $serial_name[language::getInstance()->getUseLanguage()],
            'url' => $result['g_url'],
            'menu_id' => $menu_id
        );

        // check if depend elements not exist
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_mod_menu_ditem WHERE d_owner_gid = ?");
        $stmt->bindParam(1, $menu_item_id, \PDO::PARAM_INT);
        $stmt->execute();

        $resCheck = $stmt->fetch();
        $stmt = null;
        $params['modmenu']['have_depend'] = $resCheck[0] > 0;

        if(system::getInstance()->post('submit') && csrf::getInstance()->check()) {
            if(!$params['modmenu']['have_depend']) {
                $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_menu_gitem WHERE g_menu_head_id = ? AND g_id = ?");
                $stmt->bindParam(1, $menu_id, \PDO::PARAM_INT);
                $stmt->bindParam(2, $menu_item_id, \PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
            }
            system::getInstance()->redirect("?object=modules&action=menu&make=manage&id=" . $menu_id);
        }

        return template::getInstance()->twigRender('modules/menu/itemdel.tpl', $params);
    }

    private function viewMenuItemEdit() {
        csrf::getInstance()->buildToken();
        $menu_id = (int)system::getInstance()->get('id');
        $menu_item_id = (int)system::getInstance()->get('owner');
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_mod_menu_header WHERE menu_id = ?");
        $stmt->bindParam(1, $menu_id, \PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() != 1)
            return null;

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt = null;

        $menu_serial_name = unserialize($result['menu_name']);
        $params['modmenu']['data'] = array(
            'name' => $menu_serial_name[language::getInstance()->getUseLanguage()],
            'tag' => $result['menu_tag'],
            'id' => $menu_id
        );

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_mod_menu_gitem WHERE g_menu_head_id = ? ORDER BY g_id ASC");
        $stmt->bindParam(1, $menu_id, \PDO::PARAM_INT);
        $stmt->execute();

        $itemRes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;

        foreach($itemRes as $item) {
            $serial_name = unserialize($item['g_name']);
            if($item['g_id'] == $menu_item_id) { // current menu element
                $params['modmenu']['general'] = array(
                    'url' => $item['g_url'],
                    'name' => $serial_name,
                    'priority' => $item['g_priority']
                );
            } else { // add to element child list
                $params['modmenu']['elements'][] = array(
                    'id' => $item['g_id'],
                    'name' => $serial_name[language::getInstance()->getUseLanguage()],
                    'url' => $item['g_url']
                );
            }
        }

        // dont allow change the owner if have child
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_mod_menu_ditem WHERE d_owner_gid = ?");
        $stmt->bindParam(1, $menu_item_id, \PDO::PARAM_INT);
        $stmt->execute();

        $checkRes = $stmt->fetch();
        $stmt = null;

        $params['modmenu']['have_chield'] = $checkRes[0] > 0;

        if(system::getInstance()->post('submit') && csrf::getInstance()->check()) {
            $menu_name = system::getInstance()->nohtml(system::getInstance()->post('menu_name'));
            $menu_url = system::getInstance()->nohtml(system::getInstance()->post('menu_url'));
            $menu_owner = $params['modmenu']['have_chield'] ? 0 : (int)system::getInstance()->post('menu_owner');
            $menu_priority = (int)system::getInstance()->post('menu_priority');

            if(system::getInstance()->length($menu_name[language::getInstance()->getUseLanguage()]) < 1)
                $params['notify']['name_empty'] = true;
            if(system::getInstance()->length($menu_url) < 1)
                $params['notify']['url_wrong'] = true;

            if(sizeof($params['notify']) < 1) {
                $menu_savename = serialize($menu_name);
                if($menu_owner == 0) {
                    $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_mod_menu_gitem
                                                            SET `g_name` = ?, `g_url` = ?, `g_priority` = ? WHERE g_menu_head_id = ? AND g_id = ?");
                    $stmt->bindParam(1, $menu_savename, \PDO::PARAM_STR);
                    $stmt->bindParam(2, $menu_url, \PDO::PARAM_STR);
                    $stmt->bindParam(3, $menu_priority, \PDO::PARAM_INT);
                    $stmt->bindParam(4, $menu_id, \PDO::PARAM_INT);
                    $stmt->bindParam(5, $menu_item_id, \PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                } else {
                    // INSERT in depend table and remove from general
                    $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_mod_menu_ditem
                                                        (`d_owner_gid`, `d_name`, `d_url`) VALUES (?, ?, ?)");
                    $stmt->bindParam(1, $menu_owner, \PDO::PARAM_INT);
                    $stmt->bindParam(2, $menu_savename, \PDO::PARAM_STR);
                    $stmt->bindParam(3, $menu_url, \PDO::PARAM_STR);
                    $stmt->execute();
                    $stmt = null;

                    $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_menu_gitem WHERE g_id = ?");
                    $stmt->bindParam(1, $menu_item_id, \PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                }
                system::getInstance()->redirect('?object=modules&action=menu&make=manage&id=' . $menu_id);
            }
        }


        return template::getInstance()->twigRender('modules/menu/itemadd.tpl', $params);
    }

    private function viewMenuItemAdd() {
        csrf::getInstance()->buildToken();
        $menu_id = (int)system::getInstance()->get('id');
        $owner_item_id = (int)system::getInstance()->get('owner');
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_mod_menu_header WHERE menu_id = ?");
        $stmt->bindParam(1, $menu_id, \PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() != 1)
            return null;

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt = null;

        if(system::getInstance()->post('submit') && csrf::getInstance()->check()) {
            $menu_name = system::getInstance()->nohtml(system::getInstance()->post('menu_name'));
            $menu_url = system::getInstance()->nohtml(system::getInstance()->post('menu_url'));
            $menu_owner = (int)system::getInstance()->post('menu_owner');
            $menu_priority = (int)system::getInstance()->post('menu_priority');

            if(system::getInstance()->length($menu_name[language::getInstance()->getUseLanguage()]) < 1)
                $params['notify']['name_empty'] = true;
            if(system::getInstance()->length($menu_url) < 1)
                $params['notify']['url_wrong'] = true;

            if(sizeof($params['notify']) < 1) {
                $menu_savename = serialize($menu_name);
                if($menu_owner == 0) {
                    $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_mod_menu_gitem
                                                        (`g_menu_head_id`, `g_name`, `g_url`, `g_priority`) VALUES (?, ?, ?, ?)");
                    $stmt->bindParam(1, $menu_id, \PDO::PARAM_INT);
                    $stmt->bindParam(2, $menu_savename, \PDO::PARAM_STR);
                    $stmt->bindParam(3, $menu_url, \PDO::PARAM_STR);
                    $stmt->bindParam(4, $menu_priority, \PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                } else {
                    $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_mod_menu_ditem
                                                        (`d_owner_gid`, `d_name`, `d_url`, `d_priority`) VALUES (?, ?, ?, ?)");
                    $stmt->bindParam(1, $menu_owner, \PDO::PARAM_INT);
                    $stmt->bindParam(2, $menu_savename, \PDO::PARAM_STR);
                    $stmt->bindParam(3, $menu_url, \PDO::PARAM_STR);
                    $stmt->bindParam(4, $menu_priority, \PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                }
                system::getInstance()->redirect('?object=modules&action=menu&make=manage&id=' . $menu_id);
            }
        }


        $menu_serial_name = unserialize($result['menu_name']);
        $params['modmenu']['data'] = array(
            'name' => $menu_serial_name[language::getInstance()->getUseLanguage()],
            'tag' => $result['menu_tag'],
            'id' => $menu_id,
            'owner_item' => $owner_item_id,
        );

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_mod_menu_gitem WHERE g_menu_head_id = ? ORDER BY g_id ASC");
        $stmt->bindParam(1, $menu_id, \PDO::PARAM_INT);
        $stmt->execute();

        $itemRes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;

        foreach($itemRes as $item) {
            $serial_name = unserialize($item['g_name']);
            $params['modmenu']['elements'][] = array(
                'id' => $item['g_id'],
                'name' => $serial_name[language::getInstance()->getUseLanguage()],
                'url' => $item['g_url']
            );
        }

        return template::getInstance()->twigRender('modules/menu/itemadd.tpl', $params);
    }

    private function viewMenuManage() {
        $menu_id = (int)system::getInstance()->get('id');
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_mod_menu_header WHERE menu_id = ?");
        $stmt->bindParam(1, $menu_id, \PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() != 1)
            return null;

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt = null;


        $menu_serial_name = unserialize($result['menu_name']);
        $params['modhead']['data'] = array(
            'name' => $menu_serial_name[language::getInstance()->getUseLanguage()],
            'tag' => $result['menu_tag'],
            'id' => $menu_id,
        );

        $stmt = database::getInstance()->con()->prepare
            ("SELECT g.*, d.* FROM `".property::getInstance()->get('db_prefix')."_mod_menu_gitem` as g
        LEFT OUTER JOIN `".property::getInstance()->get('db_prefix')."_mod_menu_ditem` as d
        ON g.g_id = d.d_owner_gid
        WHERE g.g_menu_head_id = ?
        ORDER BY g.g_priority ASC, d.d_priority ASC");
        $stmt->bindParam(1, $menu_id, \PDO::PARAM_INT);
        $stmt->execute();

        $resultItems = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($resultItems as $row) {
            $serial_gname = unserialize($row['g_name']);
            $serial_dname = unserialize($row['d_name']);

            $g_url = $row['g_url'];
            $d_url = $row['d_url'];

            $callback = extension::getInstance()->call(extension::TYPE_MODULE, 'menu');
            if(is_object($callback)) {
                $g_url = $callback->urlRelativeToAbsolute($g_url);
                $d_url = $callback->urlRelativeToAbsolute($d_url);
            }

            if($params['modmenu']['item'][$row['g_id']]['id'] == null) { // general item is not defined
                $params['modmenu']['item'][$row['g_id']]['id'] = $row['g_id'];
                $params['modmenu']['item'][$row['g_id']]['name'] = $serial_gname[language::getInstance()->getUseLanguage()];
                $params['modmenu']['item'][$row['g_id']]['priority'] = $row['g_priority'];
                $params['modmenu']['item'][$row['g_id']]['url'] = $g_url;
            }
            if($row['d_id'] > 0) {
                $params['modmenu']['item'][$row['d_owner_gid']]['depend_array'][] = array(
                    'id' => $row['d_id'],
                    'name' => $serial_dname[language::getInstance()->getUseLanguage()],
                    'url' => $d_url,
                    'priority' => $row['d_priority'],
                    'depend_id' => $row['d_owner_gid']
                );
            }
        }

        return template::getInstance()->twigRender('modules/menu/manage.tpl', $params);
    }

    private function viewMenuDelete() {
        csrf::getInstance()->buildToken();
        $menu_id = (int)system::getInstance()->get('id');
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_mod_menu_header WHERE menu_id = ?");
        $stmt->bindParam(1, $menu_id, \PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() != 1)
            return null;

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $serial_name = unserialize($result['menu_name']);
        $params['modmenu'] = array(
            'name' => $serial_name[language::getInstance()->getUseLanguage()],
            'tag' => $result['menu_tag']
        );

        if(system::getInstance()->post('submit') && csrf::getInstance()->check()) {
            $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_menu_header WHERE menu_id = ?");
            $stmt->bindParam(1, $menu_id, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;

            $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_menu_ditem
            WHERE d_owner_gid IN(SELECT g_id FROM ".property::getInstance()->get('db_prefix')."_mod_menu_gitem WHERE g_menu_head_id = ?)");
            $stmt->bindParam(1, $menu_id, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;

            $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_menu_gitem WHERE g_menu_head_id = ?");
            $stmt->bindParam(1, $menu_id, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            system::getInstance()->redirect("?object=modules&action=menu");
        }

        return template::getInstance()->twigRender('modules/menu/delete.tpl', $params);
    }

    private function viewMenuEdit() {
        csrf::getInstance()->buildToken();
        $menu_id = (int)system::getInstance()->get('id');
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_mod_menu_header WHERE menu_id = ?");
        $stmt->bindParam(1, $menu_id, \PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() != 1)
            return null;

        if(system::getInstance()->post('submit') && csrf::getInstance()->check()) {
            $menu_tag = system::getInstance()->nohtml(system::getInstance()->post('menu_tag'));
            $menu_tpl = system::getInstance()->post('menu_tpl');
            $menu_name = system::getInstance()->nohtml(system::getInstance()->post('menu_name'));
            $menu_display = system::getInstance()->post('menu_display') == "on" ? 1 : 0;

            if(system::getInstance()->length($menu_tag) < 1 || !system::getInstance()->isLatinOrNumeric($menu_tag) || $this->tagIsUsed($menu_tag, $menu_id))
                $params['notify']['tag_wrong'] = true;
            if(system::getInstance()->length($menu_tpl) < 1 || preg_match('/[^A-Za-z0-9_.]/s', $menu_tpl) || !in_array($menu_tpl, $this->listAvailableTemplates()))
                $params['notify']['tpl_wrong'] = true;
            if(system::getInstance()->length($menu_name[language::getInstance()->getUseLanguage()]) < 1)
                $params['notify']['name_wrong'] = true;


            if(sizeof($params['notify']) < 1) {
                $menu_serial_name = serialize($menu_name);
                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_mod_menu_header
                                                    SET `menu_name` = ?, `menu_tag` = ?, `menu_tpl` = ?, `menu_display` = ? WHERE menu_id = ?");
                $stmt->bindParam(1, $menu_serial_name, \PDO::PARAM_STR);
                $stmt->bindParam(2, $menu_tag, \PDO::PARAM_STR);
                $stmt->bindParam(3, $menu_tpl, \PDO::PARAM_STR);
                $stmt->bindParam(4, $menu_display, \PDO::PARAM_INT);
                $stmt->bindParam(5, $menu_id, \PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                system::getInstance()->redirect("?object=modules&action=menu");
            }
        }

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt = null;

        $params['modmenu'] = array(
            'menu_array' => $this->listAvailableTemplates(),
            'menu_title' => unserialize($result['menu_name']),
            'menu_tag' => $result['menu_tag'],
            'menu_tpl' => $result['menu_tpl'],
            'menu_display' => $result['menu_display']
        );

        return template::getInstance()->twigRender('modules/menu/add.tpl', $params);
    }

    private function viewMenuAdd() {
        csrf::getInstance()->buildToken();
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $params['modmenu'] = array(
            'menu_array' => $this->listAvailableTemplates()
        );

        if(system::getInstance()->post('submit') && csrf::getInstance()->check()) {
            $menu_tag = system::getInstance()->nohtml(system::getInstance()->post('menu_tag'));
            $menu_tpl = system::getInstance()->post('menu_tpl');
            $menu_name = system::getInstance()->nohtml(system::getInstance()->post('menu_name'));
            $menu_display = system::getInstance()->post('menu_display') == "on" ? 1 : 0;

            if(system::getInstance()->length($menu_tag) < 1 || !system::getInstance()->isLatinOrNumeric($menu_tag) || $this->tagIsUsed($menu_tag))
                $params['notify']['tag_wrong'] = true;
            if(system::getInstance()->length($menu_tpl) < 1 || preg_match('/[^A-Za-z0-9_.]/s', $menu_tpl) || !in_array($menu_tpl, $this->listAvailableTemplates()))
                $params['notify']['tpl_wrong'] = true;
            if(system::getInstance()->length($menu_name[language::getInstance()->getUseLanguage()]) < 1)
                $params['notify']['name_wrong'] = true;

            if(sizeof($params['notify']) < 1) {
                $menu_serial_name = serialize($menu_name);
                $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_mod_menu_header (`menu_name`, `menu_tag`, `menu_tpl`, `menu_display`) VALUES(?, ?, ?, ?)");
                $stmt->bindParam(1, $menu_serial_name, \PDO::PARAM_STR);
                $stmt->bindParam(2, $menu_tag, \PDO::PARAM_STR);
                $stmt->bindParam(3, $menu_tpl, \PDO::PARAM_STR);
                $stmt->bindParam(4, $menu_display, \PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                system::getInstance()->redirect("?object=modules&action=menu");
            }
        }

        return template::getInstance()->twigRender('modules/menu/add.tpl', $params);
    }

    private function viewMenuList() {
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $stmt = database::getInstance()->con()->query("SELECT * FROM ".property::getInstance()->get('db_prefix')."_mod_menu_header ORDER BY menu_id DESC");
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;

        foreach($result as $row) {
            $serial_name = unserialize($row['menu_name']);
            $params['modmenu']['list'][] = array(
                'id' => $row['menu_id'],
                'name' => $serial_name[language::getInstance()->getUseLanguage()],
                'tag' => $row['menu_tag'],
                'tpl' => $row['menu_tpl'],
                'display' => $row['menu_display']
            );
        }

        return template::getInstance()->twigRender('modules/menu/list.tpl', $params);
    }

    private function listAvailableTemplates() {
        $dir = root . '/' . property::getInstance()->get('tpl_dir') . '/' . property::getInstance()->get('tpl_name') . '/modules/menu/';
        $output = array();
        if(file_exists($dir)) {
            $files = scandir($dir);
            foreach($files as $file) {
                if(system::getInstance()->suffixEquals($file, '.tpl')) {
                    $output[] = $file;
                }
            }
        }
        return $output;
    }

    private function tplIsUsed($tpl) {
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_mod_menu_header WHERE `menu_tpl` = ?");
        $stmt->bindParam(1, $tpl, \PDO::PARAM_STR);
        $stmt->execute();
        $res = $stmt->fetch();
        $stmt = null;
        return $res[0] > 0;
    }

    private function tagIsUsed($tag, $defined_id = 0) {
        $stmt = null;
        if($defined_id > 0) {
            $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_mod_menu_header WHERE `menu_tag` = ? AND `menu_id` != ?");
            $stmt->bindParam(1, $tag, \PDO::PARAM_STR);
            $stmt->bindParam(2, $defined_id, \PDO::PARAM_INT);
        } else {
            $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_mod_menu_header WHERE `menu_tag` = ?");
            $stmt->bindParam(1, $tag, \PDO::PARAM_STR);
        }
        $stmt->execute();
        $res = $stmt->fetch();
        $stmt = null;
        return $res[0] > 0;
    }
}