<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

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

    const FILTER_ALL = 0;
    const FILTER_MODERATE = 1;
    const FILTER_IMPORTANT = 2;
    const FILTER_SEARCH = 3;

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
            case 'editcategory':
                $content = $this->viewNewsEditCategory();
                break;
        }
        template::getInstance()->set(template::TYPE_CONTENT, 'body', $content);
    }

    private function viewNewsEditCategory() {
        $cat_id = (int)system::getInstance()->get('id');
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['news']['categorys'] = extension::getInstance()->call(extension::TYPE_COMPONENT, 'news')->getCategoryArray();

        if(system::getInstance()->post('submit')) {
            $cat_name = system::getInstance()->nohtml(system::getInstance()->post('category_name'));
            $cat_desc = system::getInstance()->post('category_desc');
            $cat_path = system::getInstance()->nohtml(system::getInstance()->post('category_path'));
            $owner_cat_id = (int)system::getInstance()->post('category_owner');
            $stmt = database::getInstance()->con()->prepare("SELECT path FROM ".property::getInstance()->get('db_prefix')."_com_news_category WHERE category_id = ?");
            $stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
            $stmt->execute();
            $resCat = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt = null;
            $old_path = $resCat['path'];
            if(!system::getInstance()->isInt($cat_id) || $cat_id < 1) {
                $params['notify']['owner_notselect'] = true;
            }
            if(strlen($cat_name[property::getInstance()->get('lang')]) < 1) {
                $params['notify']['noname'] = true;
            }
            if($cat_id != 1 && $cat_id != $owner_cat_id && $old_path != $cat_path && !system::getInstance()->suffixEquals($old_path, $cat_path)) { // its not a general category?
                if (!$this->checkCategoryWay($cat_path, $owner_cat_id, $cat_id)) {
                    $params['notify']['wrongpath'] = true;
                }
            }
            if(sizeof($params['notify']) == 0) {
                $stmt = database::getInstance()->con()->prepare("SELECT path FROM ".property::getInstance()->get('db_prefix')."_com_news_category WHERE category_id = ?");
                $stmt->bindParam(1, $owner_cat_id, PDO::PARAM_INT);
                $stmt->execute();
                $resMother = $stmt->fetch();
                $new_category_path = null;
                if ($resMother['path'] == null) {
                    $new_category_path = $cat_path;
                } else {
                    $new_category_path = $resMother['path'] . "/" . $cat_path;
                }
                $serial_name = serialize(system::getInstance()->altaddslashes($cat_name));
                $serial_desc = serialize(system::getInstance()->altaddslashes($cat_desc));
                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_news_category SET `path` = ?, `name` = ?, `desc` = ? WHERE `category_id` = ?");
                $stmt->bindParam(1, $new_category_path, PDO::PARAM_STR);
                $stmt->bindParam(2, $serial_name, PDO::PARAM_STR);
                $stmt->bindParam(3, $serial_desc, PDO::PARAM_STR);
                $stmt->bindParam(4, $cat_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                system::getInstance()->redirect($_SERVER['PHP_SELF'] . '?object=components&action=news&make=category');
            }
        }

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_news_category WHERE category_id = ?");
        $stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        if($res = $stmt->fetch()) {
            $stmt = null;
            $path_array = system::getInstance()->altexplode('/', $res['path']);
            $last_path_name = array_pop($path_array);
            $owner_path = system::getInstance()->altimplode('/', $path_array);
            $params['cat'] = array(
                'name' => unserialize($res['name']),
                'desc' => unserialize($res['desc']),
                'path' => $last_path_name
            );
            $stmt = database::getInstance()->con()->prepare("SELECT category_id FROM ".property::getInstance()->get('db_prefix')."_com_news_category WHERE path = ?");
            $stmt->bindParam(1, $owner_path, PDO::PARAM_STR);
            $stmt->execute();
            if($resOwner = $stmt->fetch()) {
                $params['news']['selected_category'] = $resOwner['category_id'];
            }
            $stmt = null;
        } else {
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . '?object=components&action=news&make=category');
        }


        return template::getInstance()->twigRender('components/news/category_add.tpl', $params);
    }

    private function viewNewsDelCategory() {
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['news']['categorys'] = extension::getInstance()->call(extension::TYPE_COMPONENT, 'news')->getCategoryArray();

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
        $params['news']['categorys'] = extension::getInstance()->call(extension::TYPE_COMPONENT, 'news')->getCategoryArray();
        $params['news']['selected_category'] = (int)system::getInstance()->get('id');

        if (system::getInstance()->post('submit')) {
            $cat_id = system::getInstance()->post('category_owner');
            $cat_name = system::getInstance()->nohtml(system::getInstance()->post('category_name'));
            $cat_desc = system::getInstance()->post('category_desc');
            $cat_serial_name = serialize(system::getInstance()->altaddslashes($cat_name));
            $cat_serial_desc = serialize(system::getInstance()->altaddslashes($cat_desc));
            $cat_path = system::getInstance()->post('category_path');
            if(!system::getInstance()->isInt($cat_id) || $cat_id < 1) {
                $params['notify']['owner_notselect'] = true;
            }
            if(strlen($cat_name[property::getInstance()->get('lang')]) < 1) {
                $params['notify']['noname'] = true;
            }
            if (!$this->checkCategoryWay($cat_path, $cat_id)) {
                $params['notify']['wrongpath'] = true;
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

                    $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_com_news_category (`name`, `desc`, `path`) VALUES (?, ?, ?)");
                    $stmt->bindParam(1, $cat_serial_name, PDO::PARAM_STR);
                    $stmt->bindParam(2, $cat_serial_desc, PDO::PARAM_STR);
                    $stmt->bindParam(3, $new_category_path, PDO::PARAM_STR);
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

        $params['news']['categorys'] = extension::getInstance()->call(extension::TYPE_COMPONENT, 'news')->getCategoryArray();

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
        $params['config']['enable_useradd'] = extension::getInstance()->getConfig('enable_useradd', 'news', extension::TYPE_COMPONENT, 'int');
        $params['config']['multi_category'] = extension::getInstance()->getConfig('multi_category', 'news', extension::TYPE_COMPONENT, 'int');
        $params['config']['enable_tags'] = extension::getInstance()->getConfig('enable_tags', 'news', extension::TYPE_COMPONENT, 'int');
        $params['config']['poster_dx'] = extension::getInstance()->getConfig('poster_dx', 'news', extension::TYPE_COMPONENT, 'int');
        $params['config']['poster_dy'] = extension::getInstance()->getConfig('poster_dy', 'news', extension::TYPE_COMPONENT, 'int');
        $params['config']['gallery_dx'] = extension::getInstance()->getConfig('gallery_dx', 'news', extension::TYPE_COMPONENT, 'int');
        $params['config']['gallery_dy'] = extension::getInstance()->getConfig('gallery_dy', 'news', extension::TYPE_COMPONENT, 'int');
        $params['config']['enable_rss'] = extension::getInstance()->getConfig('enable_rss', 'news', extension::TYPE_COMPONENT, 'int');
        $params['config']['rss_count'] = extension::getInstance()->getConfig('rss_count', 'news', extension::TYPE_COMPONENT, 'int');
        $params['config']['enable_full_rss'] = extension::getInstance()->getConfig('enable_full_rss', 'news', extension::TYPE_COMPONENT, 'int');

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
            // delete image poster and gallery images
            if(file_exists(root . '/upload/news/poster_' . $news_id . '.jpg'))
                @unlink(root . '/upload/news/poster_' . $news_id . '.jpg');
            if(file_exists(root . '/upload/news/gallery/' . $news_id . '/'))
                system::getInstance()->removeDirectory(root . '/upload/news/gallery/' . $news_id . '/');
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
        $params['news']['categorys'] = extension::getInstance()->call(extension::TYPE_COMPONENT, 'news')->getCategoryArray();
        $params['news']['id'] = $this->searchFreeId(); // for jquery ajax gallery images
        $params['news']['action_add'] = true;
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
            if (strlen($pathway) < 1 || !extension::getInstance()->call(extension::TYPE_COMPONENT, 'news')->checkNewsWay($pathway, 0, $params['news']['cat_id'])) {
                $params['notify']['wrongway'] = true;
            }
            if (strlen($params['news']['text'][property::getInstance()->get('lang')]) < 1) {
                $params['notify']['notext'] = true;
            }
            if (sizeof($params['notify']) == 0) {
                $serial_title = serialize(system::getInstance()->altaddslashes($params['news']['title']));
                $serial_text = serialize(system::getInstance()->altaddslashes($params['news']['text']));
                $serial_description = serialize(system::getInstance()->altaddslashes($params['news']['description']));
                $serial_keywords = serialize(system::getInstance()->altaddslashes($params['news']['keywords']));
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
                // image poster for news
                if($_FILES['newsimage']['size'] > 0) {
                    $save_name = 'poster_' . $new_news_id . '.jpg';
                    $dx = extension::getInstance()->getConfig('poster_dx', 'news', extension::TYPE_COMPONENT, 'int');
                    $dy = extension::getInstance()->getConfig('poster_dy', 'news', extension::TYPE_COMPONENT, 'int');
                    extension::getInstance()->call(extension::TYPE_HOOK, 'file')->uploadResizedImage('/news/', $_FILES['newsimage'], $dx, $dy, $save_name);
                }
                $gallery_folder = (int)system::getInstance()->post('news_gallery_id');
                $full_gallery_path = root . '/upload/news/gallery/';
                if(file_exists($full_gallery_path . $gallery_folder . '/'))
                    rename($full_gallery_path . $gallery_folder . '/', $full_gallery_path . $new_news_id . '/');
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
        $params['news']['categorys'] = extension::getInstance()->call(extension::TYPE_COMPONENT, 'news')->getCategoryArray();

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
            if (strlen($pathway) < 1 || !extension::getInstance()->call(extension::TYPE_COMPONENT, 'news')->checkNewsWay($pathway, $news_id, $category_id)) {
                $params['notify']['wrongway'] = true;
            }
            if (strlen($text[property::getInstance()->get('lang')]) < 1) {
                $params['notify']['notext'] = true;
            }
            if(sizeof($params['notify']) == 0) {
                $serial_title = serialize(system::getInstance()->altaddslashes($title));
                $serial_text = serialize(system::getInstance()->altaddslashes($text));
                $serial_description = serialize(system::getInstance()->altaddslashes($description));
                $serial_keywords = serialize(system::getInstance()->altaddslashes($keywords));
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
                if($_FILES['newsimage']['size'] > 0) {
                    $dx = extension::getInstance()->getConfig('poster_dx', 'news', extension::TYPE_COMPONENT, 'int');
                    $dy = extension::getInstance()->getConfig('poster_dy', 'news', extension::TYPE_COMPONENT, 'int');
                    $save_name = 'poster_' . $news_id . '.jpg';
                    extension::getInstance()->call(extension::TYPE_HOOK, 'file')->uploadResizedImage('/news/', $_FILES['newsimage'], $dx, $dy, $save_name);
                }
            }
        }

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_news_entery WHERE id = ?");
        $stmt->bindParam(1, $news_id, PDO::PARAM_INT);
        $stmt->execute();

        if($result = $stmt->fetch()) {
            $params['news']['id'] = $news_id;
            $params['news']['title'] = system::getInstance()->altstripslashes(unserialize($result['title']));
            $params['news']['text'] = system::getInstance()->altstripslashes(unserialize($result['text']));
            $params['news']['pathway'] = system::getInstance()->noextention($result['link']);
            $params['news']['cat_id'] = $result['category'];
            $params['news']['date'] = system::getInstance()->toDate($result['date'], 'h');
            $params['news']['description'] = system::getInstance()->altstripslashes(unserialize($result['description']));
            $params['news']['keywords'] = system::getInstance()->altstripslashes(unserialize($result['keywords']));
            $params['news']['display'] = $result['display'];
            $params['news']['important'] = $result['important'];
            if(file_exists(root . '/upload/news/poster_' . $news_id . '.jpg')) {
                $params['news']['poster_path'] = '/upload/news/poster_' . $news_id . '.jpg';
                $params['news']['poster_name'] = 'poster_' . $news_id . '.jpg';
            }
        } else {
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . '?object=components&action=static');
        }


        return template::getInstance()->twigRender('components/news/edit.tpl', $params);
    }

    private function viewNewsList() {
        $params = array();

        if(system::getInstance()->post('deleteSelected')) {
            $toDelete = system::getInstance()->post('check_array');
            if(is_array($toDelete) && sizeof($toDelete) > 0) {
                foreach($toDelete as $news_single_id) { // remove posible poster files and gallery images
                    if(file_exists(root . '/upload/news/poster_' . $news_single_id . '.jpg'))
                        @unlink(root . '/upload/news/poster_' . $news_single_id . '.jpg');
                    if(file_exists(root . '/upload/news/gallery/' . $news_single_id . '/'))
                        system::getInstance()->removeDirectory(root . '/upload/news/gallery/' . $news_single_id . '/');
                }
                $listDelete = system::getInstance()->altimplode(',', $toDelete);
                if(system::getInstance()->isIntList($listDelete)) {
                    database::getInstance()->con()->query("DELETE FROM ".property::getInstance()->get('db_prefix')."_com_news_entery WHERE id IN (".$listDelete.")");
                    // drop tags
                    database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_tags WHERE object_type = 'news' AND object_id IN (".$listDelete.")");
                }
            }
        }

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['search']['value'] = system::getInstance()->nohtml(system::getInstance()->get('search'));
        $index_start = (int)system::getInstance()->get('index');
        $db_index = $index_start * self::ITEM_PER_PAGE;
        $stmt = null;
        $filter = (int)system::getInstance()->get('filter');
        if($filter === self::FILTER_MODERATE) { // 1
            $stmt = database::getInstance()->con()->prepare("SELECT a.id,a.title,a.category,a.link,b.category_id,b.path FROM ".property::getInstance()->get('db_prefix')."_com_news_entery a, ".
                property::getInstance()->get('db_prefix')."_com_news_category b WHERE a.category = b.category_id AND a.display = 0 ORDER BY a.id DESC LIMIT ?,".self::ITEM_PER_PAGE);
            $stmt->bindParam(1, $db_index, PDO::PARAM_INT);
            $stmt->execute();
        } elseif($filter === self::FILTER_IMPORTANT) { // 2
            $stmt = database::getInstance()->con()->prepare("SELECT a.id,a.title,a.category,a.link,b.category_id,b.path FROM ".property::getInstance()->get('db_prefix')."_com_news_entery a, ".
                property::getInstance()->get('db_prefix')."_com_news_category b WHERE a.category = b.category_id AND a.important = 1 ORDER BY a.id DESC LIMIT ?,".self::ITEM_PER_PAGE);
            $stmt->bindParam(1, $db_index, PDO::PARAM_INT);
            $stmt->execute();
        } elseif($filter === self::FILTER_SEARCH) { // 3
            $search_string = "%".$params['search']['value']."%";
            $stmt = database::getInstance()->con()->prepare("SELECT a.id,a.title,a.category,a.link,b.category_id,b.path FROM ".property::getInstance()->get('db_prefix')."_com_news_entery a, ".
                property::getInstance()->get('db_prefix')."_com_news_category b WHERE a.category = b.category_id AND (a.title like ? OR a.text like ?) ORDER BY a.id DESC LIMIT 0,".self::SEARCH_PER_PAGE);
            $stmt->bindParam(1, $search_string, PDO::PARAM_STR);
            $stmt->bindParam(2, $search_string, PDO::PARAM_STR);
            $stmt->execute();
        } else { // 0 || > 3
            $stmt = database::getInstance()->con()->prepare("SELECT a.id,a.title,a.category,a.link,b.category_id,b.path FROM ".property::getInstance()->get('db_prefix')."_com_news_entery a, ".
                property::getInstance()->get('db_prefix')."_com_news_category b WHERE a.category = b.category_id ORDER BY a.id DESC LIMIT ?,".self::ITEM_PER_PAGE);
            $stmt->bindParam(1, $db_index, PDO::PARAM_INT);
            $stmt->execute();
            $filter = 0;
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
        $params['pagination'] = template::getInstance()->showFastPagination($index_start, self::ITEM_PER_PAGE, $this->getTotalNewsCount($filter), '?object=components&action=news&filter='.$filter.'&index=');

        return template::getInstance()->twigRender('components/news/list.tpl', $params);
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

    public function getTotalNewsCount($filter = 0) {
        $query = null;
        switch($filter) {
            case 1:
                $query = "SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_news_entery WHERE display = 0";
                break;
            case 2:
                $query = "SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_news_entery WHERE important = 1";
                break;
            default:
                $query = "SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_news_entery";
                break;
        }
        $stmt = database::getInstance()->con()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt = null;
        return $result[0];
    }

    public function searchFreeId($i = 10) {
        $news_id = system::getInstance()->randomInt($i);
        $folder = root . '/upload/news/gallery/' . $news_id . '/';
        if(file_exists($folder)) {
            return $this->searchFreeId(++$i);
        }
        return $news_id;
    }
}