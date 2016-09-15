<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class lfTestResultExporter
{
	
	private $settings = NULL;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->settings = lfTestExportSettings::getInstance();
	}
	
	public function export()
	{
		foreach ($this->getSettings()->getItems() as $tst_ref_id)
		{
			try {
				$xml = $this->exportItem($tst_ref_id);
			}
			catch(Exception $e)
			{
				ilLoggerFactory::getLogger('lftest')->warning('Export failed. Invalid test id given: tst_id = ' . $tst_ref_id);
				continue;
			}
			$tst_obj_id = ilObject::_lookupObjId($tst_ref_id);
			$dt = new ilDateTime(time(),IL_CAL_UNIX);
			file_put_contents($this->getSettings()->getDirectory().'/'.$tst_obj_id.'_'.$dt->get(IL_CAL_FKT_DATE,'Ymd_Hi').'.xml',$xml);
			
			$this->getSettings()->setLastUpdate(new ilDateTime(time(),IL_CAL_UNIX));
			$this->getSettings()->setExported(TRUE);
			$this->getSettings()->save();
			
		}
	}
	
	protected function exportItem($test_ref_id)
	{
		global $ilSetting;
		
		include_once './Services/Object/classes/class.ilObjectFactory.php';
		$factory = new ilObjectFactory();
		$tst = $factory->getInstanceByRefId($test_ref_id, FALSE);
		if(!$tst instanceof ilObjTest)
		{
			throw new InvalidArgumentException('Invalid test id given');
		}
		
		include_once './Services/Xml/classes/class.ilXmlWriter.php';
		$writer = new ilXmlWriter();

		$il_id = "il_".$ilSetting->get('inst_id')."_tst_".$tst->getId();
		
		$writer->xmlStartTag('Test',array('id' => $il_id, 'obj_id' => $tst->getId()));
		$writer->xmlElement('Title', array(),$tst->getTitle());
		$writer->xmlElement('Description',array(),$tst->getDescription());

		$path_str = $this->getPath(ROOT_FOLDER_ID, $test_ref_id);

		$writer->xmlElement('Path', array(), $path_str);
		
		// marks
		$writer->xmlStartTag('MarkSchema',array());
		$schema = $tst->getMarkSchema();
		$schema->sort();
		foreach((array) $schema->mark_steps as $mark)
		{
			$writer->xmlElement(
				'MarkStep',
				array(
					'minimumLevel' => $mark->getMinimumLevel(),
					'passed' => ($mark->getPassed() ? 1 : 0)
				),
				$mark->getShortName()
			);
		}
		$writer->xmlEndTag('MarkSchema');
		
		// calculate max points
		$max_points = 0;
		$num_questions = 0;
		foreach($this->getQuestions($tst) as $qst_id)
		{
			include_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
			$max_points += assQuestion::_getMaximumPoints($qst_id);
			$num_questions++;
		}
		if($num_questions)
		{
			include_once './Modules/Test/classes/class.ilTestRandomQuestionSetConfig.php';
			$max_points = ($max_points / $num_questions) * $tst->getQuestionCount();
		}
		else
		{
			$max_points = 0;
		}
		$writer->xmlElement('MaxPoints',array(),(int) $max_points);
		#$writer->xmlElement('RequiredPoints',array(),5);
		
		$writer->xmlStartTag('Questions');
		#foreach ($tst->getQuestions() as $qst_id)
		foreach($this->getQuestions($tst) as $qst_id)
		{
			// @var assQuestion
			$qst = ilObjTest::_instanciateQuestion($qst_id);
			$writer->xmlStartTag('Question',array('id' => $qst->getId()));
			$writer->xmlElement('Title',array(),$qst->getTitle());
			$writer->xmlElement('MaxPoints',array(),$qst->getMaximumPoints());
			$writer->xmlEndTag('Question');
		}
		$writer->xmlEndTag('Questions');
		
		
		$ur = $tst->getDetailedTestResults($tst->getTestParticipants());
		$results = array();
		foreach($ur as $res_row)
		{
			$results[$res_row['user_id']]['questions'][$res_row['question_id']] = $res_row['reached_points'];
			$results[$res_row['user_id']]['points'] = (int) $results[$res_row['user_id']]['points'] + $res_row['reached_points'];
		}
		$writer->xmlStartTag('UserResults');
		foreach($results as $usr_id => $res_row)
		{
			$counted_ts = $this->readPassTimestamp($usr_id, $tst);
			$il_id = "il_".$ilSetting->get('inst_id')."_usr_".$usr_id;
			
			$writer->xmlStartTag('UserResult',array('id' => $il_id));
			
			$name = ilObjUser::_lookupName($usr_id);
			$writer->xmlElement('Login',array(),$name['login']);
			$writer->xmlElement('Firstname',array(),$name['firstname']);
			$writer->xmlElement('Lastname',array(),$name['lastname']);
			#$writer->xmlElement('ReachedPoints',array(),$res_row['points']);
			
			$res_atts = array();
			if($max_points)
			{
				$matching_mark = $tst->getMarkSchema()->getMatchingMark($res_row['points'] / $max_points * 100);
				if($matching_mark)
				{
					$res_atts['mark'] = $matching_mark->getShortName();
					$res_atts['points'] = $res_row['points'];
					
					if($counted_ts)
					{
						$res_atts['dt'] = $counted_ts;
					}
					
					$writer->xmlElement(
						'Result',
						$res_atts
					);
				}
			}
			else
			{
				$res_atts['points'] = $res_row['points'];
				if($counted_ts)
				{
					$res_atts['dt'] = $counted_ts;
				}
				
				$writer->xmlElement(
					'Result',
					$res_atts
				);
			}
			
			$writer->xmlStartTag('UserResultQuestions');
			foreach((array) $res_row['questions'] as $qst_id => $qst_points)
			{
				$writer->xmlElement('UserResultQuestionPoints',array('qst_id' => $qst_id),(int) $qst_points);
			}
			$writer->xmlEndTag('UserResultQuestions');
			$writer->xmlEndTag('UserResult');
		}
		$writer->xmlEndTag('UserResults');
		$writer->xmlEndTag('Test');
		
		return $writer->xmlDumpMem(FALSE);
	}

	protected function getQuestions(ilObjTest $tst)
	{
		$qsts = array();
		foreach($tst->getParticipants() as $active_id => $tmp)
		{
			$tst->loadQuestions($active_id,$tst->_getResultPass($active_id));
			$qsts = array_unique(array_merge($qsts,$tst->getQuestions()));
		}
		return $qsts;
	}
	
	protected function readPassTimestamp($a_usr_id, ilObjTest $tst)
	{
		global $ilDB;
		
		$active_id = $tst->getActiveIdOfUser($a_usr_id);
		
		if(!$active_id)
		{
			return '';
		}
		$valid_pass = (int) ilObjTest::_getResultPass($active_id);
		
		$query = 'SELECT tstamp FROM tst_pass_result '.
				'WHERE active_fi = '.$ilDB->quote($active_id,'integer').' '.
				'AND pass = '.$ilDB->quote($valid_pass,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$dt = new ilDateTime($row->tstamp,IL_CAL_UNIX);
			return $dt->get(IL_CAL_FKT_DATE, 'c', ilTimeZone::UTC);
		}
		return '';
	}


	/**
	 * @return lfTestResultSettings
	 */
	protected function getSettings()
	{
		return $this->settings;
	}

	/**
	 * returns a "<" separated path string
	 *
	 * @param $start int ref_id
	 * @param $end int re_id
	 * @return string path
	 */
	protected function getPath($start, $end)
	{
		global $lng, $tree;

		$path_ids = $tree->getPathId($end, $start);
		unset($path_ids[count($path_ids) - 1]);

		$first = true;
		$path = "";

		foreach($path_ids as $ref_id)
		{
			$obj_id = ilObject::_lookupObjId($ref_id);
			$title = ilObject::_lookupTitle($obj_id);

			if($first)
			{
				if($ref_id == ROOT_FOLDER_ID)
				{
					$title = $lng->txt('repository');
				}
				$first = false;
			}
			else
			{
				$title = " > " . $title;
			}

			$path .= $title;
		}

		return $path;
	}

}
?>
