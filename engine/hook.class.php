<?php
/**
* Получение хуков
*/
class hook
{
	private $hook_list = array();

	function __construct()
	{
		global $database,$constant;
		$query = "SELECT * FROM {$constant->db['prefix']}_hooks WHERE enabled = 1";
		$stmt = $database->con()->query($query);
		$stmt->execute();
		while($row = $stmt->fetch())
		{
			$this->hook_list[$row['type']] = $row['dir'];
		}
	}
	public function get($type)
	{
		global $constant;
		if($this->hook_list[$type] == null)
		{
			return null;
		}
		$file = $constant->root.'/extensions/hooks/'.$this->hook_list[$type].'/front.php';
		if(!file_exists($file))
		{
			return null;
		}
		require_once($file);
		$class = "hook_{$this->hook_list[$type]}";
		$init = new $class;
		return $init->load();
	}
}
?>