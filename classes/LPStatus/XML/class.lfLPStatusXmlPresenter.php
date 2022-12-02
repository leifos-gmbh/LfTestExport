<?php

class lfLPStatusXmlPresenter
{
    private const NAN = '-';

    /**
     * @var ilLanguage
     */
    private $lng;

    public function __construct(ilLanguage $lng)
    {
        $this->lng = $lng;
    }

    public function formatDate(?DateTimeImmutable $date): string
    {
        if (isset($date)) {
            return $date->format(DateTimeInterface::ATOM);
        }
        return self::NAN;
    }

    public function formatPercentage(?int $percentage): string
    {
        if (isset($percentage)) {
            return (string) $percentage;
        }
        return self::NAN;
    }

    public function formatStatus(int $status): string
    {
        switch ($status) {
            case ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
                return $this->lng->txt(ilLPStatus::LP_STATUS_IN_PROGRESS);

            case ilLPStatus::LP_STATUS_COMPLETED_NUM:
                return $this->lng->txt(ilLPStatus::LP_STATUS_COMPLETED);

            case ilLPStatus::LP_STATUS_FAILED_NUM:
                return $this->lng->txt(ilLPStatus::LP_STATUS_FAILED);

            case ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
                return $this->lng->txt(ilLPStatus::LP_STATUS_NOT_ATTEMPTED);

            default:
                return self::NAN;
        }
    }
}
