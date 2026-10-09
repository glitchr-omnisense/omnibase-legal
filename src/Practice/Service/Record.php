<?php

namespace Base\Legal\Practice\Service;

use Base\Service\SettingBagInterface;

/**
 * What only the practice knows and the back office's settings keep. Every
 * profession has these four: the practice's name (a firm's denomination,
 * the structure that holds an office), its professional body (the bar, the
 * chamber), the day the site was declared to it, how its fees are set. A
 * profession's record adds its own (Base\Legal\Lawyer\Service\LawyerRecord,
 * Base\Legal\Notary\Service\NotaryRecord) and says which fields the
 * settings' form shows.
 *
 * Read without ever failing a page: a settings table not there yet answers
 * nothing.
 */
abstract class Record
{
    public const NAME = 'legal.practice.name';
    public const BODY = 'legal.body.name';
    public const DECLARED_AT = 'legal.body.declared_at';
    public const FEES = 'legal.fees.terms';

    public function __construct(private readonly SettingBagInterface $settings)
    {
    }

    /**
     * The settings' fields of the profession, in the form's order: the
     * setting's key => its name in the profession's labels
     * (<profession>.settings.<name>).
     *
     * @return array<string, string>
     */
    abstract public function getFields(): array;

    /** @return list<string> the keys among them typed on several lines */
    public function getLongFields(): array
    {
        return [self::FEES];
    }

    /** What the practice typed here and publishes on its pages: read again by the wording check. */
    public function getPublishedText(): string
    {
        return (string) $this->getFees();
    }

    public function get(string $path): ?string
    {
        try {
            $value = $this->settings->getScalar($path);
        } catch (\Throwable) {
            return null;
        }
        $value = \is_scalar($value) ? trim((string) $value) : '';

        return '' === $value ? null : $value;
    }

    public function getName(): ?string { return $this->get(self::NAME); }
    public function getBody(): ?string { return $this->get(self::BODY); }
    public function getFees(): ?string { return $this->get(self::FEES); }

    /** The day the site was declared to the professional body, when what was typed is a date. */
    public function getDeclaredAt(): ?\DateTimeImmutable
    {
        $value = $this->get(self::DECLARED_AT);
        if (null === $value) {
            return null;
        }
        foreach (['!Y-m-d', '!d/m/Y', '!d.m.Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);
            if (false !== $date && $date->format(ltrim($format, '!')) === $value) {
                return $date;
            }
        }

        return null;
    }

    /** A setting answered "oui": a box the practice ticks in words. */
    protected function isYes(string $path): bool
    {
        return \in_array(mb_strtolower((string) $this->get($path)), ['1', 'oui', 'yes', 'true', 'on'], true);
    }
}
