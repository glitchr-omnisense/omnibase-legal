<?php

namespace Base\Legal\Practice\Compliance;

use Base\Legal\Practice\ProfileInterface;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The consumer mediator is named on the site, with how to reach it: the
 * page /mediation and the legal notice print legal.mediator - by default
 * the mediator the profession set up. For a notary: "Le notaire doit faire
 * connaître par tous moyens lisibles et appropriés la possibilité pour le
 * client d'avoir recours au médiateur de la consommation du notariat en cas
 * de différend entre eux et en indiquer les coordonnées" (règlement
 * professionnel du notariat, art. 25.1). Missing: no name, or no way to
 * reach it; a warning for a site that is not https.
 */
final class MediatorCheck implements ComplianceCheckInterface
{
    /** @param array{name?: ?string, address?: ?string, email?: ?string, url?: ?string} $mediator */
    public function __construct(
        private readonly ProfileInterface $profile,
        #[Autowire('%legal.mediator%')] private readonly array $mediator = [],
    ) {
    }

    public function check(): ComplianceResult
    {
        $label = $this->profile->getProfession()->value.'.compliance.mediator';
        $name = trim((string) ($this->mediator['name'] ?? ''));
        $url = trim((string) ($this->mediator['url'] ?? ''));
        $reach = trim((string) ($this->mediator['address'] ?? '')).$url.trim((string) ($this->mediator['email'] ?? ''));

        if ('' === $name || '' === $reach) {
            return new ComplianceResult($label, ComplianceResult::MISSING, $label.'_advice', 'legal');
        }
        if ('' !== $url && 1 !== preg_match('~^https://~i', $url)) {
            return new ComplianceResult($label, ComplianceResult::WARNING, 'compliance.mediator_url', 'legal', ['url' => $url]);
        }

        return ComplianceResult::ok($label, 'legal');
    }
}
