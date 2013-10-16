<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

/**
 * Класс для работы с изображениями
 * @author zenn
 *
 */
class file
{

    public function ckeditorLoad()
    {
        global $engine;
        if($engine->user->get('access_to_admin') < 1 || $_FILES['upload'] == null)
            return;
        $result = $this->imageupload($_FILES['upload']);
        if(!$result) {
            return '<html><body><script type="text/javascript">window.parent.CKEDITOR.tools.callFunction("'.$_GET['CKEditorFuncNum'].'", "", "'.$engine->language->get('fileupload_api_error').'");</script></body></html>';
        } else {
            return '<html><body><script type="text/javascript">window.parent.CKEDITOR.tools.callFunction("'.$_GET['CKEditorFuncNum'].'", "'.$engine->constant->url . '/upload/images/'.$result.'");</script></body></html>';
        }
    }

    public function commentUserUpload()
    {
        global $engine;
        if ($engine->user->get('id') < 1 || $_FILES['img'] == null)
            return;
        $isIframe = ($_POST["iframe"]) ? true : false;
        $idarea = $_POST["idarea"];
        $result = $this->imageupload($_FILES['img'], '/upload/comment/');
        $fulllink = $engine->constant->url . "/upload/comment/" .$result;
        if($isIframe) {
            if($result != null)
                return '<html><body>OK<script>window.parent.$("#' . $idarea . '").insertImage("' . $fulllink . '","' . $fulllink . '").closeModal().updateUI();</script></body></html>';
            else
                return '<html><body>ERROR<script>window.parent.alert("Image upload error.");</script></body></html>';
        } else {
            header("Content-type: text/javascript");
            if($result != null) {
                $json_response = array(
                    'status' => '1',
                    'msg' => 'ok',
                    'image_link' => $fulllink,
                    'thumb_link' => $fulllink
                );
            } else {
                $json_response = array(
                    'status' => '0',
                    'msg' => 'error'
                );
            }
            return stripslashes(json_encode($json_response));
        }
    }

    public function elfinderForAdmin()
    {
        global $engine;
        if ($engine->user->get('access_to_admin') < 1) {
            return;
        }
        include_once $engine->constant->root . '/resource/elfinder/php/elFinderConnector.class.php';
        include_once $engine->constant->root . '/resource/elfinder/php/elFinder.class.php';
        include_once $engine->constant->root . '/resource/elfinder/php/elFinderVolumeDriver.class.php';
        include_once $engine->constant->root . '/resource/elfinder/php/elFinderVolumeLocalFileSystem.class.php';
        function access($attr, $path, $data, $volume)
        {
            return strpos(basename($path), '.') === 0 // if file/folder begins with '.' (dot)
                ? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
                : null; // else elFinder decide it itself
        }

        $opts = array(
            // 'debug' => true,
            'roots' => array(
                array(
                    'driver' => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
                    'path' => $engine->constant->root . '/upload/', // path to files (REQUIRED)
                    'URL' => $engine->constant->url . '/upload/', // URL to files (REQUIRED)
                    'accessControl' => 'access', // disable and hide dot starting files (OPTIONAL)
                )
            )
        );
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
    }


    /**
     * Загрузка аватара текущего пользователя. Обязательна авторизация.
     * @param unknown_type $file
     * @param unknown_type $userid
     */
    public function useravatarupload($file)
    {
        global $engine;
        $userid = $engine->user->get('id');
        if (!$this->validImage($file) || $userid < 1) {
            return false;
        }
        $dir_original = $engine->constant->root . "/upload/user/avatar/original/";
        $tmp_arr = explode(".", $file['name']);
        $image_extension = array_pop($tmp_arr);
        $file_save_original = "avatar_$userid.$image_extension";
        $file_save_min_jpg = "avatar_$userid.jpg";
        $file_original_fullpath = $dir_original . $file_save_original;
        if (!file_exists($dir_original)) {
            mkdir($dir_original, 0777, true);
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

        imagejpeg($image_big_truecolor, $engine->constant->root . "/upload/user/avatar/big/$file_save_min_jpg");
        imagejpeg($image_medium_truecolor, $engine->constant->root . "/upload/user/avatar/medium/$file_save_min_jpg");
        imagejpeg($image_small_truecolor, $engine->constant->root . "/upload/user/avatar/small/$file_save_min_jpg");

        imagedestroy($image_big_truecolor);
        imagedestroy($image_medium_truecolor);
        imagedestroy($image_small_truecolor);
        return true;
    }

    private function validImage($file)
    {
        global $engine;
        $mime_type = array("image/gif", "image/jpeg", "image/jpg", "image/png", "image/bmp");
        $file_infofunction = getimagesize($file['tmp_name']);
        $image_name_split = explode('.', $file['name']);
        $image_extension = array_pop($image_name_split);
        if (in_array($file['type'], $mime_type) && in_array($file_infofunction['mime'], $mime_type) && $file['size'] > 0 && $file['size'] < $engine->constant->upload_img_max_size * 1024 && $this->validImageExtension($image_extension)) {
            return true;
        }
        return false;
    }

    /**
     * Загрузка изображения в указанную директорию на сайте.
     * @param unknown_type $file
     * @param unknown_type $dir
     * @return multitype:number
     */
    public function imageupload($file, $dir = "/upload/images/")
    {
        global $engine;
        if ($this->validImage($file)) {
            if(!file_exists($engine->constant->root . $dir)) {
                mkdir($engine->constant->root . $dir);
            }
            $object_pharse = explode(".", $file['name']);
            $image_extension = array_pop($object_pharse);
            $image_save_name = $this->analiseUploadName(implode('', $object_pharse), $image_extension, $dir);
            move_uploaded_file($file['tmp_name'], $engine->constant->root . $dir . $image_save_name . "." . $image_extension);
            return $image_save_name . "." .$image_extension;
        }
        return false;
    }

    /**
     * Загрузка архива $file в указанную директорию на сайте $dir
     * @param $file
     * @param string $dir
     * @return bool|string
     */
    public function archiveupload($file, $dir = "/upload/files/")
    {
        global $engine;
        if(!file_exists($engine->constant->root . $dir))
            mkdir($engine->constant->root . $dir);
        $object = explode(".", $file['name']);
        $extension = array_pop($object);
        if($extension === "zip" || $extension === "rar" || $extension === "gz") {
            $archive_name = $this->analiseUploadName(implode('', $object), $extension, $dir);
            move_uploaded_file($file['tmp_name'], $engine->constant->root . $dir . $archive_name . "." . $extension);
            return $archive_name . "." . $extension;
        }
        return false;
    }

    private function analiseUploadName($name, $xt, $dir, $recursive = false)
    {
        global $engine;
        $latin_data = preg_replace('/[^a-z0-9_]/i', '', $name);
        if ($engine->system->length($latin_data) < 3 || $recursive) {
            $result_file = $engine->system->randomInt(4) . "_" . $engine->system->randomString(rand(6, 10));
        } else {
            $result_file = $engine->system->randomInt(4) . "_" . $name;
        }
        $full_path = $engine->constant->root . $dir . $result_file . "." . $xt;
        // рекурсия - это не хорошо, однако перезапись существующего файла - тоже.
        if (file_exists($full_path)) {
            $result_file = $this->analiseUploadName($name, $xt, $dir, true);
        }
        return $result_file;
    }

    private function validImageExtension($image_extension)
    {
        $valid_extensions = array('jpg', 'jpeg', 'png', 'bmp', 'gif');
        return in_array($image_extension, $valid_extensions);
    }
}

?>