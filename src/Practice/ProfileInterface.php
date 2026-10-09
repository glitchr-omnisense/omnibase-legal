<?php

namespace Base\Legal\Practice;

use Base\Legal\Profession;

/**
 * What a profession tells the practice - the part a firm and an office
 * share: what identifies it on its site, the words its communication must
 * not carry beyond those common to every regulated practice, where it says
 * what it costs. The chosen profession's profile answers
 * (Base\Legal\Lawyer\LawyerProfile, Base\Legal\Notary\NotaryProfile); its
 * settings are its record's (Base\Legal\Practice\Service\Record).
 */
interface ProfileInterface
{
    /** What the settings call the practice's name, a contact, its professional body. */
    public const NAME = 'name';
    public const ADDRESS = 'address';
    public const PHONE = 'phone';
    /** A phone number or an e-mail address. */
    public const CONTACT = 'contact';
    public const BODY = 'body';

    public function getProfession(): Profession;

    /**
     * What identifies the practice, in the order it is asked: the word the
     * back office names it by when it is missing ("holder", "bar") => one
     * of the constants above, or the key of a setting of the profession's
     * own (a lawyer's structure of practice).
     *
     * @return array<string, string>
     */
    public function getIdentification(): array;

    /**
     * The profession's own forbidden words, after the common ones
     * (Base\Office\Guard\Wording): label => regular expression on the text
     * once lowered and stripped of its accents.
     *
     * @return array<string, string>
     */
    public function getWordingPatterns(): array;

    /** The route of the page that says what it costs: the fees, the tariff. */
    public function getFeesRoute(): string;
}
