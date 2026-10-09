<?php

namespace Base\Legal\Tests\DependencyInjection;

use Base\Legal\DependencyInjection\LegalConfiguration;
use Base\Legal\DependencyInjection\LegalExtension;
use Base\Legal\Profession;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

/**
 * One site, one profession: it is asked, and what the site does not say is
 * the profession's - who reads a document, which mediator, where the rules
 * are; no parameter of another profession's profile.
 */
final class ConfigurationTest extends TestCase
{
    public function testTheProfessionIsAsked(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->process([]);
    }

    public function testAProfessionTheBundleDoesNotKnowIsRefused(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->process(['profession' => 'bailiff']);
    }

    public function testWhatTheSiteDoesNotSayIsTheProfessions(): void
    {
        $lawyer = $this->process(['profession' => 'lawyer']);
        self::assertSame(['ROLE_LAWYER'], $lawyer['roles']['readers']);
        self::assertSame('Médiateur de la consommation de la profession d’avocat', $lawyer['mediator']['name']);
        self::assertStringContainsString('cnb.avocat.fr', $lawyer['rules_url']);
        self::assertSame([], $lawyer['lawyer']['domains']);
        self::assertFalse($lawyer['lawyer']['payment']['enabled']);
        self::assertArrayNotHasKey('notary', $lawyer, 'no parameter of another profession');
        self::assertArrayNotHasKey('jurist', $lawyer);

        $notary = $this->process(['profession' => 'notary']);
        self::assertSame(['ROLE_NOTARY', 'ROLE_CLERK'], $notary['roles']['readers']);
        self::assertSame('https://mediateur-notariat.notaires.fr', $notary['mediator']['url']);
        self::assertSame('notaires.fr', $notary['notary']['email_suffix']);
        self::assertSame('2028-02-29', $notary['notary']['tariff']['until']);
        self::assertArrayNotHasKey('lawyer', $notary);

        $jurist = $this->process(['profession' => 'jurist']);
        self::assertNull($jurist['jurist']['authorized_status']);
        self::assertSame([], $jurist['roles']['readers']);
    }

    public function testWhatTheSiteSaysWins(): void
    {
        $config = $this->process(['profession' => 'notary', 'roles' => ['readers' => 'ROLE_NOTARY'], 'mediator' => ['name' => 'Un autre médiateur', 'url' => 'https://example.org'], 'notary' => ['email_suffix' => 'notaires.example']]);

        self::assertSame(['ROLE_NOTARY'], $config['roles']['readers']);
        self::assertSame(['name' => 'Un autre médiateur', 'address' => null, 'email' => null, 'url' => 'https://example.org'], $config['mediator'], 'a mediator named replaces the profession\'s whole');
        self::assertSame('notaires.example', $config['notary']['email_suffix']);
    }

    public function testAJuristsStatusIsTeacherOrAnotherRegulatedProfession(): void
    {
        self::assertSame('teacher', $this->process(['profession' => 'jurist', 'jurist' => ['authorized_status' => 'teacher']])['jurist']['authorized_status']);

        $this->expectException(InvalidConfigurationException::class);
        $this->process(['profession' => 'jurist', 'jurist' => ['authorized_status' => 'lawyer']]);
    }

    public function testTheLastFileThatSetsTheProfessionWins(): void
    {
        self::assertSame(Profession::NOTARY, LegalExtension::profession([['profession' => 'lawyer'], ['mediator' => []], ['profession' => 'notary']]));
        self::assertNull(LegalExtension::profession([['mediator' => []]]));
        self::assertTrue(Profession::LAWYER->hasPractice());
        self::assertTrue(Profession::NOTARY->hasPractice());
        self::assertFalse(Profession::JURIST->hasPractice(), 'a jurist keeps no practice on omnibase/office');
    }

    /** @return array<string, mixed> */
    private function process(array $config): array
    {
        return LegalConfiguration::withProfessionDefaults((new Processor())->processConfiguration(new LegalConfiguration(), [$config]));
    }
}
