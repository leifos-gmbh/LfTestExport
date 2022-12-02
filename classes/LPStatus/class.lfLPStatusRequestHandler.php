<?php

class lfLPStatusRequestHandler
{
    /**
     * @var lfLPStatusXmlAuthor
     */
    private $author;

    /**
     * @var lfLPStatusXmlWriterFactory
     */
    private $writer_factory;

    public function __construct(
        lfLPStatusXmlAuthor $author,
        lfLPStatusXmlWriterFactory $writer_factory
    )
    {
        $this->author = $author;
        $this->writer_factory = $writer_factory;
    }

    public function getObjectLPStatusXML(int $ref_id): string
    {
        $writer = $this->getNewXmlWriter();

        $this->author->writeHeader($ref_id, $writer);
        $this->author->writeObjectLPStatus($ref_id, $writer);

        return $writer->xmlDumpMem();
    }

    private function getNewXmlWriter(): ilXmlWriter
    {
        return $this->writer_factory->get();
    }
}
