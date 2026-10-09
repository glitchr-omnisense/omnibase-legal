<?php

namespace Base\Legal\Tests;

/**
 * A test of the practice or of a profile that keeps one (a lawyer's, a
 * notary's): it needs omnibase/office, which a jurist's site does not have.
 */
trait NeedsOffice
{
    /** @before */
    #[\PHPUnit\Framework\Attributes\Before]
    protected function needsOffice(): void
    {
        if (!class_exists(\Base\Office\Entity\Office::class)) {
            self::markTestSkipped('Needs omnibase/office: the practice of a lawyer or of a notary.');
        }
    }
}
