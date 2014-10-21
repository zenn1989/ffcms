<?php

use engine\database;
use engine\property;
use engine\language;
use engine\template;
use engine\system;

class modules_menu_front extends \engine\singleton {

    public function make() {
        $params = array();
        // get all menu data - 1 query with 2 left joins is better then 2 query's for each menu.
        $stmt = database::getInstance()->con()->query("SELECT h.*, g.g_id, g.g_name, g.g_url, d.d_name, d.d_url FROM `".property::getInstance()->get('db_prefix')."_mod_menu_gitem` as g
        LEFT OUTER JOIN `".property::getInstance()->get('db_prefix')."_mod_menu_ditem` as d ON g.g_id = d.d_owner_gid
        LEFT OUTER JOIN `".property::getInstance()->get('db_prefix')."_mod_menu_header` as h ON h.menu_id = g.g_menu_head_id
        WHERE h.menu_display = 1
        ORDER BY g.g_priority ASC, d.d_priority ASC");
        $resultItems = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;

        foreach($resultItems as $row) {
            $serial_hname = unserialize($row['menu_name']);
            $serial_gname = unserialize($row['g_name']);
            $serial_dname = unserialize($row['d_name']);

            $params['modmenu'][$row['menu_id']]['name'] = $serial_hname[language::getInstance()->getUseLanguage()];
            $params['modmenu'][$row['menu_id']]['tag'] = $row['menu_tag'];
            $params['modmenu'][$row['menu_id']]['tpl'] = $row['menu_tpl'];

            $params['modmenu'][$row['menu_id']]['item'][$row['g_id']]['name'] = $serial_gname[language::getInstance()->getUseLanguage()];
            $params['modmenu'][$row['menu_id']]['item'][$row['g_id']]['url'] = $this->urlRelativeToAbsolute($row['g_url']);

            if($row['d_name'] != null) {
                $params['modmenu'][$row['menu_id']]['item'][$row['g_id']]['depend_array'][] = array(
                    'name' => $serial_dname[language::getInstance()->getUseLanguage()],
                    'url' => $this->urlRelativeToAbsolute($row['d_url'])
                );
            }
        }
        $set_var = array();
        foreach($params['modmenu'] as $menu) {
            $tag = $menu['tag'];
            $tpl = $menu['tpl'];
            $compile_tpl = template::getInstance()->twigRender('modules/menu/' . $tpl, array('modmenu' => $menu));
            $set_var[$tag] = $compile_tpl;
        }
        template::getInstance()->set(template::TYPE_MODULE, 'menu', $set_var);
    }

    public function urlRelativeToAbsolute($url) {
        if(!system::getInstance()->prefixEquals($url, 'http'))
            $url = property::getInstance()->get('url') . $url;
        return $url;
    }
}