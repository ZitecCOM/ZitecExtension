<?php
/**
 * Zitec Behat Extension
 */

namespace Zitec\ZitecExtension;

use Behat\Testwork\ServiceContainer\Extension as TestworkExtension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Zitec\ZitecExtension\Drivers\ZitecDriver;
use Zitec\ZitecExtension\Drivers\ZitecDriverFactory;


class ZitecExtension implements TestworkExtension
{

    /**
     * @ZitecDriverFactory
     */
    private $driverFactory;

    public function getConfigKey ()
    {
        return "zitecExtension";
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
        $this->driverFactory = new ZitecDriverFactory();
        if (null !== $minkExtension = $extensionManager->getExtension('mink')) {
            $minkExtension->registerDriverFactory($this->driverFactory);
        }
    }

    public function configure (ArrayNodeDefinition $builder)
    {
        $builder
          ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('wd_host')->defaultValue('http://localhost:4444/wd/hub')->end()
                ->scalarNode('browser')->defaultValue('firefox')->end()
            ->end()
        ->end();
        $this->driverFactory->configure($builder);
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $container->setParameter('zitec.driver_parameters', $config);

    }


    public function process(ContainerBuilder $container)
    {
        $parameters = $container->getParameter('zitec.driver_parameters');
        $minkDefinition = $container->getDefinition('mink');
        $factory = new ZitecDriverFactory();
        $definition = new Definition('Zitec\ZitecExtension\Session\Session', array($factory->buildDriver($parameters), new Reference('mink.selectors_handler')));;
        $minkDefinition->addMethodCall('registerSession', array('zitec_session', $definition));

    }
}
