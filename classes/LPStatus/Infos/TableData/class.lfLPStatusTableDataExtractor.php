<?php

class lfLPStatusTableDataExtractor
{
    /**
     * @var ilDBInterface
     */
    private $db;

    /**
     * @var ilTree
     */
    private $tree;

    /**
     * @var lfLPStatusUserLPInfosFactory
     */
    private $lp_infos_factory;

    /**
     * @var lfLPStatusDatetimeFactory
     */
    private $datetime_factory;

    /**
     * @var lfLPStatusPercentageUtilities
     */
    private $percentage_utilities;

    public function __construct(
        ilDBInterface $db,
        ilTree $tree,
        lfLPStatusUserLPInfosFactory $lp_infos_factory,
        lfLPStatusDatetimeFactory $datetime_factory,
        lfLPStatusPercentageUtilities $percentage_utilities
    ) {
        $this->db = $db;
        $this->tree = $tree;
        $this->lp_infos_factory = $lp_infos_factory;
        $this->datetime_factory = $datetime_factory;
        $this->percentage_utilities = $percentage_utilities;
    }

    public function fetchUsersLPInfos(
        lfLPStatusObjectInfos $object_infos
    ): lfLPStatusUserLPInfosCache {
        $percentage_available = $this->percentage_utilities->isAvailable($object_infos);
        try {
            $participants = $this->getParticipantsForObject($object_infos->refId());
        } catch (Exception $e) {
            throw new lfLPStatusException(
                'Could not determine participants of object with ref_id ' . $object_infos->refId() .
                ': ' . $e->getMessage()
            );
        }

        $query = $this->db->query(
            "SELECT u.obj_id AS obj_id, u.usr_id AS usr_id,
            status, percentage, status_changed, mark, u_comment, first_access, last_access,
            read_count+childs_read_count as access_count 
            FROM ut_lp_marks AS u LEFT JOIN read_event AS r ON
            u.obj_id = r.obj_id AND u.usr_id = r.usr_id
            WHERE u.obj_id =" . $this->db->quote($object_infos->objId(), ilDBConstants::T_INTEGER) .
            " AND " . $this->db->in('u.usr_id', $participants, false, ilDBConstants::T_INTEGER)
        );

        $infos = [];
        while ($row = $this->db->fetchObject($query)) {
            $infos[] = $this->lp_infos_factory->infos(
                (int) $row->usr_id,
                (int) $row->status,
                $percentage_available ? (int) $row->percentage : null,
                isset($row->first_access) ?
                    $this->datetime_factory->fromStandardFormat($row->first_access) : null,
                isset($row->last_access) ?
                    $this->datetime_factory->fromTimestamp((int) $row->last_access) : null,
                $this->datetime_factory->fromStandardFormat($row->status_changed),
                (int) $row->access_count,
                (string) $row->mark,
                (string) $row->u_comment
            );
        }

        return $this->lp_infos_factory->cache(...$infos);
    }

    /**
     * This is basically ilTrQuery::getParticipantsForObject without
     * the rbac checks. If the original method is changed, this should
     * be changed as well.
     * @return int[]
     */
    protected function getParticipantsFOrObject(int $ref_id): array
    {
        $obj_id = ilObject::_lookupObjectId($ref_id);
        $obj_type = ilObject::_lookupType($obj_id);

        $members = [];

        // try to get participants from (parent) course/group
        $members_read = false;
        switch ($obj_type) {
            case 'crsr':
                $members_read = true;
                $olp = \ilObjectLP::getInstance($obj_id);
                $members = $olp->getMembers();
                break;

            case 'crs':
            case 'grp':
                $members_read = true;
                $member_obj = ilParticipants::getInstance($ref_id);
                $members = $member_obj->getMembers();
                break;


            /* Mantis 19296: Individual Assessment can be subtype of crs.
              * But for LP view only his own members should be displayed.
              * We need to return the members without checking the parent path. */
            case "iass":
                $members_read = true;
                $iass = new ilObjIndividualAssessment($obj_id, false);
                $members = $iass->loadMembers()->membersIds();
                break;

            default:
                // walk path to find course or group object and use members of that object
                $path = $this->tree->getPathId($ref_id);
                array_pop($path);
                foreach (array_reverse($path) as $path_ref_id) {
                    $type = ilObject::_lookupType($path_ref_id, true);
                    if ($type == "crs" || $type == "grp") {
                        $members_read = true;
                        $members = self::getParticipantsForObject($path_ref_id);
                    }
                }
                break;
        }

        // begin-patch ouf
        if ($members_read) {
            return $members;
        }

        $users = [];

        // no participants possible: use tracking/object data where possible
        switch ($obj_type) {
            case "sahs":
                $subtype = ilObjSAHSLearningModule::_lookupSubType($obj_id);
                if ($subtype == "scorm2004") {
                    // based on cmi_node/cp_node, used for scorm tracking data views
                    $mod = new ilObjSCORM2004LearningModule($obj_id, false);
                    $all = $mod->getTrackedUsers("");
                    if ($all) {
                        $users = array();
                        foreach ($all as $item) {
                            $users[] = $item["user_id"];
                        }
                    }
                } else {
                    $users = ilObjSCORMTracking::_getTrackedUsers($obj_id);
                }
                break;

            case "exc":
                $exc = new ilObjExercise($obj_id, false);
                $members = new ilExerciseMembers($exc);
                $users = $members->getMembers();
                break;

            case "tst":
                $class = ilLPStatusFactory::_getClassById($obj_id, ilLPObjSettings::LP_MODE_TEST_FINISHED);
                $users = $class::getParticipants($obj_id);
                break;

            case "svy":
                $class = ilLPStatusFactory::_getClassById($obj_id, ilLPObjSettings::LP_MODE_SURVEY_FINISHED);
                $users = $class::getParticipants($obj_id);
                break;

            case "prg":
                $prg = new ilObjStudyProgramme($obj_id, false);
                $users = $prg->getIdsOfUsersWithRelevantProgress();
                break;
            default:
                // keep empty
                break;
        }

        return $users;
    }
}