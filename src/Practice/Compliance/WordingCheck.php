<?php

namespace Base\Legal\Practice\Compliance;

use Base\Legal\Practice\Guard\Wording;
use Base\Legal\Practice\ProfileInterface;
use Base\Legal\Practice\Repository\AreaRepository;
use Base\Legal\Practice\Service\Record;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;
use Base\Office\Repository\MemberRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * What the practice wrote for the public - the fields of practice, the
 * team's biographies, what it typed in the settings about its fees - read
 * again for comparison, disparagement and the superlatives of advertising,
 * and for what the profession forbids of its own
 * (ProfileInterface::getWordingPatterns(), which says on which texts this
 * rests). A warning names where and what.
 */
final class WordingCheck implements ComplianceCheckInterface
{
    /** @param list<string> $extra */
    public function __construct(
        private readonly ProfileInterface $profile,
        private readonly AreaRepository $areas,
        private readonly MemberRepository $members,
        private readonly Record $record,
        #[Autowire('%legal.wording%')] private readonly array $extra = [],
    ) {
    }

    public function check(): ComplianceResult
    {
        $found = [];
        foreach ($this->areas->findActive() as $area) {
            $this->collect($found, $area->getName(), $area->getPublicText());
        }
        foreach ($this->members->findVisible() as $member) {
            $this->collect($found, $member->getDisplayName(), $member->getTitle()."\n".$member->getBiography());
        }
        $this->collect($found, 'Réglages', $this->record->getPublishedText());
        $label = $this->profile->getProfession()->value.'.compliance.wording';

        return [] === $found
            ? ComplianceResult::ok($label, 'legal')
            : new ComplianceResult($label, ComplianceResult::WARNING, $label.'_advice', 'legal', ['found' => implode(' ; ', $found)]);
    }

    /** @param list<string> $found */
    private function collect(array &$found, string $where, ?string $text): void
    {
        $matches = Wording::scan($text, $this->extra, $this->profile->getWordingPatterns());
        if ([] !== $matches) {
            $found[] = sprintf('%s : « %s »', $where, implode(' », « ', $matches));
        }
    }
}
