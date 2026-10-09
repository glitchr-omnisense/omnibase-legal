<?php

namespace Base\Legal\Tests\Lawyer;

use Base\Legal\Lawyer\Compliance\DomainNameCheck;
use Base\Legal\Lawyer\Compliance\FeesInformationCheck;
use Base\Legal\Lawyer\Compliance\OrderDeclarationCheck;
use Base\Legal\Lawyer\Compliance\PaymentScopeCheck;
use Base\Legal\Lawyer\Compliance\SpecialisationCheck;
use Base\Legal\Lawyer\Entity\Attorney;
use Base\Legal\Lawyer\Payment\FeePayment;
use Base\Legal\Lawyer\Repository\AttorneyRepository;
use Base\Legal\Lawyer\Service\Domains;
use Base\Legal\Lawyer\Service\LawyerRecord;
use Base\Legal\Practice\Entity\Area;
use Base\Legal\Practice\Repository\AreaRepository;
use Base\Office\Compliance\ComplianceResult;
use Base\Office\Entity\Member;
use Base\Service\SettingBagInterface;
use PHPUnit\Framework\TestCase;

/**
 * The lawyers' own rules as the back office's "Conformité" widget answers
 * them (what every practice answers - identification, mediator, wording -
 * is in tests/Practice/ChecksTest).
 */
final class ChecksTest extends TestCase
{
    // ── RIN art. 10.5: the domain name ───────────────────────────────

    public function testTheDomainNameCarriesTheName(): void
    {
        $ok = $this->domain(['mareval-avocat.fr'])->check();
        self::assertTrue($ok->isOk());
        self::assertSame('lawyer.compliance.domain', $ok->label);
        self::assertSame('legal', $ok->domain);

        $generic = $this->domain(['mareval-avocat.fr', 'avocat-divorce.fr'])->check();
        self::assertSame(ComplianceResult::MISSING, $generic->status);
        self::assertSame('avocat-divorce.fr', $generic->parameters['domains']);

        self::assertSame('lawyer.compliance.domain_mixed', $this->domain(['mareval-divorce.fr'])->check()->advice);
        self::assertSame('lawyer.compliance.domain_unnamed', $this->domain(['lexium.fr'])->check()->advice);
        self::assertSame('lawyer.compliance.domain_local', $this->domain([], 'https://localhost:8573')->check()->advice);
        self::assertSame('lawyer.compliance.domain_none', $this->domain([], null)->check()->advice);
    }

    public function testWithoutConfigurationTheDomainIsTheRoutersDefaultAddress(): void
    {
        self::assertTrue($this->domain([], 'https://www.mareval-avocat.fr')->check()->isOk());
    }

    // ── RIN art. 10.5, 10.2, 10.3: the Ordre is told ─────────────────

    public function testTheSiteMustBeDeclaredToTheOrdre(): void
    {
        $unnamed = (new OrderDeclarationCheck($this->record([])))->check();
        self::assertSame('lawyer.compliance.order', $unnamed->label);
        self::assertSame('lawyer.compliance.order_body_advice', $unnamed->advice);

        $undated = (new OrderDeclarationCheck($this->record([LawyerRecord::BODY => 'barreau de Port-Lazenne'])))->check();
        self::assertSame(ComplianceResult::MISSING, $undated->status);
        self::assertSame('lawyer.compliance.order_advice', $undated->advice);
        self::assertSame('barreau de Port-Lazenne', $undated->parameters['body']);

        self::assertSame(ComplianceResult::MISSING, (new OrderDeclarationCheck($this->record([LawyerRecord::BODY => 'b', LawyerRecord::DECLARED_AT => 'bientôt'])))->check()->status, 'not a date');
        $future = (new OrderDeclarationCheck($this->record([LawyerRecord::BODY => 'b', LawyerRecord::DECLARED_AT => '2999-01-01'])))->check();
        self::assertSame(ComplianceResult::WARNING, $future->status);
        self::assertSame('lawyer.compliance.order_future', $future->advice);
        self::assertTrue((new OrderDeclarationCheck($this->record([LawyerRecord::BODY => 'b', LawyerRecord::DECLARED_AT => '2026-09-14'])))->check()->isOk());
    }

    // ── RIN art. 10.2: specialisations ───────────────────────────────

    public function testTheWordSpecialistIsForTheHoldersOfACertificate(): void
    {
        $without = new Attorney((new Member('Me Lucien d’Orgeval'))->setBiography('Avocat spécialisé en droit pénal.'));
        $with = (new Attorney((new Member('Me Hortense Maréval'))->setBiography('Avocate spécialiste en droit du travail.')))->setSpecialisationValues(['travail']);

        $result = $this->specialisation([$without, $with])->check();
        self::assertSame(ComplianceResult::MISSING, $result->status);
        self::assertSame('lawyer.compliance.specialisation', $result->label);
        self::assertStringContainsString('Me Lucien d’Orgeval : « specialise » sans certificat', $result->parameters['found']);
        self::assertStringNotContainsString('Maréval', $result->parameters['found']);

        self::assertTrue($this->specialisation([$with])->check()->isOk());
    }

    public function testThreeDominantFieldsAndTwoMentionsAtMost(): void
    {
        $four = (new Attorney(new Member('Me Quatre')))->setDominantFields(['a', 'b', 'c', 'd']);
        self::assertStringContainsString('plus de 3 domaines', $this->specialisation([$four])->check()->parameters['found']);

        $three = (new Attorney(new Member('Me Trois')))->setDominantFields(['a', 'b', 'c']);
        self::assertTrue($this->specialisation([$three])->check()->isOk());

        $mentions = (new Attorney(new Member('Me Mentions')))->setSpecialisationValues(['travail', 'penal', 'fiscal-douanier']);
        self::assertStringContainsString('plus de 2 mentions', $this->specialisation([$mentions])->check()->parameters['found']);

        $qualified = (new Attorney(new Member('Me Qualifiée')))->setQualification('Droit du licenciement');
        self::assertStringContainsString('qualification spécifique sans mention', $this->specialisation([$qualified])->check()->parameters['found']);
    }

    public function testAFieldOfPracticeIsNotASpecialityWhenNobodyHoldsACertificate(): void
    {
        $area = (new Area('Droit du travail'))->setSummary('Notre spécialité : le licenciement.');
        $plain = new Attorney(new Member('Me Simple'));
        $certified = (new Attorney(new Member('Me Certifiée')))->setSpecialisationValues(['travail']);

        self::assertStringContainsString('Droit du travail : « specialite »', $this->specialisation([$plain], [$area])->check()->parameters['found']);
        self::assertTrue($this->specialisation([$plain, $certified], [$area])->check()->isOk());
    }

    // ── RIN art. 11.1, 11.2: fees ────────────────────────────────────

    public function testTheFeesPageSaysHowFeesAreSet(): void
    {
        $missing = (new FeesInformationCheck($this->record([])))->check();
        self::assertSame(ComplianceResult::MISSING, $missing->status);
        self::assertSame('lawyer.compliance.fees_advice', $missing->advice);
        self::assertTrue((new FeesInformationCheck($this->record([LawyerRecord::FEES => 'Au temps passé, 220 € HT de l’heure.'])))->check()->isOk());
    }

    // ── RIN art. 11.5, 6.2: paying online is for fees only ───────────

    public function testPayingOnlineIsCheckedOnlyWhenItIsOn(): void
    {
        self::assertTrue((new PaymentScopeCheck(new FeePayment(false, null, false)))->check()->isOk());
        self::assertNull((new PaymentScopeCheck(new FeePayment(false, null, false)))->check()->advice);

        self::assertSame('lawyer.compliance.payment_missing', (new PaymentScopeCheck(new FeePayment(true, 'app_pay', false)))->check()->advice, 'no marketplace');
        self::assertSame('lawyer.compliance.payment_route', (new PaymentScopeCheck(new FeePayment(true, null, true)))->check()->advice, 'no payment page');

        $on = (new PaymentScopeCheck(new FeePayment(true, 'app_pay', true)))->check();
        self::assertTrue($on->isOk());
        self::assertSame('lawyer.compliance.payment_scope', $on->advice, 'a reminder of what the page may take');
    }

    /** @param list<string> $domains */
    private function domain(array $domains, ?string $defaultUri = 'https://localhost'): DomainNameCheck
    {
        $attorneys = $this->createStub(AttorneyRepository::class);
        $attorneys->method('findVisible')->willReturn([new Attorney(new Member('Me Hortense Maréval'))]);

        return new DomainNameCheck(new Domains($this->record([LawyerRecord::NAME => 'Cabinet Maréval']), $attorneys, $domains, $defaultUri));
    }

    /**
     * @param list<Attorney> $attorneys
     * @param list<Area>     $areas
     */
    private function specialisation(array $attorneys, array $areas = []): SpecialisationCheck
    {
        $attorneyRepository = $this->createStub(AttorneyRepository::class);
        $attorneyRepository->method('findVisible')->willReturn($attorneys);
        $areaRepository = $this->createStub(AreaRepository::class);
        $areaRepository->method('findActive')->willReturn($areas);

        return new SpecialisationCheck($attorneyRepository, $areaRepository);
    }

    /** @param array<string, string> $values */
    private function record(array $values): LawyerRecord
    {
        $settings = $this->createStub(SettingBagInterface::class);
        $settings->method('getScalar')->willReturnCallback(static fn ($path) => $values[$path] ?? null);

        return new LawyerRecord($settings);
    }
}
