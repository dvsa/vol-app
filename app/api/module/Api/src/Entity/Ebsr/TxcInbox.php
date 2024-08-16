<?php

namespace Dvsa\Olcs\Api\Entity\Ebsr;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;

/**
 * TxcInbox Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="txc_inbox",
 *    indexes={
 *        @ORM\Index(name="ix_txc_inbox_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\Index(name="ix_txc_inbox_local_authority_id", columns={"local_authority_id"}),
 *        @ORM\Index(name="ix_txc_inbox_organisation_id", columns={"organisation_id"}),
 *        @ORM\Index(name="ix_txc_inbox_zip_document_id", columns={"zip_document_id"}),
 *        @ORM\Index(name="ix_txc_inbox_route_document_id", columns={"route_document_id"}),
 *        @ORM\Index(name="ix_txc_inbox_pdf_document_id", columns={"pdf_document_id"}),
 *        @ORM\Index(name="ix_txc_inbox_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_txc_inbox_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class TxcInbox extends AbstractTxcInbox implements OrganisationProviderInterface
{
    public const SUBCATEGORY_EBSR = 36; // to-do sub category is 'EBSR' TBC
    public const SUBCATEGORY_TRANSXCHANGE_FILE = 107;
    public const SUBCATEGORY_TRANSXCHANGE_PDF = 108;

    public function __construct(
        BusReg $busReg,
        Document $zipDocument,
        LocalAuthority $localAuthority = null,
        Organisation $organisation = null
    ) {
        //check the bus reg is from EBSR
        if (!$busReg->isFromEbsr()) {
            throw new ForbiddenException('Txc Inbox may only be used for EBSR records');
        }

        //check we have one of organisation or local authority (and not both)
        if (
            ($localAuthority === null && $organisation === null)
            || ($localAuthority !== null && $organisation !== null)
        ) {
            throw new ValidationException(['Txc Inbox requires either a Local Authority or Organisation (not both)']);
        }

        $this->busReg = $busReg;
        $this->zipDocument = $zipDocument;
        $this->localAuthority = $localAuthority;
        $this->organisation = $organisation;
        $this->variationNo = $busReg->getVariationNo();
    }

    /**
     * Get organisations this entity is linked to
     *
     * @return \Dvsa\Olcs\Api\Entity\Organisation\Organisation
     */
    public function getRelatedOrganisation()
    {
        return $this->getBusReg()->getRelatedOrganisation();
    }
}
