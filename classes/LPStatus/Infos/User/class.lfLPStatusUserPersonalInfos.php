<?php

class lfLPStatusUserPersonalInfos
{
    /**
     * @var ilObjUser
     */
    private $user;

    public function __construct(
        ilObjUser $user
    ) {
        $this->user = $user;
    }

    public function usrId(): int
    {
        return $this->user->getId();
    }

    public function ADLogin(): string
    {
        return (string) $this->user->getExternalAccount();
    }

    public function login(): string
    {
        return (string) $this->user->getLogin();
    }

    public function firstname(): string
    {
        return (string) $this->user->getFirstname();
    }

    public function lastname(): string
    {
        return (string) $this->user->getLastname();
    }

    public function email(): string
    {
        return (string) $this->user->getEmail();
    }
}

