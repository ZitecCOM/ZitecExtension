<?php

namespace Zitec\ZitecExtension\Context;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Selector\SelectorsHandler;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\MinkExtension\Context\MinkAwareContext;
use Zitec\ZitecExtension\Session\ZitecAssert;
use Zitec\ZitecExtension\Session\Session;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Call\AfterStep;


class ZitecContext extends MinkContext implements MinkAwareContext
{

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
          $this->$key = (object) $param;
        } else {
          $this->$key = $param;
        }
      }
    }
  }

  /**
   * @param null $name
   * @return Session
   */
  public function getSession ($name = null)
  {
    return parent::getSession($name);
  }

  /**
   * @Given /^I press key "([^"]*)"$/
   */
  public function iPressKey($key)
  {
    $this->getSession()->pressKey($key);
  }

  /**
<<<<<<< HEAD
   * Mouse over elemenet with specified css.
=======
   * Mouse over element with specified css.
>>>>>>> Change functions that select random options.
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
   *  @When /^(?:|I )get the alert text$/
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

  public function makeScreenshot($filename = null, $filepath = null)
  {

    $filename = $filename ? : sprintf('%s.%s', date('H-i-s'), 'png');

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
   * take a string delimited by semicolon and return an array
   * @return array
   */
  protected function toArray($values)
  {
    $sourceArray = explode(";", trim($values));
    return array_map('trim', $sourceArray);
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

}
