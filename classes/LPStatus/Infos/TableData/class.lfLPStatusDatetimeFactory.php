<?php

class lfLPStatusDatetimeFactory
{
    public function fromTimestamp(int $timestamp): DateTimeImmutable
    {
        $datetime = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        return $datetime->setTimestamp($timestamp);
    }

    public function fromStandardFormat(string $datetime): DateTimeImmutable
    {
       return new DateTimeImmutable($datetime, new DateTimeZone('UTC'));
    }
}
