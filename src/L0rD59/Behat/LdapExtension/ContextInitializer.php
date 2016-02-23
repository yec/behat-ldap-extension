<?php
namespace L0rD59\Behat\LdapExtension;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer as InitializerInterface;
use Symfony\Component\Ldap\Ldap;

class ContextInitializer implements InitializerInterface
{
  protected $client;
  protected $rootDn;
  protected $bind_before_scenario;
  protected $purge_before_scenario;
  protected $authentication;
  /**
   * @param Ldap $client
   * @param bool   $purgeBeforeScenario
   */
  public function __construct(Ldap $client, $rootDn, $bind_before_scenario, $purge_before_scenario, $authentication)
  {
    $this->client = $client;
    $this->rootDn = $rootDn;
    $this->bind_before_scenario = $bind_before_scenario;
    $this->purge_before_scenario = $purge_before_scenario;
    $this->authentication = $authentication;
  }
  /**
   * @param Context $context
   *
   * @return bool
   */
  public function supports(Context $context)
  {
    return $context instanceof \L0rD59\Behat\LdapExtension\Context;
  }
  /**
   * @param Context $context
   */
  public function initializeContext(Context $context)
  {
    if (!$context instanceof \L0rD59\Behat\LdapExtension\Context) {
      return;
    }
    $context->setConfiguration($this->client, $this->rootDn, $this->bind_before_scenario, $this->purge_before_scenario, $this->authentication);
  }
}