<?php

namespace L0rD59\Behat\LdapExtension;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Context\TranslatableContext;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Ldap\LdapClient;

class Context implements TranslatableContext, SnippetAcceptingContext
{
  /**
   * @var LdapClient
   */
  protected $client;

  /**
   * @var string $rootDn dn root entry.
   */
  protected $rootDn;

  /**
   * @var boolean $bind_before_scenarion
   */
  protected $bind_before_scenarion;

  /**
   * @var string $purge_before_scenario
   */
  protected $purge_before_scenario;

  /**
   * @var array $authentication ['rdn','password']
   */
  protected $authentication;

  public static function getTranslationResources()
  {
    return glob(__DIR__.'/i18n/*.xliff');
  }

  /**
   * Sets configuration of the context.
   *
   * @param LdapClient  $client client to use for API.
   * @param string  $rootDn dn root entry.
   * @param boolean  $bind_before_scenario dn root entry.
   * @param boolean  $purge_before_scenario dn root entry.
   * @param array|null  $authentication ['rdn','password'].
   */
  public function setConfiguration(LdapClient $client, $rootDn, $bind_before_scenario, $purge_before_scenario, $authentication)
  {
    $this->client = $client;
    $this->rootDn = $rootDn;
    $this->authentication = $authentication;
    $this->bind_before_scenarion = $bind_before_scenario;
    $this->purge_before_scenario = $purge_before_scenario;
  }

  /**
   * @BeforeScenario
   */
  public function beforeScenario()
  {
      if($this->bind_before_scenarion){
        $this->client->bind($this->authentication['rdn'], $this->authentication['password']);
      }

    if($this->purge_before_scenario)
    {
    }
  }

  /**
   * Creates entries provided in the form:
   * | cn    | attribute1    | attribute2 | attributeN |
   * | primary | value1 | value2 | valueN |
   * | ...      | ...        | ...  | ... |
   *
   * @Given /^Ldap entries:$/
   */
  public function ldapEntries(TableNode $entries)
  {
    foreach ($entries->getHash() as $entry) {
      $this->client->add('cn'.'='.$entry['cn'].','.$this->rootDn, $entry);
    }
  }

  /**
   * @Then /^Ldap request "(?P<request>[^"]+)" should return (?P<integer>\d+) results$/
   */
  public function ldapRequestShouldReturnResults($request, $count)
  {
    if(is_null($results = $this->client->find($this->rootDn, $request)) || count($results['count']) != $count )
    {
      throw new \Exception('Ldap request "'.$request.'" has return '.count($results['count']).'result(s)');
    }
  }

  /**
   * @Then /^The "(?P<objectclass>[^"]+)" with cn "(?P<cn>[^"]+)" should exist in Ldap$/
   */
  public function theObjectclassWithCnShouldExistInLdap($objectclass, $cn)
  {
    if(is_null($results = $this->client->find($this->rootDn, '(cn='.$cn.')')))
    {
      throw new \Exception('Unknow entry cn='.$cn.' in Ldap');
    }

    if($results[0]['objectclass'][0] != $objectclass)
    {
      throw new \Exception('The entry cn='.$cn.' is not a '.$objectclass.' ('.$results[0]['objectclass'][0].')');
    }
  }
}