<?php

namespace Base\Legal\Tests\Notary;

use Base\Legal\Notary\Guard\EmailDomain;
use PHPUnit\Framework\TestCase;

/** Règlement professionnel du notariat, art. 16.3: the professional address ends with notaires.fr. */
final class EmailDomainTest extends TestCase
{
    public function testAnAddressOfTheProfessionIsOne(): void
    {
        self::assertTrue(EmailDomain::isNotarial('prenom.nom@notaires.fr'));
        self::assertTrue(EmailDomain::isNotarial('  Etude.12345@Notaires.FR '));
        self::assertTrue(EmailDomain::isNotarial('accueil@paris.notaires.fr'), 'a subdomain of notaires.fr');
    }

    public function testALookAlikeIsNot(): void
    {
        foreach (['contact@etude-martin.fr', 'contact@notaires.fr.example', 'contact@mes-notaires.fr', 'contact@xnotaires.fr', 'notaires.fr', '@notaires.fr', 'a b@notaires.fr', '', null] as $address) {
            self::assertFalse(EmailDomain::isNotarial($address), var_export($address, true));
        }
    }

    public function testTheSuffixIsTheConfigurationS(): void
    {
        self::assertTrue(EmailDomain::isNotarial('office@notaires.example', '.notaires.example'));
        self::assertFalse(EmailDomain::isNotarial('office@notaires.fr', 'notaires.example'));
        self::assertFalse(EmailDomain::isNotarial('office@notaires.fr', ''));
    }
}
