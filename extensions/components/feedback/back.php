<?php

class com_feedback_back implements backend
{
    private $list_count = 10;
    public function load()
    {
        global $admin, $language, $template, $system, $database, $constant;
        if($admin->getAction() == "turn") {
            return $admin->turn();
        }
        $action_page_title = $admin->getExtName() . " : ";
        $menu_theme = $template->get('config_menu');
        $work_body = null;
        $menu_link = $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id=' . $admin->getID(), $language->get('admin_component_feedback_list')), $menu_theme);
        if($admin->getAction() == "list" || $admin->getAction() == null) {
            $action_page_title .= $language->get('admin_component_feedback_list');
            $theme_list = $template->get('feedback_list', 'components/');
            $index_start = $admin->getPage();
            $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_feedback ORDER BY `id` DESC LIMIT ?, ?");
            $stmt->bindParam(1, $index_start, PDO::PARAM_INT);
            $stmt->bindParam(2, $this->list_count, PDO::PARAM_INT);
            $stmt->execute();
            $rawArray = array();
            while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $readlink = '<a href="?object=components&id='.$admin->getID().'&action=read&page='.$result['id'].'">'.$language->get('admin_component_feedback_readit').'</a>';
                $rawArray[] = array($result['id'], $result['from_name']."(".$result['from_email'].")", $result['title'], $system->toDate($result['time'], 'h'), $readlink);
            }
            $rawTable = $admin->tplRawTable(array($language->get('admin_component_feedback_th1'), $language->get('admin_component_feedback_th2'), $language->get('admin_component_feedback_th3'), $language->get('admin_component_feedback_th4'), $language->get('admin_component_feedback_th5')), $rawArray);
            $pagination_list = $admin->tplRawPagination($this->list_count, $this->getFeedCount(), 'components');
            $work_body .= $template->assign(array('feedback_table', 'ext_pagination_list'), array($rawTable, $pagination_list), $theme_list);
        } elseif($admin->getAction() == "read") {
            $message_id = $admin->getPage();
            $theme = $template->get('feedback_read', 'components/');
            $action_page_title .= $language->get('admin_component_feedback_read');
            $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_feedback WHERE id = ?");
            $stmt->bindParam(1, $message_id, PDO::PARAM_INT);
            $stmt->execute();
            if($stmt->rowCount() < 1) {
                return null;
            }
            $result = $stmt->fetch();
            $work_body = $template->assign(array('feedback_name', 'feedback_email', 'feedback_title', 'feedback_text'), array($result['from_name'], $result['from_email'], $result['title'], $result['text']), $theme);
        }
        $body_form = $template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $template->get('config_head'));
        return $body_form;
    }

    private function getFeedCount()
    {
        global $database, $constant;
        $stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_com_feedback");
        $stmt->execute();
        $res = $stmt->fetch();
        return intval($res[0]/$this->list_count);
    }

}

?>