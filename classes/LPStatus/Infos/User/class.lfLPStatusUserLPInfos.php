<?php

class lfLPStatusUserLPInfos
{
    /**
     * @var int
     */
    private $usr_id;

    /**
     * @var int
     */
    private $status;

    /**
     * @var ?int
     */
    private $percentage;

    /**
     * @var ?DateTimeImmutable
     */
    private $first_access;

    /**
     * @var ?DateTimeImmutable
     */
    private $last_access;

    /**
     * @var DateTimeImmutable
     */
    private $last_status_change;

    /**
     * @var int
     */
    private $access_count;

    /**
     * @var string
     */
    private $mark;

    /**
     * @var string
     */
    private $remark;

    public function __construct(
        int $usr_id,
        int $status,
        ?int $percentage,
        ?DateTimeImmutable $first_access,
        ?DateTimeImmutable $last_access,
        DateTimeImmutable $last_status_change,
        int $access_count,
        string $mark,
        string $remark
    ) {
        $this->usr_id = $usr_id;
        $this->status = $status;
        $this->percentage = $percentage;
        $this->first_access = $first_access;
        $this->last_access = $last_access;
        $this->last_status_change = $last_status_change;
        $this->access_count = $access_count;
        $this->mark = $mark;
        $this->remark = $remark;
    }

    public function usrId(): int
    {
        return $this->usr_id;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function percentage(): ?int
    {
        return $this->percentage;
    }

    public function firstAccess(): ?DateTimeImmutable
    {
        return $this->first_access;
    }

    public function lastAccess(): ?DateTimeImmutable
    {
        return $this->last_access;
    }

    public function lastStatusChange(): DateTimeImmutable
    {
        return $this->last_status_change;
    }

    public function accessCount(): int
    {
        return $this->access_count;
    }

    public function mark(): string
    {
        return $this->mark;
    }

    public function remark(): string
    {
        return $this->remark;
    }
}
