<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

class com_usercontrol_back
{
    private $list_count = 10;

    public function load()
    {
        global $engine;
        $action_page_title = $engine->admin->getExtName() . " : ";
        $menu_theme = $engine->template->get('config_menu');
        $menu_link = null;
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id=' . $engine->admin->getID() . '&action=list', $engine->language->get('admin_component_usercontrol_manage')), $menu_theme);
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id=' . $engine->admin->getID() . '&action=group', $engine->language->get('admin_component_usercontrol_group')), $menu_theme);
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id=' . $engine->admin->getID() . '&action=ban', $engine->language->get('admin_component_usercontrol_serviceban')), $menu_theme);
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id=' . $engine->admin->getID() . '&action=settings', $engine->language->get('admin_component_usercontrol_settings')), $menu_theme);
        $work_body = null;
        if ($engine->admin->getAction() == "list" || $engine->admin->getAction() == null) {
            $action_page_title .= $engine->language->get('admin_component_usercontrol_manage');
            $index_start = $engine->admin->getPage();
            $list_theme = $engine->template->get('usercontrol_list', 'components/');
            $manage_theme = $engine->template->get('usercontrol_list_manage', 'components/');
            $stmt = null;
            if ($engine->system->post('dosearch') && strlen($engine->system->post('search')) > 0) {
                $search_string = "%{$engine->system->post('search')}%";
                $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_user WHERE login like ? OR email like ? OR nick like ? ORDER BY id DESC LIMIT ?,?");
                $stmt->bindParam(1, $search_string, PDO::PARAM_STR);
                $stmt->bindParam(2, $search_string, PDO::PARAM_STR);
                $stmt->bindParam(3, $search_string, PDO::PARAM_STR);
                $stmt->bindParam(4, $index_start, PDO::PARAM_INT);
                $stmt->bindParam(5, $this->list_count, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_user ORDER BY id DESC LIMIT ?,?");
                $stmt->bindParam(1, $index_start, PDO::PARAM_INT);
                $stmt->bindParam(2, $this->list_count, PDO::PARAM_INT);
                $stmt->execute();
            }
            $user_data_array = array();
            while ($stmt != null && $res = $stmt->fetch()) {
                $manage_data = $engine->template->assign(array('ext_id', 'ext_page'), array($engine->admin->getID(), $res['id']), $manage_theme);
                $user_data_array[] = array($res['id'], $res['login'], $res['email'], $manage_data);
            }
            $table_result = $engine->admin->tplrawTable(array($engine->language->get('admin_component_usercontrol_th_id'), $engine->language->get('admin_component_usercontrol_th_login'), $engine->language->get('admin_component_usercontrol_th_email'), $engine->language->get('admin_component_usercontrol_th_edit')), $user_data_array);
            $pagination_list = $engine->admin->tplRawPagination($this->list_count, $this->getPageCount(), 'components');
            $work_body = $engine->template->assign(array('ext_table_data', 'ext_search_value', 'ext_pagination_list'), array($table_result, $engine->system->post('search'), $pagination_list), $list_theme);
        } elseif ($engine->admin->getAction() == "settings") {
            if ($engine->system->post('submit')) {
                $save_try = $engine->admin->trySaveConfigs();
                if ($save_try)
                    $work_body .= $engine->template->stringNotify('success', $engine->language->get('admin_extension_config_update_success'));
                else
                    $work_body .= $engine->template->stringNotify('error', $engine->language->get('admin_extension_config_update_fail'));
            }

            $action_page_title .= $engine->language->get('admin_component_usercontrol_settings');
            $config_form = $engine->template->get('config_form');
            $config_set = null;

            $config_set .= $engine->language->get('admin_component_usercontrol_description');
            $config_set .= $engine->admin->tplSettingsDirectory($engine->language->get('admin_component_usercontrol_first_data'));
            $config_set .= $engine->admin->tplSettingsSelectYorN('config:login_captcha', $engine->language->get('admin_component_usercontrol_config_logincaptcha_name'), $engine->language->get('admin_component_usercontrol_config_logincaptcha_desc'), $engine->admin->getConfig('login_captcha', 'boolean'));
            $config_set .= $engine->admin->tplSettingsSelectYorN('config:register_captcha', $engine->language->get('admin_component_usercontrol_config_regcaptcha_name'), $engine->language->get('admin_component_usercontrol_config_regcaptcha_desc'), $engine->admin->getConfig('register_captcha', 'boolean'));
            $config_set .= $engine->admin->tplSettingsSelectYorN('config:register_aprove', $engine->language->get('admin_component_usercontrol_config_aprovereg_name'), $engine->language->get('admin_component_usercontrol_config_aprovereg_desc'), $engine->admin->getConfig('register_aprove', 'boolean'));
            $config_set .= $engine->admin->tplSettingsSelectYorN('config:use_openid', $engine->language->get('admin_component_usercontrol_config_openid_name'), $engine->language->get('admin_component_usercontrol_config_openid_desc'), $engine->admin->getConfig('use_openid', 'boolean'));
            $config_set .= $engine->admin->tplSettingsDirectory($engine->language->get('admin_component_usercontrol_second_data'));
            $config_set .= $engine->admin->tplSettingsSelectYorN('config:profile_view', $engine->language->get('admin_component_usercontrol_config_guest_access_name'), $engine->language->get('admin_component_usercontrol_config_guest_access_desc'), $engine->admin->getConfig('profile_view', 'boolean'));
            $config_set .= $engine->admin->tplSettingsInputText('config:wall_post_count', $engine->admin->getConfig('wall_post_count', 'int'), $engine->language->get('admin_component_usercontrol_config_userwall_name'), $engine->language->get('admin_component_usercontrol_config_userwall_desc'));
            $config_set .= $engine->admin->tplSettingsInputText('config:marks_post_count', $engine->admin->getConfig('marks_post_count', 'int'), $engine->language->get('admin_component_usercontrol_config_marks_name'), $engine->language->get('admin_component_usercontrol_config_marks_desc'));
            $config_set .= $engine->admin->tplSettingsInputText('config:friend_page_count', $engine->admin->getConfig('friend_page_count', 'int'), $engine->language->get('admin_component_usercontrol_config_friend_page_count_name'), $engine->language->get('admin_component_usercontrol_config_friend_page_count_desc'));
            $config_set .= $engine->admin->tplSettingsInputText('config:wall_post_delay', $engine->admin->getConfig('wall_post_delay', 'int'), $engine->language->get('admin_component_usercontrol_config_wall_post_delay_name'), $engine->language->get('admin_component_usercontrol_config_wall_post_delay_desc'));
            $config_set .= $engine->admin->tplSettingsInputText('config:pm_count', $engine->admin->getConfig('pm_count', 'int'), $engine->language->get('admin_component_usercontrol_config_pm_count_name'), $engine->language->get('admin_component_usercontrol_config_pm_count_desc'));
            $config_set .= $engine->admin->tplSettingsSelectYorN('config:balance_view', $engine->language->get('admin_component_usercontrol_config_use_balance_name'), $engine->language->get('admin_component_usercontrol_config_use_balance_desc'), $engine->admin->getConfig('balance_view', 'boolean'));
            $config_set .= $engine->admin->tplSettingsDirectory($engine->language->get('admin_component_usercontrol_thred_data'));
            $config_set .= $engine->admin->tplSettingsInputText('config:userlist_count', $engine->admin->getConfig('userlist_count', 'int'), $engine->language->get('admin_component_usercontrol_config_userlist_count_name'), $engine->language->get('admin_component_usercontrol_config_userlist_count_desc'));

            $work_body .= $engine->template->assign('ext_form', $config_set, $config_form);

        } elseif ($engine->admin->getAction() == "edit") {
            $action_page_title .= $engine->language->get('admin_component_usercontrol_edit');
            $object_user_id = $engine->admin->getPage();
            $notify = null;
            if ($engine->user->exists($object_user_id)) {
                if ($engine->system->post('submit')) {
                    $new_nick = $engine->system->post('nick');
                    $new_sex = $engine->system->post('sex');
                    $new_phone = $engine->system->post('phone');
                    $new_webpage = $engine->system->post('webpage');
                    $new_birthday = $engine->system->post('birthday');
                    $new_status = $engine->system->post('status');
                    $new_groupid = $engine->system->post('groupid');
                    $new_pass = strlen($engine->system->post('newpass')) > 3 ? $engine->system->doublemd5($engine->system->post('newpass')) : $engine->user->get('pass', $object_user_id);
                    $stmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_user a INNER JOIN {$engine->constant->db['prefix']}_user_custom b USING(id) SET a.nick = ?, a.pass = ?, b.birthday = ?, b.sex = ?, b.phone = ?, b.webpage = ?, b.status = ?, a.access_level = ? WHERE a.id = ?");
                    $stmt->bindParam(1, $new_nick, PDO::PARAM_STR);
                    $stmt->bindParam(2, $new_pass, PDO::PARAM_STR, 32);
                    $stmt->bindParam(3, $new_birthday, PDO::PARAM_STR);
                    $stmt->bindParam(4, $new_sex, PDO::PARAM_INT);
                    $stmt->bindParam(5, $new_phone, PDO::PARAM_STR);
                    $stmt->bindParam(6, $new_webpage, PDO::PARAM_STR);
                    $stmt->bindParam(7, $new_status, PDO::PARAM_STR);
                    $stmt->bindParam(8, $new_groupid, PDO::PARAM_INT);
                    $stmt->bindParam(9, $object_user_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $engine->user->fulluseroverload($object_user_id);
                    $notify .= $engine->template->stringNotify('success', $engine->language->get('admin_component_usercontrol_edit_notify_success'), true);
                }
                $theme_option_active = $engine->template->get('form_option_item_active');
                $theme_option_inactive = $engine->template->get('form_option_item_inactive');
                $prepared_option = null;
                $stmt = $engine->database->con()->prepare("SELECT group_id, group_name FROM {$engine->constant->db['prefix']}_user_access_level");
                $stmt->execute();
                $resFetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($resFetch as $item_access) {
                    if ($item_access['group_id'] == $engine->user->get('group_id', $object_user_id)) {
                        $prepared_option .= $engine->template->assign(array('option_value', 'option_name'), array($item_access['group_id'], $item_access['group_name']), $theme_option_active);
                    } else {
                        $prepared_option .= $engine->template->assign(array('option_value', 'option_name'), array($item_access['group_id'], $item_access['group_name']), $theme_option_inactive);
                    }
                }
                $theme_edit = $engine->template->get('usercontrol_user_edit', 'components/');
                $work_body .= $engine->template->assign(array('target_user_id', 'target_user_login', 'target_user_nick', 'target_user_phone', 'target_user_sex', 'target_user_webpage', 'target_user_birthday', 'target_user_status', 'option_group_prepare', 'notify'),
                    array($object_user_id, $engine->user->get('login', $object_user_id), $engine->user->get('nick', $object_user_id), $engine->user->customget('phone', $object_user_id), $engine->user->customget('sex', $object_user_id), $engine->user->customget('webpage', $object_user_id), $engine->user->customget('birthday', $object_user_id), $engine->user->customget('status', $object_user_id), $prepared_option, $notify),
                    $theme_edit);
            }
        } elseif ($engine->admin->getAction() == "delete") {
            $target_user_id = $engine->admin->getPage();
            $action_page_title .= $engine->language->get('admin_component_usercontrol_delete');
            $target_user_id = $engine->admin->getPage();
            if ($engine->system->isInt($target_user_id) && $engine->user->exists($target_user_id)) {
                $notify = null;
                if ($engine->system->post('deleteuser')) {
                    // защита от дибилов
                    if ($target_user_id == $engine->system->post('target_user_id')) {
                        if ($engine->user->get('access_level', $target_user_id) == 3) {
                            $notify .= $engine->template->stringNotify('error', $engine->language->get('admin_component_usercontrol_delete_admin_fail', true));
                        } else {
                            // Логика работы PDO в данный момент наступила на грабли и убила его создателя (:
                            // выполнить в 1 мультикверь данное невозможно по непонятной причине.
                            // Удаление через DELETE [params] FROM table AS hot INNER JOIN table2 AS hot2 ON table.id = table2.id WHERE table.id = ?
                            // не приносит результата вовсе при выключенном INNODB (а это не есть базовым тербованием к цмс)
                            // поэтому код ниже может вызвать у вас приступ паники или боли в 5ой точке
                            // если у вас есть лучший вариант - присылайте на github.
                            $stmt = $engine->database->con()->prepare("DELETE FROM {$engine->constant->db['prefix']}_user WHERE id = ?");
                            $stmt->bindParam(1, $target_user_id, PDO::PARAM_INT);
                            $stmt->execute();
                            $stmt = null;
                            $stmt = $engine->database->con()->prepare("DELETE FROM {$engine->constant->db['prefix']}_user_custom WHERE id = ?");
                            $stmt->bindParam(1, $target_user_id, PDO::PARAM_INT);
                            $stmt->execute();
                            $stmt = null;
                            $engine->system->redirect(file_name . "?object=components&id=" . $engine->admin->getID());
                            // TODO: удаление из фриендлиста
                        }
                    }
                }
                $theme_delete = $engine->template->get('usercontrol_user_delete', 'components/');
                $work_body = $engine->template->assign(array('target_user_id', 'target_user_login', 'target_user_email', 'notify'),
                    array($target_user_id, $engine->user->get('login', $target_user_id), $engine->user->get('email', $target_user_id), $notify),
                    $theme_delete);
            }
        } elseif ($engine->admin->getAction() == "group") {
            if ($engine->system->post('addgroup')) {
                $stmt = $engine->database->con()->prepare("SELECT MAX(group_id) FROM {$engine->constant->db['prefix']}_user_access_level");
                $stmt->execute();
                $resTemp = $stmt->fetch();
                $lastGroupId = $resTemp[0];
                $stmt = null;
                $lastGroupId++;
                $new_group_name = "New Group";
                $stmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_user_access_level (`group_id`, `group_name`) VALUES(?, ?)");
                $stmt->bindParam(1, $lastGroupId, PDO::PARAM_INT);
                $stmt->bindParam(2, $new_group_name, PDO::PARAM_STR);
                $stmt->execute();
                $stmt = null;
            } elseif ($engine->system->post('remove')) {
                $key_for_delete = array_keys($engine->system->post('remove'));
                $delete_group_id = $key_for_delete[0];
                if ($engine->system->isInt($delete_group_id)) {
                    $stmt = $engine->database->con()->prepare("DELETE FROM {$engine->constant->db['prefix']}_user_access_level WHERE group_id = ?");
                    $stmt->bindParam(1, $delete_group_id, PDO::PARAM_INT);
                    $stmt->execute();
                }
            } elseif ($engine->system->post('acesssave')) {
                $post_access_table = $engine->system->post('access');
                $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_user_access_level");
                $stmt->execute();
                $fetchRow = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt = null;
                foreach ($fetchRow as $access_single) {
                    $current_gid = $access_single['group_id'];
                    $current_access = $post_access_table[$current_gid];
                    $update_column_data = array();
                    foreach ($access_single as $column => $value) {
                        if ($current_access[$column] == null) {
                            $update_column_data[$column] = "0";
                        } elseif ($current_access[$column] == "on") {
                            $update_column_data[$column] = "1";
                        } else {
                            $update_column_data[$column] = $current_access[$column];
                        }
                    }
                    $query_prepared_setter = $engine->system->prepareKeyDataToDbUpdate($update_column_data);
                    $stmt = $engine->database->con()->prepare("UPDATE `{$engine->constant->db['prefix']}_user_access_level` SET $query_prepared_setter WHERE `group_id` = ?");
                    $stmt->bindParam(1, $current_gid, PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                }
            }
            $action_page_title .= $engine->language->get('admin_component_usercontrol_group');
            $group_theme = $engine->template->get('usercontrol_group_manage', 'components/');
            $column_theme = $engine->template->get('usercontrol_group_manage_th', 'components/');
            $checkbox_theme = $engine->template->get('usercontrol_group_manage_checkbox', 'components/');
            $input_theme = $engine->template->get('usercontrol_group_manage_input', 'components/');
            $delete_theme = $engine->template->get('usercontrol_group_manage_delete', 'components/');
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_user_access_level");
            $stmt->execute();
            $resultFetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $columnNames = array();
            $rowContainer = array();
            $isFirstRun = true;
            foreach ($resultFetch as $values) {
                $group_id = 0;
                $rowItems = array();
                foreach ($values as $columnName => $columnData) {
                    if ($columnName == "group_id") {
                        $group_id = $columnData;
                    }
                    if ($isFirstRun) {
                        $columnNames[] = $engine->template->assign(array('group_column_helptext', 'group_column_name'), array($engine->language->get('admin_component_usercontrol_group_column_' . $columnName), $columnName), $column_theme);
                    }
                    if (($columnData == "0" || $columnData == "1") && $columnName != "group_id") {
                        $checked = null;
                        if ($columnData == "1") {
                            $checked = "checked";
                        }
                        $rowItems[] = $engine->template->assign(array('checkbox_name', 'is_checked'), array("access[{$group_id}][{$columnName}]", $checked), $checkbox_theme);
                    } else {
                        $rowItems[] = $engine->template->assign(array('input_value', 'input_name'), array($columnData, "access[{$group_id}][{$columnName}]"), $input_theme);
                    }
                }
                $rowItems[] = $engine->template->assign('group_id', $group_id, $delete_theme);
                $isFirstRun = false;
                $rowContainer[] = $rowItems;
            }
            $columnNames[] = $engine->language->get('admin_component_usercontrol_group_column_th_action');
            $edit_table = $engine->admin->tplRawTable($columnNames, $rowContainer);
            $work_body = $engine->template->assign(array('edit_table', 'component_id'), array($edit_table, $engine->admin->getID()), $group_theme);
        } elseif ($engine->admin->getAction() == "ban") {
            $notify = null;
            $continue_block = null;
            if ($engine->system->post('ipblock')) {
                $userip = $engine->system->validIP($engine->system->post('userip'));
                if ($userip) {
                    $except_time = strtotime($engine->system->post('enddate'));
                    // проверяем, возможно данный ip уже заблокирован, зачем нам дубли и попаболь?
                    $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_user_block WHERE ip = ?");
                    $stmt->bindParam(1, $userip, PDO::PARAM_STR);
                    $stmt->execute();
                    $resIpFetch = $stmt->fetch();
                    $stmt = null;
                    // запись уже есть
                    if ($resIpFetch[0] > 0) {
                        $stmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_user_block SET express = ? WHERE ip = ?");
                        $stmt->bindParam(1, $except_time, PDO::PARAM_INT);
                        $stmt->bindParam(2, $userip, PDO::PARAM_STR);
                        $stmt->execute();
                        $stmt = null;
                        $notify .= $engine->template->stringNotify('success', $engine->language->get('admin_component_usercontrol_ban_ip_refreshed'));
                    } // иначе это новый бан
                    else {
                        $stmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_user_block (`ip`, `express`) VALUES (?, ?)");
                        $stmt->bindParam(1, $userip, PDO::PARAM_STR);
                        $stmt->bindParam(2, $except_time, PDO::PARAM_INT);
                        $stmt->execute();
                        $notify .= $engine->template->stringNotify('success', $engine->language->get('admin_component_usercontrol_ban_ip_setted'));
                    }
                } else {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('admin_component_usercontrol_ban_wrong_data'), true);
                }
            } elseif ($engine->system->post('idorloginblock')) {
                $idorlogin = $engine->system->post('userdata');
                $stmt = $engine->database->con()->prepare("SELECT id FROM {$engine->constant->db['prefix']}_user WHERE id = ? or login = ?");
                $stmt->bindParam(1, $idorlogin, PDO::PARAM_STR);
                $stmt->bindParam(2, $idorlogin, PDO::PARAM_STR);
                $stmt->execute();
                if ($rowUser = $stmt->fetch()) {
                    $target_id = $rowUser['id'];
                    $continue_block = $engine->template->assign('block_user_id', $target_id, $engine->template->get('usercontrol_ban_pers', 'components/'));
                } else {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('admin_component_usercontrol_ban_wrong_data'), true);
                }
            } elseif ($engine->system->post('banuserid')) {
                // 2ая стадия блокировки
                $ban_user_id = $engine->system->post('blockuserid');
                $ban_execpt_time = strtotime($engine->system->post('enddate'));
                $stmt = $engine->database->con()->prepare("SELECT DISTINCT ip FROM {$engine->constant->db['prefix']}_statistic WHERE reg_id = ?");
                $stmt->bindParam(1, $ban_user_id, PDO::PARAM_INT);
                $stmt->execute();
                while ($result = $stmt->fetch()) {
                    $bstmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_user_block(user_id, ip, express) VALUES (?, ?, ?)");
                    $bstmt->bindParam(1, $ban_user_id, PDO::PARAM_INT);
                    $bstmt->bindParam(2, $result['ip'], PDO::PARAM_STR);
                    $bstmt->bindParam(3, $ban_execpt_time, PDO::PARAM_INT);
                    $bstmt->execute();
                    $bstmt = null;
                }
                $notify .= $engine->template->stringNotify('success', $engine->language->get('admin_component_usercontrol_ban_ip_setted'));
            }
            $action_page_title .= $engine->language->get('admin_component_usercontrol_serviceban');
            $ban_theme = $engine->template->get('usercontrol_ban', 'components/');
            $work_body .= $engine->template->assign(array('notify', 'continue_block'), array($notify, $continue_block), $ban_theme);
        }
        $body_form = $engine->template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $engine->template->get('config_head'));
        return $body_form;
    }

    private function getPageCount()
    {
        global $engine;
        $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_user");
        $stmt->execute();
        $result = $stmt->fetch();
        return intval($result[0] / $this->list_count);
    }
}

?>