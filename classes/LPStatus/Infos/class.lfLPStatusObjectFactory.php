<?php

class lfLPStatusObjectFactory
{
    public function user(int $usr_id): ilObjUser
    {
        return new ilObjUser($usr_id);
    }

    public function object(int $ref_id): ilObject
    {
        return ilObjectFactory::getInstanceByRefId($ref_id);
    }

    public function objectLP(int $obj_id): ilObjectLP
    {
        return ilObjectLP::getInstance($obj_id);
    }
}
