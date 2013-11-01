<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

// регистрируем компонент
if (!extension::registerPathWay(array('login', 'register', 'recovery', 'logout', 'aprove', 'user', 'message', 'settings', 'openid'), 'usercontrol')) {
    exit("Component usercontrol cannot be registered!");
}
page::setNoCache('login');
page::setNoCache('register');
page::setNoCache('recovery');
page::setNoCache('logout');
page::setNoCache('aprove');
page::setNoCache('user');
page::setNoCache('message');
page::setNoCache('settings');
page::setNoCache('openid');

class com_usercontrol_front
{
    public $hook_item_menu;
    public $hook_item_url;
    public $hook_item_settings;

    public function load()
    {
        global $engine;
        $way = $engine->page->getPathway();
        $engine->hook->before();
        $engine->rule->add('com.usercontrol.login_captcha', $engine->extension->getConfig('login_captcha', 'usercontrol', 'components', 'boolean'));
        $engine->rule->add('com.usercontrol.register_captcha', $engine->extension->getConfig('register_captcha', 'usercontrol', 'components', 'boolean'));
        $engine->rule->add('com.usercontrol.use_openid', $engine->extension->getConfig('use_openid', 'usercontrol', 'components', 'boolean'));
        $engine->rule->add('com.usercontrol.captcha_full', $engine->extension->getConfig('captcha_type', 'captcha', 'hooks') == "recaptcha" ? true : false);
        switch ($way[0]) {
            case "login":
                $this->loginComponent();
                break;
            case "register":
                $this->regComponent();
                break;
            case "recovery":
                $this->recoveryComponent();
                break;
            case "logout":
                $this->doLogOut();
                break;
            case "aprove":
                $this->doRegisterAprove();
                break;
            case "user":
                $this->profileComponent();
                break;
            case "message":
                $this->userPersonalMessage();
                break;
            case "settings":
                $this->userPersonalSettings();
                break;
            case "openid":
                $this->loginOpenId();
                break;
            default:
                break;
        }
    }

    private function loginOpenId()
    {
        global $engine;
        $token = $engine->system->post('token');
        if(!$engine->extension->getConfig('use_openid', 'usercontrol', 'components', 'boolean')) {
            $engine->page->setContentPosition('body', $engine->template->compile404());
            return;
        }
        if($token != null) {
            $query = file_get_contents('http://loginza.ru/api/authinfo?token='.$token);
            $result = json_decode($query, true);
            $openidIdentifity = $result['identity'];
            if($openidIdentifity == null || $engine->system->length($openidIdentifity) < 1)
                $engine->system->redirect('/login');
            // используется ли данный identity у пользователей ?
            $stmt = $engine->database->con()->prepare("SELECT COUNT(*), email, pass FROM {$engine->constant->db['prefix']}_user WHERE openid = ?");
            $stmt->bindParam(1, $openidIdentifity, PDO::PARAM_STR);
            $stmt->execute();
            $checkRes = $stmt->fetch();
            $stmt = null;
            // пользователь найден
            if($checkRes[0] == 1) {
                $dbemail = $checkRes['email'];
                $md5token = $engine->system->md5random();
                $nixtime = time();
                $stmt2 = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_user SET token = ?, token_start = ? WHERE openid = ?");
                $stmt2->bindParam(1, $md5token, PDO::PARAM_STR, 32);
                $stmt2->bindParam(2, $nixtime, PDO::PARAM_INT);
                $stmt2->bindParam(3, $openidIdentifity, PDO::PARAM_STR);
                $stmt2->execute();

                setcookie('person', $dbemail, null, '/', null, null, true);
                setcookie('token', $md5token, null, '/', null, null, true);
                $engine->system->redirect();
            } else {
            // это первая авторизация с этого identity
                $_SESSION['openid_token'] = $token;
                $_SESSION['openid_person'] = $openidIdentifity;
                $openid_login = $result['nickname'];
                $openid_email = $result['email'];
                $openid_pseudoname = $result['name']['first_name'];
                $theme_openid = $engine->template->get('openid', 'components/usercontrol/');
                $engine->page->setContentPosition('body', $engine->template->assign(array('openid_email', 'openid_login', 'openid_nick', 'openid_session'), array($openid_email, $openid_login, $openid_pseudoname, $token), $theme_openid));
            }
        } else {
            if($engine->system->post('submit') && $_SESSION['openid_token'] != null && $_SESSION['openid_token'] == $engine->system->post('openid_token')) {
                // пользователь добавил необходимые данные. Токен валиден.
                $notify = null;
                $token = $_SESSION['openid_token'];
                $nickname = $engine->system->nohtml($engine->system->post('nick'));
                $email = $engine->system->post('email');
                $login = $engine->system->post('login');
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_invalid_email_error'));
                }
                if ($this->mailExists($email)) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_mail_exist'));
                }
                if ($this->loginIsIncorrent($login)) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_login_exist'));
                }
                if (strlen($nickname) < 3 || strlen($nickname) > 64) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_nick_incorrent'));
                }
                if($notify == null) {
                    $validate = 0;
                    $pwd = $engine->system->randomString(rand(8,12));
                    $md5pwd = $engine->system->doublemd5($pwd);
                    $rand_token = $engine->system->md5random();
                    $time = time();
                    $stmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_user (`login`, `email`, `nick`, `pass`, `aprove`, `openid`, `token`, `token_start`) VALUES (?,?,?,?,?,?,?,?)");
                    $stmt->bindParam(1, $login, PDO::PARAM_STR, 64);
                    $stmt->bindParam(2, $email, PDO::PARAM_STR);
                    $stmt->bindParam(3, $nickname, PDO::PARAM_STR);
                    $stmt->bindParam(4, $md5pwd, PDO::PARAM_STR, 32);
                    $stmt->bindParam(5, $validate, PDO::PARAM_STR, 32);
                    $stmt->bindParam(6, $_SESSION['openid_person'], PDO::PARAM_STR);
                    $stmt->bindParam(7, $rand_token, PDO::PARAM_STR, 32);
                    $stmt->bindParam(8, $time, PDO::PARAM_INT);
                    $stmt->execute();
                    $user_obtained_id = $engine->database->con()->lastInsertId();
                    $stmt = null;
                    $stmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_user_custom (`id`) VALUES (?)");
                    $stmt->bindParam(1, $user_obtained_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                    $openid_desc_mail = $engine->language->get('usercontrol_openid_reg_mail_desc').$pwd;
                    $link = '<a href="'.$engine->constant->url.'">'.$engine->language->get('usercontrol_openid_reg_link').'</a>';
                    $mail_body = $engine->template->get('mail');
                    $mail_body = $engine->template->assign(array('title', 'description', 'text', 'footer'), array($engine->language->get('usercontrol_openid_reg_mail_title'), $openid_desc_mail, $link, $engine->language->get('usercontrol_reg_mail_footer')), $mail_body);
                    $engine->mail->send($email, $engine->language->get('usercontrol_reg_mail_title'), $mail_body, $nickname);
                    setcookie('person', $email, null, '/', null, null, true);
                    setcookie('token', $rand_token, null, '/', null, null, true);
                    $engine->system->redirect();
                } else {
                    $theme_openid = $engine->template->get('openid', 'components/usercontrol/');
                    $engine->page->setContentPosition('body', $engine->template->assign(array('openid_email', 'openid_login', 'openid_nick', 'openid_session', 'notify'), array($email, $login, $nickname, $token, $notify), $theme_openid));
                }
            } else {
                $engine->system->redirect('/login');
            }
        }
    }

    private function userPersonalSettings()
    {
        global $engine;
        $userid = $engine->user->get('id');
        if ($userid < 1) {
            $engine->page->setContentPosition('body', $engine->template->compile404());
            return;
        }
        $engine->rule->add('com.usercontrol.self_profile', true);
        $engine->rule->add('com.usercontrol.in_friends', true);
        $engine->rule->add('com.usercontrol.in_friends_request', false);
        $way = $engine->page->getPathway();
        $compiled_body = null;
        if ($way[1] == "avatar") {
            $notify = null;
            if ($engine->system->post('loadavatar')) {
                $image_upload = $_FILES['avatarupload'];
                if ($image_upload['size'] > 0) {
                    $upload_result = $engine->file->useravatarupload($image_upload);
                    if ($upload_result) {
                        $notify = $engine->template->stringNotify('success', $engine->language->get('usercontrol_profile_photochange_success'));
                    }
                }
                if ($notify == null) {
                    $notify = $engine->template->stringNotify('error', $engine->language->get('usercontrol_profile_photochange_fail'));
                }
            }
            $photo_theme = $engine->template->get('profile_settings_photo', 'components/usercontrol/');
            $compiled_body = $engine->template->assign('notify_message', $notify, $photo_theme);
        } elseif ($way[1] == "status") {
            if ($engine->system->post('updatestatus')) {
                $new_status = $engine->system->nohtml($engine->system->post('newstatus'));
                $stmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_user_custom SET status = ? WHERE id = ?");
                $stmt->bindParam(1, $new_status, PDO::PARAM_STR);
                $stmt->bindParam(2, $userid, PDO::PARAM_INT);
                $stmt->execute();
                $engine->user->customoverload($userid);
            }
            $theme_status = $engine->template->get('profile_settings_status', 'components/usercontrol/');
            $compiled_body = $engine->template->assign('user_status', $engine->user->customget('status'), $theme_status);
        } elseif($way[1] == "balance" && $engine->extension->getConfig('balance_view', 'usercontrol', 'components', 'boolean')) {
            $theme_balance = $engine->template->get('profile_balance_main', 'components/usercontrol/');
            $theme_tr_plus = $engine->template->get('profile_balance_main_tr_plus', 'components/usercontrol/');
            $theme_tr_minus = $engine->template->get('profile_balance_main_tr_minus', 'components/usercontrol/');
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_user_log WHERE `owner` = ? AND `type` = 'BALANCE' ORDER BY `time` DESC LIMIT 10");
            $stmt->bindParam(1, $userid, PDO::PARAM_INT);
            $stmt->execute();
            $table_result = null;
            while($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $balance_param = unserialize($res['params']);
                $operation_identifer = $balance_param['operation_type'] == "in" ? "+" : "-";
                $operation_type = $balance_param['operation_type'] == "in" ? $engine->language->get('usercontrol_profile_settings_balance_in') : $engine->language->get('usercontrol_profile_settings_balance_out');
                $table_result .= $engine->template->assign(array('operation_id', 'operation_price', 'operation_type', 'operation_date'),
                                                   array($res['id'], $operation_identifer.$balance_param['price'], $operation_type, $engine->system->toDate($res['time'], 'h')),
                                                   $balance_param['operation_type'] == "in" ? $theme_tr_plus : $theme_tr_minus);
            }
            if($table_result != null)
                $engine->rule->add('com.usercontrol.have_balance_log', true);
            $compiled_body = $engine->template->assign(array('user_balance', 'operations_table'), array($engine->user->getBalance(), $table_result), $theme_balance);
        } else {
            $notify = null;
            if ($engine->system->post('saveprofile')) {
                $birthday_array = $engine->system->post('bitrhday');
                // Y-m-d
                $birthday_string = "0000-00-00";
                $nick = $engine->system->nohtml($engine->system->post('nickname'));
                $phone = $engine->system->post('phone');
                $sex = $engine->system->post('sex');
                $webpage = $engine->system->post('website');
                // old, new, repeat new
                $password_array = array($engine->system->post('oldpwd'), $engine->system->post('newpwd'), $engine->system->post('renewpwd'));
                $password = $engine->user->get('pass');
                // анализируем то, что запостил пользователь на корректность данных
                if ($birthday_array['year'] >= 1920 && $birthday_array['year'] <= date('Y') && checkdate($birthday_array['month'], $birthday_array['day'], $birthday_array['year'])) {
                    $birthday_string = $birthday_array['year'] . "-" . $birthday_array['month'] . "-" . $birthday_array['day'];
                }
                if (strlen($nick) < 1) {

                    $nick = $engine->user->get('nick');
                }
                if (!$engine->system->validPhone($phone) && $engine->system->length($phone) > 0) {
                    $phone = $engine->user->customget('phone');
                }
                if (!$engine->system->isInt($sex) || $sex < 0 || $sex > 2) {
                    $sex = $engine->user->customget('sex');
                }
                if (!filter_var($webpage, FILTER_VALIDATE_URL) && $engine->system->length($webpage) > 0) {
                    $webpage = $engine->user->customget('webpage');
                }
                // новый пароль был назначен, новые пароли совпали а так же старый пароль введен верно
                if ($engine->system->validPasswordLength($password_array) && $engine->system->doublemd5($password_array[0]) == $password && $password_array[1] == $password_array[2] && $password_array[0] != $password_array[1]) {
                    $password = $engine->system->doublemd5($password_array[1]);
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_profile_settings_notify_passchange'));
                }
                $stmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_user a INNER JOIN {$engine->constant->db['prefix']}_user_custom b USING(id) SET a.nick = ?, a.pass = ?, b.birthday = ?, b.sex = ?, b.phone = ?, b.webpage = ? WHERE a.id = ?");
                $stmt->bindParam(1, $nick, PDO::PARAM_STR);
                $stmt->bindParam(2, $password, PDO::PARAM_STR, 32);
                $stmt->bindParam(3, $birthday_string, PDO::PARAM_STR);
                $stmt->bindParam(4, $sex, PDO::PARAM_INT);
                $stmt->bindParam(5, $phone, PDO::PARAM_STR);
                $stmt->bindParam(6, $webpage, PDO::PARAM_STR);
                $stmt->bindParam(7, $userid, PDO::PARAM_INT);
                $stmt->execute();
                $engine->user->fulluseroverload($userid);
                $notify .= $engine->template->stringNotify('success', $engine->language->get('usercontrol_profile_settings_notify_updated'));
            }
            $theme_option_inactive = $engine->template->get('form_select_option_inactive', 'components/usercontrol/');
            $theme_option_active = $engine->template->get('form_select_option_active', 'components/usercontrol/');
            list($birth_year, $birth_month, $birth_day) = explode("-", $engine->user->customget('birthday'));
            $day_range = $engine->system->generateIntRangeArray(1, 31);
            $month_range = $engine->system->generateIntRangeArray(1, 12);
            $year_range = $engine->system->generateIntRangeArray(1920, date('Y'));
            $day_option_list = null;
            $month_option_list = null;
            $year_option_list = null;
            $sex_list = null;

            $sex_int = $engine->user->customget('sex');
            // генерируем список для даты рождения
            foreach ($day_range as $s_day) {
                if ($s_day == $birth_day) {
                    $day_option_list .= $engine->template->assign(array('option_value', 'option_name'), array($s_day, $s_day), $theme_option_active);
                } else {
                    $day_option_list .= $engine->template->assign(array('option_value', 'option_name'), array($s_day, $s_day), $theme_option_inactive);
                }
            }
            foreach ($month_range as $s_month) {
                if ($s_month == $birth_month) {
                    $month_option_list .= $engine->template->assign(array('option_value', 'option_name'), array($s_month, $s_month), $theme_option_active);
                } else {
                    $month_option_list .= $engine->template->assign(array('option_value', 'option_name'), array($s_month, $s_month), $theme_option_inactive);
                }
            }
            foreach ($year_range as $s_year) {
                if ($s_year == $birth_year) {
                    $year_option_list .= $engine->template->assign(array('option_value', 'option_name'), array($s_year, $s_year), $theme_option_active);
                } else {
                    $year_option_list .= $engine->template->assign(array('option_value', 'option_name'), array($s_year, $s_year), $theme_option_inactive);
                }
            }
            for ($i = 0; $i <= 2; $i++) {
                if ($i == $sex_int) {
                    $sex_list .= $engine->template->assign(array('option_value', 'option_name'), array($i, $this->sexLang($i)), $theme_option_active);
                } else {
                    $sex_list .= $engine->template->assign(array('option_value', 'option_name'), array($i, $this->sexLang($i)), $theme_option_inactive);
                }
            }
            $engine->rule->add('com.usercontrol.use_balance', $engine->extension->getConfig('balance_view', 'usercontrol', 'components', 'boolean'));
            $compiled_body = $engine->template->assign(array('option_day', 'option_month', 'option_year', 'user_nickname', 'option_sex', 'user_phone', 'notify_messages', 'user_website'),
                array($day_option_list, $month_option_list, $year_option_list, $engine->user->get('nick'), $sex_list, $engine->user->customget('phone'), $notify, $engine->user->customget('webpage')),
                $engine->template->get('profile_settings_main', 'components/usercontrol/'));
        }
        $compiled_theme = $engine->template->assign(array('user_photo_control', 'user_header', 'user_menu', 'user_main_block'),
            array($this->userProfilePhotoSettings($userid), $this->userProfileHeaders($userid), $this->showUserMenu($userid), $compiled_body),
            $engine->template->get('profile_main', 'components/usercontrol/'));
        $engine->page->setContentPosition('body', $compiled_theme);
    }

    private function userPersonalMessage()
    {
        global $engine;
        $userid = $engine->user->get('id');
        $way = $engine->page->getPathway();
        if ($userid < 1) {
            $engine->page->setContentPosition('body', $engine->template->compile404());
            return;
        }
        if ($engine->user->get('content_view', $userid) < 1) {
            $engine->page->setContentPosition('body', $engine->template->compileBan());
            return;
        }
        $engine->rule->add('com.usercontrol.self_profile', true);
        $engine->rule->add('com.usercontrol.in_friends', true);
        $engine->rule->add('com.usercontrol.in_friends_request', false);
        $compiled_messages = null;
        if ($way[1] == "write") {
            if ($engine->system->post('sendmessage')) {
                $to_user_id = $engine->system->post('accepterid');
                $message_text = $engine->system->nohtml($engine->system->post('message'));
                if ($engine->system->isInt($to_user_id) && $this->inFriendsWith($to_user_id) && strlen($message_text) > 0) {
                    $time = time();
                    $stmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_user_messages(`from`, `to`, `message`, `timeupdate`) VALUES(?, ?, ?, ?)");
                    $stmt->bindParam(1, $userid, PDO::PARAM_INT);
                    $stmt->bindParam(2, $to_user_id, PDO::PARAM_INT);
                    $stmt->bindParam(3, $message_text, PDO::PARAM_STR);
                    $stmt->bindParam(4, $time, PDO::PARAM_INT);
                    $stmt->execute();
                    $engine->system->redirect('/message');
                }
            }

            $toid = $engine->system->toInt($way[2]);
            $theme_main = $engine->template->get('profile_message_write', 'components/usercontrol/');
            $theme_option_inactive = $engine->template->get('form_select_option_inactive', 'components/usercontrol/');
            $theme_option_active = $engine->template->get('form_select_option_active', 'components/usercontrol/');
            $result_option_select = null;

            $friendlist = $engine->user->customget('friend_list');
            $friendarray = $engine->system->altexplode(',', $friendlist);
            // Это сообщение с известным адресатом и данный адрессат есть в списке друзей и это не сам отправитель
            if ($toid > 0 && $this->inFriendsWith($toid) && $toid != $userid) {
                $friendarray = $engine->system->valueUnsetInArray($toid, $friendarray);
                $result_option_select .= $engine->template->assign(array('option_value', 'option_name'), array($toid, $engine->user->get('nick', $toid)), $theme_option_active);
            }
            // мультизагрузка, далее нужен $engine->user->get('nick')
            $engine->user->listload($friendlist);
            foreach ($friendarray as $item) {
                $result_option_select .= $engine->template->assign(array('option_value', 'option_name'), array($item, $engine->user->get('nick', $item)), $theme_option_inactive);
            }

            $compiled_messages = $engine->template->assign('option_names', $result_option_select, $theme_main);
        } elseif ($way[1] == null || $way[1] == "all" || $way[1] == "in" || $way[1] == "out") {
            if ($way[1] == null)
                $way[1] = "all";
            $page_id = (int)$way[2];
            $pm_on_page = $engine->extension->getConfig('pm_count', 'usercontrol', 'components', 'int');
            $current_marker = $page_id * $pm_on_page;
            $total_pm_count = $this->getMessageTotalRows($userid, $way[1]);
            if ($page_id > 0) {
                $engine->rule->add('com.usercontrol.have_previous', true);
            }
            if ($current_marker + $pm_on_page < $total_pm_count) {
                $engine->rule->add('com.usercontrol.have_next', true);
            }
            $theme_body = $engine->template->get('profile_message_body', 'components/usercontrol/');
            $theme_head = $engine->template->get('profile_message_head', 'components/usercontrol/');
            // обновляем маркер последнего просмотра личных сообщений
            if($way[1] == "in" || $way[1] == "all") {
                $stmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_user_custom SET lastpmview = ? where id = ?");
                $time = time();
                $stmt->bindParam(1, $time, PDO::PARAM_INT);
                $stmt->bindParam(2, $userid, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
            }
            if ($way[1] == "in") {
                $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_user_messages WHERE `to` = ? ORDER BY timeupdate DESC LIMIT ?, ?");
                $stmt->bindParam(1, $userid, PDO::PARAM_INT);
                $stmt->bindParam(2, $current_marker, PDO::PARAM_INT);
                $stmt->bindParam(3, $pm_on_page, PDO::PARAM_INT);
                $stmt->execute();
            } elseif ($way[1] == "out") {
                $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_user_messages WHERE `from` = ? ORDER BY timeupdate DESC LIMIT ?, ?");
                $stmt->bindParam(1, $userid, PDO::PARAM_INT);
                $stmt->bindParam(2, $current_marker, PDO::PARAM_INT);
                $stmt->bindParam(3, $pm_on_page, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_user_messages WHERE `to` = ? OR `from` = ? ORDER BY timeupdate DESC LIMIT ?, ?");
                $stmt->bindParam(1, $userid, PDO::PARAM_INT);
                $stmt->bindParam(2, $userid, PDO::PARAM_INT);
                $stmt->bindParam(3, $current_marker, PDO::PARAM_INT);
                $stmt->bindParam(4, $pm_on_page, PDO::PARAM_INT);
                $stmt->execute();
            }
            $resultAssoc = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // подготавливаем пользовательские данные к загрузке в 1 запрос
            $user_to_dataload = $engine->system->extractFromMultyArray('from', $resultAssoc);
            $engine->user->listload($user_to_dataload);
            foreach ($resultAssoc as $result) {
                $compiled_messages .= $engine->template->assign(array('message_from_id', 'from_nick', 'user_avatar', 'user_message', 'message_topic_id'),
                    array($result['from'], $engine->user->get('nick', $result['from']), $engine->user->buildAvatar('small', $result['from']), $result['message'], $result['id']),
                    $theme_body);
            }
            $compiled_messages = $engine->template->assign(array('message_body', 'message_type', 'message_prev', 'message_next'),
                array($compiled_messages, $way[1], $page_id - 1, $page_id + 1),
                $theme_head);
        } // отображаем всю ветку переписки
        elseif ($way[1] == "topic" && $engine->system->isInt($way[2])) {
            $topicId = $engine->system->toInt($way[2]);
            if ($engine->system->post('newanswer')) {
                $message_new = $engine->system->nohtml($engine->system->post('topicanswer'));
                // Добавление сообщения в базу и обновление таймера апдейта
                if (strlen($message_new) > 0) {
                    // является ли постер участником личной переписки?
                    $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_user_messages WHERE id = ? AND (`from` = ? OR `to` = ?)");
                    $stmt->bindParam(1, $topicId, PDO::PARAM_INT);
                    $stmt->bindParam(2, $userid, PDO::PARAM_INT);
                    $stmt->bindParam(3, $userid, PDO::PARAM_INT);
                    $stmt->execute();
                    $res = $stmt->fetch();
                    $stmt = null;
                    // постер или адресат или отправитель, вносим данные
                    if ($res[0] == "1") {
                        $time = time();
                        $stmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_user_messages_answer(`topic`, `from`, `message`, `time`) VALUES(?, ?, ?, ?)");
                        $stmt->bindParam(1, $topicId, PDO::PARAM_INT);
                        $stmt->bindParam(2, $userid, PDO::PARAM_INT);
                        $stmt->bindParam(3, $message_new, PDO::PARAM_STR);
                        $stmt->bindParam(4, $time, PDO::PARAM_INT);
                        $stmt->execute();
                        $stmt = null;
                        $stmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_user_messages SET timeupdate = ? WHERE id = ?");
                        $stmt->bindParam(1, $time, PDO::PARAM_INT);
                        $stmt->bindParam(2, $topicId, PDO::PARAM_INT);
                        $stmt->execute();
                    }
                }
            }
            $theme_head = $engine->template->get('profile_topic_head', 'components/usercontrol/');
            $theme_body = $engine->template->get('profile_topic_body', 'components/usercontrol/');
            $topics_first = null;
            $topics_body = null;
            // выбираем первое сообщение
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_user_messages WHERE id = ? AND (`from` = ? or `to` = ?)");
            $stmt->bindParam(1, $topicId, PDO::PARAM_INT);
            $stmt->bindParam(2, $userid, PDO::PARAM_INT);
            $stmt->bindParam(3, $userid, PDO::PARAM_INT);
            $stmt->execute();
            // корневой топик он 1, если нет - топика или нет или реквестер не участвовал в переписке
            if ($stmt->rowCount() == 1) {
                $result = $stmt->fetch();
                $topics_first = $engine->template->assign(array('message_from_id', 'from_nick', 'user_avatar', 'user_message'), array($result['from'], $engine->user->get('nick', $result['from']), $engine->user->buildAvatar('small', $result['from']), $result['message']), $theme_body);
                $stmt = null;
                $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_user_messages_answer where topic = ? ORDER BY id DESC");
                $stmt->bindParam(1, $topicId, PDO::PARAM_INT);
                $stmt->execute();
                while ($single_msg = $stmt->fetch()) {
                    $topics_body .= $engine->template->assign(array('message_from_id', 'from_nick', 'user_avatar', 'user_message', 'answer_date'), array($single_msg['from'], $engine->user->get('nick', $single_msg['from']), $engine->user->buildAvatar('small', $single_msg['from']), $single_msg['message'], $engine->system->toDate($single_msg['time'], 'h')), $theme_body);
                }
                $compiled_messages = $engine->template->assign(array('topic_main_message', 'topic_answers'), array($topics_first, $topics_body), $theme_head);
            } else {
                $compiled_messages = $engine->language->get('usercontrol_profile_view_null_info');
            }
        }
        $compiled_theme = $engine->template->assign(array('user_photo_control', 'user_header', 'user_menu', 'user_main_block'),
            array($this->userProfilePhotoSettings($userid), $this->userProfileHeaders($userid), $this->showUserMenu($userid), $compiled_messages),
            $engine->template->get('profile_main', 'components/usercontrol/'));
        $engine->page->setContentPosition('body', $compiled_theme);
    }

    private function profileComponent()
    {
        global $engine;
        $way = $engine->page->getPathway();
        $userid = substr($way[1], 2);
        $content = null;

        if (!$engine->extension->getConfig('profile_view', 'usercontrol', 'components', 'boolean') && $engine->user->get('id') < 1) {
            $content = $engine->template->get('guest_message', 'components/usercontrol/');
        } else {
            if ($way[1] == null || $engine->system->isInt($way[1])) {
                $content = $this->showUserList();;
            } elseif (substr($way[1], 0, 2) == "id") {
                if ($engine->system->isInt($userid) && $userid > 0 && $engine->user->exists($userid)) {
                    $engine->meta->add('title', $engine->user->get('nick', $userid));
                    if ($engine->user->get('content_view', $userid) < 1) {
                        $content = $engine->template->compileBan();
                    } else {
                        $this->dynamicRequests($userid);
                        $engine->user->get('id') == $userid ? $engine->rule->add('com.usercontrol.self_profile', true) : $engine->rule->add('com.usercontrol.self_profile', false);
                        $this->inFriendsWith($userid) ? $engine->rule->add('com.usercontrol.in_friends', true) : $engine->rule->add('com.usercontrol.in_friends', false);
                        $this->inFriendRequestWith($userid) ? $engine->rule->add('com.usercontrol.in_friends_request', true) : $engine->rule->add('com.usercontrol.in_friends_request', false);

                        switch ($way[2]) {
                            case "marks":
                                $content = $this->showBookmarks($userid);
                                break;
                            case "friends":
                                $content = $this->showFriends($userid);
                                break;
                            default:
                                if ($this->hook_item_url[$way[2]] != null) {
                                    $content = $this->hook_item_url[$way[2]];
                                } else {
                                    $content = $this->showProfileUser($userid);
                                }
                                break;
                        }
                    }
                }
            }
        }
        if ($content == null)
            $content = $engine->template->compile404();
        $engine->page->setContentPosition('body', $content);
        // можно добавить и обработчик по login / etc данным
    }

    // обработка сквозных запросов для профиля: инвайты в друзья, возможна дальнейшая доработка для хуков обновления через pop-up диалоги статуса, фото и прочее
    private function dynamicRequests($userid)
    {
        global $engine;
        if ($engine->system->post('requestfriend')) {
            // еще не было запроса, вдруг пост-фейкинг
            if (!$this->inFriendRequestWith($userid)) {
                $current_friendrequest_list = $engine->user->customget('friend_request', $userid);
                if (strlen($current_friendrequest_list) < 1) {
                    $current_friendrequest_list .= $engine->user->get('id');
                } else {
                    $current_friendrequest_list .= "," . $engine->user->get('id');
                }
                $stmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_user_custom SET friend_request = ? WHERE id = ?");
                $stmt->bindParam(1, $current_friendrequest_list, PDO::PARAM_STR);
                $stmt->bindParam(2, $userid, PDO::PARAM_INT);
                $stmt->execute();
                $engine->user->customoverload($userid);
            }
        }
    }

    private function showUserList()
    {
        global $engine;
        $engine->meta->add('title', $engine->language->get('seo_title_userlist'));
        $usercount_on_page = $engine->extension->getConfig('userlist_count', 'usercontrol', 'components', 'int');
        $way = $engine->page->getPathway();
        $theme_head = $engine->template->get('userlist_head', 'components/usercontrol/');
        $theme_body = $engine->template->get('userlist_body', 'components/usercontrol/');
        $compiled_body = null;
        $currentOnline = null;
        $current_user_id = $engine->user->get('id');
        if ($current_user_id == null)
            $current_user_id = 0;
        $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_user a, {$engine->constant->db['prefix']}_user_custom b WHERE a.aprove = 0 AND a.id = b.id");
        $stmt->execute();
        $rowRegisteredFetch = $stmt->fetch();
        $allRegisteredCount = $rowRegisteredFetch[0];
        $stmt = null;
        $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_user a, {$engine->constant->db['prefix']}_user_custom b WHERE a.aprove = 0 AND a.id = b.id AND sex = 1");
        $stmt->execute();
        $rowMaleFetch = $stmt->fetch();
        $maleRegisteredCount = $rowMaleFetch[0];
        $stmt = null;
        $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_user a, {$engine->constant->db['prefix']}_user_custom b WHERE a.aprove = 0 AND a.id = b.id AND sex = 2");
        $stmt->execute();
        $rowFemaleFetch = $stmt->fetch();
        $femaleRegisteredCount = $rowFemaleFetch[0];
        $stmt = null;
        $time_difference = time() - 15 * 60;
        $stmt = $engine->database->con()->prepare("SELECT a.reg_id, a.cookie, b.* FROM {$engine->constant->db['prefix']}_statistic a, {$engine->constant->db['prefix']}_user b WHERE a.`time` >= $time_difference AND a.reg_id > 0 AND a.reg_id = b.id GROUP BY a.reg_id, a.cookie");
        $stmt->execute();
        $rowOnlineUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rowOnlineUser as $onlineUser) {
            $currentOnline .= "<a href=\"{$engine->constant->url}/user/id{$onlineUser['id']}\">{$onlineUser['nick']}</a> ";
        }
        $stmt = null;
        $limit_start = $way[1] * $usercount_on_page;
        $stmt = $engine->database->con()->prepare("SELECT a.id, a.nick, b.regdate FROM {$engine->constant->db['prefix']}_user a, {$engine->constant->db['prefix']}_user_custom b WHERE a.id = b.id AND a.aprove = 0 ORDER BY a.id DESC LIMIT ?, ?");
        $stmt->bindParam(1, $limit_start, PDO::PARAM_INT);
        $stmt->bindParam(2, $usercount_on_page, PDO::PARAM_INT);
        $stmt->execute();
        while ($result = $stmt->fetch()) {
            $compiled_body .= $engine->template->assign(array('target_user_id', 'target_user_name', 'target_user_avatar', 'target_reg_date'), array($result['id'], $result['nick'], $engine->user->buildAvatar('small', $result['id']), $engine->system->toDate($result['regdate'], 'd')), $theme_body);
        }
        //echo $way[1];
        $pagination_tpl = $engine->template->drowNumericPagination($way[1], $usercount_on_page, $allRegisteredCount, 'user/');
        return $engine->template->assign(array('userlist', 'reg_all_count', 'reg_male_count', 'reg_female_count', 'reg_unknown_count', 'user_online_list', 'userlist_pagination'), array($compiled_body, $allRegisteredCount, $maleRegisteredCount, $femaleRegisteredCount, $allRegisteredCount - ($maleRegisteredCount + $femaleRegisteredCount), $currentOnline, $pagination_tpl), $theme_head);
    }

    private function showFriends($userid)
    {
        global $engine;
        $body_compiled = null;
        $theme_head = $engine->template->get('profile_friendlist_head', 'components/usercontrol/');
        $way = $engine->page->getPathway();

        switch ($way[3]) {
            case "request":
                if ($engine->user->get('id') == $userid) {
                    if ($engine->system->post('acceptfriend')) {
                        $this->acceptFriend($engine->system->post('target_id'));
                    } elseif ($engine->system->post('cancelfriend')) {
                        $this->rejectFriend($engine->system->post('target_id'));
                    }
                    $engine->rule->add('com.usercontrol.profile_friend_request', true);
                    $request_list = $engine->user->customget('friend_request');
                    if (strlen($request_list) > 0) {
                        $theme_body = $engine->template->get('profile_friendrequest_body', 'components/usercontrol/');
                        // загружаем данные о пользователях 1 запросом
                        $engine->user->listload($request_list);
                        $request_array = explode(",", $request_list);
                        foreach ($request_array as $requester_id) {
                            $user_nick = $engine->user->get('nick', $requester_id);
                            $user_avatar = $engine->user->buildAvatar('small', $requester_id);
                            $body_compiled .= $engine->template->assign(array('nick', 'avatar', 'target_user_id'), array($user_nick, $user_avatar, $requester_id), $theme_body);
                        }
                    }
                }
                break;
            default:
                $friend_list = $engine->user->customget('friend_list', $userid);
                if (strlen($friend_list) > 0) {
                    $friend_array = explode(",", $friend_list);
                    $page_index = 0;
                    if ($engine->system->isInt($way[3])) {
                        $page_index = $way[3];
                    }
                    $rows_count = $engine->extension->getConfig('friend_page_count', 'usercontrol', 'components', 'int');
                    $page_start = $page_index * $rows_count;
                    $theme_body = $engine->template->get('profile_friendlist_body', 'components/usercontrol/');
                    $friend_current_page = array_slice($friend_array, $page_start, $page_start + $rows_count);
                    if (sizeof($friend_current_page) > 0) {
                        $engine->user->listload(implode(",", $friend_current_page));
                        foreach ($friend_current_page as $friend_id) {
                            $user_nick = $engine->user->get('nick', $friend_id);
                            $user_avatar = $engine->user->buildAvatar('small', $friend_id);
                            $body_compiled .= $engine->template->assign(array('nick', 'avatar', 'target_user_id'), array($user_nick, $user_avatar, $friend_id), $theme_body);
                        }
                    }
                }
                break;
        }

        if ($body_compiled == null) {
            $body_compiled = $engine->language->get('usercontrol_profile_view_null_info');
        }

        $container_compiled = $engine->template->assign(array('target_user_id', 'friend_body'), array($userid, $body_compiled), $theme_head);

        $compiled_theme = $engine->template->assign(array('user_photo_control', 'user_header', 'user_menu', 'user_main_block'),
            array($this->userProfilePhotoSettings($userid), $this->userProfileHeaders($userid), $this->showUserMenu($userid), $container_compiled),
            $engine->template->get('profile_main', 'components/usercontrol/'));
        return $compiled_theme;
    }

    private function acceptFriend($id)
    {
        global $engine;
        $request_array = explode(",", $engine->user->customget('friend_request'));
        $friend_array = explode(",", $engine->user->customget('friend_list'));
        if (in_array($id, $request_array)) {
            $ownerid = $engine->user->get('id');
            // этот пользователь еще не в списках
            if (!in_array($id, $friend_array)) {
                // вносим в списки друзей и удаляем из списков запросов
                $new_request_array = $engine->system->valueUnsetInArray($id, $request_array);
                $new_request_list = $engine->system->altimplode(",", $new_request_array);
                $new_friend_array = $engine->system->arrayAdd($id, $friend_array);
                $new_friend_list = $engine->system->altimplode(",", $new_friend_array);
                $stmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_user_custom SET friend_list = ?, friend_request = ? WHERE id = ?");
                $stmt->bindParam(1, $new_friend_list, PDO::PARAM_STR);
                $stmt->bindParam(2, $new_request_list, PDO::PARAM_STR);
                $stmt->bindParam(3, $ownerid, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                // так же при принятии заносим пользователя в список друзей тому, кто подал запрос
                $requester_friendarray = explode(",", $engine->user->customget('friend_list', $id));
                $requester_friendarray = $engine->system->arrayAdd($ownerid, $requester_friendarray);
                $requester_new_friendlist = $engine->system->altimplode(",", $requester_friendarray);
                $stmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_user_custom SET friend_list = ? WHERE id = ?");
                $stmt->bindParam(1, $requester_new_friendlist, PDO::PARAM_STR);
                $stmt->bindParam(2, $id, PDO::PARAM_INT);
                $stmt->execute();
            } // как такое произошло? уже в друзьях и еще запрос прислал, чистим
            else {
                $new_request_array = $engine->system->valueUnsetInArray($id, $request_array);
                $new_request_list = $engine->system->altimplode(",", $new_request_array);
                $stmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_user_custom SET friend_request = ? WHERE id = ?");
                $stmt->bindParam(1, $new_request_list, PDO::PARAM_STR);
                $stmt->bindParam(2, $ownerid, PDO::PARAM_INT);
                $stmt->execute();
            }
            $engine->user->customoverload($ownerid);
        }

    }

    private function rejectFriend($id)
    {
        global $engine;
        $request_array = explode(",", $engine->user->customget('friend_request'));
        if (in_array($id, $request_array)) {
            $ownerid = $engine->user->get('id');
            $new_request_list = $engine->system->altimplode(",", $engine->system->valueUnsetInArray($id, $request_array));
            $stmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_user_custom SET friend_request = ? WHERE id = ?");
            $stmt->bindParam(1, $new_request_list, PDO::PARAM_STR);
            $stmt->bindParam(2, $ownerid, PDO::PARAM_INT);
            $stmt->execute();
            $engine->user->customoverload($ownerid);
        }
    }

    private function showBookmarks($userid)
    {
        global $engine;
        $way = $engine->page->getPathway();
        $marks_marker = (int)$way[3];
        $total_marks_row = $this->getMarkTotalRows($userid);
        $marks_config_rows = $engine->extension->getConfig('marks_post_count', 'usercontrol', 'components');
        $marks_index = $marks_marker * $marks_config_rows;
        $main_theme = $engine->template->get('profile_main', 'components/usercontrol/');
        $user_compiled_menu = $this->showUserMenu($userid);
        $user_marks_header = $engine->template->get('profile_marks_head', 'components/usercontrol/');
        $user_marks_body = $engine->template->get('profile_marks_body', 'components/usercontrol/');
        $user_marks_list = null;
        $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_user_bookmarks WHERE target = ? ORDER BY id DESC LIMIT ?, ?");
        $stmt->bindParam(1, $userid, PDO::PARAM_INT);
        $stmt->bindParam(2, $marks_index, PDO::PARAM_INT);
        $stmt->bindParam(3, $marks_config_rows, PDO::PARAM_INT);
        $stmt->execute();
        while ($result = $stmt->fetch()) {
            $link_compile = null;
            if($engine->system->prefixEquals($result['href'], $engine->constant->url)) {
                $link_compile = $result['href'];
            } else {
                $link_compile = $engine->constant->url . "/api.php?action=redirect&url=" . $result['href'];
            }
            $user_marks_list .= $engine->template->assign(array('mark_title', 'mark_link', 'mark_text_link'),
                array($result['title'], $link_compile, $result['href']),
                $user_marks_body);
        }
        $user_marks = $engine->template->assign(array('marks_body', 'target_user_id', 'mark_prev', 'mark_next'), array($user_marks_list, $userid, $marks_marker - 1, $marks_marker + 1), $user_marks_header);
        // позиция коретки > 0 дает понять о наличии предидущих элементов
        if ($marks_marker > 0) {
            $engine->rule->add('com.usercontrol.have_previous', true);
        }
        if ($total_marks_row > $marks_index + $marks_config_rows) {
            $engine->rule->add('com.usercontrol.have_next', true);
        }
        // если закладок у пользователя нет
        if($user_marks_list == null) {
            $user_marks = $engine->language->get('usercontrol_profile_view_null_info');
        }
        $compiled_theme = $engine->template->assign(array('user_photo_control', 'user_header', 'user_menu', 'user_main_block'),
            array($this->userProfilePhotoSettings($userid), $this->userProfileHeaders($userid), $user_compiled_menu, $user_marks),
            $main_theme);
        return $compiled_theme;
    }

    private function showUserMenu($userid)
    {
        global $engine;
        $way = $engine->page->getPathway();
        if ($way[0] == "message") {
            $engine->rule->add('com.usercontrol.menu_message', true);
        } elseif ($way[0] == "settings") {
            if ($way[1] == "avatar") {
                $engine->rule->add('com.usercontrol.menu_avatar', true);
            } else {
                $engine->rule->add('com.usercontrol.menu_settings', true);
            }
        } else {
            switch ($way[2]) {
                case "marks":
                    $engine->rule->add('com.usercontrol.menu_mark', true);
                    break;
                case "wall":
                case "":
                    $engine->rule->add('com.usercontrol.menu_wall', true);
                    break;
                case "friends":
                    $engine->rule->add('com.usercontrol.menu_friends', true);
                    break;
                default:
                    $engine->rule->add('com.usercontrol.menu_dropdown', true);
                    break;
            }
        }
        if ($this->hook_item_menu != null) {
            $engine->rule->add('com.usercontrol.menu_dropdown_notempty', true);
        }
        return $engine->template->assign(array('target_user_id', 'additional_hook_list'), array($userid, $this->hook_item_menu), $engine->template->get('profile_block_menu', 'components/usercontrol/'));
    }

    private function showProfileUser($userid)
    {
        global $engine;
        $wall_post_limit = false;
        if ($engine->system->post('wall_post')) {
            $caster = $engine->user->get('id');
            $time = time();
            $message = $engine->system->nohtml($engine->system->post('wall_text'));
            if ($engine->system->length($message) > 1 && $caster > 0 && $this->inFriendsWith($userid)) {
                $stmt = $engine->database->con()->prepare("SELECT time FROM {$engine->constant->db['prefix']}_user_wall WHERE caster = ? AND target = ? ORDER BY id DESC LIMIT 1");
                $stmt->bindParam(1, $caster, PDO::PARAM_INT);
                $stmt->bindParam(2, $userid, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetch();
                $time_last_message = $res['time'];
                $stmt = null;

                if (($time - $time_last_message) >= $engine->extension->getConfig('wall_post_delay', 'usercontrol', 'components', 'int')) {
                    $stmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_user_wall (target, caster, message, time) VALUES (?, ?, ?, ?)");
                    $stmt->bindParam(1, $userid, PDO::PARAM_INT);
                    $stmt->bindParam(2, $caster, PDO::PARAM_INT);
                    $stmt->bindParam(3, $message, PDO::PARAM_STR);
                    $stmt->bindParam(4, $time, PDO::PARAM_INT);
                    $stmt->execute();
                } else {
                    $wall_post_limit = true;
                }
            }
        }
        $way = $engine->page->getPathway();
        $wall_marker = (int)$way[3];
        $profile_theme = $engine->template->get('profile_main', 'components/usercontrol/');
        $profile_data_theme = $engine->template->get('profile_block_data', 'components/usercontrol/');
        // пользовательские данные
        $regdate = $engine->system->toDate($engine->user->customget('regdate', $userid), 'd');
        $birthday = $engine->system->toDate($engine->user->customget('birthday', $userid), 'd');
        $sex_int = $engine->user->customget('sex', $userid);
        $website = $engine->user->customget('webpage', $userid);
        if (strlen($website) > 0) {
            $engine->rule->add('com.usercontrol.have_webpage', true);
        }
        $sex = $this->sexLang($sex_int);
        $phone = $engine->user->customget('phone', $userid);
        if (strlen($phone) > 0) {
            $engine->rule->add('com.usercontrol.have_phone', true);
        }
        $user_compiled_menu = $this->showUserMenu($userid);
        $profile_compiled_data = $engine->template->assign(array('user_regdate', 'user_birthday', 'user_sex', 'user_phone', 'target_user_id', 'user_wall', 'wall_prev', 'wall_next', 'user_website'),
            array($regdate, $birthday, $sex, $phone, $userid, $this->loadUserWall($userid, $wall_marker, $wall_post_limit), $wall_marker - 1, $wall_marker + 1, $website),
            $profile_data_theme);
        $compiled_theme = $engine->template->assign(array('user_photo_control', 'user_header', 'user_menu', 'user_main_block'),
            array($this->userProfilePhotoSettings($userid), $this->userProfileHeaders($userid), $user_compiled_menu, $profile_compiled_data),
            $profile_theme);
        return $compiled_theme;
    }

    private function sexLang($int)
    {
        global $engine;
        if ($int == 1) {
            return $engine->language->get('usercontrol_profile_sex_man');
        } elseif ($int == 2) {
            return $engine->language->get('usercontrol_profile_sex_woman');
        } else {
            return $engine->language->get('usercontrol_profile_sex_unknown');
        }
    }

    private function loadUserWall($userid, $marker, $limit = false)
    {
        global $engine;
        $theme = $engine->template->get('profile_wall', 'components/usercontrol/');
        $output = null;
        if ($limit) {
            $output .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_profile_wall_answer_spamdetect'));
        }
        $wall_rows = $engine->extension->getConfig('wall_post_count', 'usercontrol', 'components', 'int');
        $marker_index = $marker * $wall_rows;
        $wall_total_rows = $this->getWallTotalRows($userid);
        if ($marker > 0) {
            $engine->rule->add('com.usercontrol.have_previous', true);
        } else {
            $engine->rule->add('com.usercontrol.have_previous', false);
        }
        if ($wall_total_rows > $marker_index + $wall_rows) {
            $engine->rule->add('com.usercontrol.have_next', true);
        } else {
            $engine->rule->add('com.usercontrol.have_next', false);
        }
        $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_user_wall WHERE target = ? ORDER by time DESC LIMIT ?, ?");
        $stmt->bindParam(1, $userid, PDO::PARAM_INT);
        $stmt->bindParam(2, $marker_index, PDO::PARAM_INT);
        $stmt->bindParam(3, $wall_rows, PDO::PARAM_INT);
        $stmt->execute();
        $resultAssoc = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $engine->user->listload($engine->system->extractFromMultyArray('caster', $resultAssoc));
        foreach ($resultAssoc as $result) {
            $from_name = $engine->user->get('nick', $result['caster']);
            if (strlen($from_name) < 1) {
                $from_name = $engine->language->get('usercontrol_profile_name_unknown');
            }
            $message = $result['message'];
            $output .= $engine->template->assign(array('wall_from', 'wall_message', 'wall_from_id', 'wall_message_id', 'user_avatar'), array($from_name, $message, $result['caster'], $result['id'], $engine->user->buildAvatar('small', $result['caster'])), $theme);
        }
        return $output;
    }

    private function inFriendsWith($userid)
    {
        global $engine;
        $friend_list = $engine->user->customget('friend_list', $userid);
        $friend_array = explode(",", $friend_list);
        if (in_array($engine->user->get('id'), $friend_array) || $engine->user->get('id') == $userid) {
            return true;
        }
        return false;
    }

    private function inFriendRequestWith($userid)
    {
        global $engine;
        $friend_request_list = $engine->user->customget('friend_request', $userid);
        $friend_request_array = explode(",", $friend_request_list);
        if (in_array($engine->user->get('id'), $friend_request_array)) {
            return true;
        }
        return false;
    }

    private function getWallTotalRows($userid)
    {
        global $engine;
        $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_user_wall WHERE target = ?");
        $stmt->bindParam(1, $userid, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result[0];
    }

    private function getMarkTotalRows($userid)
    {
        global $engine;
        $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_user_bookmarks WHERE target = ?");
        $stmt->bindParam(1, $userid, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();
        return $res[0];
    }

    private function getMessageTotalRows($userid, $type)
    {
        global $engine;
        if ($type == "in") {
            $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_user_messages WHERE `to` = ?");
            $stmt->bindParam(1, $userid, PDO::PARAM_INT);
        } elseif ($type == "out") {
            $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_user_messages WHERE `from` = ?");
            $stmt->bindParam(1, $userid, PDO::PARAM_INT);
        } else {
            $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_user_messages WHERE `to` = ? OR `from` = ?");
            $stmt->bindParam(1, $userid, PDO::PARAM_INT);
            $stmt->bindParam(2, $userid, PDO::PARAM_INT);
        }
        $stmt->execute();
        $res = $stmt->fetch();
        return $res[0];
    }

    private function userProfilePhotoSettings($userid)
    {
        global $engine;
        $parsed = $engine->template->assign(array('user_avatar', 'target_user_id'),
            array($engine->user->buildAvatar('big', $userid), $userid),
            $engine->template->get('profile_photo', 'components/usercontrol/'));
        if($this->hook_item_settings != null) {
            $engine->rule->add('com.usercontrol.have_additional', true);
            $parsed = $engine->template->assign('hook_additional_link', $this->hook_item_settings, $parsed);
        }
        return $parsed;
    }

    private function userProfileHeaders($userid)
    {
        global $engine;
        $nickname = $engine->user->get('nick', $userid);
        if (strlen($nickname) < 1) {
            $nickname = $engine->language->get('usercontrol_profile_name_unknown');
        }
        $status = $engine->user->customget('status', $userid);
        if (strlen($status) < 1) {
            $status = $engine->language->get('usercontrol_profile_status_unknown');
        }
        return $engine->template->assign(array('user_name', 'user_status'), array($nickname, $status), $engine->template->get('profile_header', 'components/usercontrol/'));
    }

    private function loginComponent()
    {
        global $engine;
        if ($engine->user->get('id') != NULL) {
            $engine->page->setContentPosition('body', $engine->template->compile404());
            return;
        }
        $notify = null;
        if ($engine->system->post('submit')) {
            $loginoremail = $engine->system->post('email');
            if ($engine->extension->getConfig('login_captcha', 'usercontrol', 'components', 'boolean') && !$engine->hook->get('captcha')->validate($engine->system->post('captcha'))) {
                $notify .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_captcha_form_error'));
            }
            if (!filter_var($loginoremail, FILTER_VALIDATE_EMAIL) && (strlen($loginoremail) < 3 || !$engine->system->isLatinOrNumeric($loginoremail))) {
                $notify .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_invalid_emailorlogin_error'));
            }
            if (strlen($engine->system->post('password')) < 4 || strlen($engine->system->post('password')) > 32) {
                $notify .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_incorrent_password_error'));
            }
            // все хорошо, ошибок нет, можно идти к SQL запросу
            if ($notify == null) {
                $md5pwd = $engine->system->doublemd5($engine->system->post('password'));
                $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_user WHERE (email = ? OR login = ?) AND pass = ?");
                $stmt->bindParam(1, $loginoremail, PDO::PARAM_STR);
                $stmt->bindParam(2, $loginoremail, PDO::PARAM_STR);
                $stmt->bindParam(3, $md5pwd, PDO::PARAM_STR, 32);
                $stmt->execute();
                if ($stmt->rowCount() == 1) {
                    $md5token = $engine->system->md5random();
                    $nixtime = time();
                    $stmt2 = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_user SET token = ?, token_start = ? WHERE (email = ? OR login = ?) AND pass = ?");
                    $stmt2->bindParam(1, $md5token);
                    $stmt2->bindParam(2, $nixtime);
                    $stmt2->bindParam(3, $loginoremail);
                    $stmt2->bindParam(4, $loginoremail);
                    $stmt2->bindParam(5, $md5pwd);
                    $stmt2->execute();

                    setcookie('person', $loginoremail, null, '/', null, null, true);
                    setcookie('token', $md5token, null, '/', null, null, true);
                    $engine->system->redirect();
                } else {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_incorrent_password_query'));
                }
            }
        }
        $way = $engine->page->getPathway();
        if($way[1] == "recoverysuccess") {
            $notify .= $engine->template->stringNotify('success', $engine->language->get('usercontrol_recovery_pass_notify'));
        }
        $theme = $engine->template->get('login', 'components/usercontrol/');
        $captcha = $engine->hook->get('captcha')->show();
        $openid_url = urlencode($engine->constant->url."/openid/");
        $theme = $engine->template->assign(array('captcha', 'notify', 'openid_url'), array($captcha, $notify, $openid_url), $theme);
        $engine->page->setContentPosition('body', $theme);
    }

    private function doRegisterAprove()
    {
        global $engine;
        $pathway = $engine->page->getPathway();
        $hash = $pathway[1];
        $stmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_user SET aprove = 0 WHERE aprove = ?");
        $stmt->bindParam(1, $hash, PDO::PARAM_STR, 32);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            $engine->page->setContentPosition('body', $engine->template->compile404());
        } else {
            $engine->page->setContentPosition('body', $engine->template->get('aprove', 'components/usercontrol/'));
        }
    }

    private function regComponent()
    {
        global $engine;
        if ($engine->user->get('id') != NULL) {
            $engine->page->setContentPosition('body', $engine->template->compile404());
            return;
        }
        if ($engine->system->post('submit')) {
            $notify = null;
            $nickname = $engine->system->nohtml($engine->system->post('nick'));
            $email = $engine->system->post('email');
            $login = $engine->system->post('login');
            $pass = $engine->system->post('password');
            $md5pwd = $engine->system->doublemd5($pass);
            if ($engine->extension->getConfig('register_captcha', 'usercontrol', 'components', 'boolean') && !$engine->hook->get('captcha')->validate($engine->system->post('captcha'))) {
                $notify .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_captcha_form_error'));
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $notify .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_invalid_email_error'));
            }
            if (!$engine->system->validPasswordLength($pass)) {
                $notify .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_incorrent_password_error'));
            }
            if ($this->mailExists($email)) {
                $notify .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_mail_exist'));
            }
            if ($this->loginIsIncorrent($login)) {
                $notify .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_login_exist'));
            }
            if (strlen($nickname) < 3 || strlen($nickname) > 64) {
                $notify .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_nick_incorrent'));
            }
            if ($notify == null) {
                $aprove_reg_from_email = $engine->extension->getConfig('register_aprove', 'usercontrol', 'components', 'boolean');
                $validate = 0;
                if ($aprove_reg_from_email) {
                    $validate = $engine->system->randomWithUnique($email);
                }
                $stmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_user (`login`, `email`, `nick`, `pass`, `aprove`) VALUES (?,?,?,?,?)");
                $stmt->bindParam(1, $login, PDO::PARAM_STR, 64);
                $stmt->bindParam(2, $email, PDO::PARAM_STR);
                $stmt->bindParam(3, $nickname, PDO::PARAM_STR);
                $stmt->bindParam(4, $md5pwd, PDO::PARAM_STR, 32);
                $stmt->bindParam(5, $validate, PDO::PARAM_STR, 32);
                $stmt->execute();
                $user_obtained_id = $engine->database->con()->lastInsertId();
                $stmt = null;
                $stmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_user_custom (`id`) VALUES (?)");
                $stmt->bindParam(1, $user_obtained_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                if ($aprove_reg_from_email) {
                    $notify .= $engine->template->stringNotify('success', $engine->language->get('usercontrol_register_success_aprove'));
                    $mail_body = $engine->template->get('mail');
                    $link = '<a href="' . $engine->constant->url . '/aprove/' . $validate . '">' . $engine->language->get('usercontrol_reg_mail_aprove_link_text') . ' - ' . $engine->constant->url . '</a>';
                    $mail_body = $engine->template->assign(array('title', 'description', 'text', 'footer'), array($engine->language->get('usercontrol_reg_mail_title'), $engine->language->get('usercontrol_reg_mail_description'), $link, $engine->language->get('usercontrol_reg_mail_footer')), $mail_body);
                    $engine->mail->send($email, $engine->language->get('usercontrol_reg_mail_title'), $mail_body, $nickname);
                } else {
                    $notify .= $engine->template->stringNotify('success', $engine->language->get('usercontrol_register_success_noaprove'));
                }
            }
        }
        $theme = $engine->template->get('register', 'components/usercontrol/');
        $captcha = $engine->hook->get('captcha')->show();
        $openid_url = urlencode($engine->constant->url."/openid/");
        $theme = $engine->template->assign(array('captcha', 'notify', 'openid_url'), array($captcha, $notify, $openid_url), $theme);
        $engine->page->setContentPosition('body', $theme);

    }

    private function recoveryComponent()
    {
        global $engine;
        $pathway = $engine->page->getPathway();
        if ($pathway[1] != null && $engine->system->isInt($pathway[1]) && $pathway[2] != null) {
            $recovery_id = $pathway[1];
            $recovery_hashsum = $pathway[2];
            $stmt = $engine->database->con()->prepare("SELECT userid,password FROM {$engine->constant->db['prefix']}_user_recovery WHERE id =? AND hash = ?");
            $stmt->bindParam(1, $recovery_id, PDO::PARAM_INT);
            $stmt->bindParam(2, $recovery_hashsum, PDO::PARAM_STR, 32);
            $stmt->execute();
            if($stmt->rowCount() == 1) {
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                $userid = $res['userid'];
                $newpwd = $res['password'];
                $stmt = null;
                $stmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_user SET pass = ? WHERE id = ?");
                $stmt->bindParam(1, $newpwd, PDO::PARAM_STR, 32);
                $stmt->bindParam(2, $userid, PDO::PARAM_INT);
                $stmt->execute();
                $engine->system->redirect('/login/recoverysuccess');
            }
            $stmt = null;
            return $engine->template->compile404();
        } else {
            $notify = null;
            if ($engine->system->post('submit')) {
                $email = $engine->system->post('email');
                if (strlen($engine->system->post('captcha')) < 1 || !$engine->hook->get('captcha')->validate($engine->system->post('captcha'))) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_captcha_form_error'));
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_invalid_email_error'));
                }
                if ($notify == null) {
                    $new_password = $engine->system->randomString(rand(8, 12));
                    $hashed_password = $engine->system->doublemd5($new_password);
                    $hash = $engine->system->md5random();
                    $stmt = null;
                    $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_user WHERE email = ?");
                    $stmt->bindParam(1, $email, PDO::PARAM_STR);
                    $stmt->execute();
                    if ($stmt->rowCount() != 1) {
                        $notify .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_recovery_mail_unknown'));
                    } else {
                        // Учетка есть, делаем запись в бд для восстановления
                        $res_stmt = $stmt->fetch(PDO::FETCH_ASSOC);
                        $userid = $res_stmt['id'];
                        $nickname = $res_stmt['nick'];
                        $stmt2 = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_user_recovery (`password`, `hash`, `userid`) VALUES (?, ?, ?)");
                        $stmt2->bindParam(1, $hashed_password, PDO::PARAM_STR, 32);
                        $stmt2->bindParam(2, $hash, PDO::PARAM_STR, 32);
                        $stmt2->bindParam(3, $userid, PDO::PARAM_INT);
                        $stmt2->execute();
                        $request_id = $engine->database->con()->lastInsertId();
                        $recovery_link = $engine->template->assign('recovery_url', $engine->constant->url . '/recovery/' . $request_id . '/' . $hash, $engine->language->get("usercontrol_mail_link_text"));
                        $recovery_desc = $engine->template->assign('new_password', $new_password, $engine->language->get('usercontrol_recovery_mail_description'));

                        $mail_body = $engine->template->get('mail');
                        $mail_body = $engine->template->assign(array('title', 'description', 'text', 'footer'), array($engine->language->get('usercontrol_recovery_mail_title'), $recovery_desc, $recovery_link, $engine->language->get('usercontrol_recovery_mail_footer')), $mail_body);
                        $send = $engine->mail->send($email, $engine->language->get('usercontrol_recovery_mail_title'), $mail_body, $nickname);
                        $notify = $engine->template->stringNotify('success', $engine->language->get('usercontrol_recovery_mail_sended'));
                    }
                }
            }
            $theme = $engine->template->get('recovery', 'components/usercontrol/');
            $captcha = $engine->hook->get('captcha')->show();
            $theme = $engine->template->assign(array('captcha', 'notify'), array($captcha, $notify), $theme);
            $engine->page->setContentPosition('body', $theme);
        }
    }

    private function doLogOut()
    {
        global $engine;
        if ($engine->user->get('id') == NULL) {
            $engine->page->setContentPosition('body', $engine->template->compile404());
            return;
        }
        setcookie('person', '', 0, '/');
        setcookie('token', '', 0, '/');
        $engine->system->redirect();
    }

    private function mailExists($mail)
    {
        global $engine;
        $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_user WHERE email = ?");
        $stmt->bindParam(1, $mail, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            return false;
        }
        return true;
    }

    private function loginIsIncorrent($login)
    {
        global $engine;
        if (strlen($login) < 3 || strlen($login) > 64) {
            return true;
        }
        $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_user WHERE login = ?");
        $stmt->bindParam(1, $login, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result[0] == 0 ? false : true;
    }

}

?>