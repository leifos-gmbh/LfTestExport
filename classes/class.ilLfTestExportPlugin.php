<?php

include_once("./Services/Cron/classes/class.ilCronHookPlugin.php");
 
/**
 * News block interface plugin
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 */
class ilLfTestExportPlugin extends ilCronHookPlugin
{
	private static $instance = null;

	/**
	 * @var string
	 */
	const CTYPE = 'Services';

	/**
	 * @var string
	 */
	const CNAME = 'Cron';

	/**
	 * @var string
	 */
	const SLOT_ID = 'crnhk';

	/**
	 * @var string
	 */
	const PNAME = 'LfTestExport';


	private $settings = NULL;

	private $logger = null;

	/**get plugin instance
	 *
	 * @return \ilLfTestExportPlugin
	 */
	public static function getInstance()
	{
		if(!self::$instance instanceof \ilLfTestExportPlugin) {
			self::$instance = \ilPluginAdmin::getPluginObject(
				self::CTYPE,
				self::CNAME,
				self::SLOT_ID,
				self::PNAME
			);
		}
		return self::$instance;
	}

	/**
	 * @return |null | \ilLogger
	 */
	public function getLogger()
	{
		return $this->logger;
	}


	function getPluginName()
	{
		return "LfTestExport";
	}

	/**
	 * Init vitero
	 */
	protected function init()
	{
		global $DIC;

		$this->logger = $DIC->logger()->lftestexeport();

		$this->initAutoLoad();
		$this->settings = lfTestExportSettings::getInstance();
	}

	/**
	 * Init auto loader
	 * @return void
	 */
	protected function initAutoLoad()
	{
		spl_autoload_register(
			array($this,'autoLoad')
		);
	}

	/**
	 * Auto load implementation
	 *
	 * @param string class name
	 */
	private final function autoLoad($a_classname)
	{
		$class_file = $this->getClassesDirectory().'/class.'.$a_classname.'.php';
		if(@include_once($class_file))
		{
			return;
		}
	}

	function getCronJobInstances()
	{
		$job = new lfTestExportCronJob();
		$job->setPlugin($this);
		return array($job);
	}

	function getCronJobInstance($a_job_id)
	{
		$job = new lfTestExportCronJob();
		$job->setPlugin($this);
		ilLoggerFactory::getLogger('lftest')->debug('new job: ' . $job->getId());
		return 	$job;
	}

	/**
	 * @return lfTestResultSettings
	 */
	function getSettings()
	{
		return $this->settings;
	}

}

?>
