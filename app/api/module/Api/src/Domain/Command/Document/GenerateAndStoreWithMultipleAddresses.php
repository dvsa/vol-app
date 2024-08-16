<?php

namespace Dvsa\Olcs\Api\Domain\Command\Document;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

class GenerateAndStoreWithMultipleAddresses extends AbstractCommand
{
    protected $generateCommandData = [];

    protected $addressBookmark;

    protected $sendToAddresses = [];

    protected $bookmarkBundle = [];

    /**
     * @return array
     */
    public function getBookmarkBundle()
    {
        return $this->bookmarkBundle;
    }

    /**
     * @return array
     */
    public function getSendToAddresses()
    {
        return $this->sendToAddresses;
    }

    /**
     * @return array
     */
    public function getGenerateCommandData()
    {
        return $this->generateCommandData;
    }

    /**
     * @return array
     */
    public function getAddressBookmark()
    {
        return $this->addressBookmark;
    }
}
