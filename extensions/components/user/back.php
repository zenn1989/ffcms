<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\system;
use engine\template;
use engine\admin;
use engine\database;
use engine\property;
use engine\user;
use engine\permission;
use engine\extension;
use engine\csrf;

class components_user_back {
    protected static $instance = null;

    const ITEM_PER_PAGE = 10;
    const SEARCH_PER_PAGE = 50;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $content = null;
        switch(system::getInstance()->get('make')) {
            case null:
                $content = $this->viewUserList();
                break;
            case 'grouplist':
                $content = $this->viewUserGroup();
                break;
            case 'groupedit':
                $content = $this->viewUserGroupedit();
                break;
            case 'groupdelete':
                $content = $this->viewUserGroupdelete();
                break;
            case 'groupadd':
                $content = $this->viewUserGroupadd();
                break;
            case 'banlist':
                $content = $this->viewUserBanlist();
                break;
            case 'banadd':
                $content = $this->viewUserBanadd();
                break;
            case 'bandelete':
                $content = $this->viewUserBandelete();
                break;
            case 'settings':
                $content = $this->viewUserSettings();
                break;
            case 'edit':
                $content = $this->viewUserEdit();
                break;
            case 'delete':
                $content = $this->viewUserDelete();
                break;
        }
        template::getInstance()->set(template::TYPE_CONTENT, 'body', $content);
    }

    public function accessData() {
        return array(
            'admin/components/user',
            'admin/components/user/grouplist',
            'admin/components/user/groupedit',
            'admin/components/user/groupdelete',
            'admin/components/user/groupadd',
            'admin/components/user/banlist',
            'admin/components/user/banadd',
            'admin/components/user/bandelete',
            'admin/components/user/settings',
            'admin/components/user/edit',
            'admin/components/user/delete',
        );
    }

    private function viewUserBandelete() {
        csrf::getInstance()->buildToken();
        $params = array();

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $ban_id = (int)system::getInstance()->get('id');
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_block WHERE id = ? LIMIT 1");
        $stmt->bindParam(1, $ban_id, PDO::PARAM_INT);
        $stmt->execute();
        if($params['ban']['list'] = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stmt = null;
            if(system::getInstance()->post('submit') && csrf::getInstance()->check()) {
                $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_user_block WHERE id = ?");
                $stmt->bindParam(1, $ban_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=user&make=banlist");
            }
        } else {
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=user&make=banlist");
        }


        return template::getInstance()->twigRender('components/user/bandelete.tpl', $params);
    }

    private function viewUserBanadd() {
        csrf::getInstance()->buildToken();
        $params = array();

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        if(system::getInstance()->post('ipblock') && csrf::getInstance()->check()) {
            $userip = system::getInstance()->validIP(system::getInstance()->post('userip'));
            if ($userip) {
                $str_time = system::getInstance()->post('enddate');
                $except_time = 0;
                if($str_time != 0 && strlen($str_time) > 0)
                    $except_time = strtotime(system::getInstance()->post('enddate'));
                // check if block is always exist
                $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_user_block WHERE ip = ?");
                $stmt->bindParam(1, $userip, PDO::PARAM_STR);
                $stmt->execute();
                $resIpFetch = $stmt->fetch();
                $stmt = null;
                // row is always exist in db
                if ($resIpFetch[0] > 0) {
                    $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user_block SET express = ? WHERE ip = ?");
                    $stmt->bindParam(1, $except_time, PDO::PARAM_INT);
                    $stmt->bindParam(2, $userip, PDO::PARAM_STR);
                    $stmt->execute();
                    $stmt = null;
                    $params['notify']['refreshed'] = true;
                } else { // its a new line in ban table
                    $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_user_block (`ip`, `express`) VALUES (?, ?)");
                    $stmt->bindParam(1, $userip, PDO::PARAM_STR);
                    $stmt->bindParam(2, $except_time, PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                    $params['notify']['added'] = true;
                }
            } else {
                $params['notify']['wrong_data'] = true;
            }
        } elseif(system::getInstance()->post('idorloginblock')) {
            $idorlogin = system::getInstance()->post('userdata');
            $stmt = database::getInstance()->con()->prepare("SELECT id FROM ".property::getInstance()->get('db_prefix')."_user WHERE id = ? or login = ?");
            $stmt->bindParam(1, $idorlogin, PDO::PARAM_STR);
            $stmt->bindParam(2, $idorlogin, PDO::PARAM_STR);
            $stmt->execute();
            if($rowUser = $stmt->fetch()) {
                $params['udata']['id'] = $rowUser['id'];
                $params['udata']['login'] = user::getInstance()->get('login', $rowUser['id']);
                $params['bansearch'] = true;
            } else {
                $params['notify']['wrong_data'] = true;
            }
        } elseif (system::getInstance()->post('banuserid')) {
            // ban after search user by id/login
            $ban_user_id = (int)system::getInstance()->post('blockuserid');
            $ban_except_time = 0;
            if(system::getInstance()->post('enddate') != 0 && strlen(system::getInstance()->post('enddate')) > 1)
                $ban_except_time = strtotime(system::getInstance()->post('enddate'));
            $stmt = database::getInstance()->con()->prepare("SELECT DISTINCT ip FROM ".property::getInstance()->get('db_prefix')."_statistic WHERE reg_id = ?");
            $stmt->bindParam(1, $ban_user_id, PDO::PARAM_INT);
            $stmt->execute();
            $fetchResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = null;
            foreach($fetchResult as $result) {
                $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_user_block(user_id, ip, express) VALUES (?, ?, ?)");
                $stmt->bindParam(1, $ban_user_id, PDO::PARAM_INT);
                $stmt->bindParam(2, $result['ip'], PDO::PARAM_STR);
                $stmt->bindParam(3, $ban_except_time, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
            }
            $params['notify']['ban_ip_set'] = true;
        }

        return template::getInstance()->twigRender('components/user/banadd.tpl', $params);
    }

    private function viewUserBanlist() {
        $params = array();
        $index = (int)system::getInstance()->get('index');
        $db_index = $index * self::ITEM_PER_PAGE;
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_block ORDER BY id DESC LIMIT ?,".self::ITEM_PER_PAGE);
        $stmt->bindParam(1, $db_index, PDO::PARAM_INT);
        $stmt->execute();
        $params['ban']['list'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;

        $params['pagination'] = template::getInstance()->showFastPagination($index, self::ITEM_PER_PAGE, $this->getUserBanTotalCount(), '?object=components&action=user&make=banlist&index=');

        return template::getInstance()->twigRender('components/user/banlist.tpl', $params);
    }

    private function viewUserGroupadd() {
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['permission_general'] = permission::getInstance()->getAllPermissions();
        $params['permission_admin'] = permission::getInstance()->getAdminPermissions();
        if(system::getInstance()->post('submit')) {
            $perm_array = system::getInstance()->post('perm');
            $string_perms = '';
            if(sizeof($perm_array) > 0) {
                $string_perms = system::getInstance()->altimplode(';', array_keys($perm_array));
            }
            $group_name = system::getInstance()->nohtml(system::getInstance()->post('group_name'));
            if($group_name != null) {
                $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_user_access_level (`group_name`, `permissions`) VALUES (?, ?)");
                $stmt->bindParam(1, $group_name, PDO::PARAM_STR);
                $stmt->bindParam(2, $string_perms, PDO::PARAM_STR);
                $stmt->execute();
                system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=user&make=grouplist");
            } else {
                $params['notify']['empty_name'] = true;
            }
        }

        return template::getInstance()->twigRender('components/user/group_edit.tpl', $params);
    }

    private function viewUserGroupdelete() {
        csrf::getInstance()->buildToken();
        $params = array();

        $group_id = (int)system::getInstance()->get('id');

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_access_level WHERE group_id = ?");
        $stmt->bindParam(1, $group_id, PDO::PARAM_INT);
        $stmt->execute();
        // if group is exist in db
        if($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stmt = null;
            if(system::getInstance()->post('submit') && csrf::getInstance()->check()) { // delete is submited
                if(system::getInstance()->contains('global/owner', $result['permissions'])) { // dont delete owner group
                    $params['notify']['cant_delete_owner'] = true;
                } else {
                    $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_user_access_level WHERE group_id = ?");
                    $stmt->bindParam(1, $group_id, PDO::PARAM_INT);
                    $stmt->execute();
                    system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=user&make=grouplist");
                }
            }
            $params['group']['name'] = $result['group_name'];
        } else {
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=user&make=grouplist");
        }

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        return template::getInstance()->twigRender('components/user/group_delete.tpl', $params);
    }

    private function viewUserGroupedit() {
        $params = array();

        $group_id = (int)system::getInstance()->get('id');

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_access_level WHERE group_id = ?");
        $stmt->bindParam(1, $group_id, PDO::PARAM_INT);
        $stmt->execute();

        if($result = $stmt->fetch()) {
            $params['group'] = array(
                'id' => $group_id,
                'name' => $result['group_name'],
                'rights' => system::getInstance()->altexplode(';', $result['permissions'])
            );
        } else {
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=user&make=grouplist");
        }

        if(system::getInstance()->post('submit')) {
            $perm_array = system::getInstance()->post('perm');
            $string_perms = null;
            if(sizeof($perm_array) > 0) {
                $string_perms = system::getInstance()->altimplode(';', array_keys($perm_array));
            }
            $group_name = system::getInstance()->nohtml(system::getInstance()->post('group_name'));

            // database query
            $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user_access_level SET group_name = ?, permissions = ? WHERE group_id = ?");
            $stmt->bindParam(1, $group_name, PDO::PARAM_STR);
            $stmt->bindParam(2, $string_perms, PDO::PARAM_STR);
            $stmt->bindParam(3, $group_id, PDO::PARAM_INT);
            $stmt->execute();

            system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=user&make=grouplist");
        }

        $params['permission_general'] = permission::getInstance()->getAllPermissions();
        $params['permission_admin'] = permission::getInstance()->getAdminPermissions();

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        return template::getInstance()->twigRender('components/user/group_edit.tpl', $params);
    }

    private function viewUserGroup() {
        $params = array();

        $query = database::getInstance()->con()->query("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_access_level");
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach($result as $row) {
            $params['group'][] = array(
                'id' => $row['group_id'],
                'name' => $row['group_name'],
                'rights' => system::getInstance()->altexplode(';', $row['permissions'])
            );
        }

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        return template::getInstance()->twigRender('components/user/group_list.tpl', $params);
    }

    private function viewUserSettings() {
        csrf::getInstance()->buildToken();
        $params = array();
        if(system::getInstance()->post('submit') && csrf::getInstance()->check()) {
            if(admin::getInstance()->saveExtensionConfigs()) {
                $params['notify']['save_success'] = true;
            }
        }
        $params['config']['login_captcha'] = extension::getInstance()->getConfig('login_captcha', 'user', extension::TYPE_COMPONENT, 'int');
        $params['config']['register_captcha'] = extension::getInstance()->getConfig('register_captcha', 'user', extension::TYPE_COMPONENT, 'int');
        $params['config']['register_aprove'] = extension::getInstance()->getConfig('register_aprove', 'user', extension::TYPE_COMPONENT, 'int');
        $params['config']['use_openid'] = extension::getInstance()->getConfig('use_openid', 'user', extension::TYPE_COMPONENT, 'int');
        $params['config']['profile_view'] = extension::getInstance()->getConfig('profile_view', 'user', extension::TYPE_COMPONENT, 'int');
        $params['config']['wall_post_count'] = extension::getInstance()->getConfig('wall_post_count', 'user', extension::TYPE_COMPONENT, 'int');
        $params['config']['marks_post_count'] = extension::getInstance()->getConfig('marks_post_count', 'user', extension::TYPE_COMPONENT, 'int');
        $params['config']['friend_page_count'] = extension::getInstance()->getConfig('friend_page_count', 'user', extension::TYPE_COMPONENT, 'int');
        $params['config']['wall_post_delay'] = extension::getInstance()->getConfig('wall_post_delay', 'user', extension::TYPE_COMPONENT, 'int');
        $params['config']['pm_count'] = extension::getInstance()->getConfig('pm_count', 'user', extension::TYPE_COMPONENT, 'int');
        $params['config']['balance_view'] = extension::getInstance()->getConfig('balance_view', 'user', extension::TYPE_COMPONENT, 'int');
        $params['config']['userlist_count'] = extension::getInstance()->getConfig('userlist_count', 'user', extension::TYPE_COMPONENT, 'int');

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        return template::getInstance()->twigRender('components/user/settings.tpl', $params);
    }

    private function viewUserDelete() {
        csrf::getInstance()->buildToken();
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $userid = system::getInstance()->get('id');
        if(!user::getInstance()->exists($userid) || permission::getInstance()->have('global/owner', $userid)) {
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=user");
        }

        if(system::getInstance()->post('deleteuser') && csrf::getInstance()->check()) {
            $stmt = database::getInstance()->con()->prepare("DELETE generaldata,customdata FROM " .
                property::getInstance()->get('db_prefix') . "_user as generaldata
                LEFT OUTER JOIN " . property::getInstance()->get('db_prefix') . "_user_custom as customdata
                ON generaldata.id = customdata.id WHERE generaldata.id = ?");
            $stmt->bindParam(1, $userid, PDO::PARAM_INT);
            $stmt->execute();
            // TODO: friendlist cleanup -> field friend_list, friend_request in user table
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=user");
        }

        $params['udata'] = array(
            'login' => user::getInstance()->get('login', $userid),
            'email' => user::getInstance()->get('email', $userid),
            'id' => $userid
        );

        return template::getInstance()->twigRender('components/user/delete.tpl', $params);
    }

    private function viewUserEdit() {
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $userid = system::getInstance()->get('id');
        if(!user::getInstance()->exists($userid)) {
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=user");
        }

        if(system::getInstance()->post('submit')) {
            $new_nick = system::getInstance()->post('nick');
            $new_sex = system::getInstance()->post('sex');
            $new_phone = system::getInstance()->post('phone');
            $new_webpage = system::getInstance()->post('webpage');
            $new_birthday = system::getInstance()->post('birthday');
            $new_status = system::getInstance()->post('status');
            $new_groupid = system::getInstance()->post('groupid');
            $new_pass = strlen(system::getInstance()->post('newpass')) > 3 ? system::getInstance()->doublemd5(system::getInstance()->post('newpass')) : user::getInstance()->get('pass', $userid);
            $stmt = database::getInstance()->con()->prepare(
                "UPDATE " . property::getInstance()->get('db_prefix') . "_user a
                INNER JOIN " . property::getInstance()->get('db_prefix') . "_user_custom b USING(id) SET a.nick = ?, a.pass = ?, b.birthday = ?, b.sex = ?, b.phone = ?, b.webpage = ?, b.status = ?, a.access_level = ? WHERE a.id = ?"
            );
            $stmt->bindParam(1, $new_nick, PDO::PARAM_STR);
            $stmt->bindParam(2, $new_pass, PDO::PARAM_STR, 32);
            $stmt->bindParam(3, $new_birthday, PDO::PARAM_STR);
            $stmt->bindParam(4, $new_sex, PDO::PARAM_INT);
            $stmt->bindParam(5, $new_phone, PDO::PARAM_STR);
            $stmt->bindParam(6, $new_webpage, PDO::PARAM_STR);
            $stmt->bindParam(7, $new_status, PDO::PARAM_STR);
            $stmt->bindParam(8, $new_groupid, PDO::PARAM_INT);
            $stmt->bindParam(9, $userid, PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            user::getInstance()->overload($userid);
            $params['notify']['saved'] = true;
        }

        $params['udata']['id'] = $userid;
        $params['udata']['login'] = user::getInstance()->get('login', $userid);
        $params['udata']['nick'] = user::getInstance()->get('nick', $userid);
        $params['udata']['email'] = user::getInstance()->get('email', $userid);
        $params['udata']['sex'] = user::getInstance()->get('sex', $userid);
        $params['udata']['webpage'] = user::getInstance()->get('webpage', $userid);
        $params['udata']['birthday'] = user::getInstance()->get('birthday', $userid);
        $params['udata']['status'] = user::getInstance()->get('status', $userid);
        $params['udata']['group_data'] = $this->getGroupArray();
        $params['udata']['current_group'] = user::getInstance()->get('access_level', $userid);

        return template::getInstance()->twigRender('components/user/edit.tpl', $params);
    }

    private function viewUserList() {
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['search']['value'] = system::getInstance()->nohtml(system::getInstance()->post('search'));
        $stmt = null;

        $index_start = (int)system::getInstance()->get('index');
        $db_index = $index_start * self::ITEM_PER_PAGE;
        if(system::getInstance()->post('dosearch') && system::getInstance()->length($params['search']['value']) > 0) {
            $search_string = "%".$params['search']['value']."%";
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user WHERE login like ? OR email like ? OR nick like ? ORDER BY id DESC LIMIT 0,".self::SEARCH_PER_PAGE);
            $stmt->bindParam(1, $search_string, PDO::PARAM_STR);
            $stmt->bindParam(2, $search_string, PDO::PARAM_STR);
            $stmt->bindParam(3, $search_string, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user ORDER BY id DESC LIMIT ?,".self::ITEM_PER_PAGE);
            $stmt->bindParam(1, $db_index, PDO::PARAM_INT);
            $stmt->execute();
        }

        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;

        foreach($res as $item) {
            $params['udata'][] = array(
                'id' => $item['id'],
                'login' => $item['login'],
                'email' => $item['email']
            );
        }

        if(!system::getInstance()->post('dosearch'))
            $params['pagination'] = template::getInstance()->showFastPagination($index_start, self::ITEM_PER_PAGE, $this->getUserTotalCount(), '?object=components&action=user&index=');

        return template::getInstance()->twigRender('components/user/list.tpl', $params);
    }

    public function getGroupArray() {
        $stmt = database::getInstance()->con()->query("SELECT group_id,group_name FROM ".property::getInstance()->get('db_prefix')."_user_access_level");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getUserTotalCount()
    {
        $stmt = database::getInstance()->con()->query("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_user");
        $result = $stmt->fetch();
        $stmt = null;
        return $result[0];
    }

    public function getUserBanTotalCount() {
        $stmt = database::getInstance()->con()->query("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_user_block");
        $result = $stmt->fetch();
        $stmt = null;
        return $result[0];
    }
}