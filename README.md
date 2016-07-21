Behat Ldap Extension
===================

Demo
---------------
Require Ldap server and adapt your configuration in `fixtures/behat.yml`.

`php vendor/bin/behat -c fixtures/behat.yml`


Usage
---------------
Configure your `behat.yml`

```
default:
  suites:
    default:
      contexts:
        - L0rD59\Behat\LdapExtension\Context
  extensions:
    L0rD59\Behat\LdapExtension\Extension:
      rootDn: "dc=basedn" # require
      host: 'localhost' #default
      port: 389 #default
      version: 3 #default
      encryption: 'none' #default
      bind_before_scenario: true #default
      purge_before_scenario: false #default
      authentication:
        rdn: ~ #default
        password: ~ #default
```

Print available step : `behat -dl`
