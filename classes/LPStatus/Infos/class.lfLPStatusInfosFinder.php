<?php

class lfLPStatusInfosFinder
{
    /**
     * @var lfLPStatusInfosFactory
     */
    private $factory;

    /**
     * @var lfLPStatusTableDataExtractor
     */
    private $extractor;

    /**
     * @var ilLogger
     */
    private $logger;

    public function __construct(
        lfLPStatusInfosFactory $factory,
        lfLPStatusTableDataExtractor $extractor,
        ilLogger $logger
    ) {
        $this->factory = $factory;
        $this->extractor = $extractor;
        $this->logger = $logger;
    }

    public function getInfosCollection(
        int $ref_id
    ): lfLPStatusInfosCollection {
        $object_infos = $this->factory->objectInfos($ref_id);
        $user_infos_cache = $this->extractor->fetchUsersLPInfos($object_infos);

        $user_infos = [];
        foreach ($user_infos_cache->allInfos() as $user_lp_infos) {
            try {
                $user_infos[] = $this->factory->userInfos(
                    $user_lp_infos->usrId(),
                    $user_lp_infos
                );
            } catch (Exception $e) {
                $this->logger->info(
                    'Cannot read learning progress of user with ID ' .
                    $user_lp_infos->usrId() . ': ' . $e->getMessage()
                );
            }
        }

        return $this->factory->infosCollection(
            $object_infos,
            ...$user_infos
        );
    }
}
