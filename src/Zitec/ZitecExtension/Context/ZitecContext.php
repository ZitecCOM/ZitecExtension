<?php

namespace Zitec\ZitecExtension\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\MinkExtension\Context\MinkAwareContext;
use Symfony\Component\BrowserKit\Cookie;
use Zitec\ZitecExtension\FileUpload\UploadHelper;
use Zitec\ZitecExtension\Session\ZitecAssert;
use Zitec\ZitecExtension\Session\Session;
use Behat\Behat\Hook\Scope\AfterStepScope;


class ZitecContext extends MinkContext implements MinkAwareContext
{
    /**
     * @var UploadHelper $fileUploadHelper
     */
    protected $fileUploadHelper;

    /**
     * @var array $allowedFileUploadExtensions
     */
    protected $allowedFileUploadExtensions;

    /**
     * Take data (if there is any) declared under parameters line in yml file configuration
     * and create object
     *
     * @param array $parameters
     */
    public function __construct(array $parameters = null)
    {
        if (!empty($parameters)) {
            foreach ($parameters as $key => $param) {
                if (is_array($param)) {
                    $this->$key = new \stdClass();
                    $this->$key = (object)$param;
                } else {
                    $this->$key = $param;
                }
            }
        }
    }

    /**
     * @Given /^I press key "([^"]*)"$/
     */
    public function iPressKey($key)
    {
        $this->getSession()->pressKey($key);
    }

    /**
     * @param null $name
     * @return Session
     */
    public function getSession($name = null)
    {
        return parent::getSession($name);
    }

    /**
     * <<<<<<< HEAD
     * Mouse over elemenet with specified css.
     * =======
     * Mouse over element with specified css.
     * >>>>>>> Change functions that select random options.
     *
     * @When /^(?:|I )put the mouse over "(?P<element>(?:[^"]|\\")*)"$/
     */
    public function mouseOver($element)
    {
        $element = $this->fixStepArgument($element);
        $this->getSession()->getPage()->find('css', $element)->mouseOver();
    }

    /**
     * @Given /^(?:|I )wait ([0-9]*) second(?:|s)$/
     */
    public function iWaitSeconds($ms)
    {
        $this->getSession()->wait($ms * 1000);
    }

    /**
     * @Given /^(?:|I )fill in "([^"]*)" from "([^"]*)" with "([^"]*)"$/
     */
    public function iFillInFromWith($field, $locator, $value)
    {
        $page = $this->getSession()->getPage();
        $div = $page->find('css', $locator);
        $div->fillField($field, $value);
    }

    /**
     * Clicks link with specified xPath.
     *
     * @Given /^(?:|I )click on the element with xPath "([^"]*)"$/
     */
    public function iClickOnTheElementWithXPath($xpath)
    {
        $xpath = $this->fixStepArgument($xpath);
        $session = $this->getSession();
        $element = $session->getPage()->find('xpath', $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath));
        $element->click();
    }

    /**
     * Clicks link with specified CSS selector.
     *
     * @Given /^(?:|I )click on the element "([^"]*)"$/
     */
    public function iClickOnTheElement($locator)
    {
        $locator = $this->fixStepArgument($locator);
        $session = $this->getSession();
        $element = $session->getPage()->find('css', $locator);
        if (null === $element) {
            throw new ElementNotFoundException($this->getSession(), 'element', 'css', $locator);
        }
        $element->click();
    }

    /**
     * @Then /^I click on all "([^"]*)" elements$/
     */
    public function iClickOnAllElements($locator)
    {
        $locator = $this->fixStepArgument($locator);
        $session = $this->getSession();
        $elements = $session->getPage()->findAll('css', $locator);
        foreach ($elements as $element) {
            $element->click();
        }
    }

    /**
     * Fills in form field with specified css selector.
     *
     * @Given /^(?:|I )fill in "([^"]*)" element with "([^"]*)"$/
     */
    public function iFillInElementWith($element, $value)
    {
        $element = $this->fixStepArgument($element);
        $page = $this->getSession()->getPage();
        $field = $page->find('css', $element);
        $field->setValue($value);
    }

    /**
     *  id|title|alt|text
     *
     * @Given /^(?:|I )follow "([^"]*)" from the "([^"]*)" element$/
     */
    public function iFollowInTheElement($link, $element)
    {
        $link = $this->fixStepArgument($link);
        $element = $this->fixStepArgument($element);
        $page = $this->getSession()->getPage();
        $div = $page->find('css', $element);
        $div->clickLink($link);
    }

    /**
     * Closes current page.
     *
     * @When /^(?:|I )close the page$/
     */
    public function close()
    {
        $this->getSession()->close();
    }

    /**
     * Accepts the currently displayed alert dialog.
     *
     * @When /^(?:|I )accept the alert$/
     */
    public function acceptAlert()
    {
        $this->getSession()->acceptAlert();
    }

    /**
     * Dismiss the currently displayed alert dialog.
     *
     * @When /^(?:|I )dismiss the alert$/
     */
    public function dismissAlert()
    {
        $this->getSession()->dismissAlert();
    }

    /**
     * Fill in the current prompt dialog.
     *
     * @When /^(?:|I )fill in alert with "([^"]*)"$/
     */
    public function setAlertText($value)
    {
        $value = $this->fixStepArgument($value);
        $this->getSession()->setAlertText($value);
    }

    /**
     * Get the text in the dialog.
     *
     * @When /^(?:|I )get the alert text$/
     */
    public function getAlertText()
    {
        return $this->getSession()->getAlertText();
    }

    /**
     * Selects a random option from a select with specified id|name|label|value.
     *
     * @When /^(?:|I )select a random option from "(?P<select>(?:[^"]|\\")*)"$/
     */
    public function selectRandomOption($select)
    {
        $select = $this->fixStepArgument($select);
        $session = $this->getSession()->getPage();
        $options = $session->findField($select)->findAll('css', 'option');
        if (empty($options)) {
            throw new ElementNotFoundException($this->getSession(), 'select option', 'random option', $select);
        }

        $randomOption = array_rand($options, 1);
        $value = $this->fixStepArgument($options[$randomOption]->getValue());
        $this->getSession()->getPage()->selectFieldOption($select, $value);
    }

    /**
     * Selects a random option from a select with specified id|name|label|value.
     * excluding specified options delimited by semicolon
     *
     * @When /^(?:|I )select a random option from "(?P<select>(?:[^"]|\\")*)", excluding "([^"]*)"$/
     */
    public function selectRandomOptionExcluding($select, $excluding)
    {
        $select = $this->fixStepArgument($select);
        $excluding = $this->toArray($excluding);
        $session = $this->getSession()->getPage();
        $options = $session->findField($select)->findAll('css', 'option');
        while (count($options)) {
            $randIndex = array_rand($options);
            $option = $options[$randIndex];
            if (!in_array($option->getText(), $excluding)) {
                $optionValue = $option->getValue();
                break;
            }
            unset($options[$randIndex]);
        }

        if (!isset($optionValue)) {
            throw new ElementNotFoundException($this->getSession(), 'select option', 'random option', $select);
        }
        $this->getSession()->getPage()->selectFieldOption($select, $optionValue);
    }

    /**
     * take a string delimited by semicolon and return an array
     * @return array
     */
    protected function toArray($values)
    {
        $sourceArray = explode(";", trim($values));
        return array_map('trim', $sourceArray);
    }

    /**
     * Select radio button with specified id|name|title|alt|value.
     *
     * @Given /^(?:|I )select the "([^"]*)" radio button$/
     */
    public function iSelectTheRadioButton($radioLabel)
    {
        $radioButton = $this->getSession()->getPage()->findField($radioLabel);
        if (null === $radioButton) {
            throw new ElementNotFoundException($this->getSession(), 'form field', 'id|name|label|value', $radioLabel);
        }
        $this->getSession()->getDriver()->click($radioButton->getXPath());
    }

    /**
     * @Given /^I move to the new window$/
     */
    public function iMoveToTheNewWindow()
    {
        $session = $this->getSession();

        //retrive from session all the windows IDs that were opened by the active session
        $allWindows = $session->getWindowNames();

        //retrieve from session the active windows ID
        $currentWindowId = $session->getWindowName();

        //search in all session the current window
        $currentWindowKey = array_search($currentWindowId, $allWindows, true);
        //compute the next window
        $nextWindowKey = $currentWindowKey + 1;

        //move to next window
        if (key_exists($nextWindowKey, $allWindows)) {
            $session->switchToWindow($allWindows[$nextWindowKey]);
        } else {
            throw new \Exception("Cannot select new window because was not found.");
        }
    }

    /**
     * @Given /^I move to the first window$/
     */
    public function iMoveToThePreviousWindow()
    {
        $session = $this->getSession();

        //retrive from session all the windows IDs that were opened by the active session
        $allWindows = $session->getWindowNames();
        //move to first opened window
        $session->switchToWindow($allWindows[0]);
    }

    /**
     * Checks, that element with specified CSS is visible on page.
     *
     * @Then /^(?:|I )should see an? "(?P<element>[^"]*)" element visible on page$/
     */
    public function assertElementVisible($element)
    {
        $this->assertSession()->elementVisible('css', $element);
    }

    /**
     * Returns session asserter.
     *
     * @param Session|string $session session object or name
     *
     * @return ZitecAssert
     */
    public function assertSession($session = null)
    {
        if (!($session instanceof Session)) {
            $session = $this->getSession($session);
        }
        return new ZitecAssert($session);
    }

    /**
     * Checks, that element with specified CSS is not visible on page.
     *
     * @Then /^(?:|I )should not see an? "(?P<element>[^"]*)" element visible on page$/
     */
    public function assertElementNotVisibleOnPage($element)
    {
        $this->assertSession()->elementNotVisible('css', $element);
    }

    /**
     * Checks, that element with specified CSS has specified text.
     *
     * @Then /^(?:|I )should see exactly "(?P<text>(?:[^"]|\\")*)" in the "(?P<element>[^"]*)" element$/
     */
    public function assertElementIsText($element, $text)
    {
        $this->assertSession()->elementTextIs('css', $element, $this->fixStepArgument($text));
    }

    /**
     * @Then /^(?:|I )take a screenshot$/
     */
    public function iTakeAScreenshot()
    {
        $this->makeScreenshot();
    }

    public function makeScreenshot($filename = null, $filepath = null)
    {

        $filename = $filename ?: sprintf('%s.%s', date('H-i-s'), 'png');

        if ($this->isJenkins()) {
            $jenkinsDetails = $this->getJenkinsDetails();
            $filepath = sprintf('/var/lib/jenkins/jobs/%s/workspace/%s_screenshots', $jenkinsDetails['job_name'], $jenkinsDetails['build_number']);
        } else {
            $filepath = 'screenshots_' . date('d-m-Y_H');
        }

        if (!file_exists($filepath)) {
            mkdir($filepath, 0755, true);
        }

        $entirePath = $filepath . '/' . $filename;
        $this->saveScreenshot($filename, $filepath);
        print("\n Screenshot saved to " . $entirePath . "\n");
    }

    /**
     * check if the build is triggered by Jenkins
     * @return boolean
     * */
    protected function isJenkins()
    {
        if (!getenv('BUILD_NUMBER')) {
            return false;
        }
        return true;
    }

    /**
     * retrieve from JENKINS variables BUILD_NUMBER and JOB_NAME
     *
     * @return array
     * */
    protected function getJenkinsDetails()
    {
        if ($this->isJenkins()) {
            return array(
                "build_number" => getenv("BUILD_NUMBER"),
                "job_name" => getenv("JOB_NAME")
            );
        }
        return false;
    }

    /**
     * Pauses the scenario until the user presses a key.
     * Useful when debugging a scenario.
     *
     * @Then /^(?:|I )put a breakpoint$/
     */
    public function iPutABreakpoint()
    {
        fwrite(STDOUT, "\033[s    \033[93m[Breakpoint] Press \033[1;93m[RETURN]\033[0;93m to continue...\033[0m");
        while (fgets(STDIN, 1024) == '') {

        }
        fwrite(STDOUT, "\033[u");

        return;
    }

    /** @BeforeScenario */
    public function before($event)
    {
        // check if window size was defined in the config file
        if (property_exists($this, 'size')) {
            $this->size !== false ? $this->setWindowSize($this->size) : false;
        }
    }

    /**
     * set the window size
     *
     * $param string width x height
     * */
    protected function setWindowSize($size)
    {
        //maximize window only if is a Selenium2Driver instance
        if (($this->getSession()->getDriver() instanceof \Behat\Mink\Driver\Selenium2Driver)) {
            if ($size == 'maximized') {
                $this->getSession()->getDriver()->maximizeWindow();
            } else {
                $dim = explode('x', $size);
                $this->getSession()->resizeWindow(intval($dim[0]), intval($dim[1]));
            }
        }
    }
//
//    /**
//     * @AfterFeature
//     */
//    public function afterFormUpload($event)
//    {
//        if ($this->fileUploadHelper instanceof UploadHelper && $this->fileUploadHelper->getTestType() === "browser") {
//            $file = fopen(UploadHelper::FILE_MONITOR, "w+");
//            fclose($file);
//        }
//    }

    /**
     * @AfterStep
     */
    public function failScreenshots(AfterStepScope $scope)
    {
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            if (!$scope->getTestResult()->isPassed() && property_exists($this, "screenshots")) {
                if ($this->screenshots->onfail == true) {
                    $scenarioLine = str_replace(' ', '_', $scope->getStep()->getLine());
                    $filename = sprintf('fail_on_line_%s_%s.png', $scenarioLine, date('H-i-s'));
                    $this->makeScreenshot($filename);
                }
            }
        }
    }

    /**
     * The keys from the table node must be the same like the ones in the $fileUploadTestSetup array
     * @Then /^(?:|I )setup the file uploading test with the following components:$/
     */
    public function iSetupTheFileUploadTest(TableNode $tableNode)
    {
        //define what values SHOULD ALWAYS be sent by the table node
        $mandatoryValues = array("allowed_extensions");
        //default values for the helper
        $fileUploadTestSetup = array(
            "allowed_extensions" => null,
            "verifications" => null,
            "test_type" => null,
            "csrf" => null,
        );
        $table = $tableNode->getTable();
        foreach ($table as $item => $value) {
            foreach ($value as $key => $fileUploadSettingOrValue) {
                if ($key % 2 == 0) {
                    if (($a = array_search($fileUploadSettingOrValue, $mandatoryValues)) !== false) {
                        unset($mandatoryValues[$a]);
                    }
                    $fileUploadTestSetup[$fileUploadSettingOrValue] = $value[$key + 1];
                }
            }
        }
        if (!empty($mandatoryValues)) {
            throw new \Exception("Not all mandatory values were sent through the table node! Please review the sent values!");
        }
        $this->allowedFileUploadExtensions = explode(",", $fileUploadTestSetup["allowed_extensions"]);
        $verifications = explode(',', $fileUploadTestSetup["verifications"]);
        if (!$this->fileUploadHelper instanceof UploadHelper) {
            $this->fileUploadHelper = UploadHelper::getInstance();
        }
        if (is_array($verifications)) {
            $this->fileUploadHelper->setVerifications($verifications);
        }
        $this->fileUploadHelper->setTestType($fileUploadTestSetup["test_type"]);
        $this->fileUploadHelper->setCsrfExistence((bool)$fileUploadTestSetup["csrf"]);
    }

    /**
     * The key provided must be the name attribute of the field that contains the CSRF token
     * @Then /^(?:|I )process the page to find the CSRF token with the key "([^"]*)"$/
     */
    public function iProcessTheHtmlContentForFileUpload($csrfTokenKey)
    {
        if (!$this->fileUploadHelper instanceof UploadHelper) {
            $this->fileUploadHelper = UploadHelper::getInstance();
        }
        $this->fileUploadHelper->setCsrfTokenKey($csrfTokenKey);
        $this->fileUploadHelper->setCsrfToken($this->findCsrfToken($csrfTokenKey));
    }

    /**
     * Find the CSRF token for the form POST test to work
     * @param string $csrfTokenKey Name of the input containing the CSRF token
     * @return string
     * @throws \Exception
     */
    protected function findCsrfToken($csrfTokenKey)
    {
        $csrfInput = $this->getSession()->getPage()->find('css', 'input[name="' . $csrfTokenKey . '"]');
        if (!empty($csrfInput)) {
            $result = $csrfInput->getValue();
        } else {
            throw new \Exception("CSRF Input element not found on page! Check that you are on the correct page and that you indicated the correct input element name!");
        }
        if ($result === null) {
            throw new \Exception('No suitable value attribute was found to extract the CSRF token from!');
        }
        return $result;
    }

    /**
     * Two parameters are mandatory in the table node: endpoint and file_upload_field
     *
     * @Then /^(?:|I )prepare the post requests with the following parameters:$/
     */
    public function iSetTheFileUploadFormParams(TableNode $tableNode)
    {
        $postParams = array();
        $session = $this->getSession();
        $foundEndPoint = false;
        $foundFileUploadField = false;
        //Process the tableNode and retrieve the post param array
        foreach ($tableNode->getTable() as $item => $value) {
            foreach ($value as $key => $postParamNameOrValue) {
                if ($key % 2 == 0) {
                    if ($postParamNameOrValue == "endpoint") {
                        $foundEndPoint = $value[$key + 1];
                    } else if ($postParamNameOrValue == "file_upload_field") {
                        $foundFileUploadField = $value[$key + 1];
                    } else {
                        $postParams[$postParamNameOrValue] = $value[$key + 1];
                    }
                }
            }
        }
        if (!$this->fileUploadHelper instanceof UploadHelper) {
            $this->fileUploadHelper = UploadHelper::getInstance();
        }
        if ($foundFileUploadField == false) {
            throw new \Exception("file_upload_field is a required parameter! Please revise the test data!");
        }
        if ($foundEndPoint == false && !$this->fileUploadHelper->hasFormEndpoint()) {
            throw new \Exception("endpoint is a required parameter! Please revise the test data!");
        }
        $this->fileUploadHelper->setSessionAndPostParams($session, $foundEndPoint, $foundFileUploadField, $postParams);
    }

    /**
     * @Then /^(?:|I )add a new cokkie with:$/
     */
    public function iAddTheCookie(TableNode $tableNode)
    {
        foreach ($tableNode->getTable() as $item => $value) {
            foreach ($value as $key => $cookieStuff) {
                if ($key % 2 == 0) {
                    if ($cookieStuff = "name") {
                        $cookieName = $value[$key + 1];
                    }
                    if ($cookieStuff = "value") {
                        $cookieValue = $value[$key + 1];
                    }
                    if ($cookieStuff = "domain") {
                        $domain = $value[$key + 1];
                    }
                }
            }
        }
        $cookie = new Cookie($cookieName, $cookieValue, null, null, $domain);
        $this->getSession()->getDriver()->getClient()->getCookieJar()->set($cookie);
    }

    /**
     * Make a POST request to an endpoint with a set of params. You need to provide the name attribute and value of the credential field
     * @Then /^(?:|I )make a POST request to "([^"]*)" with:$/
     */
    public function iMakeAPostRequestToWith($endPoint, TableNode $tableNode)
    {
        $credentials = array();
        foreach ($tableNode->getTable() as $item => $value) {
            foreach ($value as $key => $credentialsStuff) {
                if ($key % 2 == 0) {
                    $credentials[$credentialsStuff] = $value[$key + 1];
                }
            }
        }
        if (!$this->fileUploadHelper instanceof UploadHelper) {
            $this->fileUploadHelper = UploadHelper::getInstance();
        }
        $this->fileUploadHelper->postRequest($endPoint, $credentials);
    }

    /**
     * Send in the table node "success" and "failure" texts which are taken from the resulting page after the upload to verify if the action had the expected result
     * Success placeholders: filename (to use file name as a success message)
     * Failure placeholders: no_success (to verify the lack of the success message)
     * 
     * @Then /^(?:|I )test the file upload for the given test parameters:$/
     */
    public function iTestTheFileUpload(TableNode $tableNode)
    {
        $successFound = null;
        $failFound = null;
        $successPlaceholder = false;
        $failurePlaceholder = false;
        foreach ($tableNode->getTable() as $item => $value) {
            foreach ($value as $key => $successFailure) {
                if ($key % 2 == 0) {
                    if ($successFailure === "success") {
                        $successFound = $value[$key + 1];
                    }
                    if ($successFailure === "failure") {
                        $failFound = $value[$key + 1];
                    }
                }
            }
        }
        if (empty($successFound)) {
            throw new \Exception("No success text to validate the result of the form upload!");
        }
        if (empty($failFound)) {
            throw new \Exception("No failure text to validate the result of the form upload!");
        }
        if (!$this->fileUploadHelper instanceof UploadHelper) {
            throw new \Exception("File upload helper not setup! Please setup all the required parameters in order to test the file upload!");
        }
        foreach ($this->fileUploadHelper->testFileUpload() as $testTypeAndExt => $result) {
            if ($successFound === 'filename' || $successPlaceholder === true) {
                $successFound = $this->fileUploadHelper->getUploadedFileNameForTest($testTypeAndExt);
                $successPlaceholder = true;
            }
            $breakDown = explode("_", $testTypeAndExt);
            if (in_array($breakDown[0], $this->allowedFileUploadExtensions) && $breakDown[1] === "valid") {
                if (strpos($result, $successFound) === false) {
                    throw new \Exception("Success scenario for the following extension-verification combo: '" . $breakDown[0] . "-" . $breakDown[1] . "' has failed. Success text $successFound not found on resulting page");
                }
            } else {
                if ($failFound === 'no_success' || $failurePlaceholder === true) {
                    $failFound = $successFound;
                    $failurePlaceholder = true;
                    if (strpos($result, $failFound) !== false) {
                        throw new \Exception("Failure scenario for the following extension: '" . $breakDown[0] . "-" . $breakDown[1] . "' has failed. Success text $successFound identified on the page!");
                    }
                } else {
                    if (strpos($result, $failFound) === false) {
                        throw new \Exception("Failure scenario for the following extension: '" . $breakDown[0] . "-" . $breakDown[1] . "' has failed. Failure text $failFound not found on resulting page");
                    }
                }

            }
        }
    }

    /**
     * @Then /^(?:|I )try to obtain the form action URL from the form with the ID or name "([^"]*)"$/
     */
    public function iGetEndpointFromFormByIdOrName($identifier)
    {
        if (!$this->fileUploadHelper instanceof UploadHelper) {
            $this->fileUploadHelper = UploadHelper::getInstance();
        }
        if ($element = $this->getSession()->getPage()->find('css', "#$identifier")) {
            $this->fileUploadHelper->setFormEndpoint($element->getAttribute("action"));
        } else if ($element = $this->getSession()->getPage()->find('css', "form[name='$identifier']")) {
            $this->fileUploadHelper->setFormEndpoint($element->getAttribute("action"));
        } else {
            throw new \Exception("Form with the specified identifier $identifier not found!");
        }
    }

}
