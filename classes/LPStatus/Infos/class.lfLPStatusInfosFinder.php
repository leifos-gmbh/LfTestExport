<?php

class lfLPStatusInfosFinder
{
    /**
     * @var lfLPStatusInfosFactory
     */
    private $infos_factory;

    /**
     * @var lfLPStatusObjectFactory
     */
    private $object_factory;

    /**
     * @var lfLPStatusTableDataExtractor
     */
    private $extractor;

    /**
     * @var ilLogger
     */
    private $logger;

    public function __construct(
        lfLPStatusInfosFactory $infos_factory,
        lfLPStatusObjectFactory $object_factory,
        lfLPStatusTableDataExtractor $extractor,
        ilLogger $logger
    ) {
        $this->infos_factory = $infos_factory;
        $this->object_factory = $object_factory;
        $this->extractor = $extractor;
        $this->logger = $logger;
    }

    public function getInfosCollection(
        int $ref_id
    ): lfLPStatusInfosCollection {
        $object_infos = $this->infos_factory->objectInfos(
            $object = $this->fetchObject($ref_id),
            $this->fetchObjectLP($object->getId(), $ref_id)
        );
        $user_infos_cache = $this->extractor->fetchUsersLPInfos($object_infos);

        $user_infos = [];
        foreach ($user_infos_cache->allInfos() as $user_lp_infos) {
            try {
                $user_infos[] = $this->infos_factory->userInfos(
                    $this->fetchUser($user_lp_infos->usrId()),
                    $user_lp_infos
                );
            } catch (Exception $e) {
                $this->logger->info(
                    'Cannot read learning progress of user with ID ' .
                    $user_lp_infos->usrId() . ': ' . $e->getMessage()
                );
            }
        }

        return $this->infos_factory->infosCollection(
            $object_infos,
            ...$user_infos
        );
    }

    private function fetchObject(int $ref_id): ilObject {
        try {
            $object = $this->object_factory->object($ref_id);
        } catch (ilObjectNotFoundException $e) {
            throw new lfNoObjectWithThatRefIdException($ref_id);
        }

        return $object;
    }

    private function fetchObjectLP(int $obj_id, int $ref_id): ilObjectLP {
        $object_lp = $this->object_factory->objectLP($obj_id);

        if (!$object_lp->isActive()) {
            throw new lfObjectWithDeactivatedLPException($ref_id);
        }
        if ($object_lp->isAnonymized()) {
            throw new lfObjectWithAnonymizedLPException($ref_id);
        }

        return $object_lp;
    }

    private function fetchUser(int $usr_id): ilObjUser {
        try {
            $user = $this->object_factory->user($usr_id);
        } catch (Exception $e) {
            throw new lfInvalidUserException($usr_id);
        }

        return $user;
    }
}
