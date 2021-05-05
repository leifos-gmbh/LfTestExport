<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Matterhorn Fetch Series Cron Job
 * 
 * @author Per Pascal Grube <pascal.grube@tik.uni-stuttgart.de>
 *
 */
class lfTestExportCronJob extends ilCronJob
{
    /**
     * @var ilCronHookPlugin
     */
    protected $plugin;

    /**
     * @return string
     */
    public function getId() : string
	{
		return "lftestexeport";
	}

    /**
     * @return string
     */
    public function getTitle() : string
	{		
		return $this->getPlugin()->txt("title");
	}

    /**
     * @return string
     */
    public function getDescription() : string
	{
		return $this->getPlugin()->txt("cron_job_info");
	}

    /**
     * @return int
     */
    public function getDefaultScheduleType() : int
	{
		return self::SCHEDULE_TYPE_IN_MINUTES;
	}

    /**
     * @return int
     */
    public function getDefaultScheduleValue() : int
	{
		return $this->getPlugin()->getSettings()->getInterval();
	}

    /**
     * @return bool
     */
    public function hasAutoActivation() : bool
	{
		return false;
	}

    /**
     * @return bool
     */
    public function hasFlexibleSchedule() : bool
	{
		return false;
	}

    /**
     * @return bool
     */
    public function hasCustomSettings() : bool
	{
		return false;
	}

    /**
     * @return ilCronJobResult
     */
    public function run() : ilCronJobResult
	{				
		ilLoggerFactory::getLogger('lftest')->info('Starting test export...');
		$exporter = new lfTestResultExporter();
		$exporter->export();
		ilLoggerFactory::getLogger('lftest')->info('Test export finished.');

		$status = ilCronJobResult::STATUS_OK;
		$result = new ilCronJobResult();
		$result->setStatus($status);

		return $result;
	}

    /**
     * @param ilCronHookPlugin $a_plugin
     */
    public function setPlugin(ilCronHookPlugin $a_plugin)
	{
		$this->plugin = $a_plugin;
	}

    /**
     * @return ilCronHookPlugin
     */
    public function getPlugin() : ilCronHookPlugin
	{
		return $this->plugin;
	}
}

?>