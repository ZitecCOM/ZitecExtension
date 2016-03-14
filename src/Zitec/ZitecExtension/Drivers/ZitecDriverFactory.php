<?php
/**
 * Created by PhpStorm.
 * User: bianca.vadean
 * Date: 1/7/2016
 * Time: 3:13 PM
 */

namespace Zitec\ZitecExtension\Drivers;

use Behat\MinkExtension\ServiceContainer\Driver\Selenium2Factory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Definition;



class ZitecDriverFactory extends Selenium2Factory
{

  public function getDriverName ()
  {
    return 'zitec_driver';
  }

  public function buildDriver (array $config)
  {
    if(!class_exists('Zitec\ZitecExtension\Drivers\ZitecDriver')) {
      throw new \RuntimeException(sprintf(
        'Install ZitecExtension in order to use %s driver.',
        $this->getDriverName()
      ));
    }

    $extraCapabilities = $config['capabilities']['extra_capabilities'];
    unset($config['capabilities']['extra_capabilities']);

    if (getenv('TRAVIS_JOB_NUMBER')) {
      $guessedCapabilities = array(
        'tunnel-identifier' => getenv('TRAVIS_JOB_NUMBER'),
        'build' => getenv('TRAVIS_BUILD_NUMBER'),
        'tags' => array('Travis-CI', 'PHP '.phpversion()),
      );
    } elseif (getenv('JENKINS_HOME')) {
      $guessedCapabilities = array(
        'tunnel-identifier' => getenv('JOB_NAME'),
        'build' => getenv('BUILD_NUMBER'),
        'tags' => array('Jenkins', 'PHP '.phpversion(), getenv('BUILD_TAG')),
      );
    } else {
      $guessedCapabilities = array(
        'tags' => array(php_uname('n'), 'PHP '.phpversion()),
      );
    }

    return new Definition('Zitec\ZitecExtension\Drivers\ZitecDriver', array(
      $config['browser'],
      array_replace($guessedCapabilities, $extraCapabilities, $config['capabilities']),
      $config['wd_host'],
    ));
  }

}
