<?php


// Отображение статичных блоков.
class mod_static_includes
{
	public function load()
	{
		global $database,$page,$constant;
		$stmt = $database->con()->query("SELECT * FROM {$constant->db['prefix']}_mod_static_includes");
		$stmt->execute();
		while($result = $stmt->fetch())
		{
			$page->setContentPosition($result['tag'], $result['template'], $result['index']);
		}
	}
}

?>