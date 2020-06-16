<?php

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
 
/**
 * News block configuration user interface class
 *
 * @author Andreas Gachnang <andreas.gachnang@skyguide.ch>
 * @version $Id$
 *
 */
class ilLfTestExportConfigGUI extends ilPluginConfigGUI
{
	/**
	* Handles all commmands, default is "configure"
	*/
	function performCommand($cmd)
	{
		
		switch ($cmd)
		{
			case "configure":
			case "save":
			case 'doExport':
			case 'listTests':
			case 'saveExport':
			case 'generateApiKeyConfirmation':
			case 'generateApiKey':
				$this->$cmd();
				break;

		}
	}

	protected function doExport()
	{
		$exporter = new lfTestResultExporter();
		$exporter->export();
		
		ilUtil::sendSuccess($this->getPluginObject()->txt('export_completed'),true);
		$GLOBALS['ilCtrl']->redirect($this,'configure');
	}

	/**
	 * Configure screen
	 */
	function configure(ilPropertyFormGUI $form = NULL)
	{
		global $tpl, $ilToolbar;

		$this->showSubTabs('configure');

		$fn = new lfTestExportFileReader();

		$ilToolbar->setFormAction($GLOBALS['ilCtrl']->getFormAction($this));
		$ilToolbar->addFormButton($this->getPluginObject()->txt('start_export'),'doExport');
		$ilToolbar->addFormButton($this->getPluginObject()->txt('generate_api_key'), 'generateApiKeyConfirmation');
		
		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initConfigurationForm();
		}
		$tpl->setContent($form->getHTML());
	}

	function generateApiKeyConfirmation()
	{
		global $DIC, $tpl;

		$ctrl = $DIC->ctrl();
		$lng = $DIC->language();
		$main_template = $DIC->ui()->mainTemplate();

		$confirm = new \ilConfirmationGUI();
		$confirm->setHeaderText($this->getPluginObject()->txt('generate_api_key_confirm'));
		$confirm->setFormAction($ctrl->getFormAction($this));
		$confirm->setConfirm($this->getPluginObject()->txt('generate_api_key_confirm_btn'), 'generateApiKey');
		$confirm->setCancel($lng->txt('cancel'), 'configure');

		$tpl->setContent($confirm->getHTML());
	}

	function generateApiKey()
	{
		global $DIC;

		$lng = $DIC->language();
		$ctrl = $DIC->ctrl();

		$setting = \lfTestExportSettings::getInstance();
		$setting->generateApiKey();

		\ilUtil::sendSuccess($lng->txt('settings_saved'),true);
		$ctrl->redirect($this, 'configure');
	}
	
	//
	// From here on, this is just an example implementation using
	// a standard form (without saving anything)
	//
	
	/**
	 * Init configuration form.
	 *
	 * @return object form object
	 */
	public function initConfigurationForm()
	{
		global $lng, $ilCtrl;
		
		$pl = $this->getPluginObject();
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		
		$interval = new ilNumberInputGUI($pl->txt('setting_interval'),'interval');
		$interval->setSize(3);
		$interval->setValue(lfTestExportSettings::getInstance()->getInterval());
		$interval->setSuffix($pl->txt('setting_interval_days'));
		$interval->setMinValue(1);
		$interval->setRequired(true);
		$form->addItem($interval);
		
		$dir = new ilTextInputGUI($pl->txt('setting_directory'),'directory');
		$dir->setValue(lfTestExportSettings::getInstance()->getDirectory());
		$dir->setRequired(true);
		$dir->setSize(120);
		$dir->setInfo($pl->txt('setting_directory_info'));
		$form->addItem($dir);

		$api_key = new \ilNonEditableValueGUI($pl->txt('settings_api_key'));
		if (lfTestExportSettings::getInstance()->hasApiKey()) {
			$api_key->setValue(lfTestExportSettings::getInstance()->getApiKey());
		}
		else {
			$api_key->setValue($pl->txt('settings_no_api_key_generated'));
		}
		$form->addItem($api_key);

		$last = new ilNonEditableValueGUI($pl->txt('setting_last_export'));
		if(lfTestExportSettings::getInstance()->isExported())
		{
			$last->setValue(ilDatePresentation::formatDate(lfTestExportSettings::getInstance()->getLastUpdate()));
		}
		else
		{
			$last->setValue($pl->txt('setting_no_export'));
		}
		$form->addItem($last);
		
		
		$form->addCommandButton('save', $lng->txt('save'));
		
		$form->setFormAction($ilCtrl->getFormAction($this));
		
		return $form;
	}
	
	/**
	 * Save form input (currently does not save anything to db)
	 *
	 */
	public function save()
	{
		global $tpl, $lng, $ilCtrl;
	
		$pl = $this->getPluginObject();
		
		$form = $this->initConfigurationForm();
		
		if($form->checkInput())
		{
			lfTestExportSettings::getInstance()->setInterval($form->getInput('interval'));
			lfTestExportSettings::getInstance()->setDirectory($form->getInput('directory'));
			lfTestExportSettings::getInstance()->save();
			
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);
			$ilCtrl->redirect($this, "configure");
		}
		else
		{
			ilUtil::sendFailure($lng->txt('err_check_input'));
			$form->setValuesByPost();
			$this->configure($form);
		}
	}
	
	protected function listTests()
	{
		global $tpl;

		$this->showSubTabs('tests');
		
		$table = new lfObjectTableGUI($this, 'listTests', 'lfexporttst');
		$table->setFormAction($GLOBALS['ilCtrl']->getFormAction($this));
		$table->init();				
		$table->setObjects(
				ilUtil::_getObjectsByOperations('tst', 'write',$GLOBALS['ilUser']->getId(),-1)
		);
		$table->parse();
		$tpl->setContent($table->getHTML());
	}
	
	
	protected function saveExport()
	{
		$settings = lfTestExportSettings::getInstance();
		
		foreach((array) $_POST['tst_visible'] as $tst_id => $tmp)
		{
			if(array_key_exists($tst_id,(array) $_POST['tst_id']))
			{
				if(!$settings->isItemExported($tst_id))
				{
					$settings->saveExportItem($tst_id, TRUE);
				}
			}
			else
			{
				if($settings->isItemExported($tst_id))
				{
					$settings->saveExportItem($tst_id, FALSE);
				}
			}
		}
		ilUtil::sendSuccess($GLOBALS['lng']->txt('settings_saved'),true);
		$GLOBALS['ilCtrl']->redirect($this,'listTests');
	}


	protected function showSubTabs($a_active = '')
	{
		global $ilTabs;
		
		$ilTabs->addSubTab(
				'configure',
				$this->getPluginObject()->txt('subtab_configure'),
				$GLOBALS['ilCtrl']->getLinkTarget($this,'configure')
		);
		$ilTabs->addSubTab(
				'tests',
				$this->getPluginObject()->txt('subtab_test_list'),
				$GLOBALS['ilCtrl']->getLinkTarget($this,'listTests')
				
		);
		$ilTabs->activateSubTab($a_active);
	}
}
?>
