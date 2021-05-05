<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class lfTestExportRestHandler
 */
class lfTestExportRestHandler
{
    private const HEADER_API_KEY = 'HTTP_IL_TEST_RESULT_API_KEY';

    private const API_KEY_SEPERATOR = '::';

    private const PLUGIN_TEST_EXPORT = 'LfTestExport';

    /**
     * @var string
     */
    private $api_key_client_id = '';

    /**
     * @var string
     */
    private $api_key = '';

    /**
     * @var string
     */
    private $api_key_token = '';

    /**
     * @var null|ilLogger
     */
    private $logger = null;

    /**
     * @var null|ilCronHookPlugin
     */
    private $plugin = null;



    /**
     * lfTestExportRestHandler constructor.
     */
    public function __construct()
    {
    }

    /**
     * Init ILIAS
     * @throws Exception
     */
    public function initIlias()
    {
        global $DIC;
        $this->parseRequest();

        $getParams = $DIC->http()->request()->getQueryParams();

        $_COOKIE['client_id'] = $getParams['client_id'] = $this->api_key_client_id;

        include_once './Services/Init/classes/class.ilInitialisation.php';
        include_once 'Services/Context/classes/class.ilContext.php';
        ilContext::init(ilContext::CONTEXT_REST);

        \ilInitialisation::initILIAS();
    }

    /**
     *
     */
    public function initPlugin()
    {
        global $DIC;

        /*
         * @var \ilPluginAdmin
         */
        $admin = $DIC['ilPluginAdmin'];
        foreach ($admin->getActivePluginsForSlot(IL_COMP_SERVICE, 'Cron', 'crnhk') as $plugin_name) {
            if ($plugin_name == self::PLUGIN_TEST_EXPORT) {
                $this->plugin = $admin->getPluginObject(IL_COMP_SERVICE, 'Cron', 'crnhk', $plugin_name);
            }
        }
        if (!$this->plugin instanceof \ilCronHookPlugin) {
            throw new Exception('Cannot find active export plugin');
        }
    }


    public function handleRequest()
    {
        $server = new lfTestExportRestServer($this->api_key);
        $server->init();
        $server->run();
    }

    /**
     * @throws Exception
     */
    private function parseRequest()
    {
        if (empty($_SERVER[self::HEADER_API_KEY])) {
            throw new Exception('Invalid or non api key given.');
        }
        $this->api_key = $_SERVER[self::HEADER_API_KEY];

        $api_key_parts = explode(self::API_KEY_SEPERATOR, $this->api_key);

        if (count($api_key_parts) !== 2) {
            throw new Exception('Invalid api key given');
        }
        $this->api_key_client_id = $api_key_parts[0];
        $this->api_key_token = $api_key_parts[1];
    }
}
