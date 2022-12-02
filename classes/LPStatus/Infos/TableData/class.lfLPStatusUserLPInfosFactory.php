<?php

class lfLPStatusUserLPInfosFactory
{
    public function infos(
        int $usr_id,
        int $status,
        ?int $percentage,
        ?DateTimeImmutable $first_access,
        ?DateTimeImmutable $last_access,
        DateTimeImmutable $last_status_change,
        int $access_count,
        string $mark,
        string $remark
    ): lfLPStatusUserLPInfos {
        return new lfLPStatusUserLPInfos(
            $usr_id,
            $status,
            $percentage,
            $first_access,
            $last_access,
            $last_status_change,
            $access_count,
            $mark,
            $remark
        );
    }

    public function cache(
        lfLPStatusUserLPInfos ...$infos
    ): lfLPStatusUserLPInfosCache {
       return new lfLPStatusUserLPInfosCache(...$infos);
    }
}
