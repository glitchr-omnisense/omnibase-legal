<?php

namespace Base\Legal\Jurist\Service;

use Base\Legal\Jurist\Enum\ReservedActivity;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * What a jurist may offer who is neither a lawyer nor a notary (French law
 * no. 71-1130 of 31 December 1971, art. 54 and following): no personalised
 * legal consultation, no deed drafted for someone else - unless a status
 * allows it all the same (legal.jurist.authorized_status: a university
 * teacher of law under art. 57, another regulated profession within its own
 * field under art. 59). Training, documentary information (art. 66-1),
 * writing content, talks are open to all.
 *
 * The guard reads words, whatever they were filed under: a "training" sold
 * as "consultation juridique personnalisée" is caught. It is on with the
 * profession - a site whose profession is jurist has it; a lawyer's or a
 * notary's site does not load it. A safeguard, not legal advice: what it
 * does not find is not thereby allowed.
 *
 * What is offered on the site is read through it by the bundle that holds
 * the offerings (omnibase/consulting: Base\Legal\Jurist\Consulting\OfferingRule).
 */
final class RegulatedActivityGuard
{
    public function __construct(
        #[Autowire('%legal.jurist.authorized_status%')] private readonly ?string $authorizedStatus = null,
    ) {
    }

    /** Whether the jurist holds a status that allows legal advice. */
    public function isAuthorized(): bool
    {
        return null !== $this->authorizedStatus && '' !== $this->authorizedStatus;
    }

    public function getAuthorizedStatus(): ?string
    {
        return $this->isAuthorized() ? $this->authorizedStatus : null;
    }

    /** The reserved activity a text announces - whoever wrote it, whatever their status. */
    public function read(?string $text): ?ReservedActivity
    {
        $text = trim(strip_tags((string) $text));
        if ('' === $text) {
            return null;
        }
        foreach (ReservedActivity::cases() as $activity) {
            foreach ($activity->patterns() as $pattern) {
                if (1 === preg_match($pattern, $text)) {
                    return $activity;
                }
            }
        }

        return null;
    }

    /**
     * Why this jurist may not publish the text, or null when he may.
     *
     * @return string|null a translation key of the `legal` domain
     */
    public function check(?string $text): ?string
    {
        if ($this->isAuthorized()) {
            return null;
        }
        $activity = $this->read($text);

        return null === $activity ? null : 'jurist.guard.'.$activity->value;
    }
}
