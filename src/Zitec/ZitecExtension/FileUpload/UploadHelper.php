<?php
/**
 * File upload helper class
 * @author Bogdan Grigore <bogdan.grigore@zitec.com>
 */

namespace Zitec\ZitecExtension\FileUpload;

use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Element\NodeElement;
use Zitec\ZitecExtension\Session;


class UploadHelper
{
    const TEST_FILES_DIRECTORY = __DIR__ . DIRECTORY_SEPARATOR . 'Files';
    private static $helper;
    private $verifications;
    private $testType;
    private $hasCsrf;
    private $csrfTokenKey;
    /**
     * @var DocumentElement | NodeElement $csrfHtml
     */
    private $csrfHtml;
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
    }

    /**
     * Set the page or Mink Element specific where you will find the CSRF token
     * @param $html
     */
    public function setCsrfElement($html)
    {
        $this->csrfHtml = $html;
    }

    /**
     * Find the CSRF token in the given HTML
     */
    public function findCsrfToken()
    {
        //todo determine ways to circumvent CSRF protection that can be circumvented
        $csrfInput = $this->csrfHtml->find('css', 'input[name="' . $this->csrfTokenKey . '"]');
        if(!empty($csrfInput)) {
            $result = $csrfInput->getValue();
        } else {
            throw new \Exception("CSRF Input element not found on page! Check that you are on the correct page and that you indicated the correct input element name!");
        }
        if ($result === null) {
            throw new \Exception('No suitable value attribute was found to extract the CSRF token from!');
        }
        $this->csrfToken = $result;
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
        $this->formEndpoint = $endpoint;
        $this->postParameters = $params;
        $this->fileUploadFormField = $fileUploadField;
    }

    /**
     * Login through a POST request to a website.
     * @param $postEndpoint
     * @param $postParams
     */
    public function postRequest($postEndpoint, $postParams)
    {
        if(isset($this->csrfToken)) {
            $postParams[$this->csrfTokenKey] = $this->csrfToken;
        }
        $this->makePost($postEndpoint, $postParams);
    }

    /**
     * Make a post to a given endpoint with a given set of parameters. If none are provided use the ones set on the object instead.
     * @param string $formEndpoint
     * @param array $postParams
     * @param null $files
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
        if(empty($files)) {
            $files = array();
            if(isset($postParams["multi"]) && $postParams["multi"] === "da") {
                $server = array("Content-Type" => "multipart/form-data");
                unset($postParams["multi"]);
            } else {
                $server = array();
            }

        } else {
            $server = array("Content-Type" => "multipart/form-data");
        }
        $client->request("POST", $formEndpoint, $postParams, $files, $server);
    }

    /**
     * Test the file upload
     */
    public function testFileUpload()
    {
        $testFilesArray = $this->getTestFiles(self::TEST_FILES_DIRECTORY);
        switch (strtolower($this->testType)) {
            case 'post':
                foreach ($testFilesArray as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $key1 => $testTypeDir) {
                            if ($key1 === 'valid' && is_array($testTypeDir)) {
                                foreach ($testTypeDir as $key2 => $file) {
                                    if ($this->hasCsrf) {
                                        $this->postParameters[$this->csrfTokenKey] = $this->csrfToken;
                                    }
                                    $fullPathToFile = self::TEST_FILES_DIRECTORY . DIRECTORY_SEPARATOR . $key . DIRECTORY_SEPARATOR . $key1 . DIRECTORY_SEPARATOR . $file;
                                    $this->postParameters[$this->fileUploadFormField] = $file;
                                    $this->makePost(null, null, array($fullPathToFile));
                                }
                            }
                        }
                    }
                }
        }
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

}