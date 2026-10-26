# omnibase/legal

The legal professions on [omnibase](https://github.com/glitchr-omnisense/omnibase), one bundle and a
**profile** for each - `legal.profession: lawyer | notary | jurist`, one site, one profession:

| Profile | What it brings |
|---|---|
| `lawyer` | article 10 of the RIN: the domain name read against the firm's name (10.5), certified specialisations and three dominant fields at most (10.2), the declaration to the conseil de l'Ordre and its memo, the fees and the fee agreement (11.1, 11.2), fees paid online and nothing else (11.5) - the lawyers (`Attorney`), the fees page |
| `notary` | the règlement professionnel: addresses in `notaires.fr` (16.3), the declaration to the chamber (14.4), the profession's charter (14.4, 14.5), the regulated tariff and the discounts (code de commerce, L. 444-4) - the tariff page; no online payment |
| `jurist` | the law of 31 December 1971: nothing a regulated profession alone may do is offered - [omnibase/consulting](https://github.com/glitchr-omnisense/omnibase-consulting)'s offerings are refused when they announce legal advice or deeds drafted for others |

What a firm and an office share is written once, on
[omnibase/office](https://github.com/glitchr-omnisense/omnibase-office): the fields of practice, a first
appointment asked without an account, the client's space on the encrypted vault, who of the practice
reads a document, the settings, and the checks every regulated practice answers in the back office's
"Conformité" widget - it can be identified, it names its consumer mediator, its texts carry no
comparison. Each profession says what those mean for it.

Only the chosen profile is loaded: its services, its routes, its entities and their tables. A notary's
site has no table, page nor check of a lawyer's.

> **Compliance built into the code does not replace the control of the Ordre or of the chamber.**
> The checks help a practice not to forget; what could not be read in an official text is listed as
> "to confirm" in the rules of each profession, and is not written as a rule.

```sh
composer require omnibase/legal            # and omnibase/office for a lawyer or a notary
```

Documentation: [docs/](docs/index.md) - [lawyers](docs/lawyer.md), [notaries](docs/notary.md),
[jurists](docs/jurist.md).

`omnibase/legal` replaces `omnibase/lawyer` and `omnibase/notary` (2026-10-09), which are abandoned:
[docs/index.md](docs/index.md#from-omnibaselawyer-and-omnibasenotary) says what changes for a site.

License: MIT.
