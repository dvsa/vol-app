<?php

namespace Dvsa\Olcs\Api\Entity\Licence;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * LicenceNoGen Entity
 */
#[ORM\Table(name: 'licence_no_gen')]
#[ORM\Index(name: 'ix_licence_no_gen_licence_id', columns: ['licence_id'])]
#[ORM\Entity]
class LicenceNoGen extends AbstractLicenceNoGen
{
    public function __construct(Licence $licence)
    {
        $this->setLicence($licence);
    }

    /**
     * @return string
     */
    public static function getCategoryPrefix(RefData $goodsOrPsv)
    {
        return $goodsOrPsv->getId() === Licence::LICENCE_CATEGORY_PSV ? 'P' : 'O';
    }
}
