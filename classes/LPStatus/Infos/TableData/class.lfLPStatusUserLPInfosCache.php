<?php

class lfLPStatusUserLPInfosCache
{
    /**
     * @var lfLPStatusUserLPInfos[]
     */
    private $infos;

    public function __construct(lfLPStatusUserLPInfos ...$infos)
    {
        $this->infos = $infos;
    }

    /**
     * @return Generator|lfLPStatusUserLPInfos
     */
    public function allInfos(): Generator
    {
        foreach ($this->infos as $info) {
            yield $info;
        }
    }
}
