<?php

namespace Base\Legal\Practice\Entity;

use Base\Legal\Practice\Repository\AreaRepository;
use Base\Office\Entity\Member;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * A field of practice of the firm or of the office (family, real estate,
 * labour...): what it covers, what is done in it - a notary's deeds, a
 * lawyer's matters -, who of the team follows it. Information for the
 * public: its wording is checked (Base\Legal\Practice\Compliance\WordingCheck),
 * and a lawyer's never calls it a "speciality" unless a certificate is held
 * (Base\Legal\Lawyer\Compliance\SpecialisationCheck).
 *
 * The fields and the methods are omnibase/office's
 * (Base\Office\Entity\Area); the list, the members and the tables are here.
 * Mapped when the profession keeps a practice (LegalExtension::prepend()).
 */
#[ORM\Entity(repositoryClass: AreaRepository::class)]
#[ORM\Table(name: 'legal_area')]
class Area extends \Base\Office\Entity\Area
{
    /** What is done in that field, as a list: "Vente", "Donation-partage"; "Licenciement", "Rupture conventionnelle"... */
    #[ORM\Column(type: 'json')]
    protected array $items = [];

    /** @var Collection<int, Member> who of the team follows it */
    #[ORM\ManyToMany(targetEntity: Member::class)]
    #[ORM\JoinTable(name: 'legal_area_member')]
    #[ORM\JoinColumn(name: 'area_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'member_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected Collection $members;

    /** @return list<string> */
    public function getItems(): array { return $this->items; }
    public function setItems(?array $items): static { $this->items = self::lines($items); return $this; }
}
