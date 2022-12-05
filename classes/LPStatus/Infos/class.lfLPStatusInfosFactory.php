<?php

class lfLPStatusInfosFactory
{
    public function objectInfos(
        ilObject $object,
        ilObjectLP $object_lp
    ): lfLPStatusObjectInfos {
        return new lfLPStatusObjectInfos($object, $object_lp);
    }

    public function userInfos(
        ilObjUser $user,
        lfLPStatusUserLPInfos $lp_infos
    ): lfLPStatusUserInfos {
        return new lfLPStatusUserInfos(
            new lfLPStatusUserPersonalInfos($user),
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
