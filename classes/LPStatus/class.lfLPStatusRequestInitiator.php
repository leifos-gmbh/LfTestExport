<?php

class lfLPStatusRequestInitiator
{
    public function initHandler(ilLogger $logger): lfLPStatusRequestHandler
    {
        global $DIC;
        $lng = $DIC->language();
        $lng->loadLanguageModule('trac');

        return new lfLPStatusRequestHandler(
            new lfLPStatusXmlAuthor(
                new lfLPStatusInfosFinder(
                    new lfLPStatusInfosFactory(),
                    new lfLPStatusObjectFactory(),
                    new lfLPStatusTableDataExtractor(
                        $DIC->database(),
                        new lfLPStatusUserLPInfosFactory(),
                        new lfLPStatusDatetimeFactory(),
                        new lfLPStatusPercentageUtilities()
                    ),
                    $logger
                ),
                new lfLPStatusXmlPresenter(
                    $lng
                )
            ),
            new lfLPStatusXmlWriterFactory()
        );
    }
}
