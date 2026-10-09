<?php

namespace Base\Legal\Practice\Guard;

use Base\Office\Guard\Wording as OfficeWording;

/**
 * Words the communication of a lawyer or of a notary must not carry:
 * comparison with colleagues, disparagement, the superlatives of
 * advertising - and what a profession adds of its own (a lawyer's reference
 * to judicial functions). Each profile cites its texts where it gives its
 * patterns (ProfileInterface::getWordingPatterns()).
 *
 * A list of expressions is a help to the person who writes, not a judge:
 * what it finds is shown as a warning, and what it does not find is not
 * thereby allowed.
 *
 * The common expressions are omnibase/office's (Base\Office\Guard\Wording).
 */
final class Wording
{
    /**
     * @param list<string>          $extra    expressions of the site's own, matched as they are written (case and accents apart)
     * @param array<string, string> $patterns the profession's own, label => regular expression on the plain text
     *
     * @return list<string> what was found, by its label
     */
    public static function scan(?string $text, array $extra = [], array $patterns = []): array
    {
        return OfficeWording::scan($text, $extra, $patterns);
    }

    public static function isClean(?string $text, array $extra = [], array $patterns = []): bool
    {
        return [] === self::scan($text, $extra, $patterns);
    }

    /** Lowered, without accents nor tags, typographic apostrophes and spaces made plain. */
    public static function plain(string $text): string
    {
        return OfficeWording::plain($text);
    }
}
