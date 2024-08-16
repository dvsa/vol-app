<?php

namespace Dvsa\Olcs\Api\Entity\Application;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * S4 Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="s4",
 *    indexes={
 *        @ORM\Index(name="ix_s4_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_s4_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_s4_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_s4_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class S4 extends AbstractS4
{
    public const STATUS_APPROVED = 's4_sts_approved';
    public const STATUS_REFUSED = 's4_sts_refused';
    public const STATUS_CANCELLED = 's4_sts_cancelled';

    /**
     * S4 constructor.
     *
     * @param Application $application Application
     * @param Licence     $licence     Licence
     *
     * @return void
     */
    public function __construct(Application $application, Licence $licence)
    {
        $this->application = $application;
        $this->licence = $licence;

        parent::__construct();
    }
}
