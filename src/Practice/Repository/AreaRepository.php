<?php

namespace Base\Legal\Practice\Repository;

use Base\Legal\Practice\Entity\Area;
use Base\Database\Repository\Finders;

/**
 * The finders are omnibase/office's (findActive(), findOneActiveBySlug(), findForMember()).
 *
 * @extends \Base\Office\Repository\AreaRepository<Area>
 */
class AreaRepository extends \Base\Office\Repository\AreaRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, Area::class);
    }
}
