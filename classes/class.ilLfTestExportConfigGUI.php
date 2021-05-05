<?php

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
     * @var ilLanguage
     */
    private $lng;

    /**
     * @var ilCtrl
     */
    private $ctrl;

    /**
     * @var ilTemplate
     */
    private $tpl;

    /**
    * Handles all commmands, default is "configure"
    */
    public function performCommand($cmd)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        
        switch ($cmd) {
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
        
        ilUtil::sendSuccess($this->getPluginObject()->txt('export_completed'), true);
        $this->ctrl->redirect($this, 'configure');
    }

    /**
     * Configure screen
     *
     * @param ilPropertyFormGUI|null $form
     */
    public function configure(ilPropertyFormGUI $form = null)
    {
        global $DIC;
        $ilToolbar = $DIC->toolbar();

        $this->showSubTabs('configure');

        $ilToolbar->setFormAction($this->ctrl->getFormAction($this));

        $button = ilLinkButton::getInstance();
        $button->setCaption($this->getPluginObject()->txt('start_export'), false);
        $button->setUrl($this->ctrl->getLinkTarget($this, "doExport"));
        $ilToolbar->addButtonInstance($button);

        $button = ilLinkButton::getInstance();
        $button->setCaption($this->getPluginObject()->txt('generate_api_key'), false);
        $button->setUrl($this->ctrl->getLinkTarget($this, "generateApiKeyConfirmation"));
        $ilToolbar->addButtonInstance($button);
        
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initConfigurationForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function generateApiKeyConfirmation()
    {
        $confirm = new ilConfirmationGUI();
        $confirm->setHeaderText($this->getPluginObject()->txt('generate_api_key_confirm'));
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setConfirm($this->getPluginObject()->txt('generate_api_key_confirm_btn'), 'generateApiKey');
        $confirm->setCancel($this->lng->txt('cancel'), 'configure');

        $this->tpl->setContent($confirm->getHTML());
    }

    public function generateApiKey()
    {
        $setting = lfTestExportSettings::getInstance();
        $setting->generateApiKey();

        \ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'configure');
    }
    
    //
    // From here on, this is just an example implementation using
    // a standard form (without saving anything)
    //
    
    /**
     * Init configuration form.
     *
     * @return ilPropertyFormGUI
     */
    public function initConfigurationForm() : ilPropertyFormGUI
    {
        $pl = $this->getPluginObject();

        $form = new ilPropertyFormGUI();
        
        $interval = new ilNumberInputGUI($pl->txt('setting_interval'), 'interval');
        $interval->setSize(3);
        $interval->setValue(lfTestExportSettings::getInstance()->getInterval());
        $interval->setSuffix($pl->txt('setting_interval_days'));
        $interval->setMinValue(1);
        $interval->setRequired(true);
        $form->addItem($interval);
        
        $dir = new ilTextInputGUI($pl->txt('setting_directory'), 'directory');
        $dir->setValue(lfTestExportSettings::getInstance()->getDirectory());
        $dir->setRequired(true);
        $dir->setSize(120);
        $dir->setInfo($pl->txt('setting_directory_info'));
        $form->addItem($dir);

        $api_key = new \ilNonEditableValueGUI($pl->txt('settings_api_key'));
        if (lfTestExportSettings::getInstance()->hasApiKey()) {
            $api_key->setValue(lfTestExportSettings::getInstance()->getApiKey());
        } else {
            $api_key->setValue($pl->txt('settings_no_api_key_generated'));
        }
        $form->addItem($api_key);

        $last = new ilNonEditableValueGUI($pl->txt('setting_last_export'));
        if (lfTestExportSettings::getInstance()->isExported()) {
            $last->setValue(ilDatePresentation::formatDate(lfTestExportSettings::getInstance()->getLastUpdate()));
        } else {
            $last->setValue($pl->txt('setting_no_export'));
        }
        $form->addItem($last);
        
        
        $form->addCommandButton('save', $this->lng->txt('save'));
        
        $form->setFormAction($this->ctrl->getFormAction($this));
        
        return $form;
    }
    
    /**
     * Save form input (currently does not save anything to db)
     *
     */
    public function save()
    {
        $pl = $this->getPluginObject();
        
        $form = $this->initConfigurationForm();
        
        if ($form->checkInput()) {
            lfTestExportSettings::getInstance()->setInterval($form->getInput('interval'));
            lfTestExportSettings::getInstance()->setDirectory($form->getInput('directory'));
            lfTestExportSettings::getInstance()->save();
            
            ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
            $this->ctrl->redirect($this, "configure");
        } else {
            ilUtil::sendFailure($this->lng->txt('err_check_input'));
            $form->setValuesByPost();
            $this->configure($form);
        }
    }
    
    protected function listTests()
    {
        global $DIC;

        $this->showSubTabs('tests');
        
        $table = new lfObjectTableGUI($this, 'listTests', 'lfexporttst');
        $table->setFormAction($GLOBALS['ilCtrl']->getFormAction($this));
        $table->init();
        $table->setObjects(
            ilUtil::_getObjectsByOperations('tst', 'write', $DIC->user()->getId(), -1)
        );
        $table->parse();
        $this->tpl->setContent($table->getHTML());
    }
    
    
    protected function saveExport()
    {
        $settings = lfTestExportSettings::getInstance();
        
        foreach ((array) $_POST['tst_visible'] as $tst_id => $tmp) {
            if (array_key_exists($tst_id, (array) $_POST['tst_id'])) {
                if (!$settings->isItemExported($tst_id)) {
                    $settings->saveExportItem($tst_id, true);
                }
            } else {
                if ($settings->isItemExported($tst_id)) {
                    $settings->saveExportItem($tst_id, false);
                }
            }
        }
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'listTests');
    }

    /**
     * @param string $a_active
     */
    protected function showSubTabs(string $a_active = '')
    {
        global $ilTabs;
        
        $ilTabs->addSubTab(
            'configure',
            $this->getPluginObject()->txt('subtab_configure'),
            $GLOBALS['ilCtrl']->getLinkTarget($this, 'configure')
        );
        $ilTabs->addSubTab(
            'tests',
            $this->getPluginObject()->txt('subtab_test_list'),
            $GLOBALS['ilCtrl']->getLinkTarget($this, 'listTests')
                
        );
        $ilTabs->activateSubTab($a_active);
    }
}
