<?php
class api
{
	public $separator = "/";
	public function userinterface()
	{
		global $system,$user,$image;
		switch($system->get('action'))
		{
			case "wysijs":
				header("Content-type: text/javascript");
				if($user->get('can_upload_image') != 1) return; 
				return $this->tpl('custom_image_and_upload_wysihtml5', 'js/', true, '.js');
				break;
			case "lastimglist":
				header('Content-type: application/json');
				return json_encode($image->showLastImagesList('/upload/images/', 10));
				break;
			case "uploadimg":
				header('Content-type: application/json');
				return json_encode($image->upload($_FILES['file'], '/upload/images/', $system->get('seo_title'), true, true));
				break;
			default:
				break;
		}
	}
	
	public function standalone()
	{
		return "no";
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