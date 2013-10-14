<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

class com_feedback_back implements backend
{
    private $list_count = 10;
    public function load()
    {
        global $engine;
        if($engine->admin->getAction() == "turn") {
            return $engine->admin->turn();
        }
        $action_page_title = $engine->admin->getExtName() . " : ";
        $menu_theme = $engine->template->get('config_menu');
        $work_body = null;
        $menu_link = $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id=' . $engine->admin->getID(), $engine->language->get('admin_component_feedback_list')), $menu_theme);
        if($engine->admin->getAction() == "list" || $engine->admin->getAction() == null) {
            $action_page_title .= $engine->language->get('admin_component_feedback_list');
            $theme_list = $engine->template->get('feedback_list', 'components/');
            $index_start = $engine->admin->getPage();
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_feedback ORDER BY `id` DESC LIMIT ?, ?");
            $stmt->bindParam(1, $index_start, PDO::PARAM_INT);
            $stmt->bindParam(2, $this->list_count, PDO::PARAM_INT);
            $stmt->execute();
            $rawArray = array();
            while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $readlink = '<a href="?object=components&id='.$engine->admin->getID().'&action=read&page='.$result['id'].'">'.$engine->language->get('admin_component_feedback_readit').'</a>';
                $rawArray[] = array($result['id'], $result['from_name']."(".$result['from_email'].")", $result['title'], $engine->system->toDate($result['time'], 'h'), $readlink);
            }
            $rawTable = $engine->admin->tplRawTable(array($engine->language->get('admin_component_feedback_th1'), $engine->language->get('admin_component_feedback_th2'), $engine->language->get('admin_component_feedback_th3'), $engine->language->get('admin_component_feedback_th4'), $engine->language->get('admin_component_feedback_th5')), $rawArray);
            $pagination_list = $engine->admin->tplRawPagination($this->list_count, $this->getFeedCount(), 'components');
            $work_body .= $engine->template->assign(array('feedback_table', 'ext_pagination_list'), array($rawTable, $pagination_list), $theme_list);
        } elseif($engine->admin->getAction() == "read") {
            $message_id = $engine->admin->getPage();
            $theme = $engine->template->get('feedback_read', 'components/');
            $action_page_title .= $engine->language->get('admin_component_feedback_read');
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_feedback WHERE id = ?");
            $stmt->bindParam(1, $message_id, PDO::PARAM_INT);
            $stmt->execute();
            if($stmt->rowCount() < 1) {
                return null;
            }
            $result = $stmt->fetch();
            $work_body = $engine->template->assign(array('feedback_name', 'feedback_email', 'feedback_title', 'feedback_text'), array($result['from_name'], $result['from_email'], $result['title'], $result['text']), $theme);
        }
        $body_form = $engine->template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $engine->template->get('config_head'));
        return $body_form;
    }

    private function getFeedCount()
    {
        global $engine;
        $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_com_feedback");
        $stmt->execute();
        $res = $stmt->fetch();
        return intval($res[0]/$this->list_count);
    }

}

?>