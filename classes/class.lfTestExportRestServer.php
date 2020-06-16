<?php

use \Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;
use GuzzleHttp\Psr7\LazyOpenStream;

class lfTestExportRestServer extends App
{
    /**
     * @var \ilLogger |null
     */
    private $logger = null;

    /**
     * @var ilLfTestExportPlugin|null
     */
    private $plugin = null;

    /**
     * @var lfTestResultSettings|null
     */
    private $settings = null;

    /**
     * @var string
     */
    private $api_key = '';



    /**
     * lfTestExportRestServer constructor.
     * @param array $container
     */
    public function __construct(string  $api_key, $container = [])
    {
        $this->api_key = $api_key;
        $this->plugin = \ilLfTestExportPlugin::getInstance();
        $this->logger = $this->plugin->getLogger();
        $this->settings = $this->plugin->getSettings();

        parent::__construct($container);
    }

    /**
     * Init server methods
     */
    public function init()
    {
        $callback = $this;

        $this->GET('/test-results', [$callback, 'getTestResults']);
        $this->GET('/test-results/{ID}/versions', [$callback, 'getTestResultVersions']);
        $this->GET('/test-results/{ID}/versions/{VERSION_ID:[0-9_]+}', [$callback, 'getTestResultVersion']);
        $this->DELETE('/test-results/{ID}/versions/{VERSION_ID:[0-9_]+}', [$callback, 'deleteTestResultVersion']);
        $this->GET('/test-results/{ID}/versions/latest', [$callback, 'getLatestTestResultVersion']);
        $this->DELETE('/test-results/{ID}/versions/latest', [$callback, 'deleteLatestTestResultVersion']);
    }

    /**
     *
     */
    public function getLatestTestResultVersion(Request $request, Response $response, array $args)
    {
        $this->logger->info('Called get latest test result version.');

        $file_info = new \lfTestExportFileReader();
        $version_id = $file_info->getLatestVersion($args['ID']);

        return $this->getTestResultVersion(
            $request,
            $response,
            [
                'ID' => $args['ID'],
                'VERSION_ID' => $version_id
            ]
        );
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     * @return Response
     */
    public function deleteLatestTestResultVersion(Request $request, Response $response, array $args)
    {
        $this->logger->info('Called delete latest test result version.');

        $file_info = new \lfTestExportFileReader();
        $version_id = $file_info->getLatestVersion($args['ID']);

        return $this->deleteTestResultVersion(
            $request,
            $response,
            [
                'ID' => $args['ID'],
                'VERSION_ID' => $version_id
            ]

        );
    }

    /**
     * @param Request  $request
     * @param Response $response
     */
    public function getTestResultVersions(Request $request, Response $response, array $args)
    {
        $this->logger->info('Called get  test result versions.');

        if (!$this->isAuthenticated($request, $response)) {
            return $response;
        }
        $file_info = new \lfTestExportFileReader();

        if (!$file_info->idExists($args['ID'])) {
            $this->logger->warning('Invalid file id given: ' . $args['ID']);
            return $response->withStatus(StatusCode::HTTP_NOT_FOUND);
        }

        $versions = $file_info->getFileVersions($args['ID']);
        return $response
            ->withStatus(StatusCode::HTTP_OK)
            ->withJson(json_encode($versions));
    }

    public function getTestResultVersion(Request $request, Response $response, array $args)
    {
        $this->logger->info('Called get test result version.');
        if (!$this->isAuthenticated($request, $response)) {
            return $response;
        }

        $file_info = new \lfTestExportFileReader();

        if (!$file_info->idExists($args['ID'])) {
            $this->logger->warning('Invalid file id given: ' . $args['ID']);
            return $response->withStatus(StatusCode::HTTP_NOT_FOUND);
        }

        if (!$file_info->versionIdExists($args['ID'], $args['VERSION_ID'])) {
            $this->logger->warning('Invalid file id or version id given: ' . $args['ID'] . ' ' . $args['VERSION_ID']);
            return $response->withStatus(StatusCode::HTTP_NOT_FOUND);
        }

        $file = $file_info->getFile($args['ID'], $args['VERSION_ID']);

        $stream = new LazyOpenStream($file->getPathname(),'r');
        return $response
            ->withHeader('Content-Type', 'application/xml')
            ->withBody($stream);
    }

    public function deleteTestResultVersion(Request $request, Response $response, array $args)
    {
        $this->logger->info('Called delete test result version.');
        if (!$this->isAuthenticated($request, $response)) {
            return $response;
        }

        $file_info = new \lfTestExportFileReader();

        if (!$file_info->idExists($args['ID'])) {
            $this->logger->warning('Invalid file id given: ' . $args['ID']);
            return $response->withStatus(StatusCode::HTTP_NOT_FOUND);
        }

        if (!$file_info->versionIdExists($args['ID'], $args['VERSION_ID'])) {
            $this->logger->warning('Invalid file id or version id given: ' . $args['ID'] . ' ' . $args['VERSION_ID']);
            return $response->withStatus(StatusCode::HTTP_NOT_FOUND);
        }

        $file_info->deleteVersion($args['ID'], $args['VERSION_ID']);
        return $response
            ->withStatus(StatusCode::HTTP_OK);
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @return Response
     */
    public function getTestResults(Request $request, Response $response)
    {
        $this->logger->info('Called get test results.');
        if (!$this->isAuthenticated($request, $response)) {
            return $response;
        }

        $file_info = new \lfTestExportFileReader();

        return $response
            ->withStatus(StatusCode::HTTP_OK)
            ->withJson(json_encode($file_info->getIds()));
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @return bool
     */
    private function isAuthenticated(Request $request, Response &$response)
    {
        $api_key = $this->settings->getApiKey();

        if (strcmp($api_key, $this->api_key) === 0) {
            return true;
        }
        $this->logger->warning('Invalid api key given: ' . $this->api_key);
        $response = $response->withStatus(StatusCode::HTTP_UNAUTHORIZED);
        return false;
    }


}
