<?php

namespace Base\Legal\Tests\Practice;

use Base\Legal\Lawyer\LawyerProfile;
use Base\Legal\Lawyer\Service\LawyerRecord;
use Base\Legal\Notary\NotaryProfile;
use Base\Legal\Notary\Service\NotaryRecord;
use Base\Legal\Practice\Compliance\IdentificationCheck;
use Base\Legal\Practice\Compliance\MediatorCheck;
use Base\Legal\Practice\Compliance\WordingCheck;
use Base\Legal\Practice\Entity\Area;
use Base\Legal\Practice\Repository\AreaRepository;
use Base\Legal\Practice\Service\Record;
use Base\Office\Compliance\ComplianceResult;
use Base\Office\Entity\Member;
use Base\Office\Entity\Office;
use Base\Office\Repository\MemberRepository;
use Base\Office\Repository\OfficeRepository;
use Base\Service\SettingBagInterface;
use PHPUnit\Framework\TestCase;

/**
 * The checks every regulated practice answers - it can be identified, it
 * names its consumer mediator, its texts carry no comparison - asked of a
 * firm and of an office: the same check, what its profession tells it.
 */
final class ChecksTest extends TestCase
{
    // ── Identification ───────────────────────────────────────────────

    public function testAnOfficeIsIdentifiedByItsHolderItsAddressItsPhoneAndItsChamber(): void
    {
        $office = (new Office('Étude', 'etude'))->setEmail('etude@notaires.fr')->setStreet('4 place des Halles')->setCity('Sainte-Orlane')->setPhone('03 53 01 00 40');
        $full = $this->notary([Record::NAME => 'SELARL Test, titulaire d’un office notarial', Record::BODY => 'Chambre']);

        $ok = (new IdentificationCheck(new NotaryProfile(), $full, $this->offices($office)))->check();
        self::assertTrue($ok->isOk());
        self::assertSame('notary.compliance.identification', $ok->label);
        self::assertSame('legal', $ok->domain);

        $result = (new IdentificationCheck(new NotaryProfile(), $this->notary([]), $this->offices((new Office('Étude', 'etude'))->setEmail('etude@notaires.fr'))))->check();
        self::assertSame(ComplianceResult::MISSING, $result->status);
        self::assertSame('notary.compliance.identification_advice', $result->advice);
        self::assertSame('holder, address, phone, chamber', $result->parameters['missing'], 'an e-mail address is not the phone the rules ask');
    }

    public function testAFirmIsIdentifiedLocatedReachableWithItsBarAndItsStructure(): void
    {
        $office = (new Office('Cabinet Maréval', 'cabinet-mareval'))->setStreet('7 quai des Cordiers')->setCity('Port-Lazenne')->setPhone('02 61 91 00 30');
        $full = $this->lawyer([Record::NAME => 'Cabinet Maréval', Record::BODY => 'barreau de Port-Lazenne', LawyerRecord::STRUCTURE => 'Exercice individuel']);

        self::assertTrue((new IdentificationCheck(new LawyerProfile(), $full, $this->offices($office)))->check()->isOk(), 'no network: none is asked');

        $result = (new IdentificationCheck(new LawyerProfile(), $this->lawyer([]), $this->offices(new Office('x', 'x'))))->check();
        self::assertSame(ComplianceResult::MISSING, $result->status);
        self::assertSame('lawyer.compliance.identification', $result->label);
        self::assertSame('name, address, contact, bar, structure', $result->parameters['missing']);

        $byMail = (new Office('x', 'x'))->setStreet('1 rue')->setCity('Ville')->setEmail('cabinet@mareval.example');
        self::assertTrue((new IdentificationCheck(new LawyerProfile(), $full, $this->offices($byMail)))->check()->isOk(), 'a lawyer can be reached by an e-mail address too');
    }

    public function testWithoutAnOfficeNothingLocatesThePractice(): void
    {
        $result = (new IdentificationCheck(new NotaryProfile(), $this->notary([Record::NAME => 'SELARL', Record::BODY => 'Chambre']), $this->offices()))->check();

        self::assertSame('address, phone', $result->parameters['missing']);
    }

    // ── The consumer mediator ────────────────────────────────────────

    public function testTheMediatorsCoordinatesAreGiven(): void
    {
        foreach ([new NotaryProfile(), new LawyerProfile()] as $profile) {
            $label = $profile->getProfession()->value.'.compliance.mediator';

            self::assertTrue((new MediatorCheck($profile, ['name' => 'Médiateur de la consommation du notariat', 'address' => '60 boulevard de La Tour-Maubourg, 75007 Paris', 'url' => 'https://mediateur-notariat.notaires.fr']))->check()->isOk());
            self::assertTrue((new MediatorCheck($profile, ['name' => 'Médiateur', 'address' => 'Une adresse', 'url' => '']))->check()->isOk());
            self::assertTrue((new MediatorCheck($profile, ['name' => 'Médiateur', 'email' => 'mediateur@example.org']))->check()->isOk(), 'an e-mail address reaches it');

            $none = (new MediatorCheck($profile, []))->check();
            self::assertSame(ComplianceResult::MISSING, $none->status);
            self::assertSame($label, $none->label);
            self::assertSame($label.'_advice', $none->advice);
            self::assertSame(ComplianceResult::MISSING, (new MediatorCheck($profile, ['name' => 'Médiateur', 'address' => '', 'url' => null]))->check()->status, 'a name and no way to reach it');
            self::assertSame(ComplianceResult::MISSING, (new MediatorCheck($profile, ['address' => 'Une adresse']))->check()->status, 'no name');

            $http = (new MediatorCheck($profile, ['name' => 'Médiateur', 'url' => 'http://mediateur.example']))->check();
            self::assertSame(ComplianceResult::WARNING, $http->status);
            self::assertSame('compliance.mediator_url', $http->advice);
        }
    }

    // ── Wording ──────────────────────────────────────────────────────

    public function testComparativeWordingIsNamedWhereItIs(): void
    {
        $areas = $this->areas((new Area('Immobilier'))->setSummary('Le meilleur office pour vendre.'), (new Area('Famille'))->setBody('Mariage, PACS, donation.'));
        $members = $this->members((new Member('Me Jeanne Test'))->setBiography('Plus disponible que nos confrères.'));

        $result = (new WordingCheck(new NotaryProfile(), $areas, $members, $this->notary([])))->check();

        self::assertSame(ComplianceResult::WARNING, $result->status);
        self::assertSame('notary.compliance.wording', $result->label);
        self::assertStringContainsString('Immobilier : « le meilleur »', $result->parameters['found']);
        self::assertStringContainsString('Me Jeanne Test', $result->parameters['found']);
        self::assertStringNotContainsString('Famille', $result->parameters['found']);
    }

    public function testWhatIsDoneInAFieldIsReadToo(): void
    {
        $areas = $this->areas((new Area('Immobilier'))->setItems(['Vente', 'Un service incomparable']));

        self::assertStringContainsString('Immobilier : « incomparable »', (new WordingCheck(new NotaryProfile(), $areas, $this->members(), $this->notary([])))->check()->parameters['found']);
    }

    public function testWhatThePracticeTypedInTheSettingsIsReadToo(): void
    {
        $areas = $this->areas((new Area('Famille'))->setBody('Mariage, PACS, donation.'));

        self::assertTrue((new WordingCheck(new NotaryProfile(), $areas, $this->members(), $this->notary([Record::FEES => 'Consultation : sur convention.'])))->check()->isOk());
        self::assertSame(ComplianceResult::WARNING, (new WordingCheck(new NotaryProfile(), $areas, $this->members(), $this->notary([Record::FEES => 'Honoraires imbattables'])))->check()->status);
        self::assertSame(ComplianceResult::WARNING, (new WordingCheck(new NotaryProfile(), $areas, $this->members(), $this->notary([NotaryRecord::DISCOUNTS => 'Les remises les moins chères'])))->check()->status, 'an office\'s discounts');
        self::assertSame(ComplianceResult::WARNING, (new WordingCheck(new LawyerProfile(), $areas, $this->members(), $this->lawyer([LawyerRecord::FIRST_MEETING => 'Premier rendez-vous : prix imbattable'])))->check()->status, 'a firm\'s first meeting');
    }

    public function testAProfessionAddsItsOwnForbiddenWords(): void
    {
        $areas = $this->areas((new Area('Famille'))->setSummary('Des honoraires moins chers.'));
        $members = $this->members((new Member('Me Test'))->setBiography('Ancienne magistrate.'));

        $lawyer = (new WordingCheck(new LawyerProfile(), $areas, $members, $this->lawyer([])))->check();
        self::assertSame(ComplianceResult::WARNING, $lawyer->status);
        self::assertStringContainsString('Famille : « moins cher »', $lawyer->parameters['found']);
        self::assertStringContainsString('Me Test : « fonctions juridictionnelles »', $lawyer->parameters['found'], 'RIN art. 10.2');

        $notary = (new WordingCheck(new NotaryProfile(), $areas, $members, $this->notary([])))->check();
        self::assertStringNotContainsString('fonctions juridictionnelles', $notary->parameters['found'], 'the lawyers\' rule is not the notaries\'');
    }

    public function testTheSitesOwnExpressionsAreAdded(): void
    {
        $areas = $this->areas((new Area('Famille'))->setSummary('L’étude de référence du Val.'));

        self::assertTrue((new WordingCheck(new NotaryProfile(), $areas, $this->members(), $this->notary([])))->check()->isOk());
        self::assertStringContainsString('Étude de référence', (new WordingCheck(new NotaryProfile(), $areas, $this->members(), $this->notary([]), ['Étude de référence']))->check()->parameters['found']);
    }

    // ── The record ───────────────────────────────────────────────────

    public function testASettingsTableNotThereYetAnswersNothing(): void
    {
        $settings = $this->createStub(SettingBagInterface::class);
        $settings->method('getScalar')->willThrowException(new \RuntimeException('no table'));

        foreach ([new NotaryRecord($settings), new LawyerRecord($settings)] as $record) {
            self::assertNull($record->getName());
            self::assertNull($record->getBody());
            self::assertNull($record->getDeclaredAt());
            self::assertNull($record->getFees());
        }
        self::assertFalse((new NotaryRecord($settings))->isBrandApplied());
    }

    public function testEachProfessionNamesWhatItsSettingsAre(): void
    {
        $settings = $this->createStub(SettingBagInterface::class);

        self::assertSame(['firm', 'structure', 'bar', 'network', 'declared_at', 'fees', 'first_meeting', 'legal_aid'], array_values((new LawyerRecord($settings))->getFields()));
        self::assertSame(['holder', 'crpcen', 'chamber', 'declared_at', 'brand', 'discounts', 'fees'], array_values((new NotaryRecord($settings))->getFields()));
        // What every profession has is kept under the same keys, whatever it calls it.
        foreach ([new LawyerRecord($settings), new NotaryRecord($settings)] as $record) {
            foreach ([Record::NAME, Record::BODY, Record::DECLARED_AT, Record::FEES] as $key) {
                self::assertArrayHasKey($key, $record->getFields());
            }
            foreach ($record->getLongFields() as $key) {
                self::assertArrayHasKey($key, $record->getFields());
            }
        }
    }

    public function testTheDayTheSiteWasDeclaredIsADateOrNothing(): void
    {
        self::assertSame('2026-09-14', $this->notary([Record::DECLARED_AT => '2026-09-14'])->getDeclaredAt()?->format('Y-m-d'));
        self::assertSame('2026-09-14', $this->lawyer([Record::DECLARED_AT => '14/09/2026'])->getDeclaredAt()?->format('Y-m-d'));
        self::assertSame('2026-09-14', $this->lawyer([Record::DECLARED_AT => '14.09.2026'])->getDeclaredAt()?->format('Y-m-d'));
        self::assertNull($this->notary([Record::DECLARED_AT => 'bientôt'])->getDeclaredAt());
        self::assertNull($this->notary([Record::DECLARED_AT => '2026-02-31'])->getDeclaredAt(), 'no such day');
    }

    /** @param array<string, string> $values */
    private function notary(array $values): NotaryRecord
    {
        return new NotaryRecord($this->settings($values));
    }

    /** @param array<string, string> $values */
    private function lawyer(array $values): LawyerRecord
    {
        return new LawyerRecord($this->settings($values));
    }

    /** @param array<string, string> $values */
    private function settings(array $values): SettingBagInterface
    {
        $settings = $this->createStub(SettingBagInterface::class);
        $settings->method('getScalar')->willReturnCallback(static fn ($path) => $values[$path] ?? null);

        return $settings;
    }

    private function areas(Area ...$areas): AreaRepository
    {
        $repository = $this->createStub(AreaRepository::class);
        $repository->method('findActive')->willReturn($areas);

        return $repository;
    }

    private function members(Member ...$members): MemberRepository
    {
        $repository = $this->createStub(MemberRepository::class);
        $repository->method('findVisible')->willReturn($members);

        return $repository;
    }

    private function offices(Office ...$offices): OfficeRepository
    {
        $repository = $this->createStub(OfficeRepository::class);
        $repository->method('findOrdered')->willReturn($offices);
        $repository->method('findMain')->willReturn($offices[0] ?? null);

        return $repository;
    }
}
