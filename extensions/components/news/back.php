<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

class com_news_back
{
    private $list_count = 10;

    public function load()
    {
        global $engine;
        $action_page_title = $engine->admin->getExtName() . " : ";
        $work_body = null;
        $menu_theme = $engine->template->get('config_menu');
        $menu_link = null;
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id=' . $engine->admin->getID() . '&action=list', $engine->language->get('admin_component_news_manage')), $menu_theme);
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id=' . $engine->admin->getID() . '&action=add', $engine->language->get('admin_component_news_add')), $menu_theme);
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id=' . $engine->admin->getID() . '&action=category', $engine->language->get('admin_component_news_category')), $menu_theme);
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id=' . $engine->admin->getID() . '&action=settings', $engine->language->get('admin_component_news_settings')), $menu_theme);
        if ($engine->admin->getAction() == "list" || $engine->admin->getAction() == null) {
            $action_page_title .= $engine->language->get('admin_component_news_manage');
            $theme_list = $engine->template->get('news_list', 'components/');
            $theme_manage = $engine->template->get('news_list_manage', 'components/');
            $index_start = $engine->admin->getPage();
            $news_array = array();
            if ($engine->system->post('dosearch') && strlen($engine->system->post('search')) > 0) {
                $search_string = "%{$engine->system->post('search')}%";
                $stmt = $engine->database->con()->prepare("SELECT a.id,a.title,a.category,a.link,b.category_id,b.path FROM {$engine->constant->db['prefix']}_com_news_entery a, {$engine->constant->db['prefix']}_com_news_category b WHERE a.category = b.category_id AND (a.title like ? OR a.text like ?) ORDER BY a.id DESC");
                $stmt->bindParam(1, $search_string, PDO::PARAM_STR);
                $stmt->bindParam(2, $search_string, PDO::PARAM_STR);
                $stmt->execute();
            } else {
                $stmt = $engine->database->con()->prepare("SELECT a.id,a.title,a.category,a.link,b.category_id,b.path FROM {$engine->constant->db['prefix']}_com_news_entery a, {$engine->constant->db['prefix']}_com_news_category b WHERE a.category = b.category_id ORDER BY a.id DESC LIMIT ?, ?");
                $stmt->bindParam(1, $index_start, PDO::PARAM_INT);
                $stmt->bindParam(2, $this->list_count, PDO::PARAM_INT);
                $stmt->execute();
            }
            while ($result = $stmt->fetch()) {
                $lang_title = unserialize($result['title']);
                $news_id = $result['id'];
                $edit_link = "?object=components&id={$engine->admin->getID()}&action=edit&page={$news_id}";
                $delete_link = "?object=components&id={$engine->admin->getID()}&action=delete&page={$news_id}";
                $editable_name = "<a href=\"$edit_link\">{$lang_title[$engine->constant->lang]}</a>";
                $full_link = "<a href=\"{$engine->constant->url}/news/{$result['path']}/{$result['link']}\" target=\"_blank\">{$result['path']}/{$result['link']}</a>";
                $news_array[] = array($news_id, $editable_name, $full_link, $engine->template->assign(array('news_edit', 'news_delete'), array($edit_link, $delete_link), $theme_manage));
            }
            $form_table = $engine->admin->tplRawTable(array($engine->language->get('admin_component_news_th_id'), $engine->language->get('admin_component_news_th_title'), $engine->language->get('admin_component_news_th_link'), $engine->language->get('admin_component_news_th_manage')), $news_array);
            $pagination_list = $engine->admin->tplRawPagination($this->list_count, $this->getPageCount(), 'components');
            $work_body = $engine->template->assign(array('ext_table_data', 'ext_pagination_list', 'ext_search_value'), array($form_table, $pagination_list, $engine->system->post('search')), $theme_list);
        } elseif ($engine->admin->getAction() == "edit" && $this->newsExist()) {
            $news_id = $engine->admin->getPage();
            $action_page_title .= $engine->language->get('admin_component_news_modedit_title');
            $theme_body = $engine->template->get('news_edit', 'components/');
            $theme_li = $engine->template->get('news_language_li', 'components/');
            $theme_head = $engine->template->get('news_edit_header', 'components/');
            $notify = null;
            if ($engine->system->post('save')) {
                $editor_id = $engine->user->get('id');
                $title = $engine->system->nohtml($engine->system->post('title'));
                $category_id = $engine->system->post('category');
                $pathway = $engine->system->nohtml($engine->system->post('pathway')) . ".html";
                $display = $engine->system->post('display_content') == "on" ? 1 : 0;
                $important = $engine->system->post('important_content') == "on" ? 1 : 0;
                $text = $engine->system->post('text');
                $description = $engine->system->nohtml($engine->system->post('description'));
                $keywords = $engine->system->nohtml($engine->system->post('keywords'));
                $date = $engine->system->post('current_date') == "on" ? time() : $engine->system->toUnixTime($engine->system->post('date'));
                if (strlen($title[$engine->constant->lang]) < 1) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('admin_component_news_edit_notify_title_length'));
                }
                if (!$engine->system->isInt($category_id)) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('admin_component_news_edit_notify_category_wrong'));
                }
                if (strlen($pathway) < 1 || !$this->check_pageway($pathway, $news_id, $category_id)) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('admin_component_news_edit_notify_pathway_null'));
                }
                if (strlen($text[$engine->constant->lang]) < 1) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('admin_component_news_edit_notify_text_null'));
                }
                if ($notify == null) {
                    $serial_title = serialize($title);
                    $serial_text = serialize($text);
                    $serial_description = serialize($description);
                    $serial_keywords = serialize($keywords);
                    $stmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_com_news_entery SET
						title = ?, 
						text = ?, 
						link = ?, 
						category = ?, 
						date = ?, 
						author = ?, 
						description = ?, 
						keywords = ?, 
						display = ?, 
						important = ? 
						WHERE id = ?");
                    $stmt->bindParam(1, $serial_title, PDO::PARAM_STR);
                    $stmt->bindParam(2, $serial_text, PDO::PARAM_STR);
                    $stmt->bindParam(3, $pathway, PDO::PARAM_STR);
                    $stmt->bindParam(4, $category_id, PDO::PARAM_INT);
                    $stmt->bindParam(5, $date, PDO::PARAM_INT);
                    $stmt->bindParam(6, $editor_id, PDO::PARAM_INT);
                    $stmt->bindParam(7, $serial_description, PDO::PARAM_STR);
                    $stmt->bindParam(8, $serial_keywords, PDO::PARAM_STR);
                    $stmt->bindParam(9, $display, PDO::PARAM_INT);
                    $stmt->bindParam(10, $important, PDO::PARAM_INT);
                    $stmt->bindParam(11, $news_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                    $stmt = $engine->database->con()->prepare("DELETE FROM {$engine->constant->db['prefix']}_mod_tags WHERE `object_type` = 'news' AND `object_id` = ?");
                    $stmt->bindParam(1, $news_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                    foreach($keywords as $keyrow) {
                        $keyrow_array = $engine->system->altexplode(',', $keyrow);
                        foreach($keyrow_array as $objectkey) {
                            $stmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_mod_tags(`object_id`, `object_type`, `tag`) VALUES (?, 'news', ?)");
                            $stmt->bindParam(1, $news_id, PDO::PARAM_INT);
                            $stmt->bindParam(2, $objectkey, PDO::PARAM_STR);
                            $stmt->execute();
                            $stmt = null;
                        }
                    }
                    $notify .= $engine->template->stringNotify('success', $engine->language->get('admin_component_news_edit_notify_success_save'));
                }
            }
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_news_entery WHERE id = ?");
            $stmt->bindParam(1, $news_id, PDO::PARAM_INT);
            $stmt->execute();
            $news_result = $stmt->fetch();
            $category_option_list = $this->buildCategoryOptionList($news_id);
            $is_display = $news_result['display'] > 0 ? "checked" : null;
            $is_important = $news_result['important'] > 0 ? "checked" : null;

            $precompile_body = null;
            $precompile_head = null;
            $is_active_first_element = true;
            foreach($engine->language->getAvailable() as $current_language) {
                $precompile_head .= $engine->template->assign(array('current_language', 'is_active_element'), array($current_language, $is_active_first_element ? 'class="active"' : null), $theme_li);
                $precompile_body .= $engine->template->assign(array('current_language', 'is_active_element'), array($current_language, $is_active_first_element ? 'active' : null), $theme_body);
                $is_active_first_element = false;
            }
            $work_body = $engine->template->assign(array('selecter_li_languages', 'selecter_body_languages', 'notify'), array($precompile_head, $precompile_body, $notify), $theme_head);
            $title = unserialize($news_result['title']);
            $text = unserialize($news_result['text']);
            $description = unserialize($news_result['description']);
            $keywords = unserialize($news_result['keywords']);
            foreach($engine->language->getAvailable() as $current_language) {
                $work_body = $engine->template->assign(array('news_title_'.$current_language, 'news_content_'.$current_language, 'news_description_'.$current_language, 'news_keywords_'.$current_language),
                    array($title[$current_language], $text[$current_language], $description[$current_language], $keywords[$current_language]),
                    $work_body);
            }
            $work_body = $engine->template->assign(array('category_option_list', 'notify', 'news_path', 'news_date', 'news_display_check', 'news_important_check'),
                array($category_option_list, $notify, $engine->system->noextention($news_result['link']), $engine->system->toDate($news_result['date'], 'h'), $is_display, $is_important),
                $work_body);
            $stmt = null;
        } elseif ($engine->admin->getAction() == "add") {
            $action_page_title .= $engine->language->get('admin_component_news_add');
            $theme_body = $engine->template->get('news_edit', 'components/');
            $theme_li = $engine->template->get('news_language_li', 'components/');
            $theme_head = $engine->template->get('news_edit_header', 'components/');
            $notify = null;
            $title = null;
            $category_id = null;
            $pathway = null;
            $display = null;
            $important = null;
            $text = null;
            $description = null;
            $keywords = null;
            $date = null;
            if ($engine->system->post('save')) {
                $editor_id = $engine->user->get('id');
                $title = $engine->system->nohtml($engine->system->post('title'));
                $category_id = $engine->system->post('category');
                $pathway = $engine->system->nohtml($engine->system->post('pathway')) . ".html";
                $display = $engine->system->post('display_content') == "on" ? 1 : 0;
                $important = $engine->system->post('important_content') == "on" ? 1 : 0;
                $text = $engine->system->post('text');
                $description = $engine->system->nohtml($engine->system->post('description'));
                $keywords = $engine->system->nohtml($engine->system->post('keywords'));
                $date = $engine->system->post('current_date') == "on" ? time() : $engine->system->toUnixTime($engine->system->post('date'));
                if (strlen($title[$engine->constant->lang]) < 1) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('admin_component_news_edit_notify_title_length'));
                }
                if (!$engine->system->isInt($category_id)) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('admin_component_news_edit_notify_category_wrong'));
                }
                if (strlen($pathway) < 1 || !$this->check_pageway($pathway, 0, $category_id)) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('admin_component_news_edit_notify_pathway_null'));
                }
                if (strlen($text[$engine->constant->lang]) < 1) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('admin_component_news_edit_notify_text_null'));
                }
                if ($notify == null) {
                    $serial_title = serialize($title);
                    $serial_text = serialize($text);
                    $serial_description = serialize($description);
                    $serial_keywords = serialize($keywords);
                    $stmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_com_news_entery
					(`title`, `text`, `link`, `category`, `date`, `author`, `description`, `keywords`, `display`, `important`) VALUES
					(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bindParam(1, $serial_title, PDO::PARAM_STR);
                    $stmt->bindParam(2, $serial_text, PDO::PARAM_STR);
                    $stmt->bindParam(3, $pathway, PDO::PARAM_STR);
                    $stmt->bindParam(4, $category_id, PDO::PARAM_INT);
                    $stmt->bindParam(5, $date, PDO::PARAM_STR);
                    $stmt->bindParam(6, $editor_id, PDO::PARAM_INT);
                    $stmt->bindParam(7, $serial_description, PDO::PARAM_STR);
                    $stmt->bindParam(8, $serial_keywords, PDO::PARAM_STR);
                    $stmt->bindParam(9, $display, PDO::PARAM_INT);
                    $stmt->bindParam(10, $important, PDO::PARAM_INT);
                    $stmt->execute();
                    $new_news_id = $engine->database->con()->lastInsertId();
                    $stmt = null;
                    foreach($keywords as $keyrow) {
                        $keyrow_array = $engine->system->altexplode(',', $keyrow);
                        foreach($keyrow_array as $objectkey) {
                            $stmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_mod_tags(`object_id`, `object_type`, `tag`) VALUES (?, 'news', ?)");
                            $stmt->bindParam(1, $new_news_id, PDO::PARAM_INT);
                            $stmt->bindParam(2, $objectkey, PDO::PARAM_STR);
                            $stmt->execute();
                            $stmt = null;
                        }
                    }
                    $engine->system->redirect($_SERVER['PHP_SELF'] . "?object=components&id=" . $engine->admin->getID());
                    return;
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
                $work_body = $engine->template->assign(array('news_title_'.$current_language, 'news_content_'.$current_language, 'news_description_'.$current_language, 'news_keywords_'.$current_language),
                            array($title[$current_language], $text[$current_language], $description[$current_language], $keywords[$current_language]),
                            $work_body);
                }
                $work_body = $engine->template->assign(array('notify', 'news_path', 'news_date'),
                                 array($notify, $engine->system->noextention($pathway), $engine->system->toDate($date, 'h')),
                                 $work_body);
            }
            $work_body = $engine->template->assign('category_option_list', $this->buildCategoryOptionList(), $work_body);
        } elseif ($engine->admin->getAction() == "category") {
            $action_page_title .= $engine->language->get('admin_component_news_category');
            $theme_head = $engine->template->get('news_category_head', 'components/');
            $work_body = $engine->template->assign(array('news_category_list', 'component_id'), array($this->categoryListLi(), $engine->admin->getID()), $theme_head);
        } elseif ($engine->admin->getAction() == "addcategory") {
            $notify = null;
            if ($engine->system->post('submit')) {
                $cat_id = $engine->system->post('category_owner');
                $cat_name = $engine->system->post('category_name');
                $cat_serial_name = serialize($cat_name);
                $cat_path = $engine->system->post('category_path');
                if (!$engine->system->isInt($cat_id) || $cat_id < 1) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('admin_component_news_category_notify_noselectcat'));
                }
                if (strlen($cat_name[$engine->constant->lang]) < 1) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('admin_component_news_category_notify_noname'));
                }
                if (!$this->check_catway($cat_path, $cat_id)) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('admin_component_news_category_notify_pathwrong'));
                }
                if ($notify == null) {
                    $stmt = $engine->database->con()->prepare("SELECT path FROM {$engine->constant->db['prefix']}_com_news_category WHERE category_id  = ?");
                    $stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
                    $stmt->execute();
                    if ($res = $stmt->fetch()) {
                        $new_category_path = null;
                        if ($res['path'] == null) {
                            $new_category_path = $cat_path;
                        } else {
                            $new_category_path = $res['path'] . "/" . $cat_path;
                        }
                        $stmt = null;

                        $stmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_com_news_category (`name`, `path`) VALUES (?, ?)");
                        $stmt->bindParam(1, $cat_serial_name, PDO::PARAM_STR);
                        $stmt->bindParam(2, $new_category_path, PDO::PARAM_STR);
                        $stmt->execute();
                        $engine->system->redirect($_SERVER['PHP_SELF'] . "?object=components&id=" . $engine->admin->getID() . "&action=category");
                        return;
                    }
                }
            }
            $theme_li = $engine->template->get('news_language_li', 'components/');
            $theme_head = $engine->template->get('news_category_add_head', 'components/');
            $theme_body = $engine->template->get('news_category_add_body', 'components/');
            $selected_category = $engine->admin->getPage() == 0 ? null : $engine->admin->getPage();
            $action_page_title .= $engine->language->get('admin_component_news_category');
            $precompile_body = null;
            $precompile_head = null;
            $is_active_first_element = true;
            foreach($engine->language->getAvailable() as $current_language) {
                $precompile_head .= $engine->template->assign(array('current_language', 'is_active_element'), array($current_language, $is_active_first_element ? 'class="active"' : null), $theme_li);
                $precompile_body .= $engine->template->assign(array('current_language', 'is_active_element'), array($current_language, $is_active_first_element ? 'active' : null), $theme_body);
                $is_active_first_element = false;
            }
            $work_body = $engine->template->assign(array('selecter_li_languages', 'selecter_body_languages', 'notify', 'news_category_select'), array($precompile_head, $precompile_body, $notify, $this->buildCategoryOptionList(0, $selected_category)), $theme_head);
        } elseif ($engine->admin->getAction() == "delcategory") {
            $action_page_title .= $engine->language->get('admin_component_news_category');
            $theme_delete = $engine->template->get('news_category_delete', 'components/');
            $cat_id = $engine->admin->getPage();
            $cat_name = null;
            $cat_path = null;
            if ($cat_id > 0) {
                $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_news_category WHERE category_id = ?");
                $stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
                $stmt->execute();
                if ($res = $stmt->fetch()) {
                    $cat_serial_name = unserialize($res['name']);
                    $cat_name = $cat_serial_name[$engine->constant->lang];
                    $cat_path = $res['path'];
                }
                $stmt = null;
                if ($cat_path != null) {
                    $notify = null;
                    if ($engine->system->post('deletecategory')) {
                        $move_to_cat = $engine->system->post('move_to_category');
                        if (!$engine->system->isInt($move_to_cat) || $move_to_cat < 1) {
                            $notify .= $engine->template->stringNotify('error', $engine->language->get('admin_component_news_category_delete_nocat'));
                        }
                        $like_path = $cat_path . "%";
                        $stmt = $engine->database->con()->prepare("SELECT category_id FROM {$engine->constant->db['prefix']}_com_news_category WHERE path like ?");
                        $stmt->bindParam(1, $like_path, PDO::PARAM_STR);
                        $stmt->execute();
                        $cat_to_remove_array = array();
                        while ($result = $stmt->fetch()) {
                            $cat_to_remove_array[] = $result['category_id'];
                        }
                        $stmt = null;
                        $cat_remove_list = $engine->system->altimplode(',', $cat_to_remove_array);
                        $stmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_com_news_entery SET category = ? WHERE category in({$cat_remove_list})");
                        $stmt->bindParam(1, $move_to_cat, PDO::PARAM_INT);
                        $stmt->execute();
                        $stmt = null;
                        $stmt = $engine->database->con()->prepare("DELETE FROM {$engine->constant->db['prefix']}_com_news_category WHERE category_id in ({$cat_remove_list})");
                        $stmt->execute();
                        $stmt = null;
                        $engine->system->redirect($_SERVER['PHP_SELF'] . "?object=components&id=" . $engine->admin->getID() . "&action=category");
                    }
                    $work_body = $engine->template->assign(array('category_name', 'category_list'), array($cat_name, $this->buildCategoryOptionList()), $theme_delete);
                } else {
                    $work_body = $engine->template->stringNotify('error', $engine->language->get('admin_component_news_category_delete_unposible'));
                }
            }
        } elseif($engine->admin->getAction() == "delete" && $engine->admin->getPage() > 0) {
            $news_id = $engine->admin->getPage();
            if($engine->system->post('submit')) {
                $stmt = $engine->database->con()->prepare("DELETE FROM {$engine->constant->db['prefix']}_com_news_entery WHERE id = ?");
                $stmt->bindParam(1, $news_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                $stmt = $engine->database->con()->prepare("DELETE FROM {$engine->constant->db['prefix']}_mod_tags WHERE object_type = 'news' AND object_id = ?");
                $stmt->bindParam(1, $news_id, PDO::PARAM_INT);
                $stmt->execute();
                $engine->system->redirect($_SERVER['PHP_SELF'] . "?object=components&id=" . $engine->admin->getID());
            }
            $action_page_title .= $engine->language->get('admin_component_news_delete');
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_news_entery WHERE id = ?");
            $stmt->bindParam(1, $news_id, PDO::PARAM_INT);
            $stmt->execute();
            if($stmt->rowCount() == 1) {
                $result = $stmt->fetch();
                $serial_title = unserialize($result['title']);
                $array_data[] = array($result['id'], $serial_title[$engine->language->getCustom()], $result['link']);
                $rawTable = $engine->admin->tplRawTable(array($engine->language->get('admin_component_news_delete_th1'), $engine->language->get('admin_component_news_delete_th2'), $engine->language->get('admin_component_news_delete_th3')),
                    $array_data);
                $work_body = $engine->template->assign(array('news_delete_info', 'cancel_link'),
                    array($rawTable, "?object=components&id=" . $engine->admin->getID()),
                    $engine->template->get('news_delete', 'components/'));
            }
            $stmt = null;
        } elseif ($engine->admin->getAction() == "settings") {
            $action_page_title .= $engine->language->get('admin_component_news_settings');

            if ($engine->system->post('submit')) {
                $save_try = $engine->admin->trySaveConfigs();
                if ($save_try)
                    $work_body .= $engine->template->stringNotify('success', $engine->language->get('admin_extension_config_update_success'), true);
                else
                    $work_body .= $engine->template->stringNotify('error', $engine->language->get('admin_extension_config_update_fail'), true);;
            }

            $config_form = $engine->template->get('config_form');

            $config_set = $engine->language->get('admin_component_news_description');
            $config_set .= $engine->admin->tplSettingsDirectory($engine->language->get('admin_component_news_settings_mainblock'));
            $config_set .= $engine->admin->tplSettingsSelectYorN('config:delay_news_public', $engine->language->get('admin_component_news_config_newsdelay_title'), $engine->language->get('admin_component_news_config_newsdelay_desc'), $engine->admin->getConfig('delay_news_public', 'boolean'));
            $config_set .= $engine->admin->tplSettingsInputText('config:count_news_page', $engine->admin->getConfig('count_news_page', 'int'), $engine->language->get('admin_component_news_config_newscount_page_title'), $engine->language->get('admin_component_news_config_newscount_page_desc'));
            $config_set .= $engine->admin->tplSettingsInputText('config:short_news_length', $engine->admin->getConfig('short_news_length', 'int'), $engine->language->get('admin_component_news_config_newsshort_length_title'), $engine->language->get('admin_component_news_config_newsshort_length_desc'));
            $config_set .= $engine->admin->tplSettingsSelectYorN('config:enable_views_count', $engine->language->get('admin_component_news_config_viewcount_title'), $engine->language->get('admin_component_news_config_viewcount_desc'), $engine->admin->getConfig('enable_views_count', 'boolean'));
            $config_set .= $engine->admin->tplSettingsDirectory($engine->language->get('admin_component_news_settings_catblock'));
            $config_set .= $engine->admin->tplSettingsSelectYorN('config:multi_category', $engine->language->get('admin_component_news_config_newscat_multi_title'), $engine->language->get('admin_component_news_config_newscat_multi_desc'), $engine->admin->getConfig('multi_category', 'boolean'));
            $config_set .= $engine->admin->tplSettingsDirectory($engine->language->get('admin_component_news_settings_tags'));
            $config_set .= $engine->admin->tplSettingsSelectYorN('config:enable_tags', $engine->language->get('admin_component_news_config_tag_title'), $engine->language->get('admin_component_news_config_tag_desc'), $engine->admin->getConfig('enable_tags', 'boolean'));
            $work_body .= $engine->template->assign('ext_form', $config_set, $config_form);
        }

        $body_form = $engine->template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $engine->template->get('config_head'));
        return $body_form;
    }

    private function newsExist()
    {
        global $engine;
        $newsId = $engine->admin->getPage();
        if ($engine->system->isInt($newsId)) {
            $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_com_news_entery WHERE id = ?");
            $stmt->bindParam(1, $newsId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result > 0 ? true : false;
        }
        return false;
    }

    private function buildCategoryOptionList($news_id = 0, $active_category = null)
    {
        global $engine;
        $theme_option_active = $engine->template->get('form_option_item_active');
        $theme_option_inactive = $engine->template->get('form_option_item_inactive');
        $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_news_category ORDER BY `path` ASC");
        $stmt->execute();
        $result_array = array();
        $result_id = array();
        $result_name = array();
        $result_string = null;
        while ($result = $stmt->fetch()) {
            $result_array[] = $result['path'];
            $result_id[$result['path']] = $result['category_id'];
            $result_name[$result['path']] = $result['name'];
        }
        sort($result_array);
        $cstmt = $engine->database->con()->prepare("SELECT category FROM {$engine->constant->db['prefix']}_com_news_entery WHERE id = ?");
        $cstmt->bindParam(1, $news_id, PDO::PARAM_INT);
        $cstmt->execute();
        $cRes = $cstmt->fetch();
        $news_category_id = $cRes['category'];
        $cstmt = null;
        foreach ($result_array as $path) {
            $spliter_count = substr_count($path, "/");
            $add = '';
            if ($path != null) {
                for ($i = -1; $i <= $spliter_count; $i++) {
                    $add .= "-";
                }
            } else {
                $add = "-";
            }
            $current_id = $result_id[$path];
            $current_serial_name = unserialize($result_name[$path]);
            $current_name = $current_serial_name[$engine->constant->lang];
            if ($active_category == null) {
                if ($current_id == $news_category_id) {
                    $result_string .= $engine->template->assign(array('option_value', 'option_name'), array($current_id, $add . " " . $current_name), $theme_option_active);
                } else {
                    $result_string .= $engine->template->assign(array('option_value', 'option_name'), array($current_id, $add . " " . $current_name), $theme_option_inactive);
                }
            } else {
                if ($active_category == $current_id) {
                    $result_string .= $engine->template->assign(array('option_value', 'option_name'), array($current_id, $add . " " . $current_name), $theme_option_active);
                } else {
                    $result_string .= $engine->template->assign(array('option_value', 'option_name'), array($current_id, $add . " " . $current_name), $theme_option_inactive);
                }
            }
        }
        $stmt = null;
        return $result_string;
    }

    private function categoryListLi()
    {
        global $engine;
        $theme_item = $engine->template->get('news_category_item', 'components/');
        $theme_cursor = $engine->template->get('news_category_item_cursor', 'components/');
        $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_news_category ORDER BY `path` ASC");
        $stmt->execute();
        $result_array = array();
        $result_id = array();
        $result_name = array();
        $result_string = null;
        while ($result = $stmt->fetch()) {
            $result_array[] = $result['path'];
            $result_id[$result['path']] = $result['category_id'];
            $result_name[$result['path']] = $result['name'];
        }
        sort($result_array);
        foreach ($result_array as $path) {
            $spliter_count = substr_count($path, "/");
            $add = '';
            if ($path != null) {
                for ($i = -1; $i <= $spliter_count; $i++) {
                    $add .= $theme_cursor;
                }
            } else {
                $add = $theme_cursor;
            }
            $add_link = "?object=components&id=" . $engine->admin->getID() . "&action=addcategory&page=" . $result_id[$path];
            $delete_link = "?object=components&id=" . $engine->admin->getID() . "&action=delcategory&page=" . $result_id[$path];
            $current_name = unserialize($result_name[$path]);
            $result_string .= $engine->template->assign(array('news_category_path', 'news_category_name', 'news_category_add', 'news_category_delete', 'news_category_cursors'), array($path, $current_name[$engine->constant->lang], $add_link, $delete_link, $add), $theme_item);
        }
        $stmt = null;
        return $result_string;
    }

    private function check_pageway($way, $id = 0, $cat_id)
    {
        global $engine;
        if (preg_match('/[\'~`\!@#\$%\^&\*\(\)+=\{\}\[\]\|;:"\<\>,\?\\\]/', $way) || $way == "tag") {
            return false;
        }
        $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_com_news_entery WHERE link = ? AND category = ? AND id != ?");
        $stmt->bindParam(1, $way, PDO::PARAM_STR);
        $stmt->bindParam(2, $cat_id, PDO::PARAM_INT);
        $stmt->bindParam(3, $id, PDO::PARAM_INT);
        $stmt->execute();
        $pRes = $stmt->fetch();
        return $pRes[0] == 0 ? true : false;
    }

    private function check_catway($way, $cat_id)
    {
        global $engine;
        if (preg_match('/[\'~`\!@#\$%\^&\*\(\)+=\{\}\[\]\|;:"\<\>,\?\\\]/', $way)) {
            return false;
        }
        $stmt = $engine->database->con()->prepare("SELECT path FROM {$engine->constant->db['prefix']}_com_news_category WHERE category_id  = ?");
        $stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        if ($dx = $stmt->fetch()) {
            $mother_path = $dx['path'];
            $new_path_query = $dx['path'] == null ? $way . "%" : $mother_path . "/" . $way . "%";
            $stmt_check = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_com_news_category WHERE path like ?");
            $stmt_check->bindParam(1, $new_path_query, PDO::PARAM_STR);
            $stmt_check->execute();
            if ($res = $stmt_check->fetch()) {
                return $res[0] == 0 ? true : false;
            }
        }
        return false;
    }

    private function getPageCount()
    {
        global $engine;
        $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_com_news_entery");
        $stmt->execute();
        $result = $stmt->fetch();
        return intval($result[0] / $this->list_count);
    }
}

?>