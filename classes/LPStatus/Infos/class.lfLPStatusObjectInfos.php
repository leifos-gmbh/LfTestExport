<?php

class lfLPStatusObjectInfos
{
    /**
     * @var ilObject
     */
    private $object;

    /**
     * @var ilObjectLP
     */
    private $object_lp;

    public function __construct(
        int $ref_id,
        lfLPStatusObjectFactory $factory
    ) {
        $this->fetchObject($ref_id, $factory);
    }

    public function refId(): int
    {
        return $this->object->getRefId();
    }

    public function objId(): int
    {
        return $this->object->getId();
    }

    public function title(): string
    {
        return $this->object->getTitle();
    }

    public function description(): string
    {
        return $this->object->getDescription();
    }

    public function type(): string
    {
        return $this->object->getType();
    }

    public function isOnline(): bool
    {
        return (bool) ($this->object->getOfflineStatus() ?? true);
    }

    public function LPModeId(): int
    {
        return $this->object_lp->getCurrentMode();
    }

    public function LPMode(): string
    {
        return (string) $this->object_lp->getModeText($this->LPModeId());
    }

    public function LPModeInfo(): string
    {
        return (string) $this->object_lp->getModeInfoText($this->LPModeId());
    }

    private function fetchObject(
        int $ref_id,
        lfLPStatusObjectFactory $factory
    ): void {
        try {
            $object = $factory->object($ref_id);
            $object_lp = $factory->objectLP($object->getId());
        } catch (ilObjectNotFoundException $e) {
            throw new lfNoObjectWithThatRefIdException($ref_id);
        }

        if (!$object_lp->isActive()) {
            throw new lfObjectWithDeactivatedLPException($ref_id);
        }
        if ($object_lp->isAnonymized()) {
            throw new lfObjectWithAnonymizedLPException($ref_id);
        }

        $this->object = $object;
        $this->object_lp = $object_lp;
    }
}
