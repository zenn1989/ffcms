<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

class mod_comments_back implements backend
{
    private $list_count = 10;

    public function load()
    {
        global $engine;
        $action_page_title = $engine->admin->getExtName() . " : ";
        $work_body = null;
        if ($engine->admin->getAction() == "manage" || $engine->admin->getAction() == "list" || $engine->admin->getAction() == null) {
            $notify = null;
            if ($engine->system->post('delete_comments') && $engine->system->post('check_array') != null) {
                $to_delete_comments = $engine->system->altimplode(',', $engine->system->post('check_array'));
                if ($engine->system->isIntList($to_delete_comments)) {
                    $stmt = $engine->database->con()->prepare("DELETE FROM {$engine->constant->db['prefix']}_mod_comments WHERE id in ($to_delete_comments)");
                    $stmt->execute();
                    $notify .= $engine->template->stringNotify('success', $engine->language->get('admin_modules_comment_success_massdel'));
                }
            }
            $action_page_title .= $engine->language->get('admin_modules_comment_manage_title');
            $theme = $engine->template->get('comments_list', 'modules/');
            $manage_theme = $engine->template->get('comments_list_manage', 'modules/');
            $index_start = $engine->admin->getPage();
            $comment_array = array();
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_mod_comments ORDER BY id DESC LIMIT ?,?");
            $stmt->bindParam(1, $index_start, PDO::PARAM_INT);
            $stmt->bindParam(2, $this->list_count, PDO::PARAM_INT);
            $stmt->execute();
            $fetchAssoc = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $engine->user->listload($engine->system->extractFromMultyArray('author', $fetchAssoc));
            foreach ($fetchAssoc as $result) {
                $edit_link = "?object=modules&id={$engine->admin->getID()}&action=edit&page={$result['id']}";
                $delete_link = "?object=modules&id={$engine->admin->getID()}&action=delete&page={$result['id']}";
                $manage_links = $engine->template->assign(array('comment_edit', 'comment_delete'), array($edit_link, $delete_link), $manage_theme);
                $comment_array[] = array('<label class="checkbox">' . $result['id'] . '<input type="checkbox" name="check_array[]" class="check_array" value="' . $result['id'] . '"/><label>', $engine->user->get('login', $result['author']), $result['comment'], $manage_links);
            }
            $table = $engine->admin->tplRawTable(array($engine->language->get('admin_modules_comment_th1'), $engine->language->get('admin_modules_comment_th2'), $engine->language->get('admin_modules_comment_th3'), $engine->language->get('admin_modules_comment_th4')), $comment_array);
            $pagination_list = $engine->admin->tplRawPagination($this->list_count, $this->getCommentCount(), 'modules');
            $work_body = $engine->template->assign(array('comment_table', 'ext_pagination_list', 'notify'), array($table, $pagination_list, $notify), $theme);
        } elseif ($engine->admin->getAction() == "edit") {
            $action_page_title .= $engine->language->get('admin_modules_comment_manage_title');
            $comment_id = $engine->admin->getPage();
            $notify = null;
            if ($engine->system->post('save_comment') && $comment_id > 0 && strlen($engine->system->post('comment_text')) > 0) {
                $new_comment_text = $engine->system->nohtml($engine->system->post('comment_text'));
                $stmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_mod_comments SET comment = ? WHERE id = ?");
                $stmt->bindParam(1, $new_comment_text, PDO::PARAM_STR);
                $stmt->bindParam(2, $comment_id, PDO::PARAM_INT);
                $stmt->execute();
                $notify = $engine->template->stringNotify('success', $engine->language->get('admin_modules_comment_edited_success'));
            }
            $theme_edit = $engine->template->get('comment_edit', 'modules/');
            $stmt = $engine->database->con()->prepare("SELECT comment FROM {$engine->constant->db['prefix']}_mod_comments WHERE id = ?");
            $stmt->bindParam(1, $comment_id, PDO::PARAM_INT);
            $stmt->execute();
            $comment_text = null;
            if ($result = $stmt->fetch()) {
                $comment_text = $result['comment'];
            }
            $work_body = $engine->template->assign(array('comment_text', 'notify'), array($comment_text, $notify), $theme_edit);
        } elseif ($engine->admin->getAction() == "delete") {
            $comment_id = $engine->admin->getPage();
            if ($engine->system->post('delete_comment') && $comment_id > 0) {
                $stmt = $engine->database->con()->prepare("DELETE FROM {$engine->constant->db['prefix']}_mod_comments WHERE id = ?");
                $stmt->bindParam(1, $comment_id, PDO::PARAM_INT);
                $stmt->execute();
                $engine->system->redirect($_SERVER['PHP_SELF'] . "?object=modules&id=" . $engine->admin->getID());
            }
            $action_page_title .= $engine->language->get('admin_modules_comment_manage_title');
            $theme_delete = $engine->template->get('comment_delete', 'modules/');
            $result_array = array();
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_mod_comments WHERE id = ?");
            $stmt->bindParam(1, $comment_id, PDO::PARAM_INT);
            $stmt->execute();
            if ($result = $stmt->fetch()) {
                $result_array[] = array($result['id'], $engine->user->get('login', $result['author']), $result['comment']);
            }
            $stmt = null;
            $rawTable = $engine->admin->tplRawTable(array($engine->language->get('admin_modules_comment_del_th1'), $engine->language->get('admin_modules_comment_del_th2'), $engine->language->get('admin_modules_comment_del_th3')), $result_array, $theme_delete);
            $work_body = $engine->template->assign('comment_data', $rawTable, $theme_delete);
        } elseif ($engine->admin->getAction() == "settings") {
            $action_page_title .= $engine->language->get('admin_modules_comment_settings_title');
            if ($engine->system->post('submit')) {
                $save_try = $engine->admin->trySaveConfigs();
                if ($save_try)
                    $work_body .= $engine->template->stringNotify('success', $engine->language->get('admin_extension_config_update_success'), true);
                else
                    $work_body .= $engine->template->stringNotify('error', $engine->language->get('admin_extension_config_update_fail'), true);;
            }
            $config_form = $engine->template->get('config_form');
            $config_set = $engine->admin->tplSettingsInputText('config:comments_count', $engine->admin->getConfig('comments_count', 'int'), $engine->language->get('admin_modules_comment_config_count_title'), $engine->language->get('admin_modules_comment_config_count_desc'));
            $config_set .= $engine->admin->tplSettingsInputText('config:time_delay', $engine->admin->getConfig('time_delay', 'int'), $engine->language->get('admin_modules_comment_config_timedelay_title'), $engine->language->get('admin_modules_comment_config_timedelay_desc'));
            $config_set .= $engine->admin->tplSettingsInputText('config:edit_time', $engine->admin->getConfig('edit_time', 'int'), $engine->language->get('admin_modules_comment_config_edittime_title'), $engine->language->get('admin_modules_comment_config_edittime_desc'));
            $config_set .= $engine->admin->tplSettingsInputText('config:min_length', $engine->admin->getConfig('min_length', 'int'), $engine->language->get('admin_modules_comment_config_minlength_title'), $engine->language->get('admin_modules_comment_config_minlength_desc'));
            $config_set .= $engine->admin->tplSettingsInputText('config:max_length', $engine->admin->getConfig('max_length', 'int'), $engine->language->get('admin_modules_comment_config_maxlength_title'), $engine->language->get('admin_modules_comment_config_maxlength_desc'));
            $work_body .= $engine->template->assign('ext_form', $config_set, $config_form);
        } elseif ($engine->admin->getAction() == "turn") {
            return $engine->admin->turn();
        }

        $menu_theme = $engine->template->get('config_menu');
        $menu_link = null;
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=modules&id=' . $engine->admin->getID() . "&action=manage", $engine->language->get('admin_modules_comment_manage_title')), $menu_theme);
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=modules&id=' . $engine->admin->getID() . "&action=settings", $engine->language->get('admin_modules_comment_settings_title')), $menu_theme);
        $body_form = $engine->template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $engine->template->get('config_head'));
        return $body_form;
    }

    private function getCommentCount()
    {
        global $engine;
        $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_mod_comments");
        $stmt->execute();
        $result = $stmt->fetch();
        return intval($result[0] / $this->list_count);
    }

}


?>