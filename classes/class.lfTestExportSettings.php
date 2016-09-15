<?php

class lfTestExportSettings
{
	private static $instance = NULL;

	private $directory = '';
	private $interval = 1;
	private $last_update = NULL;
	private $is_exported = false;
	
	private $storage = NULL;
	
	/**
	 * Singleton construtor
	 */
	protected function __construct()
	{
		$this->storage = new ilSetting('lftestexport');
		$this->read();
	}
	
	/**
	 * @return lfTestExportSettings
	 */
	public static function getInstance()
	{
		if(self::$instance instanceof lfTestExportSettings)
		{
			return self::$instance;
		}
		return self::$instance = new self();
	}
	
	public function setExported($a_exp)
	{
		$this->is_exported = $a_exp;
	}
	
	public function isExported()
	{
		return $this->is_exported;
	}
	/**
	 * Get storage object
	 * @return ilSetting
	 */
	public function getStorage()
	{
		return $this->storage;
	}
	
	public function setInterval($a_int)
	{
		$this->interval = $a_int;
	}
	
	public function getInterval()
	{
		return $this->interval;
	}
	
	public function setDirectory($a_dir)
	{
		$this->directory = $a_dir;
	}
	
	public function getDirectory()
	{
		return $this->directory;
	}
	
	public function setLastUpdate(ilDateTime $dt)
	{
		$this->last_update = $dt;
	}
	
	/**
	 * 
	 * @return ilDateTime
	 */
	public function getLastUpdate()
	{
		if(!$this->last_update instanceof ilDateTime)
		{
			$this->last_update = new ilDateTime(time(),IL_CAL_UNIX);
			$this->last_update->increment(IL_CAL_YEAR,-1);
		}
		return $this->last_update;
	}
	
	public function save()
	{
		$this->getStorage()->set('interval', $this->getInterval());
		$this->getStorage()->set('directory', $this->getDirectory());
		$this->getStorage()->set('last_update',$this->getLastUpdate()->get(IL_CAL_DATETIME,'','UTC'));
		$this->getStorage()->set('exported',$this->isExported());
		return true;
	}
	
	protected function read()
	{
		$this->setInterval($this->getStorage()->get('interval',$this->getInterval()));
		$this->setDirectory($this->getStorage()->get('directory',$this->getDirectory()));
		$last_update = $this->getStorage()->get('last_update', 0);
		if($last_update)
		{
			$this->setLastUpdate(new ilDateTime($last_update,IL_CAL_DATETIME,'UTC'));
		}
		$this->setExported($this->getStorage()->get('exported',$this->isExported()));
		return true;
	}
	
	public function saveExportItem($a_ref_id,$a_enabled)
	{
		$items_ser = $this->getStorage()->get('export_items', serialize(array()));
		$items = unserialize($items_ser);
		
		if($a_enabled)
		{
			$items[] = $a_ref_id;
		}
		else
		{
			$items = array_diff($items, array($a_ref_id));
		}
		
		$this->getStorage()->set('export_items', serialize($items));
		return true;
	}
	
	public function isItemExported($a_ref_id)
	{
		$items_ser = $this->getStorage()->get('export_items', serialize(array()));
		$items = unserialize($items_ser);
		
		return in_array($a_ref_id,(array) $items);
	}
	
	public function getItems()
	{
		$items_ser = $this->getStorage()->get('export_items', serialize(array()));
		$items = unserialize($items_ser);
		
		return (array) $items;
	}
}
?>
