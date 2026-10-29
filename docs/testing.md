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
| `Practice\AreaTest`, `ChecksTest`, `WordingTest` | the areas of practice; the common compliance checks (declaration, mediator, identification, wording); comparison and disparagement expressions found, the trade's plain words not, the site's own added, each reported once |
| `Practice\OfficeAudienceTest` | who of a firm or an office reads a client's document, who sees its title, nobody outside the practice |
| `Lawyer\AttorneyTest` | the official list of 28 mentions; what is outside it dropped; the dominant fields typed one a line |
| `Lawyer\ChecksTest`, `DomainNameTest`, `SpecialistWordsTest`, `FeePaymentTest`, `LawyerDemoAccountsTest` | the domain name carries the name (RIN art. 10.5); the declaration to the Ordre; "specialist" for the certificate holders; at most three dominant fields and two mentions; the fees page; paying online checked only when on; the demonstration accounts |
| `Notary\ChecksTest`, `EmailDomainTest`, `NotaryDemoAccountsTest` | the notaries' checks (tariff, chamber, professional e-mail, brand block); the e-mail suffix; the demonstration accounts |
| `Jurist\RegulatedActivityGuardTest`, `OfferingRuleTest` | the words of a reserved activity refused, training, information and writing open to all, a status lifts the guard, each activity's words in French and English; a consulting offering refused under the text that carries them |
