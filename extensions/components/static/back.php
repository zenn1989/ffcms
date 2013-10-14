<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

class com_static_back
{
    private $list_count = 10;
    private $com_pathway = "static";

    public function load()
    {
        global $engine;
        $config_pharse = null;
        $work_body = null;
        $action_page_title = $engine->admin->getExtName() . " : ";
        $stmt = null;
        if ($engine->admin->getAction() == "list" || $engine->admin->getAction() == NULL) {
            $action_page_title .= $engine->language->get('admin_component_static_control');
            $index_start = $engine->admin->getPage();
            if ($engine->system->post('dosearch') && strlen($engine->system->post('search')) > 0) {
                $search_string = "%{$engine->system->post('search')}%";
                $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_static WHERE title like ? OR text like ? ORDER BY id DESC LIMIT ?,?");
                $stmt->bindParam(1, $search_string, PDO::PARAM_STR);
                $stmt->bindParam(2, $search_string, PDO::PARAM_STR);
                $stmt->bindParam(3, $index_start, PDO::PARAM_INT);
                $stmt->bindParam(4, $this->list_count, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_static ORDER BY id DESC LIMIT ?,?");
                $stmt->bindParam(1, $index_start, PDO::PARAM_INT);
                $stmt->bindParam(2, $this->list_count, PDO::PARAM_INT);
                $stmt->execute();
            }
            $static_theme = $engine->template->get('static_list', 'components/');
            $static_manage = $engine->template->get('static_list_manage', 'components/');
            $tbody = null;
            $static_array_data = array();
            while ($res = $stmt->fetch()) {
                $lang_title = unserialize($res['title']);
                $edit_link = "?object=components&id=" . $engine->admin->getID() . "&action=edit&page=" . $res['id'];
                $delete_link = "?object=components&id=" . $engine->admin->getID() . "&action=delete&page=" . $res['id'];
                $manage_link = $engine->template->assign(array('page_edit', 'page_delete'), array($edit_link, $delete_link), $static_manage);
                $title_with_edit = '<a href="' . $edit_link . '">' . $lang_title[$engine->constant->lang] . '</a>';
                $path_with_view = '<a href="' . $engine->constant->url . '/' . $this->com_pathway . '/' . $res['pathway'] . '" target="_blank">/' . $this->com_pathway . '/' . $res['pathway'] . '</a>';
                $static_array_data[] = array($res['id'], $title_with_edit, $path_with_view, $manage_link);
            }
            $tbody = $engine->admin->tplrawTable(array($engine->language->get('admin_component_static_th_id'), $engine->language->get('admin_component_static_th_title'), $engine->language->get('admin_component_static_th_path'), $engine->language->get('admin_component_static_th_edit')), $static_array_data);
            $pagination_list = $engine->admin->tplRawPagination($this->list_count, $this->getPageCount(), 'components');
            $work_body = $engine->template->assign(array('ext_table', 'ext_search_value', 'ext_pagination_list'), array($tbody, $engine->system->post('search'), $pagination_list), $static_theme);
        } elseif ($engine->admin->getAction() == "edit") {
            $notify = null;
            if ($engine->system->post('save')) {

                $page_id = $engine->admin->getPage();
                $page_title = serialize($engine->system->nohtml($engine->system->post('title')));
                $page_way = $engine->system->nohtml($engine->system->post('pathway') . ".html");
                $page_text = serialize($engine->system->post('text'));
                $page_description = serialize($engine->system->nohtml($engine->system->post('description')));
                $page_keywords = serialize($engine->system->nohtml($engine->system->post('keywords')));
                $page_date = $engine->system->post('current_date') == "on" ? time() : $engine->system->toUnixTime($engine->system->post('date'));
                if ($this->check_pageway($page_way, $page_id)) {
                    $stmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_com_static SET title = ?, text = ?, pathway = ?, description = ?, keywords = ?, date = ? WHERE id = ?");
                    $stmt->bindParam(1, $page_title, PDO::PARAM_STR);
                    $stmt->bindParam(2, $page_text, PDO::PARAM_STR);
                    $stmt->bindParam(3, $page_way, PDO::PARAM_STR);
                    $stmt->bindParam(4, $page_description, PDO::PARAM_STR);
                    $stmt->bindParam(5, $page_keywords, PDO::PARAM_STR);
                    $stmt->bindParam(6, $page_date, PDO::PARAM_INT);
                    $stmt->bindParam(7, $page_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                    $notify = $engine->template->stringNotify('success', $engine->language->get('admin_component_static_page_saved'), true);
                } else {
                    $notify = $engine->template->stringNotify('error', $engine->language->get('admin_component_static_page_notsaved'), true);
                }
            }
            $action_page_title .= $engine->language->get('admin_component_static_edit');
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_static WHERE id = ?");
            $theme_body = $engine->template->get('static_edit', 'components/');
            $theme_li = $engine->template->get('static_language_li', 'components/');
            $theme_head = $engine->template->get('static_edit_header', 'components/');
            $page_id = $engine->admin->getPage();
            $stmt->bindParam(1, $page_id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() != 1) {
                $work_body = "<p>Not found!</p>";
            } else {
                $precompile_body = null;
                $precompile_head = null;
                $is_active_first_element = true;
                foreach($engine->language->getAvailable() as $current_language) {
                    $precompile_head .= $engine->template->assign(array('current_language', 'is_active_element'), array($current_language, $is_active_first_element ? 'class="active"' : null), $theme_li);
                    $precompile_body .= $engine->template->assign(array('current_language', 'is_active_element'), array($current_language, $is_active_first_element ? 'active' : null), $theme_body);
                    $is_active_first_element = false;
                }
                $work_body = $engine->template->assign(array('selecter_li_languages', 'selecter_body_languages'), array($precompile_head, $precompile_body), $theme_head);
                $result = $stmt->fetch();
                $way = $engine->system->noextention($result['pathway']);
                $date = $engine->system->toDate($result['date'], 'd');
                $page_title = unserialize($result['title']);
                $page_text = unserialize($result['text']);
                $page_description = unserialize($result['description']);
                $page_keywords = unserialize($result['keywords']);
                foreach($engine->language->getAvailable() as $current_language) {
                    $work_body = $engine->template->assign(array('static_title_'.$current_language, 'static_text_'.$current_language, 'static_description_'.$current_language, 'static_keywords_'.$current_language),
                        array($page_title[$current_language], $page_text[$current_language], $page_description[$current_language], $page_keywords[$current_language]),
                        $work_body);
                }
                $work_body = $engine->template->assign(array('static_path', 'static_date', 'notify'), array($way, $date, $notify), $work_body);
            }
        } elseif ($engine->admin->getAction() == "add") {
            $action_page_title .= $engine->language->get('admin_component_static_add');
            $theme_body = $engine->template->get('static_edit', 'components/');
            $theme_li = $engine->template->get('static_language_li', 'components/');
            $theme_head = $engine->template->get('static_edit_header', 'components/');
            $notify = null;
            $page_title = array();
            $page_way = null;
            $page_text = array();
            $page_description = array();
            $page_keywords = array();
            $page_date = null;
            if ($engine->system->post('save')) {
                $page_title = $engine->system->nohtml($engine->system->post('title'));
                $page_way = $engine->system->nohtml($engine->system->post('pathway') . ".html");
                $page_text = $engine->system->post('text');
                $page_description = $engine->system->nohtml($engine->system->post('description'));
                $page_keywords = $engine->system->nohtml($engine->system->post('keywords'));
                $page_date = $engine->system->post('current_date') == "on" ? time() : $engine->system->toUnixTime($engine->system->post('date'));
                $page_owner = $engine->user->get('id');
                if (strlen($page_title[$engine->constant->lang]) < 1) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('admin_component_static_page_titlenull'));
                } elseif (!$this->check_pageway($page_way)) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('admin_component_static_page_pathused'), true);
                } else {
                    $serial_title = serialize($page_title);
                    $serial_text = serialize($page_text);
                    $serial_description = serialize($page_description);
                    $serial_keywords = serialize($page_keywords);
                    if($page_date == null)
                        $page_date = time();
                    $stmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_com_static (title, text, owner, pathway, date, description, keywords) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bindParam(1, $serial_title, PDO::PARAM_STR);
                    $stmt->bindParam(2, $serial_text, PDO::PARAM_STR);
                    $stmt->bindParam(3, $page_owner, PDO::PARAM_INT);
                    $stmt->bindParam(4, $page_way, PDO::PARAM_STR);
                    $stmt->bindParam(5, $page_date, PDO::PARAM_INT);
                    $stmt->bindParam(6, $serial_description, PDO::PARAM_STR);
                    $stmt->bindParam(7, $serial_keywords, PDO::PARAM_STR);
                    $stmt->execute();
                    $engine->system->redirect($_SERVER['PHP_SELF'] . "?object=components&id=" . $engine->admin->getID());
                }
            }
            $precompile_body = null;
            $precompile_head = null;
            $is_active_first_element = true;
            foreach($engine->language->getAvailable() as $current_language) {
                $precompile_head .= $engine->template->assign(array('current_language', 'is_active_element'), array($current_language, $is_active_first_element ? 'class="active"' : null), $theme_li);
                $precompile_body .= $engine->template->assign(array('current_language', 'is_active_element'), array($current_language, $is_active_first_element ? 'active' : null), $theme_body);
                $is_active_first_element = false;
            }
            $work_body = $engine->template->assign(array('selecter_li_languages', 'selecter_body_languages', 'notify'), array($precompile_head, $precompile_body, $notify), $theme_head);
            if($notify != null) {
                foreach($engine->language->getAvailable() as $current_language) {
                    $work_body = $engine->template->assign(array('static_title_'.$current_language, 'static_text_'.$current_language, 'static_description_'.$current_language, 'static_keywords_'.$current_language),
                    array($page_title[$current_language], $page_text[$current_language], $page_description[$current_language], $page_keywords[$current_language]),
                    $work_body);
                }
                $work_body = $engine->template->assign(array('static_path', 'static_date'), array($engine->system->noextention($page_way), ''), $work_body);
            }
        } elseif ($engine->admin->getAction() == "delete" && $engine->admin->getPage() > 0) {
            $action_page_title .= $engine->language->get('admin_component_static_delete');
            $page_id = $engine->admin->getPage();
            if ($engine->system->post('submit')) {
                $stmt = $engine->database->con()->prepare("DELETE FROM {$engine->constant->db['prefix']}_com_static WHERE id = ?");
                $stmt->bindParam(1, $page_id, PDO::PARAM_INT);
                $stmt->execute();
                $work_body = $engine->language->get('admin_component_static_delete_success_msg');
            } else {
                $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_static WHERE id = ?");
                $stmt->bindParam(1, $page_id, PDO::PARAM_INT);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $res = $stmt->fetch();
                    $serial_title = unserialize($res['title']);
                    $array_data[] = array($res['id'], $serial_title[$engine->language->getCustom()], $res['pathway']);
                    $tbody = $engine->admin->tplrawTable(array($engine->language->get('admin_component_static_th_id'), $engine->language->get('admin_component_static_th_title'), $engine->language->get('admin_component_static_th_path')),
                        $array_data);

                }
                $theme_delete = $engine->template->assign(array('static_delete_info', 'cancel_link'),
                    array($tbody, '?object=components&id=' . $engine->admin->getId()),
                    $engine->template->get('static_delete', 'components/'));
                $work_body = $theme_delete;
            }
        }
        $menu_theme = $engine->template->get('config_menu');
        $menu_link = null;
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id=' . $engine->admin->getID() . '&action=list', $engine->language->get('admin_component_static_control')), $menu_theme);
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id=' . $engine->admin->getID() . '&action=add', $engine->language->get('admin_component_static_add')), $menu_theme);
        $body_form = $engine->template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $engine->template->get('config_head'));
        return $body_form;
    }

    private function getPageCount()
    {
        global $engine;
        $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_com_static");
        $stmt->execute();
        $result = $stmt->fetch();
        return intval($result[0] / $this->list_count);
    }

    private function check_pageway($way, $id = 0)
    {
        global $engine;
        if (preg_match('/[\'~`\!@#\$%\^&\*\(\)+=\{\}\[\]\|;:"\<\>,\?\\\]/', $way)) {
            return false;
        }
        $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_static WHERE pathway = ? AND id != ?");
        $stmt->bindParam(1, $way, PDO::PARAM_STR);
        $stmt->bindParam(2, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() == 0 ? true : false;
    }
}

?>