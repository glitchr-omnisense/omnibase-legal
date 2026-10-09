<?php

namespace Base\Legal\Practice\Compliance;

use Base\Legal\Practice\ProfileInterface;
use Base\Legal\Practice\Service\Record;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;
use Base\Office\Repository\OfficeRepository;

/**
 * The practice can be identified on its site: a member of a regulated
 * profession names its professional title, the body it is registered with
 * and the rules that apply to it (loi n° 2004-575 du 21 juin 2004, art.
 * 19), and each profession says what its own rules ask - a notary's
 * "éléments permettant de l'identifier" (règlement professionnel, art.
 * 14.1), a lawyer's bar and structure of practice (RIN art. 10.2). What is
 * asked is the profile's (ProfileInterface::getIdentification()); where it
 * is read is the same for all: the settings, and the main office's address.
 */
final class IdentificationCheck implements ComplianceCheckInterface
{
    public function __construct(
        private readonly ProfileInterface $profile,
        private readonly Record $record,
        private readonly OfficeRepository $offices,
    ) {
    }

    public function check(): ComplianceResult
    {
        $office = $this->offices->findMain();
        $missing = [];
        foreach ($this->profile->getIdentification() as $word => $item) {
            $there = match ($item) {
                ProfileInterface::NAME => null !== $this->record->getName(),
                ProfileInterface::ADDRESS => null !== $office && null !== $office->getStreet() && null !== $office->getCity(),
                ProfileInterface::PHONE => null !== $office && null !== $office->getPhone(),
                ProfileInterface::CONTACT => null !== $office && (null !== $office->getPhone() || null !== $office->getEmail()),
                ProfileInterface::BODY => null !== $this->record->getBody(),
                default => null !== $this->record->get($item),
            };
            if (!$there) {
                $missing[] = $word;
            }
        }
        $label = $this->profile->getProfession()->value.'.compliance.identification';

        return [] === $missing
            ? ComplianceResult::ok($label, 'legal')
            : new ComplianceResult($label, ComplianceResult::MISSING, $label.'_advice', 'legal', ['missing' => implode(', ', $missing)]);
    }
}
