<?php

class lfLPStatusInfosFactory
{
    /**
     * @var lfLPStatusObjectFactory
     */
    private $obj_factory;

    public function __construct(lfLPStatusObjectFactory $obj_factory)
    {
        $this->obj_factory = $obj_factory;
    }

    public function objectInfos(int $ref_id): lfLPStatusObjectInfos
    {
        return new lfLPStatusObjectInfos($ref_id, $this->obj_factory);
    }

    public function userInfos(
        int $usr_id,
        lfLPStatusUserLPInfos $lp_infos
    ): lfLPStatusUserInfos {
        return new lfLPStatusUserInfos(
            new lfLPStatusUserPersonalInfos($usr_id, $this->obj_factory),
            $lp_infos
        );
    }

    public function infosCollection(
        lfLPStatusObjectInfos $object_infos,
        lfLPStatusUserInfos ...$users_infos
    ): lfLPStatusInfosCollection {
        return new lfLPStatusInfosCollection(
            $object_infos,
            ...$users_infos
        );
    }
}
