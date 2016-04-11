<?php
/**
 * Created by PhpStorm.
 * User: bianca.vadean
 * Date: 1/5/2016
 * Time: 5:14 PM
 */

namespace Zitec\ZitecExtension\Context;

use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Zitec\ZitecExtension\Context\ZitecContext;


class BaseContext extends PageObjectContext implements SnippetAcceptingContext
{
  /**
   * @var ZitecContext
   */
  private $minkContext;

  /** @BeforeScenario */
  public function gatherContexts(BeforeScenarioScope $scope)
  {

    $environment = $scope->getEnvironment();
    $this->minkContext = $environment->getContext('Zitec\ZitecExtension\Context\ZitecContext');
  }

  /**
   * @return ZitecContext
   */
  public function getMinkContext ()
  {
    return $this->minkContext;
  }
}
