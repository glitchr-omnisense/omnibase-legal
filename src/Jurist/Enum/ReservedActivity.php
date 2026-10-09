<?php

namespace Base\Legal\Jurist\Enum;

/**
 * What the law reserves to the professions it allows (French law no.
 * 71-1130 of 31 December 1971, art. 54): giving personalised legal advice,
 * and drafting deeds for others. Training, documentary information (art.
 * 66-1), writing content, a talk are open to anyone - they are no case
 * here. Each case knows the words that announce it, in French and in
 * English.
 */
enum ReservedActivity: string
{
    case LEGAL_CONSULTATION = 'legal_consultation';
    case DEED_DRAFTING = 'deed_drafting';

    /** Its name, a key of the `legal` domain. */
    public function label(): string
    {
        return 'jurist.activity.'.$this->value;
    }

    /** @return list<string> the regular expressions that give it away in a text */
    public function patterns(): array
    {
        return match ($this) {
            self::LEGAL_CONSULTATION => [
                '/\bconsultations?\s+juridiques?\b/iu',
                '/\bconseils?\s+juridiques?\s+(personnalis|individuel|sur\s+mesure)/iu',
                '/\bavis\s+juridiques?\s+(personnalis|individuel)/iu',
                '/\blegal\s+(advice|consultation|opinion)s?\b/iu',
            ],
            self::DEED_DRAFTING => [
                '/\br[ée]daction\s+d[\'’]actes?\b/iu',
                '/\bdraft(ing)?\s+(of\s+)?(contracts?|deeds?|legal\s+documents?)\s+for\s+(you|clients?|others)\b/iu',
            ],
        };
    }
}
