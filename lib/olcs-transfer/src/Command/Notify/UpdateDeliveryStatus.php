<?php

namespace Dvsa\Olcs\Transfer\Command\Notify;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * Delivery status callback from GOV.UK Notify
 */
#[Transfer\RouteName('backend/notify/delivery-status')]
#[Transfer\Method('POST')]
class UpdateDeliveryStatus extends AbstractCommand
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string|null
     */
    #[Transfer\Optional]
    protected $completedAt;

    /**
     * @var string|null
     */
    #[Transfer\Optional]
    protected $sentAt;

    /**
     * @var string|null
     */
    #[Transfer\Optional]
    protected $reference;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    /**
     * @return string|null
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return string|null
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }
}
