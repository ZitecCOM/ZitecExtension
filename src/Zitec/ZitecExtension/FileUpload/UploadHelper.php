<?php
/**
 * File upload helper class
 * @author Bogdan Grigore <bogdan.grigore@zitec.com>
 */

namespace Zitec\ZitecExtension\FileUpload;

use Zitec\ZitecExtension\Session;


class UploadHelper
{
    const TEST_FILES_DIRECTORY = __DIR__ . DIRECTORY_SEPARATOR . 'Files';
    const FILE_MONITOR = TEST_FILES_PATH . DIRECTORY_SEPARATOR . "used_test_files.txt";
    private static $helper;
    private $verifications;
    private $testType;
    private $hasCsrf;
    private $csrfTokenKey;
    private $csrfToken;
    private $postParameters;
    private $formEndpoint;
    private $fileUploadFormField;
    /**
     * @var Session\Session $session
     */
    private $session;

    private function __construct()
    {

    }

    /**
     * Returns the UploadHelper object
     * @return UploadHelper
     */
    public static function getInstance()
    {
        if (static::$helper == null) {
            static::$helper = new static();
        }
        return static::$helper;
    }

    /**
     * Set the type of the test.
     * Allowed values: post/browser
     * @param string $type
     */
    public function setTestType($type)
    {
        $this->testType = $type;
    }

    /**
     * Return the test type 
     * @return string
     */
    public function getTestType()
    {
        return $this->testType;
    }

    /**
     * Indicated the existence of the CSRF validation on the form
     * @param bool $csrf
     */
    public function setCsrfExistence($csrf)
    {
        $this->hasCsrf = $csrf;
    }

    /**
     * Indicate where to get the CSRF token key from the HTML output of the page
     * @param string $tokenKey
     */
    public function setCsrfTokenKey($tokenKey)
    {
        $this->csrfTokenKey = $tokenKey;
    }

    /**
     * An array with the type of verifications required for the test
     * @param array $verifications
     */
    public function setVerifications($verifications)
    {
        $this->verifications = $verifications;
        $this->verifications[] = "valid";
        if(empty($verifications)) {
            $this->verifications[] = "mime";
        }
    }

    /**
     * Set the CSRF token value to use in the post
     * @param string $token
     */
    public function setCsrfToken($token)
    {
        $this->csrfToken = $token;
    }

    /**
     * Set the browser session, post params and form endpoint URL to the helper
     * @param Session\Session $session Session of the current browser for the test
     * @param string $endpoint Form endpoint - where form sends its request
     * @param string $fileUploadField Name of the form upload field
     * @param array $params Other required form params for the POST request
     */
    public function setSessionAndPostParams($session, $endpoint, $fileUploadField, $params = null)
    {
        $this->session = $session;
        $this->setFormEndpoint($endpoint);
        $this->postParameters = $params;
        $this->fileUploadFormField = $fileUploadField;
    }

    /**
     * Set the form action endpoint
     * @param string $endpoint
     */
    public function setFormEndpoint($endpoint)
    {
        $this->formEndpoint = $endpoint;
    }

    /**
     * Login through a POST request to a website.
     * @param $postEndpoint
     * @param $postParams
     */
    public function postRequest($postEndpoint, $postParams)
    {
        $this->makePost($postEndpoint, $postParams);
    }

    /**
     * Make a post to a given endpoint with a given set of parameters. If none are provided use the ones set on the object instead.
     * @param string $formEndpoint
     * @param array $postParams
     * @param null $files
     * @return string
     */
    protected function makePost($formEndpoint = null, $postParams = null, $files = null)
    {
        /**
         * @var \Symfony\Component\BrowserKit\Client $client
         */
        $client = $this->session->getDriver()->getClient();
        if ($formEndpoint === null) {
            $formEndpoint = $this->formEndpoint;
        }
        if ($postParams === null) {
            $postParams = $this->postParameters;
        }
        if (empty($files)) {
            $files = array();
        }
        $client->request("POST", $formEndpoint, $postParams, $files);
        $response = $client->getInternalResponse()->getContent();
        return $response;
    }

    /**
     * Test the file upload via POST methods or return a acceptable file path to attach to a field
     * @return array|string
     * @throws \Exception
     */
    public function testFileUpload()
    {
        //todo finish the test method implementation
        $testFilesArray = $this->getTestFiles(self::TEST_FILES_DIRECTORY);
        switch (strtolower($this->testType)) {
            case 'post':
                foreach ($testFilesArray as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $key1 => $testTypeDir) {
                            if ((!empty($this->verifications) && in_array($key1, $this->verifications) && is_array($testTypeDir)) || (is_array($testTypeDir) && empty($this->verifications))) {
                                foreach ($testTypeDir as $key2 => $file) {
                                    $fullPathToFile = self::TEST_FILES_DIRECTORY . DIRECTORY_SEPARATOR . $key . DIRECTORY_SEPARATOR . $key1 . DIRECTORY_SEPARATOR . $file;
                                    $this->updatePostParams($file);
                                    $result[$key . "_" . $key1] = $this->makePost(null, null, array($fullPathToFile));
                                }
                            }
                        }
                    }
                }
                break;
            case 'browser':
                foreach ($testFilesArray as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $key1 => $testTypeDir) {
                            if ((!empty($this->verifications) && in_array($key1, $this->verifications) && is_array($testTypeDir)) || (is_array($testTypeDir) && empty($this->verifications))) {
                                foreach ($testTypeDir as $key2 => $file) {
                                    $fullPathToFile = self::TEST_FILES_DIRECTORY . DIRECTORY_SEPARATOR . $key . DIRECTORY_SEPARATOR . $key1 . DIRECTORY_SEPARATOR . $file;
                                    $usedFiles = file_get_contents(self::FILE_MONITOR);
                                    if (strpos($usedFiles, $fullPathToFile) !== false) {
                                        file_put_contents($usedFiles, $fullPathToFile);
                                        return $fullPathToFile;
                                    }
                                }
                            }
                        }
                    }
                }
                break;
            default:
                throw new \Exception("Indicated test type $this->testType is not supported! 'POST' or 'browser' are the accepted values.");
        }
        return $result;
    }

    /**
     * Get all the test files from the directory recursively
     * @param $dir
     * @return array|null
     */
    protected function getTestFiles($dir)
    {
        $result = null;
        $filesInDir = array_diff(scandir($dir), array('..', '.'));
        foreach ($filesInDir as $key => $value) {
            if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                $result[$value] = $this->getTestFiles($dir . DIRECTORY_SEPARATOR . $value);
            } else {
                $result[] = $value;
            }
        }
        return $result;
    }

    /**
     * Update the POST parameters
     * @param string $file Filename for the test file
     */
    protected function updatePostParams($file)
    {
        if ($this->hasCsrf) {
            $this->postParameters[$this->csrfTokenKey] = $this->csrfToken;
        }
        $this->postParameters[$this->fileUploadFormField] = $file;
    }

}