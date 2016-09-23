<?php
/**
 * File upload helper class
 * @author Bogdan Grigore <bogdan.grigore@zitec.com>
 */

namespace Zitec\ZitecExtension\FileUpload;

use Zitec\ZitecExtension\Session;


class UploadHelper
{
    private static $helper;
    private $verifications;
    private $testType;
    private $hasCsrf;
    private $csrfTokenKey;
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
     * Set the HTML of the page where to find the CSRF token to use in the POST requests
     * @param string $html
     */
    public function setCsrfHtml($html)
    {
        $this->csrfHtml = $html;
    }

    /**
     * Find the CSRF token in the given HTML
     */
    public function findCsrfToken()
    {
        //todo determine a way to get the CSRF token from the page
        //todo determine ways to circumvent CSRF protection that can be circumvented
        $a = strpos($this->csrfHtml, $this->csrfTokenKey);
        if($a == false) {
            throw new \Exception("The CSRF key was not found on the page!");
        }
        $b = explode($this->csrfTokenKey, $this->csrfHtml);
        $this->csrfToken = $result;
    }

    /**
     * Set the browser session, post params and form endpoint URL to the helper
     * @param Session\Session $session Session of the current browser for the test
     * @param string $endpoint Form endpoint - where form sends its request
     * @param string $fileUploadField Name of the form upload field 
     * @param array $params Other required form params for the POST request
     */
    public function setSessionAndPostParams($session, $endpoint, $fileUploadField, $params = null) {
        $this->session = $session;
        $this->formEndpoint = $endpoint;
        $this->postParameters = $params;
        $this->fileUploadFormField = $fileUploadField;
    }

    protected function makePost()
    {
        /**
         * @var \Symfony\Component\BrowserKit\Client $client
         */
        $client = $this->session->getDriver()->getClient();
        $client->request("POST", $this->formEndpoint, $this->postParameters);
    }

}