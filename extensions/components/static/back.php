<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\admin;
use engine\system;
use engine\template;
use engine\database;
use engine\property;
use engine\language;
use engine\user;
use engine\permission;
use engine\csrf;
use engine\extension;

class components_static_back {
    protected static $instance = null;
    const ITEM_PER_PAGE = 10;
    const SEARCH_PER_PAGE = 50;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function _version() {
        return '1.0.1';
    }

    public function _compatable() {
        return '2.0.3';
    }

    public function make() {
        $content = null;
        switch(system::getInstance()->get('make')) {
            case null:
                $content = $this->viewStaticList();
                break;
            case 'add':
                $content = $this->viewStaticAdd();
                break;
            case 'edit':
                $content = $this->viewStatidEdit();
                break;
            case 'delete':
                $content = $this->viewStaticDelete();
                break;
        }
        return $content;
    }

    public function accessData() {
        return array(
            'admin/components/static',
            'admin/components/static/add',
            'admin/components/static/edit',
            'admin/components/static/delete',
        );
    }

    private function viewStaticDelete() {
        csrf::getInstance()->buildToken();
        $params = array();
        $page_id = (int)system::getInstance()->get('id');
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_static WHERE id = ?");
        $stmt->bindParam(1, $page_id, PDO::PARAM_INT);
        $stmt->execute();

        if($result = $stmt->fetch()) { // its found
            if(system::getInstance()->post('submit') && csrf::getInstance()->check()) {
                $stmt = null;
                $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_com_static WHERE id = ? LIMIT 1");
                $stmt->bindParam(1, $page_id, PDO::PARAM_INT);
                $stmt->execute();
                system::getInstance()->redirect($_SERVER['PHP_SELF'] . '?object=components&action=static');
            }
            $title = unserialize($result['title']);
            $params['static'] = array(
                'id' => $result['id'],
                'title' => $title[language::getInstance()->getUseLanguage()],
                'pathway' => $result['pathway']
            );
        } else {
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . '?object=components&action=static');
        }
        return template::getInstance()->twigRender('components/static/delete.tpl', $params);
    }

    private function viewStatidEdit() {
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['langs']['all'] = language::getInstance()->getAvailable();
        $params['langs']['current'] = property::getInstance()->get('lang');
        $static_id = (int)system::getInstance()->get('id');
        if(system::getInstance()->post('save')) {
            $page_title = serialize(system::getInstance()->nohtml(system::getInstance()->post('title')));
            $page_way = system::getInstance()->nohtml(system::getInstance()->post('pathway') . ".html");
            $page_text = serialize(system::getInstance()->post('text'));
            $page_description = serialize(system::getInstance()->nohtml(system::getInstance()->post('description')));
            $page_keywords = serialize(system::getInstance()->nohtml(system::getInstance()->post('keywords')));
            $page_date = system::getInstance()->post('current_date') == "on" ? time() : system::getInstance()->toUnixTime(system::getInstance()->post('date'));
            if ($this->checkPageWay($page_way, $static_id)) {
                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_static SET title = ?, text = ?, pathway = ?, description = ?, keywords = ?, date = ? WHERE id = ?");
                $stmt->bindParam(1, $page_title, PDO::PARAM_STR);
                $stmt->bindParam(2, $page_text, PDO::PARAM_STR);
                $stmt->bindParam(3, $page_way, PDO::PARAM_STR);
                $stmt->bindParam(4, $page_description, PDO::PARAM_STR);
                $stmt->bindParam(5, $page_keywords, PDO::PARAM_STR);
                $stmt->bindParam(6, $page_date, PDO::PARAM_INT);
                $stmt->bindParam(7, $static_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                $params['notify']['success'] = true;
            } else {
                $params['notify']['pathmatch'] = true;
            }
        }

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_static WHERE id = ?");
        $stmt->bindParam(1, $static_id, PDO::PARAM_INT);
        $stmt->execute();
        if($result = $stmt->fetch()) {
            $params['static']['title'] = unserialize($result['title']);
            $params['static']['text'] = unserialize($result['text']);
            $params['static']['pathway'] = system::getInstance()->noextention($result['pathway']);
            $params['static']['date'] = system::getInstance()->toDate($result['date'], 'd');
            $params['static']['keywords'] = unserialize($result['keywords']);
            $params['static']['description'] = unserialize($result['description']);
        } else {
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . '?object=components&action=static');
        }
        $stmt = null;
        return template::getInstance()->twigRender('components/static/edit.tpl', $params);
    }

    private function viewStaticAdd() {
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['langs']['all'] = language::getInstance()->getAvailable();
        $params['langs']['current'] = property::getInstance()->get('lang');

        if(system::getInstance()->post('save')) {
            $params['static']['title'] = system::getInstance()->nohtml(system::getInstance()->post('title'));
            $params['static']['text'] = system::getInstance()->post('text');
            $params['static']['pathway'] = system::getInstance()->nohtml(system::getInstance()->post('pathway'));
            $params['static']['keywords'] = system::getInstance()->nohtml(system::getInstance()->post('keywords'));
            $params['static']['description'] = system::getInstance()->nohtml(system::getInstance()->post('description'));
            $page_date = system::getInstance()->post('current_date') == "on" ? time() : system::getInstance()->toUnixTime(system::getInstance()->post('date'));
            $params['static']['date'] = system::getInstance()->toDate($page_date, 'd');
            $page_owner = user::getInstance()->get('id');
            if(strlen($params['static']['title'][property::getInstance()->get('lang')]) < 1) {
                $params['notify']['notitle'] = true;
            } elseif(!$this->checkPageWay($params['static']['pathway'])) {
                $params['notify']['pathmatch'] = true;
            } else {
                $serial_title = serialize($params['static']['title']);
                $serial_text = serialize($params['static']['text']);
                $serial_description = serialize($params['static']['description']);
                $serial_keywords = serialize($params['static']['keywords']);
                $save_pathway = $params['static']['pathway'].".html";
                if($page_date == null)
                    $page_date = time();
                $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_com_static (title, text, owner, pathway, date, description, keywords) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bindParam(1, $serial_title, PDO::PARAM_STR);
                $stmt->bindParam(2, $serial_text, PDO::PARAM_STR);
                $stmt->bindParam(3, $page_owner, PDO::PARAM_INT);
                $stmt->bindParam(4, $save_pathway, PDO::PARAM_STR);
                $stmt->bindParam(5, $page_date, PDO::PARAM_INT);
                $stmt->bindParam(6, $serial_description, PDO::PARAM_STR);
                $stmt->bindParam(7, $serial_keywords, PDO::PARAM_STR);
                $stmt->execute();
                $stmt = null;
                $stream = extension::getInstance()->call(extension::TYPE_COMPONENT, 'stream');
                if(is_object($stream))
                    $stream->add('static.add', $page_owner, property::getInstance()->get('url').'/static/'.$save_pathway, $params['static']['title'][language::getInstance()->getUseLanguage()]);
                system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=static");
            }
        }
        return template::getInstance()->twigRender('components/static/edit.tpl', $params);
    }

    private function viewStaticList() {
        csrf::getInstance()->buildToken();
        $params = array();

        if(system::getInstance()->post('deleteSelected') && csrf::getInstance()->check()) {
            if(permission::getInstance()->have('global/owner') || permission::getInstance()->have('admin/components/static/delete')) {
                $toDelete = system::getInstance()->post('check_array');
                if(is_array($toDelete) && sizeof($toDelete) > 0) {
                    $listDelete = system::getInstance()->altimplode(',', $toDelete);
                    if(system::getInstance()->isIntList($listDelete)) {
                        database::getInstance()->con()->query("DELETE FROM ".property::getInstance()->get('db_prefix')."_com_static WHERE id IN (".$listDelete.")");
                    }
                }
            }
        }

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $index_start = (int)system::getInstance()->get('index');
        $db_index = $index_start * self::ITEM_PER_PAGE;
        if (system::getInstance()->post('dosearch') && strlen(system::getInstance()->post('search')) > 0) {
            $params['search']['value'] = system::getInstance()->nohtml(system::getInstance()->post('search'));
            $search_string = "%".system::getInstance()->nohtml(system::getInstance()->post('search'))."%";
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_static WHERE title like ? OR text like ? ORDER BY id DESC LIMIT 0,".self::SEARCH_PER_PAGE);
            $stmt->bindParam(1, $search_string, PDO::PARAM_STR);
            $stmt->bindParam(2, $search_string, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_static ORDER BY id DESC LIMIT ?,".self::ITEM_PER_PAGE);
            $stmt->bindParam(1, $db_index, PDO::PARAM_INT);
            $stmt->execute();
        }
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($result as $data) {
            $title_locale = unserialize($data['title']);
            $params['static'][] = array(
                'id' => $data['id'],
                'title' => $title_locale[language::getInstance()->getUseLanguage()],
                'path' => $data['pathway'],
                'date' => system::getInstance()->toDate($data['date'], 'h')
            );
        }
        $params['pagination'] = template::getInstance()->showFastPagination($index_start, self::ITEM_PER_PAGE, $this->getTotalStaticCount(), '?object=components&action=static&index=');
        return template::getInstance()->twigRender('components/static/list.tpl', $params);
    }

    private function getTotalStaticCount()
    {
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_static");
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt = null;
        return $result[0];
    }

    private function checkPageWay($way, $id = 0)
    {
        if (preg_match('/[\'~`\!@#\$%\^&\*\(\)+=\{\}\[\]\|;:"\<\>,\?\\\]/', $way) || strlen($way) < 1) {
            return false;
        }
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_static WHERE pathway = ? AND id != ?");
        $stmt->bindParam(1, $way, PDO::PARAM_STR);
        $stmt->bindParam(2, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? false : true;
    }
}


?>