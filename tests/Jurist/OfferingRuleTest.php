<?php

namespace Base\Legal\Tests\Jurist;

use Base\Consulting\Entity\Offering;
use Base\Consulting\Enum\Activity;
use Base\Legal\Jurist\Consulting\OfferingRule;
use Base\Legal\Jurist\Service\RegulatedActivityGuard;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The jurist's guard on omnibase/consulting's offerings: refused under the
 * text that carries the words - the title, the sentence, the presentation.
 */
final class OfferingRuleTest extends TestCase
{
    protected function setUp(): void
    {
        if (!interface_exists(\Base\Consulting\Offering\OfferingRuleInterface::class)) {
            self::markTestSkipped('Needs omnibase/consulting with its offering rules.');
        }
    }

    public function testAnOfferingIsRefusedUnderTheTextThatCarriesTheWords(): void
    {
        $rule = $this->rule();

        self::assertSame('title', $rule->check(new Offering('Consultation juridique', Activity::CONSULTING))?->path);
        $summary = $rule->check((new Offering('Accompagnement', Activity::TRAINING))->setSummary('Une consultation juridique personnalisée sur votre dossier.'));
        self::assertSame('summary', $summary?->path, 'filed as training, sold as advice');
        self::assertSame('[legal] jurist.guard.legal_consultation', $summary->message);
        self::assertSame('description', $rule->check((new Offering('Contrats', Activity::WRITING))->setDescription('<p>Rédaction d’actes pour votre entreprise.</p>'))?->path);

        self::assertNull($rule->check((new Offering('Formation au RGPD', Activity::TRAINING))->setSummary('Une journée sur les principes du règlement.')), 'training is open to all');
    }

    public function testAStatusLiftsIt(): void
    {
        self::assertNull($this->rule('regulated_profession')->check(new Offering('Consultation juridique', Activity::CONSULTING)));
    }

    private function rule(?string $status = null): OfferingRule
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(static fn (string $id, array $parameters = [], ?string $domain = null) => "[$domain] $id");

        return new OfferingRule(new RegulatedActivityGuard($status), $translator);
    }
}
