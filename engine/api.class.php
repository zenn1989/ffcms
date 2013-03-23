<?php
class api
{
	public $separator = "/";
	public function userinterface()
	{
		global $system,$user;
		switch($system->get('action'))
		{
			case "wysijs":
				header("Content-type: text/javascript");
				if($user->get('can_upload_image') != 1) return; 
				return $this->tpl('custom_image_and_upload_wysihtml5', 'js/', true, '.js');
				break;
			case "lastimglist":
				return $this->showLastImagesList();
				break;
			case "uploadimg":
				return $this->uploadImage();
				break;
			default:
				break;
		}
	}
	
	public function standalone()
	{
		return "no";
	}
	
	private function uploadImage($dir = "/upload/images/")
	{
		global $constant;
		$mime_type = array("image/gif", "image/jpeg", "image/png", "image/bmp");
		$image_file = $_FILES['file'];
		if(in_array($image_file['type'], $mime_type) && $image_file['size'] > 0 && $image_file['size'] < $constant->upload_img_max_size*1024)
		{
			$object_pharse = explode(".", $image_file['name']);
			$image_extension = array_pop($object_pharse);
			$image_save_name = $this->analiseImageName(implode('', $object_pharse), $image_extension);
			// AJAX бывает совершает по непонятным причинам 2-3 попытки сразу. Делаем заглушку.
			$isImageAlwaysLoaded = $this->robotImageContains($image_save_name, $image_file['size'], $image_extension);
			if($isImageAlwaysLoaded)
			{
				// повторяем действие выше. Пахнет говнокодом, но рекурсию мои мозги сюда не додумали.
				$pharse_new = explide(".", $isImageAlwaysLoaded);
				$new_extension = array_pop($pharse_new);
				$image_save_name = implode("", $pharse_new);
				$image_extension = $new_extension;
			}
			else
			{
				move_uploaded_file($image_file['tmp_name'], $constant->root."/upload/images/".$image_save_name.".".$image_extension);
			}
			return json_encode(array('status' => 1, 'file' => $constant->url."/upload/images/".$image_save_name.".".$image_extension));
		}
		return json_encode(array('status' => 0));
		/**
		$data = array('status' => 1,
				'file'=>'http://placehold.it/50/B9E4FB/260b50',
				'caption'=>'',
				'foreground'=>'',
				'background'=>'');
		echo json_encode($data);*/
	}
	
	private function robotImageContains($name, $size, $ext)
	{
		global $constant,$system;
		$img_array = array();
		$opendir = opendir($constant->root."/upload/images/");
		while($entery = readdir($opendir))
		{
			// если файл похож на картинку и содержит в своем имени нужную нам инфу а так же по размеру совпадает - скорей всего и есть искомый экземпляр
			if($this->validImage($entery) && $system->contains($name, $entery) && filesize($constant->root."/upload/images/$entery") == $size)
			{
				return $entery;
			}
		}
		return false;
	}
	
	private function analiseImageName($name, $xt, $recursive = false)
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
		$full_path = $constant->root."/upload/images/".$result_file.".".$xt;
		// рекурсия - это не хорошо, однако перезапись существующего файла - тоже.
		if(file_exists($full_path))
		{
			$result_file = $this->analiseImageName($name, $xt, true);
		}
		return $result_file;
	}
	
	private function showLastImagesList()
	{
		global $constant;
		$img_array = array();
		$last_images = array();
		header('Content-type: application/json');
		$opendir = opendir($constant->root."/upload/images/");
		while($entery = readdir($opendir))
		{
			if($this->validImage($entery))
			{
				$img_array[filectime($constant->root."/upload/images/$entery")] = $entery;
			}
		}
		closedir($opendir);
		ksort($img_array);
		if(sizeof($img_array) > 5)
		{
			$last_images = array_slice($img_array, -5, -1, true);
		}
		else
		{
			$last_images = $img_array;
		}
		$json_data = "[\n";
		$foreach_ind = 1;
		foreach($last_images as $image_name)
		{
			$json_data .= '{"file":"'.$constant->url.'/upload/images/'.$image_name.'","caption":"'.$image_name.'","foreground":"","background":""}';
			if($foreach_ind < sizeof($last_images))
			{
				$json_data .= ",\n";
			}
			$foreach_ind++;
		}
		$json_data .= "]";
		return $json_data;
	}
	
	private function validImage($image)
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
	
	private function tpl($tplname, $customdirectory, $isadmin, $property = ".tpl")
	{
		global $constant;
		if($isadmin)
		{
			$file = $constant->root.$this->separator.$constant->tpl_dir.$this->separator.$constant->admin_tpl.$this->separator.$customdirectory.$tplname.$property;
		}
		else
		{
			$file = $constant->root.$this->separator.$constant->tpl_dir.$this->separator.$constant->tpl_name.$this->separator.$customdirectory.$tplname.$property;
		}
		if(file_exists($file))
		{
			return $this->setDefaults(file_get_contents($file), true);
		}
		return;
	}
	
	private function setDefaults($theme, $isadmin)
	{
		global $constant;
		if($isadmin)
		{
			$template_path = $constant->tpl_dir.$this->separator.$constant->admin_tpl;
		}
		else
		{
			$template_path = $constant->tpl_dir.$this->separator.$constant->tpl_name;
		}
		return $this->assign(array('url', 'tpl_dir'), array($constant->url, $template_path), $theme);
	}
	
	private function assign($tag, $data, $where)
	{
		if(is_array($tag))
		{
			$copy = array();
			foreach($tag as $entery)
			{
				$copy[] = '{$'.$entery.'}';
			}
			return str_replace($copy, $data, $where);
		}
		return str_replace('{$'.$tag.'}', $data, $where);
	}
}
?>