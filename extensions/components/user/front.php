<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\language;
use engine\router;
use engine\template;
use engine\user;
use engine\system;
use engine\extension;
use engine\database;
use engine\property;
use engine\meta;
use engine\csrf;

class components_user_front extends \engine\singleton {

    protected $pub_menu_links = array();
    protected $private_menu_links = array();

    public function make() {
        template::getInstance()->set(template::TYPE_CONTENT, 'body', $this->buildUser());
    }

    private function buildUser() {
        $way = router::getInstance()->shiftUriArray();
        $content = null;
        // prepare /user/*.html way's
        switch($way[0]) {
            case "login.html":
                $content = $this->viewLogin();
                break;
            case "register.html":
                $content = $this->viewRegister();
                break;
            case "logout.html":
                $this->viewLogout();
                break;
            case "recovery.html":
                $content = $this->viewRecovery();
                break;
            case "openid.html":
                $content = $this->viewOpenId();
                break;
            case "recovery":
                $content = $this->viewAproveRecovery();
                break;
            case "aprove":
                $content = $this->viewAprove();
                break;
            case 'paynotify':
                $content = $this->viewPayNotify();
                break;
        }
        if($way[0] == null || system::getInstance()->isInt($way[0])) {
            // user list
            $content = $this->viewUserList();
        } elseif(substr($way[0], 0, 2) === "id" && system::getInstance()->isInt(substr($way[0], 2))) {
            if(user::getInstance()->get('id') > 0 || extension::getInstance()->getConfig('profile_view', 'user', 'components', 'boolean'))
                $content = $this->viewUserProfile();
        }
        return $content;
    }

    private function viewPayNotify() {
        $way = router::getInstance()->shiftUriArray();

        $result = null;
        switch($way[1]) {
            case 'wm':
                $result = $this->viewWmNotify($way[2]);
                break;
            case 'ik':
                $result = $this->viewInterkassaNotify($way[2]);
                break;
            case 'rk':
                $result = $this->viewRobokassaNotify($way[2]);
                break;
        }

        return $result;
    }

    private function viewRobokassaNotify($type) {
        $params['balancenotify']['user_id'] = (int)system::getInstance()->post('shp_userid');
        $params['balancenotify']['pay_inv'] = (int)system::getInstance()->post('InvId');

        if($params['balancenotify']['user_id'] == null)
            return null;
        if($type == 'success')
            return template::getInstance()->twigRender('components/user/balance/rk_balance_success.tpl', $params);
        elseif($type == 'fail')
            return template::getInstance()->twigRender('components/user/balance/rk_balance_fail.tpl', $params);
        return null;
    }

    private function viewInterkassaNotify($type) {
        $params['balancenotify']['pay_number'] = (int)system::getInstance()->post('ik_pm_no');
        $params['balancenotify']['pay_inv'] = (int)system::getInstance()->post('ik_inv_id');
        $params['balancenotify']['pay_date'] = system::getInstance()->nohtml(system::getInstance()->post('ik_inv_prc'));

        if($params['balancenotify']['pay_number'] == null)
            return null;
        if($type == 'success')
            return template::getInstance()->twigRender('components/user/balance/ik_balance_success.tpl', $params);
        elseif($type == 'fail')
            return template::getInstance()->twigRender('components/user/balance/ik_balance_fail.tpl', $params);
        elseif($type == 'wait')
            return template::getInstance()->twigRender('components/user/balance/ik_balance_wait.tpl', $params);
        return null;

    }

    private function viewWmNotify($type) {
        $params['balancenotify']['pay_number'] = (int)system::getInstance()->post('LMI_PAYMENT_NO');
        $params['balancenotify']['pay_invs'] = (int)system::getInstance()->post('LMI_SYS_INVS_NO');
        $params['balancenotify']['pay_trans'] = (int)system::getInstance()->post('LMI_SYS_TRANS_NO');
        if($params['balancenotify']['pay_number'] == null)
            return null;
        if($type == 'success') {
            return template::getInstance()->twigRender('components/user/balance/wm_balance_success.tpl', $params);
        } elseif($type == 'fail')
            return template::getInstance()->twigRender('components/user/balance/wm_balance_fail.tpl', $params);
        return null;
    }

    private function viewUserProfile() {
        $way = router::getInstance()->shiftUriArray();
        $target_id = substr($way[0], 2);
        $viewer_id = user::getInstance()->get('id');
        if(!user::getInstance()->exists($target_id))
            return null;
        meta::getInstance()->add('title', user::getInstance()->get('nick', $target_id));
        $content = null;
        switch($way[1]) {
            case null:
            case '':
            case 'wall':
                // its a profile main - userinfo & user wall
                $content = $this->viewUserWall($target_id, $viewer_id);
                break;
            case 'bookmarks':
                // bookmark list
                $content = $this->viewUserBookmarks($target_id, $viewer_id);
                break;
            case 'friends':
                switch($way[2]) {
                    case "list":
                        $content = $this->viewUserFriendlist($target_id, $viewer_id);
                        break;
                    case "request":
                        $content = $this->viewUserFriendRequest($target_id, $viewer_id);
                        break;
                    case "accept":
                        $content = $this->viewUserFriendAccept($target_id, $viewer_id);
                        break;
                    case "deny":
                        $content = $this->viewUserFriendDeny($target_id, $viewer_id);
                        break;
                    case "delete":
                        $content = $this->viewUserFriendDelete($target_id, $viewer_id);
                        break;
                }
                break;
            case "avatar":
                // operations with avatar
                $content = $this->viewUserAvatarOptions($target_id, $viewer_id);
                break;
            case 'settings':
                // user settings
                switch($way[2]) {
                    case null:
                        $content = $this->viewUserSettings($target_id, $viewer_id);
                        break;
                    case 'status':
                        $content = $this->viewUserStatusSettings($target_id, $viewer_id);
                        break;
                    case 'logs':
                        $content = $this->viewUserLogs($target_id, $viewer_id);
                        break;
                }
                break;
            case 'messages':
                switch($way[2]) {
                    case null:
                    case 'all':
                    case 'in':
                    case 'out':
                        $content = $this->viewAllUserMessages($target_id, $viewer_id, $way[2]);
                        break;
                    case 'write':
                        $content = $this->viewUserMessagesWrite($target_id, $viewer_id);
                        break;
                    case 'topic':
                        $content = $this->viewUserMessageTopic($target_id, $viewer_id);
                        break;
                }
                break;
            case 'news':
                $content = $this->viewUserNews($target_id, $viewer_id);
                break;
            case 'balance':
                $content = $this->viewUserBalance($target_id, $viewer_id);
                break;
        }
        return $content;
    }

    private function viewUserBalance($target, $viewer) {
        if($target != $viewer || !extension::getInstance()->getConfig('balance_view', 'news', extension::TYPE_COMPONENT, 'bol'))
            return null;
        $params = array();

        $params['config']['balance_use_webmoney'] = extension::getInstance()->getConfig('balance_use_webmoney', 'user', extension::TYPE_COMPONENT, 'int');
        $params['config']['balance_wm_purse'] = extension::getInstance()->getConfig('balance_wm_purse', 'user', extension::TYPE_COMPONENT, 'str');
        $params['config']['balance_wm_mul'] = extension::getInstance()->getConfig('balance_wm_mul', 'user', extension::TYPE_COMPONENT, 'float');
        $params['config']['balance_wm_test'] = extension::getInstance()->getConfig('balance_wm_test', 'user', extension::TYPE_COMPONENT, 'int');
        $params['config']['balance_valut_name'] = extension::getInstance()->getConfig('balance_valut_name', 'user', extension::TYPE_COMPONENT, 'str');
        $params['config']['balance_wm_type'] = "WM".system::getInstance()->altsubstr($params['config']['balance_wm_purse'], 0, 1);

        $params['config']['balance_use_ik'] = extension::getInstance()->getConfig('balance_use_ik', 'user', extension::TYPE_COMPONENT, 'int');
        $params['config']['balance_ik_id'] = extension::getInstance()->getConfig('balance_ik_id', 'user', extension::TYPE_COMPONENT, 'str');
        $params['config']['balance_ik_mul'] = extension::getInstance()->getConfig('balance_ik_mul', 'user', extension::TYPE_COMPONENT, 'float');
        $params['config']['balance_ik_valute'] = extension::getInstance()->getConfig('balance_ik_valute', 'user', extension::TYPE_COMPONENT, 'str');

        $params['config']['balance_use_rk'] = extension::getInstance()->getConfig('balance_use_rk', 'user', extension::TYPE_COMPONENT, 'int');
        $params['config']['balance_rk_id'] = extension::getInstance()->getConfig('balance_rk_id', 'user', extension::TYPE_COMPONENT, 'str');
        $params['config']['balance_rk_mul'] = extension::getInstance()->getConfig('balance_rk_mul', 'user', extension::TYPE_COMPONENT, 'float');
        $params['config']['balance_rk_valute'] = extension::getInstance()->getConfig('balance_rk_valute', 'user', extension::TYPE_COMPONENT, 'str');

        if(system::getInstance()->post('rk_submit')) {
            $topay = (float)system::getInstance()->post('topay');

            require_once(root . '/resource/payments/robokassa/robokassa.class.php');
            $init_rk = new Robokassa(
                $params['config']['balance_rk_id'],
                extension::getInstance()->getConfig('balance_rk_key_1', 'user', extension::TYPE_COMPONENT, 'str'),
                extension::getInstance()->getConfig('balance_rk_key_2', 'user', extension::TYPE_COMPONENT, 'str'),
                extension::getInstance()->getConfig('balance_rk_test', 'user', extension::TYPE_COMPONENT, 'boolean')
            );

            $init_rk->OutSum = $topay;
            $init_rk->Desc = 'Recharge balance on '.property::getInstance()->get('url').'. Userid: '.$target;
            $init_rk->Culture = language::getInstance()->getUseLanguage();

            $init_rk->addCustomValues(array(
                'shp_userid' => $target
            ));

            header('Location: ' . $init_rk->getRedirectURL());
            exit("Browser not support header accept. Payment: <a href='".$init_rk->getRedirectURL()."'>Start pay</a>");
        }

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_log WHERE `owner` = ? and `type` like 'balance.%' ORDER BY `time` DESC LIMIT 0,50");
        $stmt->bindParam(1, $target, \PDO::PARAM_INT);
        $stmt->execute();

        $resultAll = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;
        foreach($resultAll as $row) {
            $data_array = unserialize($row['params']);
            $params['balancelogs'][] = array(
                'id' => $row['id'],
                'type' => $row['type'],
                'message' => $row['message'],
                'date' => system::getInstance()->toDate($row['time'], 'h'),
                'amount' => $data_array['amount']
            );
        }

        return $this->viewUserProfileHeader($target, $viewer, $params);
    }

    private function viewUserNews($target, $viewer) {
        if($target != $viewer || !extension::getInstance()->getConfig('enable_useradd', 'news', extension::TYPE_COMPONENT, 'bol'))
            return null;
        $params = array();
        $stmt = database::getInstance()->con()->prepare("SELECT id,title,display,date FROM ".property::getInstance()->get('db_prefix')."_com_news_entery WHERE author = ? ORDER BY date DESC LIMIT 50");
        $stmt->bindParam(1, $target, PDO::PARAM_INT);
        $stmt->execute();
        $resFetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        $params['show_newsmenu'] = extension::getInstance()->getConfig('enable_useradd', 'news', extension::TYPE_COMPONENT, 'bol');
        foreach($resFetch as $row) {
            $serial_title = unserialize($row['title']);
            $params['newslist'][] = array(
                'id' => $row['id'],
                'title' => $serial_title[language::getInstance()->getUseLanguage()],
                'date' => system::getInstance()->toDate($row['date'], 'd'),
                'display' => $row['display']
            );
        }
        return $this->viewUserProfileHeader($target, $viewer, $params);
    }

    private function viewUserProfileHeader($target, $viewer, $addparams = null) {
        $way = router::getInstance()->shiftUriArray();
        $params = array();
        if($target == $viewer) {// self profile
            $params['profile']['is_self'] = true;
        }
        $params['profile']['user_id'] = $target;
        $params['profile']['user_name'] = user::getInstance()->get('nick', $target);
        $params['profile']['user_status'] = user::getInstance()->get('status', $target);
        $params['profile']['user_avatar'] = user::getInstance()->buildAvatar('big', $target);
        $params['profile']['is_friend'] = $this->inFriendsWith($target, $viewer);
        $params['profile']['is_request_friend'] = $this->inFriendRequestWith($target, $viewer);
        $params['profile']['add_menu']['public'] = $this->pub_menu_links;
        $params['profile']['add_menu']['private'] = $this->private_menu_links;
        $params['profile']['show_usernews'] = extension::getInstance()->getConfig('enable_useradd', 'news', extension::TYPE_COMPONENT, 'bol');
        $params['profile']['show_balance'] = extension::getInstance()->getConfig('balance_view', 'news', extension::TYPE_COMPONENT, 'bol');

        $params['path'] = $way[1]; // variable for menu active item
        $params['action'] = $way[2]; // action or pagination id
        if(is_array($addparams)) {
            $params = array_merge($params, $addparams);
        }

        $params['profile']['use_karma'] = extension::getInstance()->getConfig('use_karma', 'user', extension::TYPE_COMPONENT, 'int');
        if($params['profile']['use_karma'] == 1) {
            $params['profile']['karma'] = user::getInstance()->get('karma', $target);
            // karma logs
            if($params['profile']['is_self']) {
                $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_karma WHERE `to_id` = ? ORDER BY `date` DESC LIMIT 0,10");
                $stmt->bindParam(1, $target, \PDO::PARAM_INT);
                $stmt->execute();
                while($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $params['profile']['karma_history'][] = array(
                        'type' => $result['type'],
                        'date' => system::getInstance()->toDate($result['date'], 'h')
                    );
                }
                $stmt = null;
            }
        }

        return template::getInstance()->twigRender('components/user/profile/profile_head.tpl', array('local' => $params));
    }

    private function viewUserMessageTopic($target, $viewer) {
        if($target != $viewer)
            return null;
        $params = array();
        $way = router::getInstance()->shiftUriArray();
        $topic_id = $way[3];
        // get main message
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_messages WHERE id = ? AND (`from` = ? or `to` = ?)");
        $stmt->bindParam(1, $topic_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $target, PDO::PARAM_INT);
        $stmt->bindParam(3, $target, PDO::PARAM_INT);
        $stmt->execute();
        if($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $params['message']['main'] = array(
                'user_id' => $result['from'],
                'user_name' => user::getInstance()->get('nick', $result['from']),
                'user_avatar' => user::getInstance()->buildAvatar('small', $result['from']),
                'body' => system::getInstance()->nohtml($result['message']),
                'time' => system::getInstance()->toDate($result['timeupdate'], 'h')
            );
        } else {
            return null; // main message not founded, display 404
        }
        $stmt = null;
        $result = null;
        if(system::getInstance()->post('newanswer')) {
            $answer_text = system::getInstance()->nohtml(system::getInstance()->post('topicanswer'));
            if(system::getInstance()->length($answer_text) > 0) {
                $time = time();
                $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_user_messages_answer(`topic`, `from`, `message`, `time`) VALUES(?, ?, ?, ?)");
                $stmt->bindParam(1, $topic_id, PDO::PARAM_INT);
                $stmt->bindParam(2, $target, PDO::PARAM_INT);
                $stmt->bindParam(3, $answer_text, PDO::PARAM_STR);
                $stmt->bindParam(4, $time, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user_messages SET timeupdate = ? WHERE id = ?");
                $stmt->bindParam(1, $time, PDO::PARAM_INT);
                $stmt->bindParam(2, $topic_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
            }
        }
        // get answers
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_messages_answer where topic = ? ORDER BY id DESC");
        $stmt->bindParam(1, $topic_id, PDO::PARAM_INT);
        $stmt->execute();
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $params['message']['answer'][] = array(
                'user_id' => $result['from'],
                'user_name' => user::getInstance()->get('nick', $result['from']),
                'user_avatar' => user::getInstance()->buildAvatar('small', $result['from']),
                'body' => $result['message'],
                'time' => system::getInstance()->toDate($result['time'], 'h')
            );
        }
        return $this->viewUserProfileHeader($target, $viewer, $params);
    }

    private function viewUserMessagesWrite($target, $viewer) {
        if($target != $viewer)
            return null;
        $way = router::getInstance()->shiftUriArray();
        $params = array();
        $friend_array = system::getInstance()->altexplode(',', user::getInstance()->get('friend_list', $target));
        user::getInstance()->listload($friend_array);
        $params['message']['target'] = $way[3];
        foreach($friend_array as $friend_id) {
            $params['message']['friend'][] = array(
                'user_id' => $friend_id,
                'user_name' => user::getInstance()->get('nick', $friend_id)
            );
        }
        if(system::getInstance()->post('sendmessage')) {
            $message = system::getInstance()->nohtml(system::getInstance()->post('message'));
            $receiver = system::getInstance()->post('accepterid');
            if(user::getInstance()->exists($receiver) && $this->inFriendsWith($receiver, $target) && system::getInstance()->length($message) > 0) {
                $time = time();
                $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_user_messages(`from`, `to`, `message`, `timeupdate`) VALUES(?, ?, ?, ?)");
                $stmt->bindParam(1, $target, PDO::PARAM_INT);
                $stmt->bindParam(2, $receiver, PDO::PARAM_INT);
                $stmt->bindParam(3, $message, PDO::PARAM_STR);
                $stmt->bindParam(4, $time, PDO::PARAM_INT);
                $stmt->execute();
                system::getInstance()->redirect('/user/id'.$target.'/messages/out/');
            }
        }
        return $this->viewUserProfileHeader($target, $viewer, $params);
    }

    private function viewAllUserMessages($target, $viewer, $action) {
        if($target != $viewer)
            return null;
        $way = router::getInstance()->shiftUriArray();
        $params = array();
        if($action == null || $action == 'all' || $action == 'in' || $action == 'out') {
            $page_id = (int)$way[3];
            $pm_on_page = extension::getInstance()->getConfig('pm_count', 'user', 'components', 'int');
            $current_marker = $page_id * $pm_on_page;
            $total_pm_count = $this->getMessageTotalRows($target, $action);
            $params['pagination']['index'] = $page_id;
            $params['pagination']['total'] = $total_pm_count;
            $params['pagination']['perpage'] = $pm_on_page;
            if ($action == "in") {
                $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_messages WHERE `to` = ? ORDER BY timeupdate DESC LIMIT ?, ?");
                $stmt->bindParam(1, $target, PDO::PARAM_INT);
                $stmt->bindParam(2, $current_marker, PDO::PARAM_INT);
                $stmt->bindParam(3, $pm_on_page, PDO::PARAM_INT);
                $stmt->execute();
            } elseif ($action == "out") {
                $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_messages WHERE `from` = ? ORDER BY timeupdate DESC LIMIT ?, ?");
                $stmt->bindParam(1, $target, PDO::PARAM_INT);
                $stmt->bindParam(2, $current_marker, PDO::PARAM_INT);
                $stmt->bindParam(3, $pm_on_page, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_messages WHERE `to` = ? OR `from` = ? ORDER BY timeupdate DESC LIMIT ?, ?");
                $stmt->bindParam(1, $target, PDO::PARAM_INT);
                $stmt->bindParam(2, $target, PDO::PARAM_INT);
                $stmt->bindParam(3, $current_marker, PDO::PARAM_INT);
                $stmt->bindParam(4, $pm_on_page, PDO::PARAM_INT);
                $stmt->execute();
            }
            $resultAssoc = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // prepare userdata for fast access
            $user_to_dataload = system::getInstance()->extractFromMultyArray('from', $resultAssoc);
            user::getInstance()->listload($user_to_dataload);
            foreach($resultAssoc as $topic_item) {
                $params['message'][] = array(
                    'topic_id' => $topic_item['id'],
                    'user_id' => $topic_item['from'],
                    'user_name' => user::getInstance()->get('nick', $topic_item['from']),
                    'user_avatar' => user::getInstance()->buildAvatar('small', $topic_item['from']),
                    'body' => system::getInstance()->nohtml($topic_item['message']),
                    'time' => system::getInstance()->toDate($topic_item['timeupdate'], 'h'),
                    'not_readed' => user::getInstance()->get('lastpmview', $target) < $topic_item['timeupdate']
                );
            }
            if($action == "in" || $action == "all" || $action == null) {
                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user_custom SET lastpmview = ? where id = ?");
                $time = time();
                $stmt->bindParam(1, $time, PDO::PARAM_INT);
                $stmt->bindParam(2, $target, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
            }
        }
        return $this->viewUserProfileHeader($target, $viewer, $params);
    }

    private function viewUserLogs($target, $viewer) {
        if($target != $viewer)
            return null;
        $params = array();

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_log WHERE `owner` = ? AND `type` like 'profile.%' ORDER BY `time` DESC LIMIT 0,50");
        $stmt->bindParam(1, $target, \PDO::PARAM_INT);
        $stmt->execute();

        $resAll = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;

        foreach($resAll as $row) {
            $serial_params = unserialize($row['params']);
            $params['profilelog'][] = array(
                'date' => system::getInstance()->toDate($row['time'], 'h'),
                'type' => $row['type'],
                'ip' => $serial_params['ip'],
                'lang_type' => language::getInstance()->get('usercontrol_profile_settings_logs_gtype.' . $row['type'])
            );
        }

        return $this->viewUserProfileHeader($target, $viewer, $params);
    }

    private function viewUserStatusSettings($target, $viewer) {
        if($target != $viewer)
            return null;
        csrf::getInstance()->buildToken();
        $params = array();
        if(system::getInstance()->post('updatestatus') && csrf::getInstance()->check()) {
            $status = system::getInstance()->nohtml(system::getInstance()->post('newstatus'));
            $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user_custom SET status = ? WHERE id = ?");
            $stmt->bindParam(1, $status, PDO::PARAM_STR);
            $stmt->bindParam(2, $target, PDO::PARAM_INT);
            $stmt->execute();
            user::getInstance()->overload($target);
        }
        $params['status'] = user::getInstance()->get('status', $target);
        return $this->viewUserProfileHeader($target, $viewer, $params);
    }

    private function viewUserSettings($target, $viewer) {
        csrf::getInstance()->buildToken();
        if($target != $viewer)
            return null;
        $params = array();

        $params['ufields']['data'] = $this->getUfieldData($target);

        if(system::getInstance()->post('saveprofile') && csrf::getInstance()->check()) {
            $params['form']['submit'] = true;
            $birthday_array = system::getInstance()->post('bitrhday');
            // Y-m-d
            $birthday_string = "0000-00-00";
            $nick = system::getInstance()->nohtml(system::getInstance()->post('nickname'));
            $phone = system::getInstance()->post('phone');
            $sex = system::getInstance()->post('sex');
            $webpage = system::getInstance()->nohtml(system::getInstance()->post('website'));
            // [old, new, repeat_new]
            $password_array = array(system::getInstance()->post('oldpwd'), system::getInstance()->post('newpwd'), system::getInstance()->post('renewpwd'));
            $password = user::getInstance()->get('pass');
            // analyse input data
            if ($birthday_array['year'] >= (date('Y')-125) && $birthday_array['year'] <= date('Y') && checkdate($birthday_array['month'], $birthday_array['day'], $birthday_array['year'])) {
                $birthday_string = $birthday_array['year'] . "-" . $birthday_array['month'] . "-" . $birthday_array['day'];
            }
            if (strlen($nick) < 1) {
                $nick = user::getInstance()->get('nick');
            }
            if (!system::getInstance()->validPhone($phone) && system::getInstance()->length($phone) > 0) {
                $phone = user::getInstance()->get('phone');
            }
            if (!system::getInstance()->isInt($sex) || $sex < 0 || $sex > 2) {
                $sex = user::getInstance()->get('sex');
            }
            if (!filter_var($webpage, FILTER_VALIDATE_URL) && system::getInstance()->length($webpage) > 0) {
                $webpage = user::getInstance()->get('webpage');
            }
            // if new password is setted - validate
            if (system::getInstance()->validPasswordLength($password_array) && system::getInstance()->doublemd5($password_array[0]) === $password && $password_array[1] === $password_array[2] && $password_array[0] != $password_array[1]) {
                $password = system::getInstance()->doublemd5($password_array[1]);
                $params['form']['pass_changed'] = true;
                // save logs
                $log_params = array(
                    'ip' => system::getInstance()->getRealIp()
                );
                user::getInstance()->putLog($target, 'profile.changepass', $log_params, 'Change profile password');
            }
            // prepare custom ufields data
            $ufield_save = unserialize(user::getInstance()->get('ufields', $target)); // default data
            foreach($params['ufields']['data'] as $allow_ufield) {
                if($allow_ufield['type'] == 'text') {
                    $post_ufield = system::getInstance()->nohtml(system::getInstance()->post('ufield_' . $allow_ufield['id']));
                    if(system::getInstance()->length($post_ufield) > 0) {
                        // check preg_match rules
                        $checked = null;
                        if($allow_ufield['reg_cond'] == '1') { // direct: if(preg_match(cond)). can be ternar shortly, but hardest for understand
                            $checked = preg_match($allow_ufield['reg_exp'], $post_ufield);
                        } else { // exclude: if(!preg_match(cond))
                            $checked = !preg_match($allow_ufield['reg_exp'], $post_ufield);
                        }

                        if($checked) {
                            $ufield_save[$allow_ufield['id']] = array(
                                'type' => 'text',
                                'data' => $post_ufield
                            );
                        }
                    } else { // user remove data
                        $ufield_save[$allow_ufield['id']] = null; // override data
                    }
                } elseif($allow_ufield['type'] == 'img') {
                    $post_ufield = $_FILES['ufield_' . $allow_ufield['id']];
                    if($post_ufield != null && $post_ufield['size'] > 0 && $post_ufield['error'] == 0) { // sounds like isset file
                        $object = extension::getInstance()->call(extension::TYPE_HOOK, 'file');
                        if(is_object($object)) {
                            $upload_dir = '/user/ufield/' . $allow_ufield['id'] . '/';
                            $upload_name = $object->uploadResizedImage($upload_dir, $post_ufield, $allow_ufield['img_dx'], $allow_ufield['img_dy']);
                            if($upload_name != null) {
                                $ufield_save[$allow_ufield['id']] = array(
                                    'type' => 'img',
                                    'data' => $upload_dir . $upload_name
                                );
                            }
                        }
                    }
                } elseif($allow_ufield['type'] == 'link') {
                    $post_ufield = system::getInstance()->nohtml(system::getInstance()->post('ufield_' . $allow_ufield['id']));
                    if(system::getInstance()->length($post_ufield) > 0) {
                        // validate url via domain
                        $parse_url = parse_url($post_ufield);
                        if($parse_url['host'] != null && $parse_url['host'] == $allow_ufield['domain']) {
                            $ufield_save[$allow_ufield['id']] = array(
                                'type' => 'link',
                                'data' => $post_ufield
                            );
                        }
                    } else { // user remove data
                        $ufield_save[$allow_ufield['id']] = null; // override data
                    }
                }
            }

            $ufield_save = serialize($ufield_save);
            $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user a
            INNER JOIN ".property::getInstance()->get('db_prefix')."_user_custom b USING(id)
            SET a.nick = ?, a.pass = ?, b.birthday = ?, b.sex = ?, b.phone = ?, b.webpage = ?, b.ufields = ? WHERE a.id = ?");
            $stmt->bindParam(1, $nick, \PDO::PARAM_STR);
            $stmt->bindParam(2, $password, \PDO::PARAM_STR, 32);
            $stmt->bindParam(3, $birthday_string, \PDO::PARAM_STR);
            $stmt->bindParam(4, $sex, \PDO::PARAM_INT);
            $stmt->bindParam(5, $phone, \PDO::PARAM_STR);
            $stmt->bindParam(6, $webpage, PDO::PARAM_STR);
            $stmt->bindParam(7, $ufield_save, \PDO::PARAM_STR);
            $stmt->bindParam(8, $target, \PDO::PARAM_INT);
            $stmt->execute();
            user::getInstance()->overload($target);
            $params['ufields']['data'] = $this->getUfieldData($target); // refresh data
        }
        list($birth_year, $birth_month, $birth_day) = explode("-", user::getInstance()->get('birthday'));
        $params['settings'] = array(
            'current_year' => date('Y'),
            'user_birth_year' => $birth_year,
            'user_birth_month' => $birth_month,
            'user_birth_day' => $birth_day,
            'user_name' => user::getInstance()->get('nick', $target),
            'user_sex' => user::getInstance()->get('sex', $target),
            'user_phone' => user::getInstance()->get('phone', $target),
            'user_website' => user::getInstance()->get('webpage', $target)
        );
        return $this->viewUserProfileHeader($target, $viewer, $params);
    }

    private function viewUserAvatarOptions($target, $viewer) {
        if($target != $viewer)
            return null;
        $params = array();
        if(system::getInstance()->post('loadavatar')) {
            $params['avatar']['submit'] = true;
            $file = $_FILES['avatarupload'];
            $upload = extension::getInstance()->call(extension::TYPE_HOOK, 'file')->uploadAvatar($file);
            if($upload) {
                $params['avatar']['success'] = true;
            } else {
                $params['avatar']['fail'] = true;
            }
        }
        return $this->viewUserProfileHeader($target, $viewer, $params);
    }

    private function viewUserFriendDelete($target, $viewer) {
        if($target != $viewer)
            return null;
        $params = array();
        $way = router::getInstance()->shiftUriArray();
        $friend_id = (int)$way[3];
        if($target != $viewer || !$this->inFriendsWith($target, $friend_id) || !user::getInstance()->exists($friend_id))
            return null;
        // action submited
        if(system::getInstance()->post('delete')) {
            // delete from self list
            $friend_list = user::getInstance()->get('friend_list', $target);
            $new_friend_array = system::getInstance()->valueUnsetInArray($friend_id, system::getInstance()->altexplode(',', $friend_list));
            $new_friend_list = system::getInstance()->altimplode(',', $new_friend_array);
            $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user_custom SET friend_list = ? WHERE id = ?");
            $stmt->bindParam(1, $new_friend_list, PDO::PARAM_STR);
            $stmt->bindParam(2, $target, PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            // delete from friend list
            $tfriend_list = user::getInstance()->get('friend_list', $friend_id);
            $new_tfriend_array = system::getInstance()->valueUnsetInArray($target, system::getInstance()->altexplode(',', $tfriend_list));
            $new_tfriend_list = system::getInstance()->altimplode(',', $new_tfriend_array);
            $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user_custom SET friend_list = ? WHERE id = ?");
            $stmt->bindParam(1, $new_tfriend_list, PDO::PARAM_STR);
            $stmt->bindParam(2, $friend_id, PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            system::getInstance()->redirect('/user/id'.$target);
        }
        $params['friend']['delete'] = array(
            'user_id' => $friend_id,
            'user_name' => user::getInstance()->get('nick', $friend_id),
            'user_avatar' => user::getInstance()->buildAvatar('small', $friend_id)
        );
        return $this->viewUserProfileHeader($target, $viewer, $params);
    }

    private function viewUserFriendDeny($target, $viewer) {
        if($target != $viewer)
            return null;
        $way = router::getInstance()->shiftUriArray();
        $requester_id = (int)$way[3];
        if($target != $viewer || !$this->inFriendRequestWith($target, $way[3]) || !user::getInstance()->exists($way[3]))
            return null;
        $request_array = explode(",", user::getInstance()->get('friend_request'));
        $new_request_list = system::getInstance()->altimplode(",", system::getInstance()->valueUnsetInArray($requester_id, $request_array));
        $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user_custom SET friend_request = ? WHERE id = ?");
        $stmt->bindParam(1, $new_request_list, PDO::PARAM_STR);
        $stmt->bindParam(2, $target, PDO::PARAM_INT);
        $stmt->execute();
        system::getInstance()->redirect('/user/id'.$target.'/friends/list');
        return null;
    }

    private function viewUserFriendAccept($target, $viewer) {
        if($target != $viewer)
            return null;
        $way = router::getInstance()->shiftUriArray();
        $requester_id = (int)$way[3];
        if($target != $viewer || !$this->inFriendRequestWith($target, $way[3]) || !user::getInstance()->exists($way[3]))
            return null;
        $request_array = explode(",", user::getInstance()->get('friend_request', $target));
        $friend_array = explode(",", user::getInstance()->get('friend_list', $target));
        if(in_array($requester_id, $request_array)) {
            // not in friend list
            if (!in_array($requester_id, $friend_array)) {
                // add to friend list and remove from request list
                $new_request_array = system::getInstance()->valueUnsetInArray($requester_id, $request_array);
                $new_request_list = system::getInstance()->altimplode(",", $new_request_array);
                $new_friend_array = system::getInstance()->arrayAdd($requester_id, $friend_array);
                $new_friend_list = system::getInstance()->altimplode(",", $new_friend_array);
                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user_custom SET friend_list = ?, friend_request = ? WHERE id = ?");
                $stmt->bindParam(1, $new_friend_list, PDO::PARAM_STR);
                $stmt->bindParam(2, $new_request_list, PDO::PARAM_STR);
                $stmt->bindParam(3, $target, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                // also add to friend list for requester
                $requester_friendarray = explode(",", user::getInstance()->get('friend_list', $requester_id));
                $requester_friendarray = system::getInstance()->arrayAdd($target, $requester_friendarray);
                $requester_new_friendlist = system::getInstance()->altimplode(",", $requester_friendarray);
                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user_custom SET friend_list = ? WHERE id = ?");
                $stmt->bindParam(1, $requester_new_friendlist, PDO::PARAM_STR);
                $stmt->bindParam(2, $requester_id, PDO::PARAM_INT);
                $stmt->execute();
            } // how can this happend - always in friends and make request to friends again? cleanup
            else {
                $new_request_array = system::getInstance()->valueUnsetInArray($requester_id, $request_array);
                $new_request_list = system::getInstance()->altimplode(",", $new_request_array);
                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user_custom SET friend_request = ? WHERE id = ?");
                $stmt->bindParam(1, $new_request_list, PDO::PARAM_STR);
                $stmt->bindParam(2, $target, PDO::PARAM_INT);
                $stmt->execute();
            }
            system::getInstance()->redirect("/user/id" . $target . "/friends/list");
        }
        return null;
    }

    private function viewUserFriendRequest($target, $viewer) {
        if($target != $viewer)
            return null;
        $params = array();
        $request_array = system::getInstance()->altexplode(',', user::getInstance()->get('friend_request', $target));
        user::getInstance()->listload($request_array);
        foreach($request_array as $request_id) {
            $params['friend']['request'][] = array(
                'user_id' => $request_id,
                'user_name' => user::getInstance()->get('nick', $request_id),
                'user_avatar' => user::getInstance()->buildAvatar('small', $request_id)
            );
        }
        return $this->viewUserProfileHeader($target, $viewer, $params);
    }

    private function viewUserFriendlist($target, $viewer) {
        $way = router::getInstance()->shiftUriArray();
        $params = array();
        $friend_array = system::getInstance()->altexplode(',', user::getInstance()->get('friend_list', $target));
        $friend_perpage = extension::getInstance()->getConfig('friend_page_count', 'user', 'components', 'int');
        $index = (int)$way[3];
        $item_index = $index * $friend_perpage;
        $friend_array_current = array_slice($friend_array, $item_index, $friend_perpage+$item_index);
        user::getInstance()->listload($friend_array_current);
        foreach($friend_array_current as $friend_id) {
            $params['friend']['list'][] = array(
                'user_id' => $friend_id,
                'user_name' => user::getInstance()->get('nick', $friend_id),
                'user_avatar' => user::getInstance()->buildAvatar('small', $friend_id)
            );
        }
        $params['friend']['index'] = $index;
        $params['friend']['total'] = $this->totalFriendsCount($target);
        $params['friend']['perpage'] = $friend_perpage;
        return $this->viewUserProfileHeader($target, $viewer, $params);
    }

    private function viewUserBookmarks($target, $viewer) {
        $params = array();
        $way = router::getInstance()->shiftUriArray();
        $index = (int)$way[2];
        $marks_perpage = extension::getInstance()->getConfig('marks_post_count', 'user', 'components');
        $index_marker = $marks_perpage * $index;
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_bookmarks WHERE target = ? ORDER BY id DESC LIMIT ?, ?");
        $stmt->bindParam(1, $target, PDO::PARAM_INT);
        $stmt->bindParam(2, $index_marker, PDO::PARAM_INT);
        $stmt->bindParam(3, $marks_perpage, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($result as $item) {
            $params['bookmarks'][] = array(
                'title' => $item['title'],
                'link' => $item['href']
            );
        }
        $params['wall']['bookindex'] = $index;
        $params['wall']['bookperpage'] = $marks_perpage;
        $params['wall']['maxbook'] = $this->getMarkCout($target);
        return $this->viewUserProfileHeader($target, $viewer, $params);
    }

    private function viewUserWall($target, $viewer) {
        $params = array();
        if(system::getInstance()->post('wall_post')) {
            // add wall post
            $params['wall']['dopost'] = true;
            $time = time();
            $message = system::getInstance()->nohtml(system::getInstance()->post('wall_text'));
            if (system::getInstance()->length($message) > 1 && ($this->inFriendsWith($target, $viewer) || $target == $viewer)) {
                $stmt = database::getInstance()->con()->prepare("SELECT time FROM ".property::getInstance()->get('db_prefix')."_user_wall WHERE caster = ? AND target = ? ORDER BY id DESC LIMIT 1");
                $stmt->bindParam(1, $viewer, PDO::PARAM_INT);
                $stmt->bindParam(2, $target, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetch();
                $time_last_message = $res['time'];
                $stmt = null;
                if (($time - $time_last_message) >= extension::getInstance()->getConfig('wall_post_delay', 'user', 'components', 'int')) {
                    $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_user_wall (target, caster, message, time) VALUES (?, ?, ?, ?)");
                    $stmt->bindParam(1, $target, PDO::PARAM_INT);
                    $stmt->bindParam(2, $viewer, PDO::PARAM_INT);
                    $stmt->bindParam(3, $message, PDO::PARAM_STR);
                    $stmt->bindParam(4, $time, PDO::PARAM_INT);
                    $stmt->execute();
                    $stream = extension::getInstance()->call(extension::TYPE_COMPONENT, 'stream');
                    if(is_object($stream))
                        $stream->add('user.wallpost', $viewer, property::getInstance()->get('url').'/user/id'.$target, $message);
                } else {
                    $params['wall']['time_limit'] = true;
                }
            }
        }
        if(system::getInstance()->post('requestfriend') && !$this->inFriendRequestWith($target, $viewer)) {
            $current_friendrequest_list = user::getInstance()->get('friend_request', $target);
            if (strlen($current_friendrequest_list) < 1) {
                $current_friendrequest_list .= $viewer;
            } else {
                $current_friendrequest_list .= "," . $viewer;
            }
            $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user_custom SET friend_request = ? WHERE id = ?");
            $stmt->bindParam(1, $current_friendrequest_list, PDO::PARAM_STR);
            $stmt->bindParam(2, $target, PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            user::getInstance()->overload($target);
        }
        $way = router::getInstance()->shiftUriArray();
        $index = (int)$way[2];
        $wall_count = extension::getInstance()->getConfig('wall_post_count', 'user', 'components', 'int');
        $limit = $index * $wall_count;
        // personal info
        $params['wall']['reg_date'] = user::getInstance()->get('regdate', $target);
        $params['wall']['birthday'] = user::getInstance()->get('birthday', $target);
        $params['wall']['sex'] = $this->intToSex(user::getInstance()->get('sex', $target));
        $params['wall']['phone'] = user::getInstance()->get('phone', $target);
        $params['wall']['webpage'] = user::getInstance()->get('webpage', $target);
        // wall posts index & pagination
        $params['wall']['postindex'] = $index;
        $params['wall']['postperpage'] = $wall_count;
        $params['wall']['maxindex'] = $this->getWallPostCount($target);

        // TODO: add custom fields there
        $params['wall']['ufields'] = $this->getUfieldData($target);

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_wall WHERE target = ? ORDER by time DESC LIMIT ?, ?");
        $stmt->bindParam(1, $target, PDO::PARAM_INT);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->bindParam(3, $wall_count, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        user::getInstance()->listload(system::getInstance()->extractFromMultyArray('caster', $result));
        foreach($result as $item) {
            $params['post'][] = array(
                'caster_id' => $item['caster'],
                'caster_name' => user::getInstance()->get('nick', $item['caster']),
                'caster_avatar' => user::getInstance()->buildAvatar('small', $item['caster']),
                'message' => $item['message'],
                'time' => system::getInstance()->toDate($item['time'], 'h'),
                'id' => $item['id']
            );
        }
        return $this->viewUserProfileHeader($target, $viewer, $params);
    }

    private function viewUserList() {
        $way = router::getInstance()->shiftUriArray();
        meta::getInstance()->add('title', language::getInstance()->get('seo_title_userlist'));
        $index = $way[0] ?: 0;
        $usercount_on_page = extension::getInstance()->getConfig('userlist_count', 'user', 'components', 'int');
        $totalUsers = $this->totalUserCount();
        $params['statistic'] = array(
            'total' => $totalUsers,
            'male' => $this->maleUserCount(),
            'female' => $this->femaleUserCount()
        );
        $limit_start = $index * $usercount_on_page;
        $stmt = database::getInstance()->con()->prepare("SELECT a.id, a.nick, b.regdate FROM ".property::getInstance()->get('db_prefix')."_user a,
        ".property::getInstance()->get('db_prefix')."_user_custom b WHERE a.id = b.id AND a.aprove = 0 ORDER BY a.id DESC LIMIT ?, ?");
        $stmt->bindParam(1, $limit_start, PDO::PARAM_INT);
        $stmt->bindParam(2, $usercount_on_page, PDO::PARAM_INT);
        $stmt->execute();
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $params['user'][] = array(
                'user_id' => $result['id'],
                'user_name' => $result['nick'],
                'user_avatar' => user::getInstance()->buildAvatar('small', $result['id']),
                'user_regdate' => system::getInstance()->toDate($result['regdate'], 'd')
            );
        }
        $stmt = null;
        $params['pagination'] = template::getInstance()->showFastPagination($index, $usercount_on_page, $totalUsers, 'user');
        $visit_time = time() - 15 * 60;
        $stmt = database::getInstance()->con()->prepare("SELECT a.reg_id, a.cookie, b.* FROM ".property::getInstance()->get('db_prefix')."_statistic a,
        ".property::getInstance()->get('db_prefix')."_user b WHERE a.`time` >= ? AND a.reg_id > 0 AND a.reg_id = b.id GROUP BY a.reg_id, a.cookie");
        $stmt->bindParam(1, $visit_time, PDO::PARAM_INT);
        $stmt->execute();
        while($onlineNow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $params['online'][] = array(
                'user_id' => $onlineNow['reg_id'],
                'user_name' => $onlineNow['nick']
            );
        }
        $stmt = null;
        return template::getInstance()->twigRender('components/user/list.tpl', array('local' => $params));
    }

    private function viewOpenId() {
        if(user::getInstance()->get('id') > 0 || !extension::getInstance()->getConfig('use_openid', 'user', 'components', 'boolean'))
            return null;
        session_start();
        $token = system::getInstance()->post('token');
        if($token != null) {
            $query = file_get_contents('http://loginza.ru/api/authinfo?token='.$token);
            $result = json_decode($query, true);
            $openidIdentifity = $result['identity'];
            if($openidIdentifity == null || system::getInstance()->length($openidIdentifity) < 1)
                system::getInstance()->redirect('/user/login.html');
            // did this OpenID is always in db?
            $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*), email, pass, id FROM ".property::getInstance()->get('db_prefix')."_user WHERE openid = ?");
            $stmt->bindParam(1, $openidIdentifity, PDO::PARAM_STR);
            $stmt->execute();
            $checkRes = $stmt->fetch();
            $stmt = null;
            // user founded
            if($checkRes[0] == 1) {
                $dbemail = $checkRes['email'];
                $md5token = system::getInstance()->md5random();
                $nixtime = time();
                $user_ip = system::getInstance()->getRealIp();
                $stmt2 = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user SET token = ?, token_start = ?, token_ip = ? WHERE openid = ?");
                $stmt2->bindParam(1, $md5token, PDO::PARAM_STR, 32);
                $stmt2->bindParam(2, $nixtime, PDO::PARAM_INT);
                $stmt2->bindParam(3, $user_ip, PDO::PARAM_STR);
                $stmt2->bindParam(4, $openidIdentifity, PDO::PARAM_STR);
                $stmt2->execute();

                $user_id = $checkRes['id'];
                $log_params = array(
                    'ip' => $user_ip,
                    'openid' => $openidIdentifity
                );
                user::getInstance()->putLog($user_id, 'profile.openidauth', $log_params, 'Success auth on profile via openid');

                setcookie('person', $dbemail, null, '/', null, null, true);
                setcookie('token', $md5token, null, '/', null, null, true);
                system::getInstance()->redirect();
            } else {
                // first auth with this OpenID
                $_SESSION['openid_token'] = $token;
                $_SESSION['openid_person'] = $openidIdentifity;
                $params['openid'] = array(
                    'login' => $result['nickname'],
                    'email' => $result['email'],
                    'name' => $result['name']['first_name'],
                    'session' => $token
                );
                return template::getInstance()->twigRender('components/user/auth/openid.tpl', array('local' => $params));
            }
        } else {
            if(system::getInstance()->post('submit') && $_SESSION['openid_token'] != null && $_SESSION['openid_token'] == system::getInstance()->post('openid_token')) {
                // user add missed information
                $params = array();
                $token = $_SESSION['openid_token'];
                $nickname = system::getInstance()->nohtml(system::getInstance()->post('nick'));
                $email = system::getInstance()->nohtml(system::getInstance()->post('email'));
                $login = system::getInstance()->nohtml(system::getInstance()->post('login'));
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $params['notify']['email_invalid'] = true;
                }
                if (user::getInstance()->mailIsExists($email)) {
                    $params['notify']['email_exist'] = true;
                }
                if (user::getInstance()->loginIsExists($login)) {
                    $params['notify']['login_exist'] = true;
                }
                if (strlen($nickname) < 3 || strlen($nickname) > 64) {
                    $params['notify']['nick_wronglength'] = true;
                }
                if(sizeof($params['notify']) == 0) {
                    $validate = 0;
                    $pwd = system::getInstance()->randomString(rand(8,12));
                    $md5pwd = system::getInstance()->doublemd5($pwd);
                    $rand_token = system::getInstance()->md5random();
                    $time = time();
                    $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_user (`login`, `email`, `nick`, `pass`, `aprove`, `openid`, `token`, `token_start`) VALUES (?,?,?,?,?,?,?,?)");
                    $stmt->bindParam(1, $login, PDO::PARAM_STR, 64);
                    $stmt->bindParam(2, $email, PDO::PARAM_STR);
                    $stmt->bindParam(3, $nickname, PDO::PARAM_STR);
                    $stmt->bindParam(4, $md5pwd, PDO::PARAM_STR, 32);
                    $stmt->bindParam(5, $validate, PDO::PARAM_STR, 32);
                    $stmt->bindParam(6, $_SESSION['openid_person'], PDO::PARAM_STR);
                    $stmt->bindParam(7, $rand_token, PDO::PARAM_STR, 32);
                    $stmt->bindParam(8, $time, PDO::PARAM_INT);
                    $stmt->execute();
                    $user_obtained_id = database::getInstance()->con()->lastInsertId();
                    $stmt = null;
                    $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_user_custom (`id`) VALUES (?)");
                    $stmt->bindParam(1, $user_obtained_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                    $openid_desc_mail = language::getInstance()->get('usercontrol_openid_reg_mail_desc').$pwd;
                    $link = '<a href="'.property::getInstance()->get('url').'">'.language::getInstance()->get('usercontrol_openid_reg_link').'</a>';
                    extension::getInstance()->call(extension::TYPE_HOOK, 'mail')->send($email,
                        language::getInstance()->get('usercontrol_openid_reg_mail_title'),
                        template::getInstance()->twigRender('mail.tpl',
                            array('mail' =>
                                array(
                                    'title' => language::getInstance()->get('usercontrol_openid_reg_mail_title'),
                                    'description' => $openid_desc_mail,
                                    'text' => $link,
                                    'footer' => language::getInstance()->get('usercontrol_reg_mail_footer')
                                )
                            )
                        ),
                        $nickname
                    );
                    $stream = extension::getInstance()->call(extension::TYPE_COMPONENT, 'stream');
                    if(is_object($stream))
                        $stream->add('user.register', $user_obtained_id, property::getInstance()->get('url').'/user/id'.$user_obtained_id);
                    setcookie('person', $email, null, '/', null, null, true);
                    setcookie('token', $rand_token, null, '/', null, null, true);
                    system::getInstance()->redirect();
                } else {
                    $params['openid'] = array(
                        'login' => $login,
                        'email' => $email,
                        'name' => $nickname,
                        'session' => $token
                    );
                    return template::getInstance()->twigRender('components/user/auth/openid.tpl', array('local' => $params));
                }
            } else {
                system::getInstance()->redirect('/user/login.html');
            }
        }
        return null;
    }

    private function viewRecovery() {
        if(user::getInstance()->get('id') > 0)
            return null;
        $params = array();
        $params['cfg']['captcha_full'] = extension::getInstance()->getConfig('captcha_type', 'captcha', 'hooks') == "recaptcha" ? true : false;
        $params['captcha'] = extension::getInstance()->call(extension::TYPE_HOOK, 'captcha')->show();
        if(system::getInstance()->post('submit')) {
            $params['submit'] = true;
            $email = system::getInstance()->post('email');
            if (!extension::getInstance()->call(extension::TYPE_HOOK, 'captcha')->validate(system::getInstance()->post('captcha'))) {
                $params['notify']['captcha_error'] = true;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $params['notify']['email_typewrong'] = true;
            }
            if (sizeof($params['notify']) == 0) {
                $new_password = system::getInstance()->randomString(rand(8, 12));
                $hashed_password = system::getInstance()->doublemd5($new_password);
                $hash = system::getInstance()->randomSecureString128(); // 128char
                $userid = user::getInstance()->getIdByEmail($email);
                if ($userid < 1) {
                    $params['notify']['email_notexist'] = true;
                } else {
                    // make recovery row in db table
                    $nickname = user::getInstance()->get('nick', $userid);
                    $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_user_recovery (`password`, `hash`, `userid`) VALUES (?, ?, ?)");
                    $stmt->bindParam(1, $hashed_password, PDO::PARAM_STR, 32);
                    $stmt->bindParam(2, $hash, PDO::PARAM_STR);
                    $stmt->bindParam(3, $userid, PDO::PARAM_INT);
                    $stmt->execute();
                    $request_id = database::getInstance()->con()->lastInsertId();
                    $stmt = null;

                    // save request to logs
                    $log_params = array(
                        'ip' => system::getInstance()->getRealIp()
                    );
                    user::getInstance()->putLog($userid, 'profile.restore', $log_params, 'Request profile restore');

                    $recovery_link = "<a href=".property::getInstance()->get('url') . '/user/recovery/' . $request_id . '/' . $hash.">".language::getInstance()->get("usercontrol_mail_link_text")."</a>";
                    extension::getInstance()->call(extension::TYPE_HOOK, 'mail')->send($email,
                        language::getInstance()->get('usercontrol_recovery_mail_title'),
                        template::getInstance()->twigRender('mail.tpl',
                            array('mail' =>
                                array(
                                    'title' => language::getInstance()->get('usercontrol_recovery_mail_title'),
                                    'description' => language::getInstance()->get('usercontrol_recovery_mail_description').$new_password,
                                    'text' => $recovery_link,
                                    'footer' => language::getInstance()->get('usercontrol_recovery_mail_footer')
                                )
                            )
                        ),
                        $nickname
                    );
                    $params['notify']['success'] = true;
                }
            }
        }
        return template::getInstance()->twigRender('components/user/auth/recovery.tpl', array('local' => $params));
    }

    private function viewAproveRecovery() {
        $way = router::getInstance()->shiftUriArray();
        $request_id = (int)$way[1];
        $hash = $way[2];
        $stmt = database::getInstance()->con()->prepare("SELECT userid,password FROM ".property::getInstance()->get('db_prefix')."_user_recovery WHERE id =? AND hash = ?");
        $stmt->bindParam(1, $request_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $hash, PDO::PARAM_STR, 128);
        $stmt->execute();
        if($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $userid = $res['userid'];
            $newpwd = $res['password'];
            $stmt = null;
            $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user SET pass = ? WHERE id = ?");
            $stmt->bindParam(1, $newpwd, PDO::PARAM_STR, 32);
            $stmt->bindParam(2, $userid, PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            // unlink
            $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_user_recovery WHERE id = ?");
            $stmt->bindParam(1, $request_id, PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            return template::getInstance()->twigRender('components/user/auth/recovery_notify.tpl', array());
        }
        $stmt = null;
        return null;
    }

    private function viewAprove() {
        $way = router::getInstance()->shiftUriArray();
        $hash = $way[1];
        if(system::getInstance()->length($hash) < 32)
            return null;
        $stmt = database::getInstance()->con()->prepare("SELECT id FROM ".property::getInstance()->get('db_prefix')."_user WHERE aprove = ?");
        $stmt->bindParam(1, $hash, \PDO::PARAM_STR);
        $stmt->execute();

        if($stmt->rowCount() == 1) {
            $res = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stream = extension::getInstance()->call(extension::TYPE_COMPONENT, 'stream');
            if(is_object($stream))
                $stream->add('user.register', $res['id'], property::getInstance()->get('url').'/user/id'.$res['id']);
        } else {
            $stmt = null;
            return null;
        }
        $stmt = null;

        $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user SET aprove = 0 WHERE aprove = ?");
        $stmt->bindParam(1, $hash, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() == 1) {
            return template::getInstance()->twigRender('components/user/auth/aprove.tpl', array());
        }
        $stmt = null;
        return null;
    }

    private function viewRegister() {
        if(user::getInstance()->get('id') > 0)
            return null;
        $params = array();
        $params['cfg']['use_captcha'] = extension::getInstance()->getConfig('register_captcha', 'user', 'components', 'boolean');
        $params['cfg']['captcha_full'] = extension::getInstance()->getConfig('captcha_type', 'captcha', 'hooks') == "recaptcha" ? true : false;
        $params['cfg']['use_openid'] = extension::getInstance()->getConfig('use_openid', 'user', 'components', 'boolean');
        $params['captcha'] = extension::getInstance()->call(extension::TYPE_HOOK, 'captcha')->show();
        if(system::getInstance()->post('submit')) {
            $params['submit'] = true;
            $notify = null;
            $nickname = system::getInstance()->nohtml(system::getInstance()->post('nick'));
            $email = system::getInstance()->post('email');
            $login = system::getInstance()->nohtml(system::getInstance()->post('login'));
            $pass = system::getInstance()->post('password');
            $md5pwd = system::getInstance()->doublemd5($pass);
            if ($params['cfg']['use_captcha'] && !extension::getInstance()->call(extension::TYPE_HOOK, 'captcha')->validate(system::getInstance()->post('captcha'))) {
                $params['notify']['captcha_error'] = true;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $params['notify']['email_error'] = true;
            }
            if (!system::getInstance()->validPasswordLength($pass)) {
                $params['notify']['passlength_error'] = true;
            }
            if (user::getInstance()->mailIsExists($email)) {
                $params['notify']['mail_exist'] = true;
            }
            if (user::getInstance()->loginIsExists($login)) {
                $params['notify']['login_exist'] = true;
            }
            if (strlen($nickname) < 3 || strlen($nickname) > 64) {
                $params['notify']['nicklength_error'] = true;
            }
            if (sizeof($params['notify']) == 0) {
                $aprove_reg_from_email = extension::getInstance()->getConfig('register_aprove', 'user', 'components', 'boolean');
                // max is 128chars
                // this can take resources, but if we use static it can be hacked.
                $validate = 0;
                if ($aprove_reg_from_email) {
                    $validate = system::getInstance()->randomSecureString128();
                }
                $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_user (`login`, `email`, `nick`, `pass`, `aprove`) VALUES (?,?,?,?,?)");
                $stmt->bindParam(1, $login, PDO::PARAM_STR, 64);
                $stmt->bindParam(2, $email, PDO::PARAM_STR);
                $stmt->bindParam(3, $nickname, PDO::PARAM_STR);
                $stmt->bindParam(4, $md5pwd, PDO::PARAM_STR, 32);
                $stmt->bindParam(5, $validate, PDO::PARAM_STR, 32);
                $stmt->execute();
                $user_obtained_id = database::getInstance()->con()->lastInsertId();
                $stmt = null;
                $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_user_custom (`id`) VALUES (?)");
                $stmt->bindParam(1, $user_obtained_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                if($aprove_reg_from_email) {
                    $link = '<a href="' . property::getInstance()->get('url') . '/user/aprove/' . $validate . '">' . language::getInstance()->get('usercontrol_reg_mail_aprove_link_text') . ' - ' . property::getInstance()->get('url') . '</a>';
                    extension::getInstance()->call(extension::TYPE_HOOK, 'mail')->send($email,
                        language::getInstance()->get('usercontrol_reg_mail_title'),
                        template::getInstance()->twigRender('mail.tpl',
                            array('mail' =>
                                array(
                                    'title' => language::getInstance()->get('usercontrol_reg_mail_title'),
                                    'description' => language::getInstance()->get('usercontrol_reg_mail_description'),
                                    'text' => $link,
                                    'footer' => language::getInstance()->get('usercontrol_reg_mail_footer')
                                )
                            )
                        ),
                        $nickname
                    );
                    $params['notify']['aprove_sended'] = true;
                } else {
                    $stream = extension::getInstance()->call(extension::TYPE_COMPONENT, 'stream');
                    if(is_object($stream))
                        $stream->add('user.register', $user_obtained_id, property::getInstance()->get('url').'/user/id'.$user_obtained_id);
                    $params['notify']['success'] = true;
                }
            }
        }
        return template::getInstance()->twigRender('components/user/auth/register.tpl', array('local' => $params));
    }

    /**
     * Show login form and process post action
     */
    public function viewLogin() {
        if(user::getInstance()->get('id') > 0) // its always authorised user, no reason to display form
            return null;
        $params = array();
        if(system::getInstance()->post('submit')) { // form is submited, try to check input params
            $params['submit'] = true;
            $loginoremail = system::getInstance()->post('email');
            if (extension::getInstance()->getConfig('login_captcha', 'user', 'components', 'boolean') && !extension::getInstance()->call(extension::TYPE_HOOK, 'captcha')->validate(system::getInstance()->post('captcha'))) {
                $params['notify']['captcha_error'] = true;
            }
            if (!filter_var($loginoremail, FILTER_VALIDATE_EMAIL) && (strlen($loginoremail) < 3 || !system::getInstance()->isLatinOrNumeric($loginoremail))) {
                $params['notify']['login_error'] = true;
            }
            if (strlen(system::getInstance()->post('password')) < 4 || strlen(system::getInstance()->post('password')) > 32) { // if length > 32 sym. can collision
                $params['notify']['pass_error'] = true;
            }
            if(sizeof($params['notify']) == 0) { // no error added
                $md5pwd = system::getInstance()->doublemd5(system::getInstance()->post('password'));
                $user_ip = system::getInstance()->getRealIp();
                $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user WHERE (email = ? OR login = ?) AND pass = ?");
                $stmt->bindParam(1, $loginoremail, PDO::PARAM_STR);
                $stmt->bindParam(2, $loginoremail, PDO::PARAM_STR);
                $stmt->bindParam(3, $md5pwd, PDO::PARAM_STR, 32);
                $stmt->execute();
                if ($stmt->rowCount() == 1) {
                    $md5token = system::getInstance()->md5random();
                    $nixtime = time();
                    $stmt2 = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user SET token = ?, token_start = ?, token_ip = ? WHERE (email = ? OR login = ?) AND pass = ?");
                    $stmt2->bindParam(1, $md5token, PDO::PARAM_STR, 32);
                    $stmt2->bindParam(2, $nixtime, PDO::PARAM_INT);
                    $stmt2->bindParam(3, $user_ip, PDO::PARAM_STR);
                    $stmt2->bindParam(4, $loginoremail, PDO::PARAM_STR);
                    $stmt2->bindParam(5, $loginoremail, PDO::PARAM_STR);
                    $stmt2->bindParam(6, $md5pwd, PDO::PARAM_STR, 32);
                    $stmt2->execute();
                    $stmt2 = null;

                    // save auth log
                    $ures = $stmt->fetch(\PDO::FETCH_ASSOC);
                    $user_id = $ures['id'];
                    $log_params = array(
                        'ip' => $user_ip
                    );
                    user::getInstance()->putLog($user_id, 'profile.auth', $log_params, 'Success auth on profile');

                    if(system::getInstance()->post('longsession') == "on") {
                        setcookie('person', $loginoremail, system::MAX_INTEGER_32, '/', null, null, true);
                        setcookie('token', $md5token, system::MAX_INTEGER_32, '/', null, null, true);
                    } else {
                        $_SESSION['person'] = $loginoremail;
                        $_SESSION['token'] = $md5token;
                    }
                    system::getInstance()->redirect();
                } else {
                    $params['notify']['wrong_data'] = true;
                }
                $stmt = null;
            }
        }
        $params['cfg']['use_captcha'] = extension::getInstance()->getConfig('login_captcha', 'user', 'components', 'boolean');
        $params['cfg']['captcha_full'] = extension::getInstance()->getConfig('captcha_type', 'captcha', 'hooks') == "recaptcha" ? true : false; // full designed captcha like ReCaptcha or only image
        $params['cfg']['use_openid'] = extension::getInstance()->getConfig('use_openid', 'user', 'components', 'boolean');
        $params['captcha'] = extension::getInstance()->call(extension::TYPE_HOOK, 'captcha')->show();

        $form = template::getInstance()->twigRender('components/user/auth/login.tpl', array('local' => $params));
        return $form;
    }

    private function viewLogout() {
        $userid = user::getInstance()->get('id');
        if($userid < 1) // no auth user have no reason to make logout
            return null;
        $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user SET token = NULL WHERE id = ?");
        $stmt->bindParam(1, $userid, PDO::PARAM_INT);
        $stmt->execute();
        $stmt = null;
        setcookie('person', null, null, '/', null, null, true);
        setcookie('token', null, null, '/', null, null, true);
        unset($_SESSION['token']);
        unset($_SESSION['person']);
        system::getInstance()->redirect();
    }

    /**
     * Get total user count
     * @return int
     */
    public function totalUserCount() {
        $stmt = database::getInstance()->con()->query("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_user WHERE aprove = 0");
        $stmt->execute();
        $rowRegisteredFetch = $stmt->fetch();
        $stmt = null;
        return $rowRegisteredFetch[0];
    }

    /**
     * Get male sex user count
     * @return int
     */
    public function maleUserCount() {
        $stmt = database::getInstance()->con()->query("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_user a,
        ".property::getInstance()->get('db_prefix')."_user_custom b WHERE a.aprove = 0 AND a.id = b.id AND b.sex = 1");
        $stmt->execute();
        $rowMaleFetch = $stmt->fetch();
        $stmt = null;
        return $rowMaleFetch[0];
    }

    /**
     * Get female sex user count
     * @return int
     */
    public function femaleUserCount() {
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_user a,
        ".property::getInstance()->get('db_prefix')."_user_custom b WHERE a.aprove = 0 AND a.id = b.id AND b.sex = 2");
        $stmt->execute();
        $rowFemaleFetch = $stmt->fetch();
        $stmt = null;
        return $rowFemaleFetch[0];
    }

    /**
     * Get total website online
     * @return int
     */
    public function getOnlineCount() {
        $timestamp = time();
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(DISTINCT(cookie)) FROM ffcms_statistic WHERE `time` >= ?");
        $stmt->bindParam(1, $timestamp, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();
        $stmt = null;
        return $res[0];
    }

    /**
     * Get only registered users online
     * @return mixed
     */
    public function getRegOnlineCount() {
        $timestamp = time();
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(DISTINCT(reg_id)) FROM ffcms_statistic WHERE `time` >= ? WHERE reg_id > 0");
        $stmt->bindParam(1, $timestamp, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();
        $stmt = null;
        return $res[0];
    }

    private function inFriendsWith($target, $viewer)
    {
        $friend_list = user::getInstance()->get('friend_list', $target);
        $friend_array = explode(",", $friend_list);
        if (in_array($viewer, $friend_array)) {
            return true;
        }
        return false;
    }

    private function inFriendRequestWith($target, $viewer)
    {
        $friend_request_list = user::getInstance()->get('friend_request', $target);
        $friend_request_array = explode(",", $friend_request_list);
        if (in_array($viewer, $friend_request_array)) {
            return true;
        }
        return false;
    }

    public function getWallPostCount($target) {
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_user_wall WHERE target = ?");
        $stmt->bindParam(1, $target, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();
        $stmt = null;
        return $res[0];
    }

    public function getMarkCout($target)
    {
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_user_bookmarks WHERE target = ?");
        $stmt->bindParam(1, $target, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();
        $stmt = null;
        return $res[0];
    }

    /**
     * Convert integer sex identify to language lexema
     * @param $int
     * @return string
     */
    public function intToSex($int)
    {
        if ($int == 1) {
            return language::getInstance()->get('usercontrol_profile_sex_man');
        } elseif ($int == 2) {
            return language::getInstance()->get('usercontrol_profile_sex_woman');
        } else {
            return language::getInstance()->get('usercontrol_profile_sex_unknown');
        }
    }

    public function totalFriendsCount($userid) {
        $flist = user::getInstance()->get('friend_list', $userid);
        $farray = system::getInstance()->altexplode(',', $flist);
        return sizeof($farray);
    }

    public function getMessageTotalRows($userid, $type)
    {
        if ($type == "in") {
            $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_user_messages WHERE `to` = ?");
            $stmt->bindParam(1, $userid, PDO::PARAM_INT);
        } elseif ($type == "out") {
            $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_user_messages WHERE `from` = ?");
            $stmt->bindParam(1, $userid, PDO::PARAM_INT);
        } else {
            $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_user_messages WHERE `to` = ? OR `from` = ?");
            $stmt->bindParam(1, $userid, PDO::PARAM_INT);
            $stmt->bindParam(2, $userid, PDO::PARAM_INT);
        }
        $stmt->execute();
        $res = $stmt->fetch();
        $stmt = null;
        return $res[0];
    }

    /**
     * Add menu item to public menu
     * @param string $link
     * @param string $text
     */
    public function addPublicMenuItem($link, $text) {
        $this->pub_menu_links[] = array(
            'link' => $link,
            'text' => $text
        );
    }

    /**
     * Add menu item to private menu
     * @param string $link
     * @param string $text
     */
    public function addPrivateMenuItem($link, $text) {
        $this->private_menu_links[] = array(
            'link' => $link,
            'text' => $text
        );
    }

    public function getUfieldData($target_id) {
        $stmt = database::getInstance()->con()->query("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_fields");
        $allFields = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;

        $ufield_user = unserialize(user::getInstance()->get('ufields', $target_id));
        $output = array();
        foreach($allFields as $ufield) {
            $title_serial = unserialize($ufield['name']);
            $params_serial = unserialize($ufield['params']);
            if($ufield['type'] == 'text') {
                $output[] = array(
                    'id' => $ufield['id'],
                    'type' => $ufield['type'],
                    'title' => $title_serial[language::getInstance()->getUseLanguage()],
                    'reg_exp' => $params_serial['regexp'],
                    'reg_cond' => $params_serial['regcond'],
                    'default' => $ufield_user[$ufield['id']]['data']
                );
            } elseif($ufield['type'] == 'img') {
                $output[] = array(
                    'id' => $ufield['id'],
                    'type' => $ufield['type'],
                    'title' => $title_serial[language::getInstance()->getUseLanguage()],
                    'img_dx' => $params_serial['dx'],
                    'img_dy' => $params_serial['dy'],
                    'default' => $ufield_user[$ufield['id']]['data']
                );
            } elseif($ufield['type'] == 'link') {
                $output[] = array(
                    'id' => $ufield['id'],
                    'type' => $ufield['type'],
                    'title' => $title_serial[language::getInstance()->getUseLanguage()],
                    'domain' => $params_serial['domain'],
                    'redirect' => $params_serial['redirect'],
                    'default' => $ufield_user[$ufield['id']]['data']
                );
            }
        }
        return $output;
    }


}