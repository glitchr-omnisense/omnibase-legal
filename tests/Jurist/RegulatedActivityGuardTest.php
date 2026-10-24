<?php

namespace Base\Legal\Tests\Jurist;

use Base\Legal\Jurist\Enum\ReservedActivity;
use Base\Legal\Jurist\Service\RegulatedActivityGuard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * What a jurist may not offer (law no. 71-1130 of 31 December 1971, art.
 * 54): the words that announce a personalised legal consultation or deeds
 * drafted for others, whatever the offering was filed under - unless a
 * status allows it all the same (art. 57, 59). What it was in
 * omnibase/consulting (RegulatedActivityGuardTest), on words now: the
 * activities themselves are no longer that bundle's.
 */
final class RegulatedActivityGuardTest extends TestCase
{
    /** @dataProvider reserved */
    #[DataProvider('reserved')]
    public function testTheWordsOfAReservedActivityAreRefused(string $text, ReservedActivity $activity): void
    {
        $guard = new RegulatedActivityGuard();

        self::assertSame($activity, $guard->read($text), $text);
        self::assertSame('jurist.guard.'.$activity->value, $guard->check($text), $text);
    }

    public static function reserved(): iterable
    {
        yield ['Une consultation juridique personnalisée sur votre dossier.', ReservedActivity::LEGAL_CONSULTATION];
        yield ['Consultations juridiques', ReservedActivity::LEGAL_CONSULTATION];
        yield ['Un conseil juridique sur mesure pour votre entreprise.', ReservedActivity::LEGAL_CONSULTATION];
        yield ['Avis juridique personnalisé', ReservedActivity::LEGAL_CONSULTATION];
        yield ['<p>Personalised <b>legal advice</b> on your case.</p>', ReservedActivity::LEGAL_CONSULTATION];
        yield ['Rédaction d’actes pour votre entreprise.', ReservedActivity::DEED_DRAFTING];
        yield ["Redaction d'actes", ReservedActivity::DEED_DRAFTING];
        yield ['Drafting contracts for clients.', ReservedActivity::DEED_DRAFTING];
    }

    /** @dataProvider open */
    #[DataProvider('open')]
    public function testTrainingInformationAndWritingAreOpenToAll(?string $text): void
    {
        self::assertNull((new RegulatedActivityGuard())->check($text), (string) $text);
    }

    public static function open(): iterable
    {
        yield ['Le droit des contrats pour les associations : une formation de deux jours, cas pratiques et documentation.'];
        yield ['Veille juridique'];
        yield ['Rédaction de contenus juridiques : articles de vulgarisation, fiches pédagogiques.'];
        yield ['Une conférence sur le règlement général sur la protection des données.'];
        yield ['Conseil en organisation'];
        yield [''];
        yield [null];
    }

    public function testAStatusThatAllowsItLiftsTheGuard(): void
    {
        $teacher = new RegulatedActivityGuard('teacher');

        self::assertTrue($teacher->isAuthorized());
        self::assertSame('teacher', $teacher->getAuthorizedStatus());
        self::assertNull($teacher->check('Une consultation juridique personnalisée.'));
        self::assertSame(ReservedActivity::LEGAL_CONSULTATION, $teacher->read('Une consultation juridique personnalisée.'), 'it still knows what the words say');

        self::assertFalse((new RegulatedActivityGuard())->isAuthorized());
        self::assertFalse((new RegulatedActivityGuard(''))->isAuthorized());
    }

    public function testEachReservedActivityHasItsWordsInFrenchAndInEnglish(): void
    {
        foreach (['fr', 'en', 'de'] as $locale) {
            $catalogue = \Symfony\Component\Yaml\Yaml::parseFile(\dirname(__DIR__, 2).'/translations/legal+intl-icu.'.$locale.'.yaml')['jurist'];
            foreach (ReservedActivity::cases() as $activity) {
                self::assertNotEmpty($catalogue['activity'][$activity->value] ?? null, "$activity->value in $locale");
                self::assertNotEmpty($catalogue['guard'][$activity->value] ?? null, "$activity->value in $locale");
                self::assertSame('jurist.activity.'.$activity->value, $activity->label());
            }
        }
    }
}
