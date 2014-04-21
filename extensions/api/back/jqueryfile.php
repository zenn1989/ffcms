<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\system;
use engine\extension;
use engine\property;
use engine\permission;

class api_jqueryfile_back {
    protected static $instance = null;
    const filepath = '/news/gallery/';

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        if(!permission::getInstance()->have('global/owner'))
            return;
        header('Content-type: application/json');
        switch(system::getInstance()->get('action')) {
            case 'upload':
                $this->viewUpload();
                break;
            case 'list':
                $this->viewList();
                break;
            case 'delete':
                $this->viewDelete();
                break;
        }
    }

    private function viewDelete() {
        $fname = system::getInstance()->get('name');
        $news_id = (int)system::getInstance()->get('id');
        $file_split = explode('.', $fname);
        $file_ext = array_pop($file_split);
        if(!in_array($file_ext, array('jpg', 'png', 'gif', 'bmp', 'jpeg')) || $news_id < 1) {
            return;
        }
        $full_img = root . '/upload' . self::filepath . $news_id . '/orig/' . $fname;
        $thumb_img = root . '/upload' . self::filepath . $news_id . '/thumb/' . $fname;
        if(file_exists($full_img) && file_exists($thumb_img)) {
            @unlink($full_img);
            @unlink($thumb_img);
        }
    }

    private function viewList() {
        $news_id = (int)system::getInstance()->get('id');
        $path = root . '/upload' . self::filepath . $news_id . '/';
        if($news_id < 1 || !file_exists($path))
            return;
        $output = array();
        foreach(system::getInstance()->altscandir($path . 'orig/', true) as $files) {
            $file_split = explode('.', $files);
            $file_ext = array_pop($file_split);
            if(in_array($file_ext, array('jpg', 'png', 'gif', 'bmp', 'jpeg'))) {
                $output[] = array(
                    'name' => $files,
                    'url' => property::getInstance()->get('script_url') . '/upload' . self::filepath . $news_id . '/orig/' . $files,
                    'thumbnailUrl' => property::getInstance()->get('script_url') . '/upload' . self::filepath . $news_id . '/thumb/' . $files,
                );
            }
        }
        echo json_encode(array('files' => $output));
    }

    private function viewUpload() {
        $file = $_FILES['files'];
        $news_id = (int)system::getInstance()->get('id');

        if($file['size'] < 1 || !is_int($news_id) || $news_id < 1) {
            return;
        }

        $dir = self::filepath . $news_id . '/';

        $full_img = extension::getInstance()->call(extension::TYPE_HOOK, 'file')->uploadImage($dir . 'orig/', $file);
        if(!$full_img)
            return;
        $full_path = root . '/upload' . $dir . 'orig/' . $full_img;

        $dx = extension::getInstance()->getConfig('gallery_dx', 'news', extension::TYPE_COMPONENT, 'int');
        $dy = extension::getInstance()->getConfig('gallery_dy', 'news', extension::TYPE_COMPONENT, 'int');
        $thumb_img = extension::getInstance()->call(extension::TYPE_HOOK, 'file')->uploadResizedImage($dir . 'thumb/', $full_path, $dx, $dy, $full_img);

        $output[] = array(
            'name' => $full_img,
            'url' => property::getInstance()->get('script_url') . '/upload' . $dir . 'orig/' . $full_img,
            'thumbnailUrl' => property::getInstance()->get('script_url') . '/upload' . $dir . 'thumb/' . $thumb_img,
        );
        echo json_encode(array('files' => $output));

    }
}