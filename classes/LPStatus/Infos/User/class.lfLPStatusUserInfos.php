<?php

class lfLPStatusUserInfos
{
    /**
     * @var lfLPStatusUserPersonalInfos
     */
    private $personal;

    /**
     * @var lfLPStatusUserLPInfos
     */
    private $learning_progress;

    public function __construct(
        lfLPStatusUserPersonalInfos $personal,
        lfLPStatusUserLPInfos $learning_progress
    ) {
        $this->personal = $personal;
        $this->learning_progress = $learning_progress;
    }

    public function personal(): lfLPStatusUserPersonalInfos
    {
        return $this->personal;
    }

    public function learningProgress(): lfLPStatusUserLPInfos
    {
        return $this->learning_progress;
    }
}
