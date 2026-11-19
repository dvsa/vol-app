<?php

namespace Dvsa\Olcs\Transfer\Command\Notify;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Command\LoggerOmitContentInterface;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * Delivery status callback from GOV.UK Notify. Carries the callback bearer token, so its content is kept out of
 * the request logs.
 */
#[Transfer\RouteName('backend/notify/delivery-status')]
#[Transfer\Method('POST')]
class UpdateDeliveryStatus extends AbstractCommand implements LoggerOmitContentInterface
{
    /**
     * @var string
     */
    protected $token;

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
    public function getToken()
    {
        return $this->token;
    }

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
