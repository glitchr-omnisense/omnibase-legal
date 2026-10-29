---
title: Notaries - rules and sources
order: 31
---

# Notaries: rules and sources

Each rule the profile codes (classes under `Base\Legal\`; tests under `tests/`), the text it rests on, where that text was read, and the class and
test that carry it. Read on 2026-10-05.

> Compliance built into the code does not replace the chamber's control: the chamber of notaries
> verifies its notaries' sites and is the only judge of their conformity.

## The texts

| Text | Read at |
|---|---|
| **RPN** - règlement professionnel du notariat, approved by the order of 29 January 2024 (NOR JUSC2402635A, JORF of 31 January 2024, in force 1 February 2024) | full text of the Journal officiel: <https://anc.notaires.fr/wp-content/uploads/2024/09/Arrete-RPN-du-29.01.24.pdf> ; Légifrance: <https://www.legifrance.gouv.fr/jorf/id/JORFTEXT000049060695> |
| **Code de déontologie des notaires** - decree n° 2023-1297 of 28 December 2023 | Légifrance: <https://www.legifrance.gouv.fr/jorf/id/JORFTEXT000048706693> - **not readable from the build environment** (Légifrance refuses automated readers); only what the RPN and the Autorité de la concurrence quote of it was used |
| Decree n° 73-1202 of 28 December 1973 as amended by decree n° 2019-257 of 29 March 2019 (personalised solicitation, online services) | as summarised by the Autorité de la concurrence, opinion n° 23-A-19 of 1 December 2023, § 140-141: <https://www.autoritedelaconcurrence.fr/sites/default/files/integral_texts/2024-02/23a19.pdf> |
| Code de commerce, art. L. 444-1 and following, R. 444-1 and following, A. 444-53 and following (the tariff) | as presented by the Conseil supérieur du notariat: <https://www.notaires.fr/fr/profession-notaire/le-tarif-du-notaire-emoluments-et-honoraires> ; the code: <https://www.legifrance.gouv.fr/codes/texte_lc/LEGITEXT000005634379> |
| Order of 25 February 2026 fixing the notaries' regulated tariffs (NOR ECOC2604872A), 1 March 2026 - 29 February 2028 | Service-Public.fr: <https://www.service-public.gouv.fr/particuliers/actualites/A18834> ; Légifrance: <https://www.legifrance.gouv.fr/eli/arrete/2026/2/25/ECOC2604872A/jo/texte> |
| Loi n° 2004-575 of 21 June 2004 (LCEN), art. 6 and 19: a site's mandatory mentions, a regulated profession's | Service-Public.fr: <https://entreprendre.service-public.gouv.fr/vosdroits/F31228> |

## Coded rules

| # | Rule | Text | Class | Test |
|---|---|---|---|---|
| 1 | The professional e-mail address ends with `.notaires.fr` | RPN art. 16.3: « L'adresse de messagerie professionnelle sortante du notaire se termine par ".notaires.fr", afin d'éviter toute confusion dans l'esprit du public. » | `Notary\Guard\EmailDomain`, `Notary\Compliance\ProfessionalEmailCheck` | `EmailDomainTest`, `ChecksTest` |
| 2 | The site, and any change to the pages where the office offers its services, is declared to the chamber | RPN art. 14.4: « L'office notarial qui ouvre un site internet, y propose ses services ou ouvre ou modifie une ou plusieurs pages web destinées aux mêmes fins sur un site internet tiers doit en informer sans délai la chambre des notaires dont il dépend. » | `Notary\Compliance\ChamberDeclarationCheck` (on `Practice\Compliance\DeclarationCheck`) | `ChecksTest` |
| 3 | The consumer mediator of the notariat is named, with its coordinates | RPN art. 25.1: « Le notaire doit faire connaître par tous moyens lisibles et appropriés la possibilité pour le client d'avoir recours au médiateur de la consommation du notariat en cas de différend entre eux et en indiquer les coordonnées. » | `Practice\Compliance\MediatorCheck`, `/mediation`, `legal.mediator` | `ChecksTest` |
| 4 | The tariff is displayed on the site, with the discounts practised | Code de commerce, art. L. 444-4 (the tariffs are posted in the office and on its website); notaires.fr: the notary « doit afficher dans son office et publier sur son site internet, les taux de remise pratiqués par catégorie d'actes et tranches d'assiette » | `Notary\Compliance\TariffDisplayCheck`, `/tarifs` | `ChecksTest` |
| 5 | The tariff is not copied: the page links to the official text, and the back office warns when the order's period is over | the tariff is revised by order (the one in force: 25 February 2026, until 29 February 2028) | `Notary\Compliance\TariffDisplayCheck` (`legal.notary.tariff.until`) | `ChecksTest` |
| 6 | Fees for what the tariff does not cover are agreed in writing beforehand | Code de commerce, art. L. 444-1, as quoted by notaires.fr: « Les notaires concluent par écrit avec leur client une convention d'honoraires, qui précise, notamment, le montant ou le mode de détermination des honoraires » ; RPN art. 23 | the tariff page's text; `legal.notary.tariff.fees` | page test in the application |
| 7 | No comparison, no disparagement, no advertising superlative in what the office writes | RPN art. 14.1 (« la dignité, la loyauté, la confraternité, la délicatesse ; la neutralité, l'impartialité et l'objectivité »), art. 14.2 (« Le notaire s'abstient de toute démarche à caractère publicitaire ») ; decree n° 73-1202 as amended: a personalised solicitation « ne peut pas inclure d'éléments comparatifs ou dénigrants » | `Practice\Guard\Wording`, `Practice\Compliance\WordingCheck` | `WordingTest`, `ChecksTest` |
| 8 | The office is identified: its structure, its address, its phone, its chamber | RPN art. 14.1: « Tout document destiné à la correspondance ou à la communication du notaire doit mentionner les éléments permettant de l'identifier » ; LCEN art. 19 (title, professional body, rules) | `Practice\Compliance\IdentificationCheck` | `ChecksTest` |
| 9 | The profession's graphic charter and brand block are applied - declared by the office, since they cannot be read from outside | RPN art. 14.4 (« respecter la charte graphique [...] et le plan de nommage arrêtés par le Conseil supérieur du notariat et publiés sur le portail intranet de la profession ») and art. 14.5 (the brand block with the NOTAIRES DE FRANCE logo « sur tous les supports de communication ») | `Notary\Compliance\BrandBlockCheck` | `ChecksTest` |
| 10 | Within the office, the notaries and the clerks read a client's document; the reception sees a title | the office's choice, under the professional secrecy the RPN recalls (art. 14.1: « tenu au secret professionnel ») | `Practice\Security\OfficeAudience` | `OfficeAudienceTest` |

The wording check is a list of expressions (`Practice\Guard\Wording`): a help to whoever writes, shown as a
warning. What it does not find is not thereby allowed.

## What the bundle does not do, by design

- **No online payment.** The bundle offers none and the tariff page says so. The plan's reason
  ("clients' funds go through the Caisse des dépôts") was **not verified on an official text** as a
  prohibition of card payment of a notary's fees: it is therefore a choice of the product, not a
  coded rule. *To confirm* with the chamber before any payment is added.
- **No testimonials, no client reviews, no comparison table.** The bundle has no such entity or
  page. That testimonials are forbidden as such was **not found in the texts read** (the RPN
  forbids advertising, art. 14.2, and paid referencing): *to confirm*.
- **No advertising insert.** RPN art. 14.4: « Le site internet du notaire ne comporte aucun encart
  ou bannière publicitaire autre que celui ou celle de la profession. » Nothing in the bundle
  displays one; the application must not add any (no check can see a template).
- **No paid referencing, no mass mailing.** RPN art. 14.2: « La pratique du référencement payant
  est prohibée » ; art. 14.3: a personalised solicitation is a letter or an e-mail only. The bundle
  sends nothing but the answers to what a client asked.

## To confirm (not coded as rules)

- The **code de déontologie** itself (decree n° 2023-1297): its articles on communication and on the
  title (art. 15, referred to by RPN art. 15) could not be read. To be read on Légifrance.
- The **exact wording of art. L. 444-4** of the code de commerce and the article that asks for the
  discounts to be published (R. 444-10, as recalled from memory): read through notaires.fr only.
- The **graphic charter and naming plan** of the Conseil supérieur du notariat (intranet of the
  profession): a real office's site must follow them - its domain name too (RPN art. 16.2). The
  demonstration's own design is therefore provisional for a real office.
- Whether `@notaires.fr` is required of the **site's sender address** as well as of the notary's own
  address (the check warns, it does not fail).
- The mediator's **e-mail address** (only the postal address and the site were confirmed:
  <https://mediateur-notariat.notaires.fr>, postal address also on justice.fr and inc-conso.fr).
- A **salaried notary** must name the holder of the office and its seat in what they write
  (RPN art. 15, last paragraph): left to the member's biography, not checked.
