<?php 
/**
 * Класс для работы с изображениями
 * @author zenn
 *
 */
class file
{

	/**
	 * Крючек для работы ElFinder редактора
	 */
	public function elfinder()
	{
		global $constant,$user;
		if($user->get('access_to_admin') < 1)
		{
			return;
		}
		include_once $constant->root.'/resource/elfinder/php/elFinderConnector.class.php';
		include_once $constant->root.'/resource/elfinder/php/elFinder.class.php';
		include_once $constant->root.'/resource/elfinder/php/elFinderVolumeDriver.class.php';
		include_once $constant->root.'/resource/elfinder/php/elFinderVolumeLocalFileSystem.class.php';
		function access($attr, $path, $data, $volume) {
			return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
			? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
			:  null;                                    // else elFinder decide it itself
		}
		$opts = array(
				// 'debug' => true,
				'roots' => array(
						array(
								'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
								'path'          => $constant->root.'/upload/all/',         // path to files (REQUIRED)
								'URL'           => $constant->url.'/upload/all/', // URL to files (REQUIRED)
								'accessControl' => 'access',             // disable and hide dot starting files (OPTIONAL)
								'uploadAllow' => array('image'),
								'uploadDeny'   => array('all'),
								'uploadOrder'  => 'deny,allow',
						)
				)
		);
		$connector = new elFinderConnector(new elFinder($opts));
		$connector->run();
	}

    public function elfinderForAdmin()
    {
        global $constant,$user;
        if($user->get('access_to_admin') < 1)
        {
            return;
        }
        include_once $constant->root.'/resource/elfinder/php/elFinderConnector.class.php';
        include_once $constant->root.'/resource/elfinder/php/elFinder.class.php';
        include_once $constant->root.'/resource/elfinder/php/elFinderVolumeDriver.class.php';
        include_once $constant->root.'/resource/elfinder/php/elFinderVolumeLocalFileSystem.class.php';
        function access($attr, $path, $data, $volume) {
            return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
                ? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
                :  null;                                    // else elFinder decide it itself
        }
        $opts = array(
            // 'debug' => true,
            'roots' => array(
                array(
                    'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
                    'path'          => $constant->root.'/upload/',         // path to files (REQUIRED)
                    'URL'           => $constant->url.'/upload/', // URL to files (REQUIRED)
                    'accessControl' => 'access',             // disable and hide dot starting files (OPTIONAL)
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
		global $constant,$user;
		$userid = $user->get('id');
		if(!$this->validImage($file) || $userid < 1)
		{
			return false;
		}
		$dir_original = $constant->root."/upload/user/avatar/original/";
		$tmp_arr = explode(".", $file['name']);
		$image_extension = array_pop($tmp_arr);
		$file_save_original = "avatar_$userid.$image_extension";
		$file_save_min_jpg = "avatar_$userid.jpg";
		$file_original_fullpath = $dir_original.$file_save_original;
		if(!file_exists($dir_original))
		{
			mkdir($dir_original, 0777, true);
		}
		move_uploaded_file($file['tmp_name'], $file_original_fullpath);
		$file_infofunction = getimagesize($file_original_fullpath);
		$image_buffer = null;
		if($file_infofunction['mine'] == "image/jpg" || $file_infofunction['mime'] == "image/jpeg")
		{
			$image_buffer = imagecreatefromjpeg($file_original_fullpath);
		}
		elseif($file_infofunction['mime'] == "image/gif")
		{
			$image_buffer = imagecreatefromgif($file_original_fullpath);
		}
		elseif($file_infofunction['mime'] == "image/png")
		{
			$image_buffer = imagecreatefrompng($file_original_fullpath);
		}
		else
		{
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
		
		imagecopyresized($image_big_truecolor, $image_buffer, 0,0,0,0,$image_big_dx,$image_big_dy,$image_ox,$image_oy);
		imagecopyresized($image_medium_truecolor, $image_buffer, 0,0,0,0,$image_medium_dx,$image_medium_dy,$image_ox,$image_oy);
		imagecopyresized($image_small_truecolor, $image_buffer, 0,0,0,0,$image_small_dx,$image_small_dy,$image_ox,$image_oy);
		
		imagejpeg($image_big_truecolor, $constant->root."/upload/user/avatar/big/$file_save_min_jpg");
		imagejpeg($image_medium_truecolor, $constant->root."/upload/user/avatar/medium/$file_save_min_jpg");
		imagejpeg($image_small_truecolor, $constant->root."/upload/user/avatar/small/$file_save_min_jpg");

		imagedestroy($image_big_truecolor);
		imagedestroy($image_medium_truecolor);
		imagedestroy($image_small_truecolor);
		return true;
	}
	
	private function validImage($file)
	{
		global $constant;
		$mime_type = array("image/gif", "image/jpeg", "image/jpg", "image/png", "image/bmp");
		$file_infofunction = getimagesize($file['tmp_name']);
		$image_name_split = explode('.', $file['name']);
		$image_extension = array_pop($image_name_split);
		if(in_array($file['type'], $mime_type) && in_array($file_infofunction['mime'], $mime_type) && $file['size'] > 0 && $file['size'] < $constant->upload_img_max_size*1024 && $this->validImageExtension($image_extension))
		{
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
		global $constant,$system;
		if($this->validImage($file))
		{
			$object_pharse = explode(".", $file['name']);
			$image_extension = array_pop($object_pharse);
			$image_save_name = $this->analiseImageName(implode('', $object_pharse), $image_extension, $dir);
			move_uploaded_file($file['tmp_name'], $constant->root.$dir.$image_save_name.".".$image_extension);
		}
	}

	private function analiseImageName($name, $xt, $dir, $recursive = false)
	{
		global $system,$constant;
		$latin_data = preg_replace('/[^a-z0-9_]/i', '', $name);
		if($system->length($latin_data) < 3 || $recursive)
		{
			$result_file = $system->randomInt(4)."_".$system->randomString(rand(6,10));
		}
		else
		{
			$result_file = $system->randomInt(4)."_".$name;
		}
		$full_path = $constant->root.$dir.$result_file.".".$xt;
		// рекурсия - это не хорошо, однако перезапись существующего файла - тоже.
		if(file_exists($full_path))
		{
			$result_file = $this->analiseImageName($name, $xt, $dir, true);
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