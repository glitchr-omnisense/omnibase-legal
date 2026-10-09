<?php

namespace Base\Legal\Practice\Twig;

use Base\Legal\Practice\Entity\Area;
use Base\Legal\Practice\ProfileInterface;
use Base\Legal\Practice\Repository\AreaRepository;
use Base\Legal\Practice\Service\Record;
use Base\Office\Entity\Member;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * The practice in templates, whatever the profession: legal_profession()
 * ("lawyer", "notary": the bundle's texts of a profession are under its
 * name), legal_record() (what the settings keep: the name, the professional
 * body, the fees, and the profession's own), legal_areas(member|null) (the
 * fields of practice, or a member's), legal_mediator(), legal_rules_url(),
 * legal_fees_route() (the page that says what it costs). A profile adds its
 * own functions (legal_attorney(), legal_payment(); legal_tariff()).
 */
class LegalExtension extends AbstractExtension
{
    public function __construct(
        private readonly ProfileInterface $profile,
        private readonly Record $record,
        private readonly AreaRepository $areas,
        #[Autowire('%legal.mediator%')] private readonly array $mediator = [],
        #[Autowire('%legal.rules_url%')] private readonly ?string $rulesUrl = null,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('legal_profession', fn (): string => $this->profile->getProfession()->value),
            new TwigFunction('legal_record', fn (): Record => $this->record),
            /** @return list<Area> */
            new TwigFunction('legal_areas', fn (?Member $member = null): array => null === $member ? $this->areas->findActive() : $this->areas->findForMember($member)),
            new TwigFunction('legal_mediator', fn (): array => $this->mediator),
            new TwigFunction('legal_rules_url', fn (): ?string => $this->rulesUrl),
            new TwigFunction('legal_fees_route', fn (): string => $this->profile->getFeesRoute()),
        ];
    }
}
