Feature: Ldap

  Scenario: Search in Ldap
    Given Ldap entries:
      | cn       | objectclass | sn   |
      | John Doe | person      | John |
    Then Ldap request "(cn=John Doe)" should return 1 results
    And The "person" with cn "John Doe" should exist in Ldap