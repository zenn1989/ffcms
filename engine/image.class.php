<?php 
/**
 * Класс для работы с изображениями
 * @author zenn
 *
 */
class image
{
	/**
	 * Загрузка изображения в указанную директорию на сайте.
	 * @param unknown_type $file
	 * @param unknown_type $dir
	 * @param unknown_type $seo_title
	 * @param unknown_type $check_exist
	 * @param unknown_type $response_array
	 * @return multitype:number
	 */
	public function upload($file, $dir = "/upload/images/", $seo_title = '', $check_exist = false, $response_array = false)
	{
		global $constant,$database,$system;
		$mime_type = array("image/gif", "image/jpeg", "image/png", "image/bmp");
		if(in_array($file['type'], $mime_type) && $file['size'] > 0 && $file['size'] < $constant->upload_img_max_size*1024)
		{
			$object_pharse = explode(".", $file['name']);
			$image_extension = array_pop($object_pharse);
			$image_save_name = $this->analiseImageName(implode('', $object_pharse), $image_extension, $dir);
			// возможно, изображение было загружено недавно?
			if(!$check_exist || $this->imageNeverExist($image_save_name, $file['size']))
			{
				$stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_images (name, extension, title, size, pathway) VALUES (?, ?, ?, ?, ?)");
				$stmt->bindParam(1, $image_save_name, PDO::PARAM_STR);
				$stmt->bindParam(2, $image_extension, PDO::PARAM_STR);
				$stmt->bindParam(3, $seo_title, PDO::PARAM_STR);
				$stmt->bindParam(4, $file['size'], PDO::PARAM_INT);
				$stmt->bindParam(5, $dir, PDO::PARAM_STR);
				$stmt->execute();
				move_uploaded_file($file['tmp_name'], $constant->root.$dir.$image_save_name.".".$image_extension);
				if($response_array)
				{
					return array('status' => 1, 'file' => $constant->url.$dir.$image_save_name.".".$image_extension, 'caption' => $seo_title);
				}
			}
		}
		if($response_array)
			return array('status' => 0);
	}
	
	private function imageNeverExist($name, $size)
	{
		global $constant,$database;
		$clear_name = explode("_", $name);
		unset($clear_name[0]);
		$new_name = implode("", $clear_name);
		$name_rule = '%'.$new_name.'%';
		$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_images WHERE name like ? AND size = ? ORDER BY id DESC LIMIT 10");
		$stmt->bindParam(1, $name_rule, PDO::PARAM_STR);
		$stmt->bindParam(2, $size, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch();
		if($result[0] == 0)
		{
			return true;
		}
		return false;
	}
	
	public function showLastImagesList($dir = "/upload/images/", $limit = 10)
	{
		global $constant,$database;
		$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_images WHERE pathway = ? ORDER by id DESC LIMIT ?");
		$stmt->bindParam(1, $dir, PDO::PARAM_STR);
		$stmt->bindParam(2, $limit, PDO::PARAM_INT);
		$stmt->execute();
		$result_array = array();
		while($result = $stmt->fetch())
		{
			$result_array[] = array('file' => $constant->url.$dir.$result['name'].".".$result['extension'], 'caption' => $result['title']);
		}
		return $result_array;
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
	
	private function validImageExtension($image)
	{
		$valid_extensions = array('.jpg', '.jpeg', '.png', '.bmp', '.gif');
		foreach($valid_extensions as $ret)
		{
			if(substr($image, -strlen($ret)) == $ret)
			{
				return true;
			}
		}
		return false;
	}
}

?>