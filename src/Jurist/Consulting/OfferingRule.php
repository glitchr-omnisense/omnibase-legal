<?php

namespace Base\Legal\Jurist\Consulting;

use Base\Consulting\Entity\Offering;
use Base\Consulting\Offering\OfferingRuleInterface;
use Base\Consulting\Offering\OfferingViolation;
use Base\Legal\Jurist\Service\RegulatedActivityGuard;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The jurist's guard on what omnibase/consulting offers: an offering whose
 * title, sentence or presentation announces a legal consultation or the
 * drafting of deeds does not validate - in the back office's form the
 * reason is printed under the text that carries the words.
 *
 * Loaded when omnibase/consulting is installed (config/services.php): that
 * bundle knows nothing of the law, it asks its rules.
 */
final class OfferingRule implements OfferingRuleInterface
{
    public function __construct(
        private readonly RegulatedActivityGuard $guard,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function check(Offering $offering): ?OfferingViolation
    {
        foreach (['title' => $offering->getTitle(), 'summary' => $offering->getSummary(), 'description' => $offering->getDescription()] as $path => $text) {
            if (null !== $reason = $this->guard->check($text)) {
                return new OfferingViolation($this->translator->trans($reason, [], 'legal'), $path);
            }
        }

        return null;
    }
}
