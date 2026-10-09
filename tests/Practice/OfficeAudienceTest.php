<?php

namespace Base\Legal\Tests\Practice;

use App\Entity\User;
use Base\Legal\DependencyInjection\LegalConfiguration;
use Base\Legal\Practice\Security\OfficeAudience;
use Base\Office\Entity\Share\Document;
use Base\Office\Share\AudienceResolverInterface as Audience;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

/**
 * Professional secrecy within the practice: who sees and reads a client's
 * document, beyond the client and its sender - with each profession's
 * readers (LegalConfiguration::DEFAULTS, legal.roles.readers).
 */
final class OfficeAudienceTest extends TestCase
{
    /** @return iterable<string, array{string, list<string>, string}> the profession, its readers, a role of the rest of its staff */
    public static function practices(): iterable
    {
        yield 'a firm: the lawyers read, the secretariat sees a title' => ['lawyer', ['ROLE_LAWYER'], 'ROLE_SECRETARY'];
        yield 'an office: the notaries and the clerks read, the reception sees a title' => ['notary', ['ROLE_NOTARY', 'ROLE_CLERK'], 'ROLE_STAFF'];
    }

    #[DataProvider('practices')]
    public function testThoseWhoWorkOnTheFilesRead(string $profession, array $readers, string $other): void
    {
        self::assertSame($readers, LegalConfiguration::DEFAULTS[$profession]['readers']);

        foreach ($readers as $role) {
            self::assertTrue($this->audience($profession)->decide(Audience::READ, $this->document(), $this->user([$role])), $role);
            self::assertTrue($this->audience($profession)->decide(Audience::VIEW, $this->document(), $this->user([$role])), $role);
            self::assertNull($this->audience($profession)->decide(Audience::REVOKE, $this->document(), $this->user([$role])), 'only its sender withdraws a document');
        }
    }

    #[DataProvider('practices')]
    public function testTheRestOfTheStaffSeesTheTitleNotTheContent(string $profession, array $readers, string $other): void
    {
        $staff = $this->user([$other]);

        self::assertTrue($this->audience($profession)->decide(Audience::VIEW, $this->document(), $staff));
        self::assertNull($this->audience($profession)->decide(Audience::READ, $this->document(), $staff));
        self::assertTrue($this->audience($profession)->decide(Audience::READ, $this->document(confidential: false), $staff));
    }

    #[DataProvider('practices')]
    public function testNobodyOutsideThePractice(string $profession, array $readers, string $other): void
    {
        foreach ([[], ['ROLE_USER'], ['ROLE_SOMETHING_ELSE']] as $roles) {
            self::assertNull($this->audience($profession)->decide(Audience::READ, $this->document(confidential: false), $this->user($roles)));
            self::assertNull($this->audience($profession)->decide(Audience::VIEW, $this->document(), $this->user($roles)));
        }
    }

    public function testAnotherProfessionsReadersAreOnlyStaffHere(): void
    {
        // A clerk's role on a lawyers' site reads nothing more than the staff does.
        self::assertNull($this->audience('lawyer')->decide(Audience::READ, $this->document(), $this->user(['ROLE_CLERK'])));
    }

    public function testADocumentWithoutARecipientIsNobodysBusinessHere(): void
    {
        $document = new Document();

        self::assertNull($this->audience('lawyer')->decide(Audience::READ, $document, $this->user(['ROLE_LAWYER'])));
    }

    private function audience(string $profession): OfficeAudience
    {
        return new OfficeAudience(
            new RoleHierarchy(['ROLE_LAWYER' => ['ROLE_STAFF'], 'ROLE_SECRETARY' => ['ROLE_STAFF'], 'ROLE_NOTARY' => ['ROLE_STAFF'], 'ROLE_CLERK' => ['ROLE_STAFF'], 'ROLE_STAFF' => ['ROLE_USER']]),
            LegalConfiguration::DEFAULTS[$profession]['readers'],
        );
    }

    private function document(bool $confidential = true): Document
    {
        return (new Document($this->createStub(User::class)))->setConfidential($confidential);
    }

    /** @param list<string> $roles */
    private function user(array $roles): User
    {
        $user = $this->createStub(User::class);
        $user->method('getRoles')->willReturn($roles);

        return $user;
    }
}
