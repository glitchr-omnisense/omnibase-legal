<?php

namespace Base\Legal\Tests\Notary;

use Base\Legal\Notary\Compliance\BrandBlockCheck;
use Base\Legal\Notary\Compliance\ChamberDeclarationCheck;
use Base\Legal\Notary\Compliance\ProfessionalEmailCheck;
use Base\Legal\Notary\Compliance\TariffDisplayCheck;
use Base\Legal\Notary\Service\NotaryRecord;
use Base\Office\Compliance\ComplianceResult;
use Base\Office\Entity\Office;
use Base\Office\Repository\OfficeRepository;
use Base\Service\SettingBagInterface;
use PHPUnit\Framework\TestCase;

/**
 * The notaries' own rules as the back office's "Conformité" widget answers
 * them (what every practice answers - identification, mediator, wording -
 * is in tests/Practice/ChecksTest).
 */
final class ChecksTest extends TestCase
{
    // ── Art. 16.3: addresses ending in notaires.fr ───────────────────

    public function testAnOfficeAddressOutsideNotairesFrIsMissing(): void
    {
        $result = (new ProfessionalEmailCheck($this->offices($this->office('contact@etude.example')), 'etude@notaires.fr'))->check();

        self::assertSame(ComplianceResult::MISSING, $result->status);
        self::assertSame('contact@etude.example', $result->parameters['addresses']);
        self::assertSame('notary.compliance.email', $result->label);
        self::assertSame('legal', $result->domain);
    }

    public function testNoAddressAtAllIsMissing(): void
    {
        self::assertSame('notary.compliance.email_none', (new ProfessionalEmailCheck($this->offices($this->office(null))))->check()->advice);
    }

    public function testTheSitesSenderIsOnlyAWarning(): void
    {
        $result = (new ProfessionalEmailCheck($this->offices($this->office('etude.dupont@notaires.fr')), 'noreply@site.example'))->check();

        self::assertSame(ComplianceResult::WARNING, $result->status);
        self::assertSame('notary.compliance.email_sender', $result->advice);
    }

    public function testNotarialAddressesPass(): void
    {
        self::assertTrue((new ProfessionalEmailCheck($this->offices($this->office('etude.dupont@notaires.fr')), 'etude.dupont@notaires.fr'))->check()->isOk());
        self::assertTrue((new ProfessionalEmailCheck($this->offices($this->office('etude.dupont@notaires.fr')), null))->check()->isOk());
    }

    // ── Art. 14.4: the chamber is told ───────────────────────────────

    public function testTheSiteMustBeDeclaredToItsChamber(): void
    {
        $unnamed = (new ChamberDeclarationCheck($this->record([])))->check();
        self::assertSame('notary.compliance.chamber', $unnamed->label);
        self::assertSame('notary.compliance.chamber_body_advice', $unnamed->advice);

        $undated = (new ChamberDeclarationCheck($this->record([NotaryRecord::BODY => 'Chambre des notaires de la Côte-d’Or'])))->check();
        self::assertSame(ComplianceResult::MISSING, $undated->status);
        self::assertSame('notary.compliance.chamber_advice', $undated->advice);
        self::assertSame('Chambre des notaires de la Côte-d’Or', $undated->parameters['body']);

        self::assertSame(ComplianceResult::MISSING, (new ChamberDeclarationCheck($this->record([NotaryRecord::BODY => 'Chambre', NotaryRecord::DECLARED_AT => 'bientôt'])))->check()->status, 'not a date');
        self::assertSame(ComplianceResult::WARNING, (new ChamberDeclarationCheck($this->record([NotaryRecord::BODY => 'Chambre', NotaryRecord::DECLARED_AT => '2999-01-01'])))->check()->status);
        self::assertTrue((new ChamberDeclarationCheck($this->record([NotaryRecord::BODY => 'Chambre', NotaryRecord::DECLARED_AT => '2026-09-14'])))->check()->isOk());
        self::assertTrue((new ChamberDeclarationCheck($this->record([NotaryRecord::BODY => 'Chambre', NotaryRecord::DECLARED_AT => '14/09/2026'])))->check()->isOk());
    }

    // ── Code de commerce, art. L. 444-4: tariff and discounts displayed ──

    public function testTheTariffPageLinksToTheOfficialTextAndPrintsTheDiscounts(): void
    {
        $tariff = ['code_url' => 'https://www.legifrance.gouv.fr/codes/texte_lc/LEGITEXT000005634379', 'order' => 'arrêté du 25 février 2026', 'order_url' => '', 'until' => '2028-02-29'];
        $today = new \DateTimeImmutable('2026-10-05');

        self::assertSame('notary.compliance.tariff_discounts_advice', (new TariffDisplayCheck($this->record([]), $tariff, $today))->check()->advice);
        self::assertTrue((new TariffDisplayCheck($this->record([NotaryRecord::DISCOUNTS => 'Aucune remise']), $tariff, $today))->check()->isOk());
        self::assertSame('notary.compliance.tariff_link_advice', (new TariffDisplayCheck($this->record([NotaryRecord::DISCOUNTS => 'Aucune remise']), ['code_url' => '', 'order_url' => ''], $today))->check()->advice);
    }

    public function testPastTheOrdersPeriodTheReferenceIsToBeUpdated(): void
    {
        $tariff = ['code_url' => 'https://example.org', 'order' => 'arrêté du 25 février 2026', 'until' => '2028-02-29'];
        $record = $this->record([NotaryRecord::DISCOUNTS => 'Aucune remise']);

        self::assertTrue((new TariffDisplayCheck($record, $tariff, new \DateTimeImmutable('2028-02-29')))->check()->isOk(), 'its last day');
        $late = (new TariffDisplayCheck($record, $tariff, new \DateTimeImmutable('2028-03-01')))->check();
        self::assertSame(ComplianceResult::WARNING, $late->status);
        self::assertSame('29/02/2028', $late->parameters['date']);
    }

    // ── Art. 14.4, 14.5: charter and brand block ─────────────────────

    public function testTheCharterIsAReminderUntilTheOfficeConfirmsIt(): void
    {
        self::assertSame(ComplianceResult::WARNING, (new BrandBlockCheck($this->record([])))->check()->status);
        self::assertSame(ComplianceResult::WARNING, (new BrandBlockCheck($this->record([NotaryRecord::BRAND => 'non'])))->check()->status);
        self::assertTrue((new BrandBlockCheck($this->record([NotaryRecord::BRAND => 'Oui'])))->check()->isOk());
    }

    /** @param array<string, string> $values */
    private function record(array $values): NotaryRecord
    {
        $settings = $this->createStub(SettingBagInterface::class);
        $settings->method('getScalar')->willReturnCallback(static fn ($path) => $values[$path] ?? null);

        return new NotaryRecord($settings);
    }

    private function office(?string $email): Office
    {
        return (new Office('Étude', 'etude'))->setEmail($email);
    }

    private function offices(Office ...$offices): OfficeRepository
    {
        $repository = $this->createStub(OfficeRepository::class);
        $repository->method('findOrdered')->willReturn($offices);
        $repository->method('findMain')->willReturn($offices[0] ?? null);

        return $repository;
    }
}
