<?php

namespace Base\Legal\Notary;

use Base\Legal\Practice\ProfileInterface;
use Base\Legal\Profession;

/**
 * The notaries' profile: what a notary's office site owes to its
 * professional rules (règlement professionnel du notariat, approved by the
 * order of 29 January 2024), written as checks the back office runs - the
 * regulated tariff and the discounts displayed, the consumer mediator of
 * the notariat, e-mail addresses ending in notaires.fr, the site declared
 * to the chamber, wording free of comparison, the profession's charter -
 * and its own page: the tariff. No online payment: the profile has none.
 *
 * Compliance built into the code does not replace the chamber's control.
 */
final class NotaryProfile implements ProfileInterface
{
    public function getProfession(): Profession
    {
        return Profession::NOTARY;
    }

    /**
     * "Tout document destiné à la correspondance ou à la communication du
     * notaire doit mentionner les éléments permettant de l'identifier"
     * (règlement professionnel du notariat, art. 14.1): the legal notice
     * prints the structure that holds the office, its address, its phone,
     * its chamber.
     */
    public function getIdentification(): array
    {
        return [
            'holder' => self::NAME,
            'address' => self::ADDRESS,
            'phone' => self::PHONE,
            'chamber' => self::BODY,
        ];
    }

    /**
     * Nothing of the notaries' alone so far: the common list is theirs.
     * The notary's communication keeps to "la dignité, la loyauté, la
     * confraternité, la délicatesse ; la neutralité, l'impartialité et
     * l'objectivité" (règlement professionnel, art. 14.1), abstains from
     * "toute démarche à caractère publicitaire" (art. 14.2), and a
     * personalised solicitation or an online offer of services "ne peut
     * pas inclure d'éléments comparatifs ou dénigrants" (decree n° 73-1202
     * of 28 December 1973 as amended by decree n° 2019-257 of 29 March
     * 2019).
     */
    public function getWordingPatterns(): array
    {
        return [];
    }

    public function getFeesRoute(): string
    {
        return 'legal_tariff';
    }
}
