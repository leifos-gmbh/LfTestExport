<?php

class lfNoObjectWithThatRefIdException extends lfLPStatusException
{
    public function __construct(int $ref_id)
    {
        parent::__construct('There is no object with the ref_id ' . $ref_id . '.');
    }
}