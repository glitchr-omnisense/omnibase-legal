<?php

namespace Base\Legal\Tests\Lawyer;

use Base\Legal\Lawyer\Guard\SpecialistWords;
use Base\Legal\Tests\NeedsOffice;
use PHPUnit\Framework\TestCase;

/** RIN art. 10.2: four words for the holders of a certificate only. */
final class SpecialistWordsTest extends TestCase
{
    use NeedsOffice;

    public function testTheFourWordsAreFoundInAllTheirForms(): void
    {
        self::assertSame(['specialiste'], SpecialistWords::find('Avocate SPÉCIALISTE du divorce'));
        self::assertSame(['specialisee'], SpecialistWords::find('Spécialisée en droit du travail.'));
        self::assertSame(['specialites', 'specialisation'], SpecialistWords::find('Nos spécialités ; une spécialisation reconnue.'));
        self::assertTrue(SpecialistWords::uses('des avocats <b>spécialisés</b>'));
    }

    public function testOtherWordsAreNot(): void
    {
        self::assertSame([], SpecialistWords::find('Elle intervient principalement en droit du travail ; une pratique spéciale des référés.'));
        self::assertFalse(SpecialistWords::uses(null));
    }
}
