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
	protected $plugin; // [ilCronHookPlugin]
	
	public function getId()
	{
		return "lftestexeport";
	}
	
	public function getTitle()
	{		
		return $this->getPlugin()->txt("title");
	}
	
	public function getDescription()
	{
		return $this->getPlugin()->txt("cron_job_info");
	}
	
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_IN_HOURS;
	}
	
	public function getDefaultScheduleValue()
	{
		return $this->getPlugin()->getSettings()->getInterval();
	}
	
	public function hasAutoActivation()
	{
		return false;
	}
	
	public function hasFlexibleSchedule()
	{
		return false;
	}
	
	public function hasCustomSettings() 
	{
		return false;
	}
	
	public function run()
	{				
		global $ilLog;

		$ilLog->write(__METHOD__.': Starting export ...');
		$exporter = new lfTestResultExporter();
		$exporter->export();
		$ilLog->write(__METHOD__.': Export finished...');

		$status = ilCronJobResult::STATUS_OK;
		$result = new ilCronJobResult();
		$result->setStatus($status);

		return $result;
	}
	
	public function setPlugin(ilCronHookPlugin $a_plugin)
	{
		$this->plugin = $a_plugin;
	}	
	
	public function getPlugin()
	{
		return $this->plugin;
	}
}

?>