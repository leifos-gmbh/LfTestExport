<?php

class lfLPStatusInfosCollection
{
    /**
     * @var lfLPStatusObjectInfos
     */
    private $object_infos;

    /**
     * @var lfLPStatusUserInfos[]
     */
    private $users_infos;

    public function __construct(
        lfLPStatusObjectInfos $object_infos,
        lfLPStatusUserInfos ...$users_infos
    ) {
        $this->object_infos = $object_infos;
        $this->users_infos = $users_infos;
    }

    public function objectInfos(): lfLPStatusObjectInfos
    {
        return $this->object_infos;
    }

    /**
     * @return Generator|lfLPStatusUserInfos[]
     */
    public function allUserInfos(): Generator
    {
        foreach ($this->users_infos as $user_infos) {
            yield $user_infos;
        }
    }
}
