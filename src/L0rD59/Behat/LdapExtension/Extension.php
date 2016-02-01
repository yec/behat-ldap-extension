<?php
namespace L0rD59\Behat\LdapExtension;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class Extension implements ExtensionInterface
{
  /**
   * {@inheritdoc}
   */
  public function getConfigKey()
  {
    return 'ldap';
  }

  /**
   * {@inheritdoc}
   */
  public function initialize(\Behat\Testwork\ServiceContainer\ExtensionManager $extensionManager)
  {
  }

  /**
   * {@inheritdoc}
   */
  public function configure(\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $builder)
  {
    $builder
      ->children()
        ->scalarNode('host')->defaultValue('localhost')->end()
        ->integerNode('port')->defaultValue(389)->end()
        ->integerNode('version')->defaultValue(3)->end()
        ->booleanNode('useSsl')->defaultValue(false)->end()
        ->booleanNode('useStartTls')->defaultValue(false)->end()
        ->booleanNode('optReferrals')->defaultValue(false)->end()
        ->booleanNode('bind_before_scenario')->defaultValue(true)->end()
        ->booleanNode('purge_before_scenario')->defaultValue(false)->end()
        ->arrayNode('authentication')
          ->children()
            ->scalarNode('rdn')->defaultValue(null)->end()
            ->scalarNode('password')->defaultValue(null)->end()
          ->end()
        ->end()
        ->scalarNode('rootDn')->end()
      ->end()
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function load(\Symfony\Component\DependencyInjection\ContainerBuilder $container, array $config)
  {
    $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/services'));
    $loader->load('core.xml');
    $this->loadContextInitializer($container);
    $container->setParameter('behat.ldap.client.host', $config['host']);
    $container->setParameter('behat.ldap.client.port', $config['port']);
    $container->setParameter('behat.ldap.client.version', $config['version']);
    $container->setParameter('behat.ldap.client.useSsl', $config['useSsl']);
    $container->setParameter('behat.ldap.client.useStartTls', $config['useStartTls']);
    $container->setParameter('behat.ldap.client.optReferrals', $config['optReferrals']);
    $container->setParameter('behat.ldap.context.bind_before_scenario', $config['bind_before_scenario']);
    $container->setParameter('behat.ldap.context.purge_before_scenario', $config['purge_before_scenario']);
    $container->setParameter('behat.ldap.context.authentication', $config['authentication']);
    $container->setParameter('behat.ldap.context.rootDn', $config['rootDn']);
  }

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container)
  {

  }

  /**
   * @param ContainerBuilder $container
   */
  private function loadContextInitializer(ContainerBuilder $container)
  {
    $definition = new Definition('L0rD59\Behat\LdapExtension\ContextInitializer', array(
      new Reference('ldap'),
      '%behat.ldap.context.rootDn%',
      '%behat.ldap.context.bind_before_scenario%',
      '%behat.ldap.context.purge_before_scenario%',
      '%behat.ldap.context.authentication%'
    ));
    $definition->addTag(ContextExtension::INITIALIZER_TAG, array('priority' => 0));
    $container->setDefinition('ldap.context_initializer', $definition);
  }

}