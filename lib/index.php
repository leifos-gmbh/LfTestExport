<?php
/**
 * ilTestResultApi
 * @version 1.0
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = new Slim\App();


/**
 * DELETE deleteTestResultsIDVersionsLatest
 * Summary: 
 * Notes: Delete Test Result export
 * Output-Formats: [application/json, application/xml]
 */
$app->DELETE('/testResultRestApi.php/test-results/{ID}/versions/latest', function($request, $response, $args) {
            
            
            
            
            $response->write('How about implementing deleteTestResultsIDVersionsLatest as a DELETE method ?');
            return $response;
            });


/**
 * DELETE deleteTestResultsIDVersionsVERSIONID
 * Summary: 
 * Notes: Delete Test Result export
 * Output-Formats: [application/json, application/xml]
 */
$app->DELETE('/testResultRestApi.php/test-results/{ID}/versions/{VERSION_ID}', function($request, $response, $args) {
            
            
            
            
            $response->write('How about implementing deleteTestResultsIDVersionsVERSIONID as a DELETE method ?');
            return $response;
            });


/**
 * GET getTestResults
 * Summary: Your GET endpoint
 * Notes: Get list of IDs of all exported tests. The IDs corresspond to ILIAS obj_id. 
 * Output-Formats: [application/json, application/xml]
 */
$app->GET('/testResultRestApi.php/test-results', function($request, $response, $args) {
            

            
            $response->write('How about implementing getTestResults as a GET method ?');
            return $response;
            });


/**
 * GET getTestResultsIDVersions
 * Summary: Your GET endpoint
 * Notes: Get versions of exported tests. A version id a string of the generation date time.  E.g \&quot;20200608_1301\&quot; 
 * Output-Formats: [application/json, application/xml]
 */
$app->GET('/testResultRestApi.php/test-results/{ID}/versions', function($request, $response, $args) {
            
            
            
            
            $response->write('How about implementing getTestResultsIDVersions as a GET method ?');
            return $response;
            });


/**
 * GET getTestResultsIDVersionsLatest
 * Summary: 
 * Notes: Get latest Test Result for ID
 * Output-Formats: [application/xml]
 */
$app->GET('/testResultRestApi.php/test-results/{ID}/versions/latest', function($request, $response, $args) {
            
            
            
            
            $response->write('How about implementing getTestResultsIDVersionsLatest as a GET method ?');
            return $response;
            });


/**
 * GET getTestResultsIDVersionsVERSIONID
 * Summary: Your GET endpoint
 * Notes: Get a result version of a test
 * Output-Formats: [application/xml]
 */
$app->GET('/testResultRestApi.php/test-results/{ID}/versions/{VERSION_ID}', function($request, $response, $args) {
            
            
            
            
            $response->write('How about implementing getTestResultsIDVersionsVERSIONID as a GET method ?');
            return $response;
            });



$app->run();
