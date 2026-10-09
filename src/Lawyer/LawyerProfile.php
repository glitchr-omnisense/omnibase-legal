<?php

namespace Base\Legal\Lawyer;

use Base\Legal\Lawyer\Service\LawyerRecord;
use Base\Legal\Practice\ProfileInterface;
use Base\Legal\Profession;

/**
 * The lawyers' profile: what a law firm's site owes to article 10 of the
 * Règlement intérieur national (RIN), written as checks the back office
 * runs - the domain name (10.5), specialisations from the certified list
 * only and three dominant fields at most (10.2), wording free of
 * comparison (10.2), the firm identified (10.2), the site and what it says
 * declared to the conseil de l'Ordre (10.2, 10.3, 10.5), the fees explained
 * and the fee agreement announced (11.1, 11.2) - with its own entity (a
 * member of the team as a lawyer: Entity\Attorney) and its own pages: the
 * fees, the memo for the Ordre. Paying online is for fees only, through
 * omnibase/marketplace when it is installed; the bundle itself takes no
 * payment.
 *
 * Compliance built into the code does not replace the Ordre's control.
 */
final class LawyerProfile implements ProfileInterface
{
    public function getProfession(): Profession
    {
        return Profession::LAWYER;
    }

    /**
     * « L'avocat doit, dans toute communication, [...] faire état de sa
     * qualité et permettre, quel que soit le support utilisé, de
     * l'identifier, de le localiser, de le joindre, de connaître le barreau
     * auquel il est inscrit, la structure d'exercice à laquelle il
     * appartient et, le cas échéant, le réseau dont il est membre » (RIN
     * art. 10.2). The network is asked only of who has one.
     */
    public function getIdentification(): array
    {
        return [
            'name' => self::NAME,
            'address' => self::ADDRESS,
            'contact' => self::CONTACT,
            'bar' => self::BODY,
            'structure' => LawyerRecord::STRUCTURE,
        ];
    }

    /**
     * Beyond comparison and disparagement, RIN art. 10.2 forbids « toute
     * référence à des fonctions juridictionnelles »: a former judge does
     * not say so to recommend himself.
     */
    public function getWordingPatterns(): array
    {
        return [
            'fonctions juridictionnelles' => '\b(?:ancien(?:ne)?\s+(?:magistrate?|juge|procureure?|conseill(?:er|ere)\s+prud)|juge\s+(?:consulaire|de\s+proximite|suppleant)|magistrate?\s+(?:honoraire|a\s+titre\s+temporaire))\b',
        ];
    }

    public function getFeesRoute(): string
    {
        return 'legal_fees';
    }
}
