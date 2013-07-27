<?php
class com_static_back
{
    private $list_count = 10;
    private $com_pathway = "static";

    public function load()
    {
        global $template, $admin, $language, $database, $constant, $system, $user;
        $config_pharse = null;
        $work_body = null;
        $action_page_title = $admin->getExtName() . " : ";
        $stmt = null;
        if ($admin->getAction() == "list" || $admin->getAction() == NULL) {
            $action_page_title .= $language->get('admin_component_static_control');
            $index_start = $admin->getPage();
            if ($system->post('dosearch') && strlen($system->post('search')) > 0) {
                $search_string = "%{$system->post('search')}%";
                $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_static WHERE title like ? OR text like ? ORDER BY id DESC LIMIT ?,?");
                $stmt->bindParam(1, $search_string, PDO::PARAM_STR);
                $stmt->bindParam(2, $search_string, PDO::PARAM_STR);
                $stmt->bindParam(3, $index_start, PDO::PARAM_INT);
                $stmt->bindParam(4, $this->list_count, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_static ORDER BY id DESC LIMIT ?,?");
                $stmt->bindParam(1, $index_start, PDO::PARAM_INT);
                $stmt->bindParam(2, $this->list_count, PDO::PARAM_INT);
                $stmt->execute();
            }
            $static_theme = $template->get('static_list', 'components/');
            $static_manage = $template->get('static_list_manage', 'components/');
            $tbody = null;
            $static_array_data = array();
            while ($res = $stmt->fetch()) {
                $lang_title = unserialize($res['title']);
                $edit_link = "?object=components&id=" . $admin->getID() . "&action=edit&page=" . $res['id'];
                $delete_link = "?object=components&id=" . $admin->getID() . "&action=delete&page=" . $res['id'];
                $manage_link = $template->assign(array('page_edit', 'page_delete'), array($edit_link, $delete_link), $static_manage);
                $title_with_edit = '<a href="' . $edit_link . '">' . $lang_title[$constant->lang] . '</a>';
                $path_with_view = '<a href="' . $constant->url . '/' . $this->com_pathway . '/' . $res['pathway'] . '" target="_blank">/' . $this->com_pathway . '/' . $res['pathway'] . '</a>';
                $static_array_data[] = array($res['id'], $title_with_edit, $path_with_view, $manage_link);
            }
            $tbody = $admin->tplrawTable(array($language->get('admin_component_static_th_id'), $language->get('admin_component_static_th_title'), $language->get('admin_component_static_th_path'), $language->get('admin_component_static_th_edit')), $static_array_data);
            $pagination_list = $admin->tplRawPagination($this->list_count, $this->getPageCount(), 'components');
            $work_body = $template->assign(array('ext_table', 'ext_search_value', 'ext_pagination_list'), array($tbody, $system->post('search'), $pagination_list), $static_theme);
        } elseif ($admin->getAction() == "edit") {
            $notify = null;
            if ($system->post('save')) {

                $page_id = $admin->getPage();
                $page_title = serialize($system->nohtml($system->post('title')));
                $page_way = $system->nohtml($system->post('pathway') . ".html");
                $page_text = serialize($system->post('text'));
                $page_description = serialize($system->nohtml($system->post('description')));
                $page_keywords = serialize($system->nohtml($system->post('keywords')));
                $page_date = $system->toUnixTime($system->post('date'));
                if ($this->check_pageway($page_way, $page_id)) {
                    $stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_com_static SET title = ?, text = ?, pathway = ?, description = ?, keywords = ?, date = ? WHERE id = ?");
                    $stmt->bindParam(1, $page_title, PDO::PARAM_STR);
                    $stmt->bindParam(2, $page_text, PDO::PARAM_STR);
                    $stmt->bindParam(3, $page_way, PDO::PARAM_STR);
                    $stmt->bindParam(4, $page_description, PDO::PARAM_STR);
                    $stmt->bindParam(5, $page_keywords, PDO::PARAM_STR);
                    $stmt->bindParam(6, $page_date, PDO::PARAM_INT);
                    $stmt->bindParam(7, $page_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                    $notify = $template->stringNotify('success', $language->get('admin_component_static_page_saved'), true);
                } else {
                    $notify = $template->stringNotify('error', $language->get('admin_component_static_page_notsaved'), true);
                }
            }
            $action_page_title .= $language->get('admin_component_static_edit');
            $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_static WHERE id = ?");
            $theme_body = $template->get('static_edit', 'components/');
            $theme_li = $template->get('static_language_li', 'components/');
            $theme_head = $template->get('static_edit_header', 'components/');
            $page_id = $admin->getPage();
            $stmt->bindParam(1, $page_id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() != 1) {
                $work_body = "<p>Not found!</p>";
            } else {
                $precompile_body = null;
                $precompile_head = null;
                $is_active_first_element = true;
                foreach($language->getAvailable() as $current_language) {
                    $precompile_head .= $template->assign(array('current_language', 'is_active_element'), array($current_language, $is_active_first_element ? 'class="active"' : null), $theme_li);
                    $precompile_body .= $template->assign(array('current_language', 'is_active_element'), array($current_language, $is_active_first_element ? 'active' : null), $theme_body);
                    $is_active_first_element = false;
                }
                $work_body = $template->assign(array('selecter_li_languages', 'selecter_body_languages'), array($precompile_head, $precompile_body), $theme_head);
                $result = $stmt->fetch();
                $way = $system->noextention($result['pathway']);
                $date = $system->toDate($result['date'], 'd');
                $page_title = unserialize($result['title']);
                $page_text = unserialize($result['text']);
                $page_description = unserialize($result['description']);
                $page_keywords = unserialize($result['keywords']);
                foreach($language->getAvailable() as $current_language) {
                    $work_body = $template->assign(array('static_title_'.$current_language, 'static_text_'.$current_language, 'static_description_'.$current_language, 'static_keywords_'.$current_language),
                        array($page_title[$current_language], $page_text[$current_language], $page_description[$current_language], $page_keywords[$current_language]),
                        $work_body);
                }
                $work_body = $template->assign(array('static_path', 'static_date', 'notify'), array($way, $date, $notify), $work_body);
            }
        } elseif ($admin->getAction() == "add") {
            $action_page_title .= $language->get('admin_component_static_add');
            $theme_body = $template->get('static_edit', 'components/');
            $theme_li = $template->get('static_language_li', 'components/');
            $theme_head = $template->get('static_edit_header', 'components/');
            $notify = null;
            $page_title = array();
            $page_way = null;
            $page_text = array();
            $page_description = array();
            $page_keywords = array();
            $page_date = null;
            if ($system->post('save')) {
                $page_title = $system->nohtml($system->post('title'));
                $page_way = $system->nohtml($system->post('pathway') . ".html");
                $page_text = $system->post('text');
                $page_description = $system->nohtml($system->post('description'));
                $page_keywords = $system->nohtml($system->post('keywords'));
                $page_date = $system->toUnixTime($system->post('date'));
                $page_owner = $user->get('id');
                if (strlen($page_title[$constant->lang]) < 1) {
                    $notify .= $template->stringNotify('error', $language->get('admin_component_static_page_titlenull'));
                } elseif (!$this->check_pageway($page_way)) {
                    $notify .= $template->stringNotify('error', $language->get('admin_component_static_page_pathused'), true);
                } else {
                    $serial_title = serialize($page_title);
                    $serial_text = serialize($page_text);
                    $serial_description = serialize($page_description);
                    $serial_keywords = serialize($page_keywords);
                    if($page_date == null)
                        $page_date = time();
                    $stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_com_static (title, text, owner, pathway, date, description, keywords) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bindParam(1, $serial_title, PDO::PARAM_STR);
                    $stmt->bindParam(2, $serial_text, PDO::PARAM_STR);
                    $stmt->bindParam(3, $page_owner, PDO::PARAM_INT);
                    $stmt->bindParam(4, $page_way, PDO::PARAM_STR);
                    $stmt->bindParam(5, $page_date, PDO::PARAM_INT);
                    $stmt->bindParam(6, $serial_description, PDO::PARAM_STR);
                    $stmt->bindParam(7, $serial_keywords, PDO::PARAM_STR);
                    $stmt->execute();
                    $system->redirect($_SERVER['PHP_SELF'] . "?object=components&id=" . $admin->getID());
                }
            }
            $precompile_body = null;
            $precompile_head = null;
            $is_active_first_element = true;
            foreach($language->getAvailable() as $current_language) {
                $precompile_head .= $template->assign(array('current_language', 'is_active_element'), array($current_language, $is_active_first_element ? 'class="active"' : null), $theme_li);
                $precompile_body .= $template->assign(array('current_language', 'is_active_element'), array($current_language, $is_active_first_element ? 'active' : null), $theme_body);
                $is_active_first_element = false;
            }
            $work_body = $template->assign(array('selecter_li_languages', 'selecter_body_languages', 'notify'), array($precompile_head, $precompile_body, $notify), $theme_head);
            if($notify != null) {
                foreach($language->getAvailable() as $current_language) {
                    $work_body = $template->assign(array('static_title_'.$current_language, 'static_text_'.$current_language, 'static_description_'.$current_language, 'static_keywords_'.$current_language),
                    array($page_title[$current_language], $page_text[$current_language], $page_description[$current_language], $page_keywords[$current_language]),
                    $work_body);
                }
                $work_body = $template->assign(array('static_path', 'static_date'), array($system->noextention($page_way), ''), $work_body);
            }
        } elseif ($admin->getAction() == "delete" && $admin->getPage() > 0) {
            $action_page_title .= $language->get('admin_component_static_delete');
            $page_id = $admin->getPage();
            if ($system->post('submit')) {
                $stmt = $database->con()->prepare("DELETE FROM {$constant->db['prefix']}_com_static WHERE id = ?");
                $stmt->bindParam(1, $page_id, PDO::PARAM_INT);
                $stmt->execute();
                $work_body = $language->get('admin_component_static_delete_success_msg');
            } else {
                $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_static WHERE id = ?");
                $stmt->bindParam(1, $page_id, PDO::PARAM_INT);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $res = $stmt->fetch();
                    $serial_title = unserialize($res['title']);
                    $array_data[] = array($res['id'], $serial_title[$language->getCustom()], $res['pathway']);
                    $tbody = $admin->tplrawTable(array($language->get('admin_component_static_th_id'), $language->get('admin_component_static_th_title'), $language->get('admin_component_static_th_path')),
                        $array_data);

                }
                $theme_delete = $template->assign(array('static_delete_info', 'cancel_link'),
                    array($tbody, '?object=components&id=' . $admin->getId()),
                    $template->get('static_delete', 'components/'));
                $work_body = $theme_delete;
            }
        }
        $menu_theme = $template->get('config_menu');
        $menu_link = null;
        $menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id=' . $admin->getID() . '&action=list', $language->get('admin_component_static_control')), $menu_theme);
        $menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id=' . $admin->getID() . '&action=add', $language->get('admin_component_static_add')), $menu_theme);
        $body_form = $template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $template->get('config_head'));
        return $body_form;
    }

    private function getPageCount()
    {
        global $database, $constant;
        $stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_com_static");
        $stmt->execute();
        $result = $stmt->fetch();
        return intval($result[0] / $this->list_count);
    }

    private function check_pageway($way, $id = 0)
    {
        global $database, $constant;
        if (preg_match('/[\'~`\!@#\$%\^&\*\(\)+=\{\}\[\]\|;:"\<\>,\?\\\]/', $way)) {
            return false;
        }
        $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_static WHERE pathway = ? AND id != ?");
        $stmt->bindParam(1, $way, PDO::PARAM_STR);
        $stmt->bindParam(2, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() == 0 ? true : false;
    }
}

?>