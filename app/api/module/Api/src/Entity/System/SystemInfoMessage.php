<?php

namespace Dvsa\Olcs\Api\Entity\System;

use Doctrine\ORM\Mapping as ORM;

/**
 * SystemInfoMessage Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="system_info_message",
 *    indexes={
 *        @ORM\Index(name="ix_system_info_message_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_system_info_message_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_system_info_message_is_internal_start_date_end_date",
 *     columns={"is_internal","start_date","end_date"})
 *    }
 * )
 */
class SystemInfoMessage extends AbstractSystemInfoMessage
{
    /**
     * Gets calculated values
     *
     * @return array
     */
    public function getCalculatedBundleValues()
    {
        return [
            'isActive' => $this->isActive(),
        ];
    }

    /**
     * @return bool
     */
    private function isActive()
    {
        $now = time();

        return (
            strtotime($this->getStartDate()) <= $now
            && $now <= strtotime($this->getEndDate())
        );
    }
}
