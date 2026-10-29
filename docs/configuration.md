---
title: "Configuration reference"
order: 40
---

# Configuration reference

## The `legal` tree

Dumped with Symfony's `YamlReferenceDumper` from
`Base\Legal\DependencyInjection\LegalConfiguration`:

```yaml
legal:

    # The profession the site is for: its profile is loaded, and it alone - its compliance checks, its entities and their tables, its pages, its demonstration accounts.
    profession:           ~ # One of "lawyer"; "notary"; "jurist", Required

    # The roles omnibase's groups give (security.yaml puts the readers under the staff's).
    roles:

        # Who of the practice reads a client's document; the rest of the staff sees that it exists. Default: the profession's - a lawyer [ROLE_LAWYER], a notary [ROLE_NOTARY, ROLE_CLERK].
        readers:              []
        staff:                ROLE_STAFF

    # The consumer mediator whose coordinates the practice gives (/mediation, the legal notice). Default: the one the profession set up.
    mediator:
        name:                 null
        address:              null
        email:                null
        url:                  null

    # The profession's rules, linked from the legal notice. Default: the profession's (a lawyer: the RIN on the Conseil national des barreaux's site).
    rules_url:            ~

    # Expressions added to the built-in list the texts are checked against (comparison, disparagement, and the profession's own).
    wording:              []

    # The lawyers' profile.
    lawyer:

        # The domain names the site answers on, checked against RIN art. 10.5 and listed in the memo for the Ordre. None: the host of the router's default address.
        domains:              []

        # Where the domain name is read when legal.lawyer.domains is empty.
        default_uri:          '%env(default::DEFAULT_URI)%'

        # Paying a fee note online - fees only, never clients' funds. Needs omnibase/marketplace: without it the link is not shown, whatever is set here.
        payment:
            enabled:              false

            # The application's route where a fee note is paid (the marketplace's checkout of a fee note).
            route:                null

    # The notaries' profile.
    notary:

        # What a notary's professional e-mail address ends with (règlement professionnel, art. 16.3).
        email_suffix:         notaires.fr

        # Where the regulated tariff is read: the official text, the order in force, the profession's explanation.
        tariff:

            # Code de commerce, articles A444-53 and following (the notaries' tariff).
            code_url:             'https://www.legifrance.gouv.fr/codes/texte_lc/LEGITEXT000005634379'

            # The order fixing the tariff in force, as it is named on the page.
            order:                'arrêté du 25 février 2026'
            order_url:            'https://www.legifrance.gouv.fr/eli/arrete/2026/2/25/ECOC2604872A/jo/texte'

            # The last day of the order's period: past it, the back office asks for the new order.
            until:                '2028-02-29'
            info_url:             'https://www.notaires.fr/fr/profession-notaire/le-tarif-du-notaire-emoluments-et-honoraires'

    # The jurists' profile.
    jurist:

        # A status that lets the jurist give legal advice all the same (law of 31 December 1971): a university teacher of law (art. 57), another regulated profession within its own field (art. 59). A lawyer or a notary is not a jurist's status: that is another profession.
        authorized_status:    null # One of null; "teacher"; "regulated_profession"
```

## What reads each key

| Key | Default | Read by |
|---|---|---|
| `profession` | required: `lawyer`, `notary` or `jurist` | the bundle: the profile loaded, and it alone (its checks, entities, pages, demonstration accounts) |
| `roles.readers` | the profession's: `[ROLE_LAWYER]`, `[ROLE_NOTARY, ROLE_CLERK]` | `OfficeAudience`: who of the practice reads a client's document |
| `roles.staff` | `ROLE_STAFF` | `OfficeAudience`: the rest of the staff, who see that a document exists |
| `mediator.name`, `address`, `email`, `url` | the profession's mediator | `/mediation`, the legal notice, the mediator check; a mediator named here replaces it whole |
| `rules_url` | the profession's (a lawyer: the RIN) | the legal notice |
| `wording` | none | `Wording`: expressions added to the built-in list the texts are checked against |
| `lawyer.domains` | none: the host of `lawyer.default_uri` | `Domains`, the domain name check (RIN art. 10.5), the memo for the Ordre |
| `lawyer.default_uri` | `%env(default::DEFAULT_URI)%` | `Domains`, when `lawyer.domains` is empty |
| `lawyer.payment.enabled`, `route` | false, null | `FeePayment`: paying a fee note online, with omnibase/marketplace only |
| `notary.email_suffix` | `notaires.fr` | the professional e-mail check (règlement professionnel art. 16.3) |
| `notary.tariff.*` | the Code de commerce, the order of 25 February 2026, its end on 2028-02-29, the profession's page | `/tarifs`, `legal_tariff()`, the tariff check (past `until`, the back office asks for the new order) |
| `jurist.authorized_status` | null: `teacher` (art. 57) or `regulated_profession` (art. 59) | `RegulatedActivityGuard`, the consulting offering rule |

Only the chosen profession's section becomes parameters
(`legal.lawyer.domains`, `legal.notary.tariff`...), with the common keys
(`legal.profession`, `legal.roles.readers`...).

## Services

`config/services.php` loads the practice's common part (`src/Practice`, on
omnibase/office) when the profession keeps a practice (a lawyer, a notary),
and the chosen profession's directory alone (`src/Lawyer`, `src/Notary` or
`src/Jurist`); `bin/console debug:container --tag=legal.profile` lists what
the profile registered. The
routes follow the same rule through `Base\Legal\Routing\ProfileRouteLoader`
([omnibase/legal](index.md#installation)).

## Translations

The domain `legal`, in English, French and German (ICU format).
