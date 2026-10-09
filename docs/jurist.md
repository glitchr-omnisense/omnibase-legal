---
title: Jurists - the law of 31 December 1971
order: 5
---

# Jurists: what the law reserves

French law no. 71-1130 of 31 December 1971 (art. 54 and following) reserves personalised legal advice
and the drafting of deeds for others to the professions it allows: a lawyer, a notary, a university
teacher of law (art. 57), another regulated profession within its own field (art. 59). Training,
documentary information (art. 66-1), writing content, talks are open to anyone.

```yaml
# config/packages/legal.yaml
legal:
    profession: jurist
    jurist:
        authorized_status: ~     # teacher (art. 57) or regulated_profession (art. 59) lifts the guard
```

A jurist keeps no practice: omnibase/office is not needed, and none of the practice's pages, tables
or checks is loaded. The profile is the guard:

- `Jurist\Service\RegulatedActivityGuard::check($text)` - null, or `jurist.guard.legal_consultation` /
  `jurist.guard.deed_drafting` (domain `legal`) when the text announces a reserved activity
  (`Jurist\Enum\ReservedActivity`: "consultation juridique", "conseil juridique personnalisé",
  "rédaction d'actes", "legal advice"...), whatever the offering was filed under;
- with [omnibase/consulting](https://github.com/glitchr-studio/omnibase-consulting), its rule
  `Jurist\Consulting\OfferingRule` (an `OfferingRuleInterface` of that bundle): an offering whose title,
  sentence or presentation announces one does not validate, and the back office prints why under that
  text.

Until 2026-10-09 this was omnibase/consulting's `RegulatedActivityGuard` (`consulting.regulated_activity_guard`,
`consulting.authorized_status`): a site moves those two options here, as above.

It is a safeguard written into the code, not legal advice: what it does not find is not thereby
allowed, and the profession's own rules still apply. Tests: `tests/Jurist/`.
