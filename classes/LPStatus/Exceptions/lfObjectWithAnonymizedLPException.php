<?php

class lfObjectWithAnonymizedLPException extends lfLPStatusException
{
    public function __construct(int $ref_id)
    {
        parent::__construct('Learning progress is anonymized in the object with ref_id ' . $ref_id . '.');
    }
}