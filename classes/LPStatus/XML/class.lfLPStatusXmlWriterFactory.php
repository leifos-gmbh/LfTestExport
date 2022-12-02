<?php

class lfLPStatusXmlWriterFactory
{
    public function get(): ilXmlWriter
    {
        return new ilXmlWriter();
    }
}
