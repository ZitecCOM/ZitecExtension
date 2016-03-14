<?php
/**
 * Created by PhpStorm.
 * User: bianca.vadean
 * Date: 1/6/2016
 * Time: 4:07 PM
 */

namespace Zitec\ZitecExtension\Session;

use Behat\Mink\WebAssert;
use Behat\Mink\Element\Element,
  Behat\Mink\Element\NodeElement,
  Behat\Mink\Exception\ElementNotFoundException,
  Behat\Mink\Exception\ExpectationException,
  Behat\Mink\Exception\ResponseTextException,
  Behat\Mink\Exception\ElementHtmlException,
  Behat\Mink\Exception\ElementTextException;

class ZitecAssert extends WebAssert
{
  /**
   * Checks that specific element is visible on the current page.
   *
   * @param string  $selectorType element selector type (css, xpath)
   * @param string  $selector     element selector
   * @param Element $container    document to check against
   *
   * @return NodeElement
   *
   * @throws ElementNotFoundException
   */
  public function elementVisible($selectorType, $selector, Element $container = null)
  {
    $container = $container ? : $this->session->getPage();
    $node = $container->find($selectorType, $selector)->isVisible();

    if (null === $node) {
      throw new ElementNotFoundException($this->session, 'element', $selectorType, $selector);
    }

    return $node;
  }

  /**
   * Checks that specific element is not visible on the current page.
   *
   * @param string  $selectorType element selector type (css, xpath)
   * @param string  $selector     element selector
   * @param Element $container    document to check against
   *
   * @throws ExpectationException
   */
  public function elementNotVisible($selectorType, $selector, Element $container = null)
  {
    $container = $container ? : $this->session->getPage();
    $node = $container->find($selectorType, $selector)->isVisible();

    if (null !== $node) {
      $message = sprintf('An element matching %s "%s" is visible on this page, but it should not.', $selectorType, $selector);
      throw new ExpectationException($message, $this->session);
    }
  }

  /**
   * Checks that specific element has text.
   *
   * @param string $selectorType element selector type (css, xpath)
   * @param string $selector     element selector
   * @param string $text         expected text
   *
   * @throws ElementTextException
   */
  public function elementTextIs($selectorType, $selector, $text)
  {
    $element = $this->elementExists($selectorType, $selector);
    $actual = $element->getText();

    if ($actual != trim($text)) {
      $message = sprintf('The text "%s" is different from the text found in the element matching %s "%s".', $text, $selectorType, $selector);
      throw new ElementTextException($message, $this->session, $element);
    }
  }

  /**
   * Checks that an attribute exists in an element.
   *
   * @param $selectorType
   * @param $selector
   * @param $attribute
   * @return NodeElement|null
   * @throws ElementHtmlException
   */
  public function elementAttributeExists($selectorType, $selector, $attribute)
  {
    $element = $this->elementExists($selectorType, $selector);

    if (!$element->hasAttribute($attribute)) {
      $message = sprintf('The attribute "%s" was not found in the element matching %s "%s".', $attribute, $selectorType, $selector);
      throw new ElementHtmlException($message, $this->session, $element);
    }

    return $element;
  }

  /**
   * Checks that an attribute of a specific elements contains text.
   *
   * @param $selectorType
   * @param $selector
   * @param $attribute
   * @param $text
   *
   * @throws ElementHtmlException
   */
  public function elementAttributeContains($selectorType, $selector, $attribute, $text)
  {
    $element = $this->elementAttributeExists($selectorType, $selector, $attribute);
    $actual = $element->getAttribute($attribute);
    $regex = '/' . preg_quote($text, '/') . '/ui';

    if (!preg_match($regex, $actual)) {
      $message = sprintf('The text "%s" was not found in the attribute "%s" of the element matching %s "%s".', $text, $attribute, $selectorType, $selector);
      throw new ElementHtmlException($message, $this->session, $element);
    }
  }

  /**
   * Checks that an attribute of a specific elements does not contain text.
   *
   * @param $selectorType
   * @param $selector
   * @param $attribute
   * @param $text
   *
   * @throws ElementHtmlException
   */
  public function elementAttributeNotContains($selectorType, $selector, $attribute, $text)
  {
    $element = $this->elementAttributeExists($selectorType, $selector, $attribute);
    $actual = $element->getAttribute($attribute);
    $regex = '/' . preg_quote($text, '/') . '/ui';

    if (preg_match($regex, $actual)) {
      $message = sprintf('The text "%s" was found in the attribute "%s" of the element matching %s "%s".', $text, $attribute, $selectorType, $selector);
      throw new ElementHtmlException($message, $this->session, $element);
    }
  }
}
