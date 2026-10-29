---
title: "Testing"
order: 60
---

# Testing

## Running the suite

Inside a host application that has omnibase/office (the practice's tests
need it, `tests/NeedsOffice.php`):

```bash
php vendor/bin/phpunit -c vendor/omnibase/legal/phpunit.xml.dist
```

omnisense/omnibase-harness has no omnibase/office: its `test legal` errs on
`Base\Office\Entity\Area`. With the checkouts of omnibase/office and
omnibase/consulting on the harness's autoloader and no kernel, the suite on
this checkout gives:

```text
Tests: 141, Assertions: 472, Errors: 8.
```

The eight errors are `Practice\OfficeAudienceTest`'s, which needs the host
kernel's `App\Entity\User` aliases.

## What the tests check

| Test | Checks |
|---|---|
| `DependencyInjection\ConfigurationTest` | the profession is asked; an unknown one refused; what the site does not say is the profession's; what it says wins; a jurist's status; the last file that sets the profession wins |
| `Controller\CrudRequiredFieldsTest` | omnibase/admin's `CrudFieldsTestCase` on the screens |
| `Practice\AreaTest`, `ChecksTest`, `WordingTest` | an area's work is a list edited one a line, one table and its join table whatever the profession; an office or a firm identified, located and reachable (holder, address, phone, chamber or bar), the mediator's coordinates given, the declaration's day a date or nothing; comparison and disparagement found where they are (fields, settings), each profession's own words, the site's own added, each reported once |
| `Practice\OfficeAudienceTest` | who of a firm or an office reads a client's document, who sees its title, nobody outside the practice |
| `Lawyer\AttorneyTest` | the official list of 28 mentions; what is outside it dropped; the dominant fields typed one a line |
| `Lawyer\ChecksTest`, `DomainNameTest`, `SpecialistWordsTest`, `FeePaymentTest`, `LawyerDemoAccountsTest` | the domain name carries the name (RIN art. 10.5); the declaration to the Ordre; "specialist" for the certificate holders; at most three dominant fields and two mentions; the fees page; paying online off by default, shown only with omnibase/marketplace and a route; the demonstration accounts |
| `Notary\ChecksTest`, `EmailDomainTest`, `NotaryDemoAccountsTest` | an office address outside notaires.fr is missing, the site's sender only a warning; the declaration to the chamber; the tariff page links the official text and prints the discounts, past the order's period the reference is to be updated; the charter a reminder until confirmed; the e-mail suffix and its look-alikes; the demonstration accounts |
| `Jurist\RegulatedActivityGuardTest`, `OfferingRuleTest` | the words of a reserved activity refused, training, information and writing open to all, a status lifts the guard, each activity's words in French and English; a consulting offering refused under the text that carries them |
