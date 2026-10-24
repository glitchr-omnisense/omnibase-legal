<?php

namespace Base\Legal\Tests\Practice;

use Base\Legal\Lawyer\LawyerProfile;
use Base\Legal\Notary\NotaryProfile;
use Base\Legal\Practice\Guard\Wording;
use PHPUnit\Framework\Attributes\DataProvider;
use Base\Legal\Tests\NeedsOffice;
use PHPUnit\Framework\TestCase;

/**
 * No comparison, no disparagement, none of advertising's superlatives - a
 * notary's règlement professionnel (art. 14.1 and 14.2; decree n° 73-1202
 * as amended), a lawyer's RIN (art. 10.2) - and what a profession adds: a
 * lawyer's reference to judicial functions.
 */
final class WordingTest extends TestCase
{
    use NeedsOffice;

    /** @dataProvider forbidden */
    #[DataProvider('forbidden')]
    public function testComparisonAndAdvertisingAreFoundWhateverTheProfession(string $text, string $label): void
    {
        self::assertContains($label, Wording::scan($text), $text);
        self::assertContains($label, Wording::scan($text, [], (new NotaryProfile())->getWordingPatterns()), $text);
        self::assertContains($label, Wording::scan($text, [], (new LawyerProfile())->getWordingPatterns()), $text);
    }

    public static function forbidden(): iterable
    {
        yield ['Le meilleur notaire du canton.', 'le meilleur'];
        yield ['Le meilleur avocat du barreau.', 'le meilleur'];
        yield ['Nos tarifs sont MEILLEURS QUE ceux d’à côté', 'meilleur que'];
        yield ['Office n°1 de la région', 'numéro 1'];
        yield ['L’étude numéro un des successions', 'numéro 1'];
        yield ['Le cabinet numéro un du divorce', 'numéro 1'];
        yield ['Des honoraires moins chers.', 'moins cher'];
        yield ['Plus réactifs que nos confrères', 'plus … que nos confrères'];
        yield ['Contrairement à d’autres études, nous répondons.', 'contrairement à'];
        yield ['Contrairement à d’autres cabinets, nous répondons.', 'contrairement à'];
        yield ['Un savoir-faire <em>inégalé</em>', 'incomparable'];
        yield ['Résultat garanti', 'résultat garanti'];
        yield ['Offre spéciale de rentrée', 'offre spéciale'];
    }

    /** @dataProvider judicial */
    #[DataProvider('judicial')]
    public function testALawyerDoesNotReferToJudicialFunctions(string $text): void
    {
        self::assertContains('fonctions juridictionnelles', Wording::scan($text, [], (new LawyerProfile())->getWordingPatterns()), $text);
        self::assertSame([], Wording::scan($text, [], (new NotaryProfile())->getWordingPatterns()), 'the lawyers\' rule alone');
    }

    public static function judicial(): iterable
    {
        yield ['Ancien magistrat, il plaide depuis 2012.'];
        yield ['Avocate et juge consulaire.'];
        yield ['Ancienne conseillère prud’homale.'];
    }

    /** @dataProvider allowed */
    #[DataProvider('allowed')]
    public function testThePlainWordsOfTheTradeAreNot(string $text): void
    {
        self::assertSame([], Wording::scan($text, [], (new LawyerProfile())->getWordingPatterns()), $text);
        self::assertSame([], Wording::scan($text, [], (new NotaryProfile())->getWordingPatterns()), $text);
    }

    public static function allowed(): iterable
    {
        yield ['Vente en l’état futur d’achèvement et promotion immobilière.'];
        yield ['Nous vous répondons dans les meilleurs délais.'];
        yield ['Droit de la concurrence et de la distribution.'];
        yield ['L’office accompagne les familles depuis 1987.'];
        yield ['Le cabinet assiste les salariés devant le conseil de prud’hommes.'];
        yield ['Le juge aux affaires familiales fixe la résidence des enfants.'];
        yield ['Le bail commercial est plus protecteur que le bail dérogatoire.'];
        yield [''];
    }

    public function testTheSitesOwnExpressionsAreAdded(): void
    {
        self::assertSame(['Étude de référence'], Wording::scan('L’étude de référence du Val.', ['Étude de référence']));
        self::assertSame([], Wording::scan('Les références cadastrales.', ['Étude de référence']));
        self::assertTrue(Wording::isClean('Rien à signaler.'));
        self::assertFalse(Wording::isClean('Ancien juge.', [], (new LawyerProfile())->getWordingPatterns()));
    }

    public function testEachExpressionIsReportedOnce(): void
    {
        self::assertSame(['numéro 1'], Wording::scan('N°1 ici, numéro 1 là.'));
    }
}
