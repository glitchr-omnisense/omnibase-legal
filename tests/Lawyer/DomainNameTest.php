<?php

namespace Base\Legal\Tests\Lawyer;

use Base\Legal\Lawyer\Guard\DomainName;
use PHPUnit\Framework\Attributes\DataProvider;
use Base\Legal\Tests\NeedsOffice;
use PHPUnit\Framework\TestCase;

/**
 * RIN art. 10.5: the domain name carries the lawyer's name or the firm's
 * denomination, in full or abbreviated, before or after the word "avocat";
 * a name evoking generically the title, a field of law or an activity of
 * the profession is forbidden.
 */
final class DomainNameTest extends TestCase
{
    use NeedsOffice;

    private const NAMES = ['Cabinet Maréval', 'Me Hortense Maréval', 'Me Lucien d’Orgeval'];

    /** @dataProvider domains */
    #[DataProvider('domains')]
    public function testADomainNameIsReadAgainstTheNames(string $host, string $expected): void
    {
        self::assertSame($expected, DomainName::assess($host, self::NAMES), $host);
    }

    public static function domains(): iterable
    {
        // The name, alone or with "avocat".
        yield ['mareval-avocat.example', DomainName::OK];
        yield ['www.mareval-avocat.example', DomainName::OK];
        yield ['avocat-mareval.example', DomainName::OK];
        yield ['mareval.example', DomainName::OK];
        yield ['cabinet-mareval.example', DomainName::OK];
        yield ['marevalavocats.example', DomainName::OK];
        yield ['hortense-mareval.avocat.example', DomainName::OK];
        yield ['https://MAREVAL-AVOCATS.example/honoraires', DomainName::OK];
        yield ['orgeval-avocat.example', DomainName::OK];

        // Generic: the title, a field of law, an activity - and no name.
        yield ['avocat-divorce.example', DomainName::GENERIC];
        yield ['avocats.example', DomainName::GENERIC];
        yield ['droit-du-travail.example', DomainName::GENERIC];
        yield ['avocat-permis.example', DomainName::GENERIC];
        yield ['cabinet-juridique.example', DomainName::GENERIC];
        yield ['defense-penale.example', DomainName::GENERIC];

        // The name, and a field of law besides.
        yield ['mareval-divorce.example', DomainName::MIXED];
        yield ['mareval-avocat-droit-travail.example', DomainName::MIXED];

        // No name the site knows.
        yield ['dupont-avocat.example', DomainName::UNNAMED];
        yield ['lexemplum.example', DomainName::UNNAMED];

        // Nothing to assess.
        yield ['localhost', DomainName::LOCAL];
        yield ['localhost:8573', DomainName::LOCAL];
        yield ['127.0.0.1', DomainName::LOCAL];
        yield ['avocat.local', DomainName::LOCAL];
        yield ['', DomainName::LOCAL];
    }

    public function testTheInitialsOfADenominationAreAnAbbreviation(): void
    {
        $names = ['Dupont Martin & Associés'];

        self::assertSame(DomainName::OK, DomainName::assess('dma-avocats.example', $names));
        self::assertSame(DomainName::OK, DomainName::assess('dupont-martin.example', $names));
        self::assertSame(DomainName::UNNAMED, DomainName::assess('xyz-avocats.example', $names));
    }

    public function testWithoutAnyNameKnownNothingPasses(): void
    {
        self::assertSame(DomainName::UNNAMED, DomainName::assess('mareval-avocat.example', []));
        self::assertSame(DomainName::GENERIC, DomainName::assess('avocat-divorce.example', []));
    }

    public function testAFieldOfLawInAFirmsNameDoesNotMakeItsName(): void
    {
        // "Cabinet Droit Social" has no name in it: its words are generic.
        self::assertSame(DomainName::GENERIC, DomainName::assess('droit-social.example', ['Cabinet Droit Social']));
    }
}
