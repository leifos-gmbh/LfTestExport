<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir('../../../../../../..');

include_once './Customizing/global/plugins/Services/Cron/CronHook/LfTestExport/classes/class.lfTestExportRestHandler.php';

try {
    $rest_handler = new lfTestExportRestHandler();
    $rest_handler->initIlias();
    $rest_handler->initPlugin();
    $rest_handler->handleRequest();
} catch (Exception $e) {
    header('HTTP/1.1 401 Unauthorized');
    print_r($_SERVER);
    print_r($e->getMessage());
    exit;
}
