<?php

namespace Base\Legal\Tests\Practice;

use Base\Legal\Practice\Entity\Area;
use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\TestCase;

/**
 * A field of practice, the same entity for a firm and for an office: what
 * is done in it is one list (a notary's deeds, a lawyer's matters), in one
 * table - legal_area - whatever the profession.
 */
final class AreaTest extends TestCase
{
    public function testWhatIsDoneInTheFieldIsAListEditedOneALine(): void
    {
        $area = (new Area('Succession et transmission'))->setItemsText("Acte de notoriété\n\n  Donation-partage \nTestament");

        self::assertSame(['Acte de notoriété', 'Donation-partage', 'Testament'], $area->getItems());
        self::assertSame("Acte de notoriété\nDonation-partage\nTestament", $area->getItemsText());
        self::assertSame('succession-et-transmission', $area->getSlug());
        self::assertStringContainsString('Donation-partage', $area->getPublicText(), 'the wording check reads the list too');
        self::assertSame([], $area->setItems(null)->getItems());
    }

    public function testOneTableAndItsJoinTableWhateverTheProfession(): void
    {
        $class = new \ReflectionClass(Area::class);

        self::assertSame('legal_area', $class->getAttributes(ORM\Table::class)[0]->newInstance()->name);
        $members = $class->getProperty('members');
        self::assertSame('legal_area_member', $members->getAttributes(ORM\JoinTable::class)[0]->newInstance()->name);
        self::assertSame('area_id', $members->getAttributes(ORM\JoinColumn::class)[0]->newInstance()->name);
        self::assertSame('member_id', $members->getAttributes(ORM\InverseJoinColumn::class)[0]->newInstance()->name);
        self::assertSame('json', $class->getProperty('items')->getAttributes(ORM\Column::class)[0]->newInstance()->type);
    }
}
