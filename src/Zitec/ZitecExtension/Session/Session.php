<?php
/**
 * Created by PhpStorm.
 * User: bianca.vadean
 * Date: 12/15/2015
 * Time: 4:04 PM
 */

namespace Zitec\ZitecExtension\Session;

use \Behat\Mink\Session as MinkSession;
use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Selector\SelectorsHandler;


class Session extends MinkSession
{
  public function __construct (DriverInterface $driver, SelectorsHandler $selectorsHandler)
  {
    parent::__construct($driver, $selectorsHandler);
  }

  /**
   * Accepts the currently displayed alert dialog.
   *
   * Usually, this is equivalent to clicking on the 'OK' button in the dialog.
   */
  public function acceptAlert()
  {
    return $this->getDriver()->acceptAlert();
  }

  /**
   * Dismiss currently displayed alert dialog.
   *
   * Usually, this is equivalent to clicking on the 'X' button in the dialog.
   */
  public function dismissAlert()
  {
    return $this->getDriver()->dismissAlert();
  }

  /**
   * Get alert dialog text.
   *
   * @return string
   */
  public function getAlertText()
  {
    return $this->getDriver()->getAlertText();
  }

  /**
   * Set prompt dialog text.
   *
   * @param string $text
   */
  public function setAlertText($text)
  {
    return $this->getDriver()->setAlertText($text);
  }
  /**
   * Close current window
   */
  public function close()
  {
    return $this->getDriver()->close();
  }

  /**
   * Return an opaque handle to this window that uniquely identifies it within this driver instance.
   *
   * @return string
   */
  public function getWindowName()
  {
    return $this->getDriver()->getWindowName();
  }

  /**
   * Return a set of window handles which can be used to iterate over all open windows of this
   * webdriver instance by passing them to switchToWindow()
   *
   * @return array
   */
  public function getWindowNames()
  {
    return $this->getDriver()->getWindowNames();
  }

  /**
   * Presses specific keyboard key.
   *
   * @param   mixed   $key       must be a button from keyboard (ENTER for example)
   */
  public function pressKey($key)
  {
    return $this->getDriver()->pressKey($key);
  }


}
