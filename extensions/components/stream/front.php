<?php

use engine\template;
use engine\database;
use engine\system;
use engine\property;
use engine\user;
use engine\language;
use engine\meta;
use engine\extension;
use engine\router;

class components_stream_front extends engine\singleton {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function make() {
        $params = array();

        $way = router::getInstance()->shiftUriArray();
        $item_per_page = extension::getInstance()->getConfig('count_stream_page', 'stream', extension::TYPE_COMPONENT, 'int');
        if($item_per_page < 1)
            $item_per_page = 10;
        $page_index = (int)$way[0];
        $db_index = $page_index * $item_per_page;

        $seo_title = language::getInstance()->get('stream_title');
        if($page_index > 0)
            $seo_title .= " - ".$page_index;
        meta::getInstance()->add('title', $seo_title);
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_stream ORDER BY `date` DESC LIMIT ?,?");
        $stmt->bindParam(1, $db_index, \PDO::PARAM_INT);
        $stmt->bindParam(2, $item_per_page, \PDO::PARAM_INT);
        $stmt->execute();

        $resultAll = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;
        $ids = system::getInstance()->extractFromMultyArray('caster_id', $resultAll);
        $load_id = array();
        foreach($ids as $id) {
            if(system::getInstance()->isInt($id))
                $load_id[] = $id;
        }
        user::getInstance()->listload($load_id);

        $urlfix_object = extension::getInstance()->call(extension::TYPE_HOOK, 'urlfixer');
        $bbobject = extension::getInstance()->call(extension::TYPE_HOOK, 'bbtohtml');

        foreach($resultAll as $row) {
            $url_target = $row['target_object'];
            $text_target = system::getInstance()->nohtml($row['text_preview']);
            if(is_object($urlfix_object))
                $url_target = $urlfix_object->fix($url_target);
            if(is_object($bbobject))
                $text_target = $bbobject->nobbcode($text_target);
            $params['stream'][] = array(
                'id' => $row['id'],
                'type' => $row['type'],
                'type_language' => language::getInstance()->get('stream_gtype_'.$row['type']),
                'user_id' => $row['caster_id'],
                'user_name' => system::getInstance()->isInt($row['caster_id']) ? user::getInstance()->get('nick', $row['caster_id']) : '',
                'url' => $url_target,
                'text' => $text_target,
                'date' => system::getInstance()->todate($row['date'], 'h')
            );
        }

        $params['pagination'] = template::getInstance()->showFastPagination($page_index, $item_per_page, $this->streamCount(), 'stream');
        $tpl = template::getInstance()->twigRender('components/stream/list.tpl', $params);
        template::getInstance()->set(template::TYPE_CONTENT, 'body', $tpl);
    }

    /**
     * Add line to stream logs user activity
     * @param string $type
     * @param int|string $caster_id
     * @param string $target_url
     * @param null|string $preview_text
     * @param bool $save_syntax
     * @return bool
     */
    public function add($type, $caster_id, $target_url, $preview_text = null, $save_syntax = true) {
        if(strlen($type) < 1)
            return false;
        if(system::getInstance()->isInt($caster_id)) {
            if(!user::getInstance()->exists($caster_id))
                return false;
        } else {
            if(system::getInstance()->length($caster_id) < 1)
                return false;
        }
        if(!system::getInstance()->prefixEquals($target_url, property::getInstance()->get('url')))
            return false;

        if(!$save_syntax) {
            $preview_text = system::getInstance()->nohtml($preview_text);
            $bbobject = extension::getInstance()->call(extension::TYPE_HOOK, 'bbtohtml');
            if(is_object($bbobject))
                $preview_text = $bbobject->nobbcode($preview_text);
        }

        if(system::getInstance()->length($preview_text) > 25)
            $preview_text = system::getInstance()->sentenceSub($preview_text, 25) . '...';

        $date = time();
        $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_com_stream (`type`, `caster_id`, `target_object`, `text_preview`, `date`) VALUES (?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $type, \PDO::PARAM_STR);
        $stmt->bindParam(2, $caster_id, \PDO::PARAM_STR);
        $stmt->bindParam(3, $target_url, \PDO::PARAM_STR);
        $stmt->bindParam(4, $preview_text, \PDO::PARAM_STR|\PDO::PARAM_NULL);
        $stmt->bindParam(5, $date, \PDO::PARAM_INT);
        $stmt->execute();
        return true;
    }

    public function streamCount() {
        $stmt = database::getInstance()->con()->query("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_stream");
        $result = $stmt->fetch();
        $stmt = null;
        return $result[0];
    }
}