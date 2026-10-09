<?php

namespace Base\Legal\Notary\Service;

use Base\Legal\Practice\Service\Record;

/**
 * What only the office knows and the back office's settings keep: the
 * structure that holds the office, its CRPCEN number, its chamber, the day
 * the site was declared to it, whether the profession's charter is
 * applied, the discounts it practises, its fees for what the tariff does
 * not cover.
 */
class NotaryRecord extends Record
{
    public const CRPCEN = 'legal.notary.crpcen';
    public const BRAND = 'legal.notary.brand_applied';
    public const DISCOUNTS = 'legal.notary.discounts';

    public function getFields(): array
    {
        return [
            self::NAME => 'holder',
            self::CRPCEN => 'crpcen',
            self::BODY => 'chamber',
            self::DECLARED_AT => 'declared_at',
            self::BRAND => 'brand',
            self::DISCOUNTS => 'discounts',
            self::FEES => 'fees',
        ];
    }

    public function getLongFields(): array
    {
        return [self::DISCOUNTS, self::FEES];
    }

    public function getPublishedText(): string
    {
        return $this->getFees()."\n".$this->getDiscounts();
    }

    /** The structure that holds the office (name and form). */
    public function getHolder(): ?string { return $this->getName(); }
    /** The chamber the office depends on. */
    public function getChamber(): ?string { return $this->getBody(); }
    public function getCrpcen(): ?string { return $this->get(self::CRPCEN); }
    public function getDiscounts(): ?string { return $this->get(self::DISCOUNTS); }

    /** The profession's graphic charter and brand block, applied - as the office says. */
    public function isBrandApplied(): bool
    {
        return $this->isYes(self::BRAND);
    }
}
