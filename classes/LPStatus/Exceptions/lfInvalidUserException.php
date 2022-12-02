<?php

class lfInvalidUserException extends lfLPStatusException
{
    public function __construct(int $usr_id)
    {
        parent::__construct('User with ID ' . $usr_id . ' cannot be initiated.');
    }
}