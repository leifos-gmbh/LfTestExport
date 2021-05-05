<?php

class lfTestExportSettings
{
    /**
     * @var lfTestExportSettings|null
     */
    private static $instance = null;

    /**
     * @var string
     */
    private $directory = '';

    /**
     * @var int
     */
    private $interval = 1;

    /**
     * @var null|ilDateTime
     */
    private $last_update = null;

    /**
     * @var bool
     */
    private $is_exported = false;

    /**
     * @var string
     */
    private $api_key = '';

    /**
     * @var ilSetting|null
     */
    private $storage = null;
    
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
    public static function getInstance() : lfTestExportSettings
    {
        if (self::$instance instanceof lfTestExportSettings) {
            return self::$instance;
        }
        return self::$instance = new self();
    }

    /**
     * @param bool $a_exp
     */
    public function setExported(bool $a_exp)
    {
        $this->is_exported = $a_exp;
    }

    /**
     * @return bool
     */
    public function isExported() : bool
    {
        return $this->is_exported;
    }

    /**
     * Get storage object
     * @return ilSetting
     */
    public function getStorage() : ilSetting
    {
        return $this->storage;
    }

    /**
     * @param int $a_int
     */
    public function setInterval(int $a_int)
    {
        $this->interval = $a_int;
    }

    /**
     * @return int
     */
    public function getInterval() : int
    {
        return $this->interval;
    }

    /**
     * @param string $a_dir
     */
    public function setDirectory(string $a_dir)
    {
        $this->directory = $a_dir;
    }

    /**
     * @return string
     */
    public function getDirectory() : string
    {
        return $this->directory;
    }

    /**
     * @param ilDateTime $dt
     */
    public function setLastUpdate(ilDateTime $dt)
    {
        $this->last_update = $dt;
    }

    /**
     * @return string
     */
    public function getApiKey() : string
    {
        return $this->api_key;
    }

    /**
     * @return int
     */
    public function hasApiKey() : int
    {
        return strlen($this->api_key);
    }

    public function generateApiKey()
    {
        $this->api_key = uniqid(CLIENT_ID . '::', true);
        $this->getStorage()->set('api_key', $this->api_key);
    }
    
    /**
     *
     * @return ilDateTime
     */
    public function getLastUpdate() : ilDateTime
    {
        if (!$this->last_update instanceof ilDateTime) {
            $this->last_update = new ilDateTime(time(), IL_CAL_UNIX);
            $this->last_update->increment(IL_CAL_YEAR, -1);
        }
        return $this->last_update;
    }

    /**
     * @return bool
     */
    public function save() : bool
    {
        $this->getStorage()->set('interval', $this->getInterval());
        $this->getStorage()->set('directory', $this->getDirectory());
        $this->getStorage()->set('last_update', $this->getLastUpdate()->get(IL_CAL_DATETIME, '', 'UTC'));
        $this->getStorage()->set('exported', $this->isExported());

        return true;
    }

    /**
     * @return bool
     * @throws ilDateTimeException
     */
    protected function read() : bool
    {
        $this->setInterval($this->getStorage()->get('interval', $this->getInterval()));
        $this->setDirectory($this->getStorage()->get('directory', $this->getDirectory()));
        $last_update = $this->getStorage()->get('last_update', 0);
        if ($last_update) {
            $this->setLastUpdate(new ilDateTime($last_update, IL_CAL_DATETIME, 'UTC'));
        }
        $this->setExported($this->getStorage()->get('exported', $this->isExported()));
        $this->api_key = $this->getStorage()->get('api_key', '');
        return true;
    }

    /**
     * @param int  $a_ref_id
     * @param bool $a_enabled
     * @return bool
     */
    public function saveExportItem(int $a_ref_id, bool $a_enabled) : bool
    {
        $items_ser = $this->getStorage()->get('export_items', serialize(array()));
        $items = unserialize($items_ser);
        
        if ($a_enabled) {
            $items[] = $a_ref_id;
        } else {
            $items = array_diff($items, array($a_ref_id));
        }
        
        $this->getStorage()->set('export_items', serialize($items));
        return true;
    }

    /**
     * @param int $a_ref_id
     * @return bool
     */
    public function isItemExported(int $a_ref_id) : bool
    {
        $items_ser = $this->getStorage()->get('export_items', serialize(array()));
        $items = unserialize($items_ser);
        
        return in_array($a_ref_id, (array) $items);
    }

    /**
     * @return array
     */
    public function getItems() : array
    {
        $items_ser = $this->getStorage()->get('export_items', serialize(array()));
        $items = unserialize($items_ser);

        return (array) $items;
    }
}
