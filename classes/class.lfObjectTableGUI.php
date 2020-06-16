<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Settings for LO courses
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class lfObjectTableGUI extends ilTable2GUI
{
	protected $settings = null;
	protected $objects = array();
	
	/**
	 * Constructor
	 * @param type $a_parent_obj
	 * @param type $a_parent_cmd
	 * @param type $a_id
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_id)
	{
		$this->setId('obj_table_'.$a_id);
		parent::__construct($a_parent_obj, $a_parent_cmd, '');
				
		$this->setOrderColumn('title');
		$this->setRowTemplate(
		    'tpl.object_table_row.html',
            $this->getParentObject()->getPluginObject()->getDirectory()
		);
		
		$this->settings = lfTestExportSettings::getInstance();
	}
	
	/**
	 * Get settings object
	 * @return lfExportSettings
	 */
	public function getSettings()
	{
		return $this->settings;
	}
	
	
	public function setObjects($a_ref_ids)
	{
		$this->objects = $a_ref_ids;
	}
	
	public function getObjects()
	{
		return $this->objects;
	}
	
	public function init()
	{
		$this->setFormName('tests');
		$this->addColumn('','','1px');
		$this->addColumn($this->lng->txt('type'), 'type','30px');
		$this->addColumn($this->lng->txt('title'),'title','80%');
		$this->addColumn($this->getParentObject()->getPluginObject()->txt('tst_table_col_exportable'),'exportable','20%');
		
		$this->setSelectAllCheckbox('tst_id');
		$this->setShowRowsSelector(TRUE);
		
		$this->addMultiCommand('saveExport', $this->getParentObject()->getPluginObject()->txt('enable_export'));
	}
	
	public function fillRow($set)
	{
	
		$this->tpl->setVariable('VAL_ID',$set['ref_id']);
		
		include_once './Services/Link/classes/class.ilLink.php';
		$this->tpl->setVariable('OBJ_LINK',ilLink::_getLink($set['ref_id'], $set['type']));
		$this->tpl->setVariable('OBJ_LINKED_TITLE',$set['title']);
		$this->tpl->setVariable('TYPE_IMG',ilUtil::getTypeIconPath($set['type'], $set['obj_id']));
		$this->tpl->setVariable('TYPE_STR',$this->lng->txt('obj_'.$set['type']));
		
		if($this->getSettings()->isItemExported($set['ref_id']))
		{
			$this->tpl->setVariable('VAL_CHECKED','checked="checked"');
			$this->tpl->setVariable('EXPORT_IMG',ilUtil::getImagePath('icon_ok.png'));
			
		}
		else
		{
			$this->tpl->setVariable('EXPORT_IMG',ilUtil::getImagePath('icon_not_ok.png'));
		}
	}
	
	/**
	 * Parse objects
	 */
	public function parse()
	{
		$counter = 0;
		$set = array();
		foreach($this->getObjects() as $ref_id)
		{
			$type = ilObject::_lookupType(ilObject::_lookupObjId($ref_id));
			if($type == 'rolf')
			{
				continue;
			}
			
			$set[$counter]['ref_id'] = $ref_id;
			$set[$counter]['obj_id'] = ilObject::_lookupObjId($ref_id);
			$set[$counter]['type'] = ilObject::_lookupType(ilObject::_lookupObjId($ref_id));
			$set[$counter]['title'] = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
			$counter++;
		}
		
		$this->setData($set);
	}
}