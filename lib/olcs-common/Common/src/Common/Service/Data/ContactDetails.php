<?php

namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Dvsa\Olcs\Transfer\Query as TransferQry;

/**
 * Class ContactDetails
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class ContactDetails extends AbstractListDataService
{
    private static $sort = 'description';

    private static $order = 'ASC';

    /** @var int */
    private $contactType;

    /**
     * Fetch list data
     *
     * @param string $context Category
     *
     * @return array
     * @throw DataServiceException
     */
    #[\Override]
    public function fetchListData($context = null)
    {
        $data = (array)$this->getData('ContactDetails');
        if ([] !== $data) {
            return $data;
        }

        $this->contactType = ($context ?: $this->contactType);

        $query = TransferQry\ContactDetail\ContactDetailsList::create(
            [
                'sort' => self::$sort,
                'order' => self::$order,
                'contactType' => $this->contactType,
            ]
        );
        $response = $this->handleQuery($query);

        if (!$response->isOk()) {
            throw new DataServiceException('unknown-error');
        }

        $result = $response->getResult();

        $this->setData('ContactDetails', ($result['results'] ?? null));

        return $this->getData('ContactDetails');
    }

    /**
     * Set contact type
     *
     * @param string $type Type of Contact
     *
     * @return $this
     */
    public function setContactType($type)
    {
        $this->contactType = $type;
        return $this;
    }

    /**
     * Returns contact type
     *
     * @return int
     */
    public function getContactType()
    {
        return $this->contactType;
    }
}
