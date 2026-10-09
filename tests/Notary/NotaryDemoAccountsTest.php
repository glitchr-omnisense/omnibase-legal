<?php

namespace Base\Legal\Tests\Notary;

use Base\Demo\DemoAccountRegistry;
use Base\Legal\Notary\Demo\NotaryDemoAccounts;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Yaml\Yaml;

/**
 * The demonstration accounts of a notary's office: one for each role, the
 * office's roles through their groups, a label and a sentence for each in
 * the bundle's catalogue, in French and in English - and nobody above the office's administrator.
 */
class NotaryDemoAccountsTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(DemoAccountRegistry::class)) {
            self::markTestSkipped('Requires a glitchr/omnibase with the demo environment.');
        }
    }

    /** The role hierarchy the bundle's documentation gives an application (docs/index.md). */
    private function registry(): DemoAccountRegistry
    {
        return new DemoAccountRegistry([new NotaryDemoAccounts()], [], new RoleHierarchy([
            'ROLE_NOTARY' => ['ROLE_STAFF'],
            'ROLE_CLERK' => ['ROLE_STAFF'],
            'ROLE_STAFF' => ['ROLE_USER'],
            'ROLE_ADMIN' => ['ROLE_STAFF'],
            'ROLE_SUPERADMIN' => ['ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'],
            'ROLE_EDITOR' => ['ROLE_SUPERADMIN'],
        ]));
    }

    public function testOneAccountForEachRoleOfTheOffice(): void
    {
        $accounts = $this->registry()->all();

        $this->assertSame(['notaire', 'clerc', 'accueil', 'etude', 'client'], array_keys($accounts));
        $this->assertSame(NotaryDemoAccounts::NOTARIES, $accounts['notaire']->group);
        $this->assertSame(['ROLE_USER', 'ROLE_NOTARY'], $accounts['notaire']->getAllRoles());
        $this->assertSame(NotaryDemoAccounts::CLERKS, $accounts['clerc']->group);
        $this->assertSame(['ROLE_USER', 'ROLE_CLERK'], $accounts['clerc']->getAllRoles());
        $this->assertSame(NotaryDemoAccounts::RECEPTION, $accounts['accueil']->group);
        $this->assertSame(['ROLE_USER', 'ROLE_STAFF'], $accounts['accueil']->getAllRoles());
        $this->assertSame(['ROLE_ADMIN'], $accounts['etude']->getAllRoles());
        $this->assertSame(NotaryDemoAccounts::CLIENTS, $accounts['client']->group);
        $this->assertSame(['ROLE_USER'], $accounts['client']->getAllRoles(), 'a client holds no role of the office');
        $this->assertSame('notaire', $accounts['notaire']->getPassword(), 'the password is the identifier, as in the fixtures');
    }

    public function testNobodyAboveTheOfficesAdministrator(): void
    {
        $registry = $this->registry();
        foreach ($registry->all() as $account) {
            $this->assertFalse($registry->reachesSuperAdmin($account->getAllRoles()), $account->identifier);
        }
    }

    public function testEachHasItsLabelAndItsSentenceInTheCatalogue(): void
    {
        $catalogues = [];
        foreach (['fr', 'en'] as $locale) {
            $catalogues[$locale] = Yaml::parseFile(\dirname(__DIR__, 2).'/translations/legal+intl-icu.'.$locale.'.yaml')['notary']['demo'];
        }

        foreach ($this->registry()->all() as $identifier => $account) {
            $this->assertSame('@legal.notary.demo.'.$identifier.'.label', $account->label);
            $this->assertSame('@legal.notary.demo.'.$identifier.'.description', $account->description);
            foreach ($catalogues as $locale => $catalogue) {
                $this->assertNotEmpty($catalogue[$identifier]['label'] ?? null, "$identifier in $locale");
                $this->assertNotEmpty($catalogue[$identifier]['description'] ?? null, "$identifier in $locale");
            }
        }
    }
}
