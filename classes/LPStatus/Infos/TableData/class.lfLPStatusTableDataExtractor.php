<?php

class lfLPStatusTableDataExtractor
{
    /**
     * @var ilDBInterface
     */
    private $db;

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
        lfLPStatusUserLPInfosFactory $lp_infos_factory,
        lfLPStatusDatetimeFactory $datetime_factory,
        lfLPStatusPercentageUtilities $percentage_utilities
    ) {
        $this->db = $db;
        $this->lp_infos_factory = $lp_infos_factory;
        $this->datetime_factory = $datetime_factory;
        $this->percentage_utilities = $percentage_utilities;
    }

    public function fetchUsersLPInfos(
        lfLPStatusObjectInfos $object_infos
    ): lfLPStatusUserLPInfosCache {
        $percentage_available = $this->percentage_utilities->isAvailable($object_infos);

        $query = $this->db->query(
            "SELECT u.obj_id AS obj_id, u.usr_id AS usr_id,
            status, percentage, status_changed, mark, u_comment, first_access, last_access,
            read_count+childs_read_count as access_count 
            FROM ut_lp_marks AS u LEFT JOIN read_event AS r ON
            u.obj_id = r.obj_id AND u.usr_id = r.usr_id
            WHERE u.obj_id =" . $this->db->quote($object_infos->objId(), ilDBConstants::T_INTEGER)
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
}