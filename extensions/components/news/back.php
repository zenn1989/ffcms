<?php

use engine\system;
use engine\admin;
use engine\template;
use engine\database;
use engine\property;
use engine\language;
use engine\user;
use engine\extension;

class components_news_back {
    protected static $instance = null;

    const ITEM_PER_PAGE = 10;
    const SEARCH_PER_PAGE = 50;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $content = null;
        switch(system::getInstance()->get('make')) {
            case null:
            case 'list':
                $content = $this->viewNewsList();
                break;
            case 'edit':
                $content = $this->viewNewsEdit();
                break;
            case 'add':
                $content = $this->viewNewsAdd();
                break;
            case 'delete':
                $content = $this->viewNewsDelete();
                break;
            case 'settings':
                $content = $this->viewNewsSettings();
                break;
            case 'category':
                $content = $this->viewNewsCategory();
                break;
            case 'addcategory':
                $content = $this->viewNewsAddCategory();
                break;
            case 'delcategory':
                $content = $this->viewNewsDelCategory();
                break;
        }
        template::getInstance()->set(template::TYPE_CONTENT, 'body', $content);
    }

    private function viewNewsDelCategory() {
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['news']['categorys'] = $this->getCategoryArray();

        $cat_id = (int)system::getInstance()->get('id');

        $params['news']['selected_category'] = $cat_id;

        if($cat_id < 1)
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=news&make=category");

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM " . property::getInstance()->get('db_prefix') . "_com_news_category WHERE category_id = ?");
        $stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        if ($res = $stmt->fetch()) {
            $cat_serial_name = unserialize($res['name']);
            $params['cat']['name'] = $cat_serial_name[property::getInstance()->get('lang')];
            $params['cat']['path'] = $res['path'];
        }
        $stmt = null;
        if($params['cat']['path'] != null) {
            $notify = null;
            if (system::getInstance()->post('deletecategory')) {
                $move_to_cat = (int)system::getInstance()->post('move_to_category');
                if($move_to_cat < 1) {
                    $params['notify']['nomoveto'] = true;
                } else {
                    $like_path = $params['cat']['path'] . "%";
                    $stmt = database::getInstance()->con()->prepare("SELECT category_id FROM " . property::getInstance()->get('db_prefix') . "_com_news_category WHERE path like ?");
                    $stmt->bindParam(1, $like_path, PDO::PARAM_STR);
                    $stmt->execute();
                    $cat_to_remove_array = array();
                    while ($result = $stmt->fetch()) {
                        $cat_to_remove_array[] = $result['category_id'];
                    }
                    $stmt = null;
                    $cat_remove_list = system::getInstance()->altimplode(',', $cat_to_remove_array); // is safefull, cuz id's defined in db like INT with autoincrement
                    $stmt = database::getInstance()->con()->prepare("UPDATE " . property::getInstance()->get('db_prefix') . "_com_news_entery SET category = ? WHERE category in({$cat_remove_list})");
                    $stmt->bindParam(1, $move_to_cat, PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                    $stmt = database::getInstance()->con()->prepare("DELETE FROM " . property::getInstance()->get('db_prefix') . "_com_news_category WHERE category_id in ({$cat_remove_list})");
                    $stmt->execute();
                    $stmt = null;
                    system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=news&make=category");;
                }
            }
        } else {
            $params['notify']['unpos_delete'] = true;
        }
        return template::getInstance()->twigRender('components/news/category_del.tpl', $params);
    }

    private function viewNewsAddCategory() {
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['langs']['all'] = language::getInstance()->getAvailable();
        $params['langs']['current'] = property::getInstance()->get('lang');
        $params['news']['categorys'] = $this->getCategoryArray();
        $params['news']['selected_category'] = (int)system::getInstance()->get('id');

        if (system::getInstance()->post('submit')) {
            $cat_id = system::getInstance()->post('category_owner');
            $cat_name = system::getInstance()->post('category_name');
            $cat_serial_name = serialize($cat_name);
            $cat_path = system::getInstance()->post('category_path');
            if(!system::getInstance()->isInt($cat_id) || $cat_id < 1) {
                $params['notify']['owner_notselect'] = true;
                //$notify .= template::stringNotify('error', language::get('admin_component_news_category_notify_noselectcat'));
            }
            if(strlen($cat_name[property::getInstance()->get('lang')]) < 1) {
                $params['notify']['noname'] = true;
                //$notify .= template::stringNotify('error', language::get('admin_component_news_category_notify_noname'));
            }
            if (!$this->checkCategoryWay($cat_path, $cat_id)) {
                $params['notify']['wrongpath'] = true;
                //$notify .= template::stringNotify('error', language::get('admin_component_news_category_notify_pathwrong'));
            }
            if (sizeof($params['notify']) == 0) {
                $stmt = database::getInstance()->con()->prepare("SELECT path FROM ".property::getInstance()->get('db_prefix')."_com_news_category WHERE category_id  = ?");
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

                    $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_com_news_category (`name`, `path`) VALUES (?, ?)");
                    $stmt->bindParam(1, $cat_serial_name, PDO::PARAM_STR);
                    $stmt->bindParam(2, $new_category_path, PDO::PARAM_STR);
                    $stmt->execute();
                    system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=news&make=category");
                }
            }
        }

        return template::getInstance()->twigRender('components/news/category_add.tpl', $params);
    }

    private function viewNewsCategory() {
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $params['news']['categorys'] = $this->getCategoryArray();

        return template::getInstance()->twigRender('components/news/category_list.tpl', $params);
    }

    private function viewNewsSettings() {
        $params = array();
        if(system::getInstance()->post('submit')) {
            if(admin::getInstance()->saveExtensionConfigs()) {
                $params['notify']['save_success'] = true;
            }
        }
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['config']['count_news_page'] = extension::getInstance()->getConfig('count_news_page', 'news', extension::TYPE_COMPONENT, 'int');
        $params['config']['short_news_length'] = extension::getInstance()->getConfig('short_news_length', 'news', extension::TYPE_COMPONENT, 'int');
        $params['config']['enable_views_count'] = extension::getInstance()->getConfig('enable_views_count', 'news', extension::TYPE_COMPONENT, 'int');
        $params['config']['multi_category'] = extension::getInstance()->getConfig('multi_category', 'news', extension::TYPE_COMPONENT, 'int');
        $params['config']['enable_tags'] = extension::getInstance()->getConfig('enable_tags', 'news', extension::TYPE_COMPONENT, 'int');

        return template::getInstance()->twigRender('components/news/settings.tpl', $params);
    }

    private function viewNewsDelete() {
        $news_id = (int)system::getInstance()->get('id');
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        if(system::getInstance()->post('submit')) {
            $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_com_news_entery WHERE id = ?");
            $stmt->bindParam(1, $news_id, PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_tags WHERE object_type = 'news' AND object_id = ?");
            $stmt->bindParam(1, $news_id, PDO::PARAM_INT);
            $stmt->execute();
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=news");
        }

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_news_entery WHERE id = ?");
        $stmt->bindParam(1, $news_id, PDO::PARAM_INT);
        $stmt->execute();

        if($result = $stmt->fetch()) {
            $title = unserialize($result['title']);

            $params['news'] = array(
                'id' => $news_id,
                'title' => $title[language::getInstance()->getUseLanguage()],
                'pathway' => $result['link']
            );
        }

        return template::getInstance()->twigRender('components/news/delete.tpl', $params);
    }

    private function viewNewsAdd() {
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['langs']['all'] = language::getInstance()->getAvailable();
        $params['langs']['current'] = property::getInstance()->get('lang');
        $params['news']['categorys'] = $this->getCategoryArray();
        if(system::getInstance()->post('save')) {
            $editor_id = user::getInstance()->get('id');
            $params['news']['title'] = system::getInstance()->nohtml(system::getInstance()->post('title'));
            $params['news']['cat_id'] = system::getInstance()->post('category');
            $params['news']['pathway'] = system::getInstance()->nohtml(system::getInstance()->post('pathway'));
            $pathway = $params['news']['pathway'] . ".html";
            $params['news']['display'] = system::getInstance()->post('display_content') == "on" ? 1 : 0;
            $params['news']['important'] = system::getInstance()->post('important_content') == "on" ? 1 : 0;
            $params['news']['text'] = system::getInstance()->post('text');
            $params['news']['description'] = system::getInstance()->nohtml(system::getInstance()->post('description'));
            $params['news']['keywords'] = system::getInstance()->nohtml(system::getInstance()->post('keywords'));
            $date = system::getInstance()->post('current_date') == "on" ? time() : system::getInstance()->toUnixTime(system::getInstance()->post('date'));
            $params['news']['date'] = system::getInstance()->toDate($date, 'h');
            if (strlen($params['news']['title'][property::getInstance()->get('lang')]) < 1) {
                $params['notify']['notitle'] = true;
            }
            if (!system::getInstance()->isInt($params['news']['cat_id'])) {
                $params['notify']['nocat'] = true;
            }
            if (strlen($pathway) < 1 || !$this->checkNewsWay($pathway, 0, $params['news']['cat_id'])) {
                $params['notify']['wrongway'] = true;
            }
            if (strlen($params['news']['text'][property::getInstance()->get('lang')]) < 1) {
                $params['notify']['notext'] = true;
            }
            if (sizeof($params['notify']) < 1) {
                $serial_title = serialize($params['news']['title']);
                $serial_text = serialize($params['news']['text']);
                $serial_description = serialize($params['news']['description']);
                $serial_keywords = serialize($params['news']['keywords']);
                $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_com_news_entery
					(`title`, `text`, `link`, `category`, `date`, `author`, `description`, `keywords`, `display`, `important`) VALUES
					(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bindParam(1, $serial_title, PDO::PARAM_STR);
                $stmt->bindParam(2, $serial_text, PDO::PARAM_STR);
                $stmt->bindParam(3, $pathway, PDO::PARAM_STR);
                $stmt->bindParam(4, $params['news']['cat_id'], PDO::PARAM_INT);
                $stmt->bindParam(5, $date, PDO::PARAM_STR);
                $stmt->bindParam(6, $editor_id, PDO::PARAM_INT);
                $stmt->bindParam(7, $serial_description, PDO::PARAM_STR);
                $stmt->bindParam(8, $serial_keywords, PDO::PARAM_STR);
                $stmt->bindParam(9, $params['news']['display'], PDO::PARAM_INT);
                $stmt->bindParam(10, $params['news']['important'], PDO::PARAM_INT);
                $stmt->execute();
                $new_news_id = database::getInstance()->con()->lastInsertId();
                $stmt = null;
                foreach($params['news']['keywords'] as $keyrow) {
                    $keyrow_array = system::getInstance()->altexplode(',', $keyrow);
                    foreach($keyrow_array as $objectkey) {
                        $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_mod_tags(`object_id`, `object_type`, `tag`) VALUES (?, 'news', ?)");
                        $stmt->bindParam(1, $new_news_id, PDO::PARAM_INT);
                        $stmt->bindParam(2, $objectkey, PDO::PARAM_STR);
                        $stmt->execute();
                        $stmt = null;
                    }
                }
                system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=news");
            }
        }
        return template::getInstance()->twigRender('components/news/edit.tpl', $params);
    }

    private function viewNewsEdit() {
        $params = array();
        $news_id = (int)system::getInstance()->get('id');
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['langs']['all'] = language::getInstance()->getAvailable();
        $params['langs']['current'] = property::getInstance()->get('lang');
        $params['news']['categorys'] = $this->getCategoryArray();

        if(system::getInstance()->post('save')) {
            $editor_id = user::getInstance()->get('id');
            $title = system::getInstance()->nohtml(system::getInstance()->post('title'));
            $category_id = system::getInstance()->post('category');
            $pathway = system::getInstance()->nohtml(system::getInstance()->post('pathway')) . ".html";
            $display = system::getInstance()->post('display_content') == "on" ? 1 : 0;
            $important = system::getInstance()->post('important_content') == "on" ? 1 : 0;
            $text = system::getInstance()->post('text');
            $description = system::getInstance()->nohtml(system::getInstance()->post('description'));
            $keywords = system::getInstance()->nohtml(system::getInstance()->post('keywords'));
            $date = system::getInstance()->post('current_date') == "on" ? time() : system::getInstance()->toUnixTime(system::getInstance()->post('date'));
            if (strlen($title[property::getInstance()->get('lang')]) < 1) {
                $params['notify']['notitle'] = true;
            }
            if (!system::getInstance()->isInt($category_id)) {
                $params['notify']['nocat'] = true;
            }
            if (strlen($pathway) < 1 || !$this->checkNewsWay($pathway, $news_id, $category_id)) {
                $params['notify']['wrongway'] = true;
            }
            if (strlen($text[property::getInstance()->get('lang')]) < 1) {
                $params['notify']['notext'] = true;
            }
            if(sizeof($params['notify']) < 1) {
                $serial_title = serialize($title);
                $serial_text = serialize($text);
                $serial_description = serialize($description);
                $serial_keywords = serialize($keywords);
                $stmt = database::getInstance()->con()->prepare("UPDATE " . property::getInstance()->get('db_prefix') . "_com_news_entery SET title = ?, text = ?, link = ?,
						category = ?, date = ?, author = ?, description = ?, keywords = ?, display = ?, important = ? WHERE id = ?");
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
                $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_tags WHERE `object_type` = 'news' AND `object_id` = ?");
                $stmt->bindParam(1, $news_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                foreach($keywords as $keyrow) {
                    $keyrow_array = system::getInstance()->altexplode(',', $keyrow);
                    foreach($keyrow_array as $objectkey) {
                        $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_mod_tags(`object_id`, `object_type`, `tag`) VALUES (?, 'news', ?)");
                        $stmt->bindParam(1, $news_id, PDO::PARAM_INT);
                        $stmt->bindParam(2, $objectkey, PDO::PARAM_STR);
                        $stmt->execute();
                        $stmt = null;
                    }
                }
                $params['notify']['success'] = true;
            }
        }

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_news_entery WHERE id = ?");
        $stmt->bindParam(1, $news_id, PDO::PARAM_INT);
        $stmt->execute();

        if($result = $stmt->fetch()) {
            $params['news']['title'] = unserialize($result['title']);
            $params['news']['text'] = unserialize($result['text']);
            $params['news']['pathway'] = system::getInstance()->noextention($result['link']);
            $params['news']['cat_id'] = $result['category'];
            $params['news']['date'] = system::getInstance()->toDate($result['date'], 'h');
            $params['news']['description'] = unserialize($result['description']);
            $params['news']['keywords'] = unserialize($result['keywords']);
            $params['news']['display'] = $result['display'];
            $params['news']['important'] = $result['important'];
        } else {
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . '?object=components&action=static');
        }


        return template::getInstance()->twigRender('components/news/edit.tpl', $params);
    }

    // magic inside (:
    private function getCategoryArray() {
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_news_category ORDER BY `path` ASC");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        $work_data = array();
        $total_result = array();
        foreach($result as $item) {
            $work_data[$item['path']] = array(
                'id' => $item['category_id'],
                'name' => $item['name']
            );
        }
        ksort($work_data); // sort
        foreach($work_data as $path=>$row) {
            $cname = unserialize($row['name']);
            $spliter_count = substr_count($path, "/");
            $add = '';
            if ($path != null) {
                for ($i = -1; $i <= $spliter_count; $i++) {
                    $add .= "-";
                }
            } else {
                $add = "-";
            }
            $total_result[] = array(
                'id' => $row['id'],
                'name' => $add . ' ' . $cname[language::getInstance()->getUseLanguage()],
                'path' => $path
            );
        }
        return $total_result;
    }

    private function viewNewsList() {
        $params = array();

        if(system::getInstance()->post('deleteSelected')) {
            $toDelete = system::getInstance()->post('check_array');
            if(is_array($toDelete) && sizeof($toDelete) > 0) {
                $listDelete = system::getInstance()->altimplode(',', $toDelete);
                if(system::getInstance()->isIntList($listDelete)) {
                    database::getInstance()->con()->query("DELETE FROM ".property::getInstance()->get('db_prefix')."_com_news_entery WHERE id IN (".$listDelete.")");
                }
            }
        }

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['search']['value'] = system::getInstance()->nohtml(system::getInstance()->post('search'));
        $index_start = (int)system::getInstance()->get('index');
        $db_index = $index_start * self::ITEM_PER_PAGE;
        $stmt = null;
        if(system::getInstance()->post('dosearch') && strlen($params['search']['value']) > 0) {
            $search_string = "%".$params['search']['value']."%";
            $stmt = database::getInstance()->con()->prepare("SELECT a.id,a.title,a.category,a.link,b.category_id,b.path FROM ".property::getInstance()->get('db_prefix')."_com_news_entery a, ".
                property::getInstance()->get('db_prefix')."_com_news_category b WHERE a.category = b.category_id AND (a.title like ? OR a.text like ?) ORDER BY a.id DESC LIMIT 0,".self::SEARCH_PER_PAGE);
            $stmt->bindParam(1, $search_string, PDO::PARAM_STR);
            $stmt->bindParam(2, $search_string, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $stmt = database::getInstance()->con()->prepare("SELECT a.id,a.title,a.category,a.link,b.category_id,b.path FROM ".property::getInstance()->get('db_prefix')."_com_news_entery a, ".
                property::getInstance()->get('db_prefix')."_com_news_category b WHERE a.category = b.category_id ORDER BY a.id DESC LIMIT ?,".self::ITEM_PER_PAGE);
            $stmt->bindParam(1, $db_index, PDO::PARAM_INT);
            $stmt->execute();
        }
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        foreach($result as $data) {
            $title = unserialize($data['title']);
            $link = $data['path'];
            if($link != null)
                $link .= "/";
            $link .= $data['link'];
            $params['news'][] = array(
                'id' => $data['id'],
                'title' => $title[language::getInstance()->getUseLanguage()],
                'link' => $link
            );
        }
        $params['pagination'] = template::getInstance()->showFastPagination($index_start, self::ITEM_PER_PAGE, $this->getTotalNewsCount(), '?object=components&action=news&index=');

        return template::getInstance()->twigRender('components/news/list.tpl', $params);
    }

    private function checkNewsWay($way, $id = 0, $cat_id)
    {
        if (preg_match('/[\'~`\!@#\$%\^&\*\(\)+=\{\}\[\]\|;:"\<\>,\?\\\]/', $way) || $way == "tag") {
            return false;
        }
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_news_entery WHERE link = ? AND category = ? AND id != ?");
        $stmt->bindParam(1, $way, PDO::PARAM_STR);
        $stmt->bindParam(2, $cat_id, PDO::PARAM_INT);
        $stmt->bindParam(3, $id, PDO::PARAM_INT);
        $stmt->execute();
        $pRes = $stmt->fetch();
        $stmt = null;
        return $pRes[0] > 0 ? false : true;
    }

    private function checkCategoryWay($way, $cat_id)
    {
        if (preg_match('/[\'~`\!@#\$%\^&\*\(\)+=\{\}\[\]\|;:"\<\>,\?\\\]/', $way) || system::getInstance()->length($way) < 1) {
            return false;
        }
        $stmt = database::getInstance()->con()->prepare("SELECT path FROM ".property::getInstance()->get('db_prefix')."_com_news_category WHERE category_id  = ?");
        $stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        if ($result = $stmt->fetch()) {
            $stmt = null;
            $mother_path = $result['path'];
            $new_path_query = $result['path'] == null ? $way . "%" : $mother_path . "/" . $way . "%";
            $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_news_category WHERE path like ?");
            $stmt->bindParam(1, $new_path_query, PDO::PARAM_STR);
            $stmt->execute();
            if($res = $stmt->fetch()) {
                return $res[0] == 0 ? true : false;
            }
        }
        return false;
    }

    public function getTotalNewsCount() {
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_news_entery");
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt = null;
        return $result[0];
    }
}