<?php

namespace Base\Legal\Lawyer\Service;

use Base\Legal\Practice\Service\Record;

/**
 * What only the firm knows and the back office's settings keep: its
 * denomination, its structure of practice, its bar, its network, the day
 * the site was declared to the conseil de l'Ordre, how its fees are set,
 * what a first meeting costs, whether legal aid is taken.
 */
class LawyerRecord extends Record
{
    public const STRUCTURE = 'legal.lawyer.structure';
    public const NETWORK = 'legal.lawyer.network';
    public const FIRST_MEETING = 'legal.lawyer.first_meeting';
    public const LEGAL_AID = 'legal.lawyer.legal_aid';

    public function getFields(): array
    {
        return [
            self::NAME => 'firm',
            self::STRUCTURE => 'structure',
            self::BODY => 'bar',
            self::NETWORK => 'network',
            self::DECLARED_AT => 'declared_at',
            self::FEES => 'fees',
            self::FIRST_MEETING => 'first_meeting',
            self::LEGAL_AID => 'legal_aid',
        ];
    }

    public function getPublishedText(): string
    {
        return $this->getFees()."\n".$this->getFirstMeeting();
    }

    /** The firm's denomination. */
    public function getFirm(): ?string { return $this->getName(); }
    /** The bar the firm is registered with. */
    public function getBar(): ?string { return $this->getBody(); }
    public function getStructure(): ?string { return $this->get(self::STRUCTURE); }
    public function getNetwork(): ?string { return $this->get(self::NETWORK); }
    public function getFirstMeeting(): ?string { return $this->get(self::FIRST_MEETING); }
    public function getLegalAid(): ?string { return $this->get(self::LEGAL_AID); }
}
