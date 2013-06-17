<?php

class mod_comments_back implements backend
{
    private $list_count = 10;
    public function load()
    {
        global $template,$admin,$language,$system,$database,$constant,$user;
        $action_page_title = $admin->getExtName()." : ";
        $work_body = null;
        if($admin->getAction() == "manage" || $admin->getAction() == "list" || $admin->getAction() == null)
        {
            $notify = null;
            if($system->post('delete_comments') && $system->post('check_array') != null)
            {
                $to_delete_comments = $system->altimplode(',', $system->post('check_array'));
                if($system->isIntList($to_delete_comments))
                {
                    $stmt = $database->con()->prepare("DELETE FROM {$constant->db['prefix']}_mod_comments WHERE id in ($to_delete_comments)");
                    $stmt->execute();
                    $notify .= $template->stringNotify('success', $language->get('admin_modules_comment_success_massdel'));
                }
            }
            $action_page_title .= $language->get('admin_modules_comment_manage_title');
            $theme = $template->tplget('comments_list', 'modules/', true);
            $manage_theme = $template->tplget('comments_list_manage', 'modules/', true);
            $index_start = $admin->getPage();
            $comment_array = array();
            $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_mod_comments ORDER BY id DESC LIMIT ?,?");
            $stmt->bindParam(1, $index_start, PDO::PARAM_INT);
            $stmt->bindParam(2, $this->list_count, PDO::PARAM_INT);
            $stmt->execute();
            $fetchAssoc = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $user->listload($system->extractFromMultyArray('author', $fetchAssoc));
            foreach($fetchAssoc as $result)
            {
                $edit_link = "?object=modules&id={$admin->getID()}&action=edit&page={$result['id']}";
                $delete_link = "?object=modules&id={$admin->getID()}&action=delete&page={$result['id']}";
                $manage_links = $template->assign(array('comment_edit', 'comment_delete'), array($edit_link, $delete_link), $manage_theme);
                $comment_array[] = array('<label class="checkbox">'.$result['id'].'<input type="checkbox" name="check_array[]" class="check_array" value="'.$result['id'].'"/><label>', $user->get('login', $result['author']), $result['comment'], $manage_links);
            }
            $table = $admin->tplRawTable(array($language->get('admin_modules_comment_th1'), $language->get('admin_modules_comment_th2'), $language->get('admin_modules_comment_th3'), $language->get('admin_modules_comment_th4')), $comment_array);
            $pagination_list = $admin->tplRawPagination($this->list_count, $this->getCommentCount(), 'modules');
            $work_body = $template->assign(array('comment_table', 'ext_pagination_list', 'notify'), array($table, $pagination_list, $notify), $theme);
        }
        elseif($admin->getAction() == "settings")
        {
            $action_page_title .= $language->get('admin_modules_comment_settings_title');
            if($system->post('submit'))
            {
                $save_try = $admin->trySaveConfigs();
                if($save_try)
                    $work_body .= $template->stringNotify('success', $language->get('admin_extension_config_update_success'), true);
                else
                    $work_body .= $template->stringNotify('error', $language->get('admin_extension_config_update_fail'), true);;
            }
            $config_form = $template->tplget('config_form', null, true);
            $config_set = $admin->tplSettingsInputText('config:comments_count', $admin->getConfig('comments_count', 'int'), $language->get('admin_modules_comment_config_count_title'), $language->get('admin_modules_comment_config_count_desc'));
            $config_set .= $admin->tplSettingsInputText('config:time_delay', $admin->getConfig('time_delay', 'int'), $language->get('admin_modules_comment_config_timedelay_title'), $language->get('admin_modules_comment_config_timedelay_desc'));
            $config_set .= $admin->tplSettingsInputText('config:edit_time', $admin->getConfig('edit_time', 'int'), $language->get('admin_modules_comment_config_edittime_title'), $language->get('admin_modules_comment_config_edittime_desc'));
            $config_set .= $admin->tplSettingsInputText('config:min_length', $admin->getConfig('min_length', 'int'), $language->get('admin_modules_comment_config_minlength_title'), $language->get('admin_modules_comment_config_minlength_desc'));
            $config_set .= $admin->tplSettingsInputText('config:max_length', $admin->getConfig('max_length', 'int'), $language->get('admin_modules_comment_config_maxlength_title'), $language->get('admin_modules_comment_config_maxlength_desc'));
            $work_body .= $template->assign('ext_form', $config_set, $config_form);
        }

        $menu_theme = $template->tplget('config_menu', null, true);
        $menu_link = null;
        $menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=modules&id='.$admin->getID()."&action=manage", $language->get('admin_modules_comment_manage_title')), $menu_theme);
        $menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=modules&id='.$admin->getID()."&action=settings", $language->get('admin_modules_comment_settings_title')), $menu_theme);
        $body_form = $template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $template->tplget('config_head', null, true));
        return $body_form;
    }

    private function getCommentCount()
    {
        global $database,$constant;
        $stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_mod_comments");
        $stmt->execute();
        $result = $stmt->fetch();
        return intval($result[0]/$this->list_count);
    }

}







?>