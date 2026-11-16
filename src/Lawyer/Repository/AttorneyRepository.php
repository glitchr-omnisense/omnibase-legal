<?php

namespace Base\Legal\Lawyer\Repository;

use Base\Legal\Lawyer\Entity\Attorney;
use Base\Office\Entity\Member;
use Base\Database\Repository\ServiceEntityRepository;
use Base\Database\Repository\Finders;

/** @extends ServiceEntityRepository<Attorney> */
class AttorneyRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, Attorney::class);
    }

    public function findOneByMember(?Member $member): ?Attorney
    {
        return null === $member ? null : $this->findOneBy(['member' => $member]);
    }

    /** @return list<Attorney> those the site shows */
    public function findVisible(): array
    {
        return $this->findBy(['member.visible' => true, 'member.active' => true], ['member.position' => 'ASC', 'member.displayName' => 'ASC']);
    }
}
