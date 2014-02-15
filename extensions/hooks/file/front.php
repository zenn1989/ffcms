<?php
use engine\logger;
use engine\system;
use engine\property;
use engine\user;

class hooks_file_front {
    protected static $instance = null;
    protected $directory = "/upload";

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function uploadAvatar($file) {
        $userid = user::getInstance()->get('id');
        if (!$this->validImageMime($file) || $userid < 1) {
            return false;
        }
        $dir_original = root . "/upload/user/avatar/original/";
        $tmp_arr = explode(".", $file['name']);
        $image_extension = array_pop($tmp_arr);
        $file_save_original = "avatar_$userid.$image_extension";
        $file_save_min_jpg = "avatar_$userid.jpg";
        $file_original_fullpath = $dir_original . $file_save_original;
        if (!file_exists($dir_original)) {
            @mkdir($dir_original, 0777, true);
        }
        move_uploaded_file($file['tmp_name'], $file_original_fullpath);
        $file_infofunction = getimagesize($file_original_fullpath);
        $image_buffer = null;
        if ($file_infofunction['mine'] == "image/jpg" || $file_infofunction['mime'] == "image/jpeg") {
            $image_buffer = imagecreatefromjpeg($file_original_fullpath);
        } elseif ($file_infofunction['mime'] == "image/gif") {
            $image_buffer = imagecreatefromgif($file_original_fullpath);
        } elseif ($file_infofunction['mime'] == "image/png") {
            $image_buffer = imagecreatefrompng($file_original_fullpath);
        } else {
            return false;
        }
        $image_ox = imagesx($image_buffer);
        $image_oy = imagesy($image_buffer);

        $image_big_dx = 400;
        $image_medium_dx = 200;
        $image_small_dx = 100;

        $image_big_dy = floor($image_oy * ($image_big_dx / $image_ox));
        $image_medium_dy = floor($image_oy * ($image_medium_dx / $image_ox));
        $image_small_dy = floor($image_oy * ($image_small_dx / $image_ox));

        $image_big_truecolor = imagecreatetruecolor($image_big_dx, $image_big_dy);
        $image_medium_truecolor = imagecreatetruecolor($image_medium_dx, $image_medium_dy);
        $image_small_truecolor = imagecreatetruecolor($image_small_dx, $image_small_dy);

        imagecopyresized($image_big_truecolor, $image_buffer, 0, 0, 0, 0, $image_big_dx, $image_big_dy, $image_ox, $image_oy);
        imagecopyresized($image_medium_truecolor, $image_buffer, 0, 0, 0, 0, $image_medium_dx, $image_medium_dy, $image_ox, $image_oy);
        imagecopyresized($image_small_truecolor, $image_buffer, 0, 0, 0, 0, $image_small_dx, $image_small_dy, $image_ox, $image_oy);

        if(!file_exists(root . '/upload/user/avatar/big/'))
            @mkdir(root . '/upload/user/avatar/big/');
        if(!file_exists(root . '/upload/user/avatar/medium/'))
            @mkdir(root . '/upload/user/avatar/medium/');
        if(!file_exists(root . '/upload/user/avatar/small/'))
            @mkdir(root . '/upload/user/avatar/small/');

        imagejpeg($image_big_truecolor, root . "/upload/user/avatar/big/$file_save_min_jpg");
        imagejpeg($image_medium_truecolor, root . "/upload/user/avatar/medium/$file_save_min_jpg");
        imagejpeg($image_small_truecolor, root . "/upload/user/avatar/small/$file_save_min_jpg");

        imagedestroy($image_big_truecolor);
        imagedestroy($image_medium_truecolor);
        imagedestroy($image_small_truecolor);
        return true;
    }

    /**
     * Upload image to folder /upload/. Return image file name or false if upload is failed.
     * @param string $dir
     * @param $file
     * @return bool|string
     */
    public function uploadImage($dir = '/images/', $file) {
        $full_dir = root . $this->directory . $dir;
        // make directory for upload if it dosnt exists
        if(!file_exists($full_dir)) {
            try {
                mkdir($full_dir);
            } catch(Exception $e) {
                logger::getInstance()->log(logger::LEVEL_ERR, "Failed to create folder: ".$full_dir.". Message: ".$e->getMessage());
                return false;
            }
        }
        // shit happends ...
        if($file['size'] < 1 || $file['tmp_name'] == null || !$this->validImageMime($file)) {
            return false;
        }
        $object_pharse = explode(".", $file['name']); // filename after array_pop
        $image_extension = array_pop($object_pharse); // file extension
        $image_new_name = $this->analiseUploadName(implode('', $object_pharse), $image_extension, $full_dir);
        move_uploaded_file($file['tmp_name'], $full_dir . $image_new_name . "." . $image_extension);
        return $image_new_name . "." .$image_extension;
    }

    private function analiseUploadName($name, $xt, $dir, $recursive = false)
    {
        $latin_data = preg_replace('/[^a-z0-9_]/i', '', $name);
        if (system::getInstance()->length($latin_data) < 3 || $recursive) {
            $result_file = system::getInstance()->randomInt(4) . "_" . system::getInstance()->randomString(rand(6, 10));
        } else {
            $result_file = system::getInstance()->randomInt(4) . "_" . $name;
        }
        $full_path = $dir . $result_file . "." . $xt;
        // recursive function if name is always taked
        if (file_exists($full_path)) {
            $result_file = $this->analiseUploadName($name, $xt, $dir, true);
        }
        return $result_file;
    }

    private function validImageMime($file) {
        $mime_type = array("image/gif", "image/jpeg", "image/jpg", "image/png", "image/bmp");
        $ext_image = array('jpg', 'gif', 'jpeg', 'png', 'bmp');
        $file_infofunction = getimagesize($file['tmp_name']);
        $image_name_split = explode('.', $file['name']);
        $image_extension = array_pop($image_name_split);
        if (in_array($file['type'], $mime_type) && in_array($file_infofunction['mime'], $mime_type)
            && $file['size'] > 0 && $file['size'] < property::getInstance()->get('upload_img_max_size') * 1024
            && in_array(strtolower($image_extension), $ext_image)) {
                return true;
        }
        return false;
    }
}