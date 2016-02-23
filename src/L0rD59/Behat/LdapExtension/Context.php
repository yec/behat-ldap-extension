<?php

namespace L0rD59\Behat\LdapExtension;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Context\TranslatableContext;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Ldap;

class Context implements TranslatableContext, SnippetAcceptingContext
{
  /**
   * @var Ldap
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
   * @param Ldap  $client client to use for API.
   * @param string  $rootDn dn root entry.
   * @param boolean  $bind_before_scenario dn root entry.
   * @param boolean  $purge_before_scenario dn root entry.
   * @param array|null  $authentication ['rdn','password'].
   */
  public function setConfiguration(Ldap $client, $rootDn, $bind_before_scenario, $purge_before_scenario, $authentication)
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
      $ldapEntry = new Entry('cn'.'='.$entry['cn'].','.$this->rootDn, $entry);
      $this->client->getEntryManager()->add($ldapEntry);
    }
  }

  /**
   * @Then /^Ldap request "(?P<request>[^"]+)" should return (?P<integer>\d+) results$/
   */
  public function ldapRequestShouldReturnResults($request, $count)
  {
    if(is_null($results = $this->client->query($this->rootDn, $request)->execute()) || $results->count() != $count )
    {
      throw new \Exception('Ldap request "'.$request.'" has return '.$results->count().'result(s)');
    }
  }

  /**
   * @Then /^The "(?P<objectclass>[^"]+)" with cn "(?P<cn>[^"]+)" should exist in Ldap$/
   */
  public function theObjectclassWithCnShouldExistInLdap($objectclass, $cn)
  {
    if(is_null($results = $this->client->query($this->rootDn, '(cn='.$cn.')')->execute()))
    {
      throw new \Exception('Unknow entry cn='.$cn.' in Ldap');
    }

    $results = $results->toArray();

    if($results[0]->getAttribute('objectclass')[0] != $objectclass)
    {
      throw new \Exception('The entry cn='.$cn.' is not a '.$objectclass.' ('.$results[0]->getAttribute('objectclass')[0].')');
    }
  }
}