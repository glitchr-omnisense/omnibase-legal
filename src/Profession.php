<?php

namespace Base\Legal;

/**
 * The profession a site is for (legal.profession): one site, one
 * profession. Each has its profile - a folder of src/ loaded when it is the
 * one chosen, and then alone: its compliance checks, the words its
 * communication must not carry, its own entities, its demonstration
 * accounts, its pages.
 */
enum Profession: string
{
    case LAWYER = 'lawyer';
    case NOTARY = 'notary';
    case JURIST = 'jurist';

    /**
     * Whether the profession keeps a practice (a firm, an office) on
     * omnibase/office: fields of practice, appointments asked, the client's
     * space, the compliance widget. A jurist who teaches, writes and trains
     * has none: the profile is then the guard on what is offered, nothing more.
     */
    public function hasPractice(): bool
    {
        return self::JURIST !== $this;
    }

    /** The profile's folder under src/, and its namespace under Base\Legal. */
    public function folder(): string
    {
        return ucfirst($this->value);
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $profession) => $profession->value, self::cases());
    }
}
