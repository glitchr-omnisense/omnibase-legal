---
title: omnibase/legal
order: 1
---

# omnibase/legal

The legal professions, one bundle and a profile for each. A site says which profession it is for;
the bundle loads that profile, and it alone.

> Compliance built into the code does not replace the control of the Ordre or of the chamber.

## Installation

```sh
composer require omnibase/legal          # a lawyer or a notary: omnibase/office too
```

```php
// config/bundles.php - a lawyer's or a notary's site
Base\Office\OfficeBundle::class => ['all' => true],
Base\Legal\LegalBundle::class => ['all' => true],
```

```yaml
# config/packages/legal.yaml
legal:
    profession: lawyer          # lawyer | notary | jurist - required
```

```yaml
# config/routes.yaml - after omnibase/office's
legal:
    resource: .
    type: legal
```

The loader imports the practice's pages and the chosen profile's, the back office's when
omnibase/admin is installed - none of another profession's.

```yaml
# config/packages/security.yaml
security:
    role_hierarchy:
        ROLE_LAWYER:    [ROLE_STAFF]     # a lawyer's site
        ROLE_SECRETARY: [ROLE_STAFF]
        ROLE_NOTARY:    [ROLE_STAFF]     # a notary's site
        ROLE_CLERK:     [ROLE_STAFF]
        ROLE_STAFF:     [ROLE_USER]
        ROLE_ADMIN:     [ROLE_STAFF]
```

Then `make:migration`: a lawyer's or a notary's site gets `legal_area` and `legal_area_member`; a
lawyer's `legal_attorney` too; a jurist's none.

In the layout, after the office's stylesheet:

```twig
<link rel="stylesheet" href="{{ asset('bundles/legal/css/legal.css') }}">
```

## How a profile is made

```
src/
  Profession.php          lawyer | notary | jurist; hasPractice()
  Practice/               what a firm and an office share, on omnibase/office
    ProfileInterface      what a profession tells the practice: what identifies it,
                          its own forbidden words, its page of what it costs
    Entity/Area           a field of practice (table legal_area), its list "items"
    Service/Record        the settings every profession has: name, professional body,
                          the day the site was declared to it, the fees
    Compliance/           IdentificationCheck, MediatorCheck, WordingCheck, DeclarationCheck
    Security/OfficeAudience  who reads a client's document (legal.roles.readers)
    Controller/           /domaines, /mediation, /rendez-vous/demande, /espace; the CRUD of areas
  Lawyer/  Notary/  Jurist/   one profile each
```

A profile's folder is loaded only when it is the site's profession, and every service in it carries
the tag `legal.profile` with its profession (`debug:container --tag=legal.profile`). Its checks are
omnibase/office's `ComplianceCheckInterface` (autoconfigured): only the chosen profile's run in the
widget. Its entities are mapped by `LegalExtension::prepend()` for that profession alone - a site has no
table of another's.

A profile gives the practice `<Profile>Profile` (a `ProfileInterface`) and `Service\<Profile>Record`
(a `Record`); its labels are under its name in the `legal` translation domain
(`lawyer.compliance.domain`, `notary.tariff.title`...), in French and English.

What is common to a whole profession stays in its profile (a lawyer's certified specialisations, fee
agreement and declaration to the Ordre; a notary's chamber, regulated tariff and professional
address). Only what is one firm's or one office's goes into the site's own code.

## Configuration

```yaml
legal:
    profession: notary
    roles:
        readers: ~           # default: the profession's - [ROLE_LAWYER]; [ROLE_NOTARY, ROLE_CLERK]
        staff: ROLE_STAFF
    mediator: ~              # default: the profession's consumer mediator; a mediator named here replaces it whole
    rules_url: ~             # default: the profession's (a lawyer: the RIN)
    wording: []              # expressions added to the built-in list
    lawyer:
        domains: []          # RIN art. 10.5; none: the host of DEFAULT_URI
        payment: { enabled: false, route: ~ }   # fees only, with omnibase/marketplace
    notary:
        email_suffix: notaires.fr
        tariff: { code_url: …, order: 'arrêté du 25 février 2026', order_url: …, until: '2028-02-29', info_url: … }
    jurist:
        authorized_status: ~ # teacher | regulated_profession
```

Only the chosen profession's section becomes parameters (`legal.lawyer.domains`,
`legal.notary.tariff`...).

## Settings (back office, Réglages)

| Setting | Lawyer | Notary |
|---|---|---|
| `legal.practice.name` | the firm's denomination | the structure that holds the office |
| `legal.body.name` | the bar | the chamber |
| `legal.body.declared_at` | declared to the conseil de l'Ordre on | declared to the chamber on |
| `legal.fees.terms` | how the fees are set | fees for what the tariff does not cover |
| `legal.lawyer.structure`, `.network`, `.first_meeting`, `.legal_aid` | ✓ | |
| `legal.notary.crpcen`, `.brand_applied`, `.discounts` | | ✓ |

## Pages

| Route | Path | |
|---|---|---|
| `legal_areas`, `legal_area` | `/domaines`, `/domaines/{slug}` | the fields of practice |
| `legal_mediation` | `/mediation` | the practice, then the profession's consumer mediator, then its professional body |
| `legal_request` | `/rendez-vous/demande` | a first appointment asked without an account (omnibase/office's form) |
| `legal_space` | `/espace` | the client's space: appointments, documents |
| `legal_fees` | `/honoraires` | lawyer: how fees are set, the agreement, paying online |
| `legal_tariff` | `/tarifs` | notary: what the costs are made of, the official text, the discounts |
| `legal_admin_declaration` | `/admin/legal/declaration` | lawyer: the memo for the conseil de l'Ordre |

Templates extend omnibase/office's layout and are overridden the usual way
(`templates/bundles/LegalBundle/...`). Twig: `legal_profession()`, `legal_record()`,
`legal_areas(member)`, `legal_mediator()`, `legal_rules_url()`, `legal_fees_route()`; a lawyer's
`legal_attorney(member)`, `legal_payment()`; a notary's `legal_tariff()`.

## Demonstration accounts

In glitchr/omnibase's `demo` environment the profile declares one account for each role:
`avocate`, `avocat`, `secretariat`, `cabinet`, `client` (lawyer); `notaire`, `clerc`, `accueil`,
`etude`, `client` (notary). The password is the identifier; labels `<profession>.demo.<identifier>.*`
in the `legal` domain. A jurist's site declares its own.

## From omnibase/lawyer and omnibase/notary

The two bundles were one structure twice; they are abandoned for this one (2026-10-09). For a site:

- `composer require omnibase/legal` in place of either; `Base\Legal\LegalBundle` in `config/bundles.php`;
  `legal.profession`; the routes by `type: legal`;
- classes: `Base\Lawyer\Entity\Area` / `Base\Notary\Entity\Area` → `Base\Legal\Practice\Entity\Area`
  (`getMatters()` / `getDeeds()` → `getItems()`), `Base\Lawyer\Entity\Attorney` →
  `Base\Legal\Lawyer\Entity\Attorney`, `Base\Lawyer\Service\Record` → `Base\Legal\Lawyer\Service\LawyerRecord`,
  `Base\Notary\Service\Record` → `Base\Legal\Notary\Service\NotaryRecord`;
- routes `lawyer_*` / `notary_*` → `legal_*`; Twig `lawyer_*()` / `notary_*()` → `legal_*()`;
  `@Lawyer/client/_attorney.html.twig` → `@Legal/lawyer/_attorney.html.twig`; classes `la-*` / `no-*` → `lg-*`;
  parameters `lawyer.*` / `notary.*` → `legal.*` (`legal.mediator`, `legal.lawyer.domains`, `legal.notary.tariff`);
- a migration that **renames**, never drops: `lawyer_area` / `notary_area` → `legal_area` (`matters` /
  `deeds` → `items`), `*_area_member` → `legal_area_member` (`area_id`, `member_id`),
  `lawyer_attorney` → `legal_attorney`, and the settings' paths (`lawyer.firm.name` →
  `legal.practice.name`...). The sites avocat and notaire carry one each to copy.
