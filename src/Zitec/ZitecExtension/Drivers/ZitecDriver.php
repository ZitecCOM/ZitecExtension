<?php

namespace Zitec\ZitecExtension\Drivers;

use \Behat\Mink\Driver\Selenium2Driver as SeleniumDriver;

class ZitecDriver extends SeleniumDriver
{

//  public function __construct ($browserName, $desiredCapabilities, $wdHost)
//  {
//    parent::__construct($browserName, $desiredCapabilities, $wdHost);
//  }

  /**
   * Accepts the currently displayed alert dialog.
   *
   * Usually, this is equivalent to clicking on the 'OK' button in the dialog.
   */
  public function acceptAlert()
  {
    $this->getWebDriverSession()->accept_alert();
  }


  /**
   * Dismiss currently displayed alert dialog.
   *
   * Usually, this is equivalent to clicking on the 'X' button in the dialog.
   */
  public function dismissAlert()
  {
    $this->getWebDriverSession()->dismiss_alert();
  }


  /**
   * Get alert dialog text.
   *
   * @return string
   */
  public function getAlertText()
  {
    return $this->getWebDriverSession()->getAlert_text();
  }

  /**
   * Set prompt dialog text.
   *
   * @param string $text
   */
  public function setAlertText($text)
  {
    $this->getWebDriverSession()->postAlert_text(array('text' => $text));
  }

  /**
   * Close current window
   */
  public function close()
  {
    $this->getWebDriverSession()->window();
  }

  /**
   * Presses specific keyboard key.
   *
   * @param   mixed   $key       must be a button from keyboard (ENTER for example)
   */
  public function pressKey($key)
  {
    $allKeys = new \ReflectionClass('WebDriver\Key');
    $availableKeys = $allKeys->getConstants();
    if (!key_exists($key, $availableKeys)) {
      $key = array('value' => array($key));
    } else {
      $key = array('value' => array($availableKeys[$key]));
    }

    return $this->getWebDriverSession()->keys($key);
  }

  /**
   * {@inheritdoc}
   */
  public function getId($xpath)
  {
    return $this->getWebDriverSession()->element('xpath', $xpath)->getID();
  }
}

