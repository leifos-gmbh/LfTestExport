<?php

class lfLPStatusUserPersonalInfos
{
    /**
     * @var ilObjUser
     */
    private $user;

    public function __construct(
        int $usr_id,
        lfLPStatusObjectFactory $factory
    ) {
        $this->fetchUser($usr_id, $factory);
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

    private function fetchUser(
        int $usr_id,
        lfLPStatusObjectFactory $factory
    ): void {
        try {
            $user = $factory->user($usr_id);
        } catch (Exception $e) {
            throw new lfInvalidUserException($usr_id);
        }

        $this->user = $user;
    }
}

