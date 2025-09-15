<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

final class ProcessPackTransaction extends AbstractProcessPack implements
    TransactionedInterface
{
    public function handleCommand(CommandInterface $command)
    {
        /** @var EbsrSubmissionEntity $ebsrSub */
        $ebsrSub = $this->getRepo('EbsrSubmission')->fetchUsingId($command);
        $ebsrSub->beginValidating($this->getRepo()->getRefdataReference(EbsrSubmissionEntity::VALIDATING_STATUS));
        $this->getRepo('EbsrSubmission')->save($ebsrSub);

        $config = $this->getConfig();

        if (!isset($config['ebsr']['tmp_extra_path'])) {
            throw new \RuntimeException('No tmp directory specified in config');
        }

        $this->result->addId('ebsrSubmission', $ebsrSub->getId());

        /** @var OrganisationEntity $organisation */
        $organisation = $ebsrSub->getOrganisation();

        /** @var DocumentEntity $doc */
        $doc = $ebsrSub->getDocument();

        //set the sub directory of /tmp where we extract the EBSR files
        $this->getFileProcessor()->setSubDirPath($config['ebsr']['tmp_extra_path']);

        try {
            $xmlName = $this->getFileProcessor()->fetchXmlFileNameFromDocumentStore($doc->getIdentifier());
        } catch (\Exception $e) {
            //process the validation failure information
            $this->processFailure($ebsrSub, $doc, ['upload-failure' => $e->getMessage()], '', []);
            return $this->result;
        }

        //validate the xml structure
        $xmlDocContext = ['xml_filename' => $xmlName];
        $ebsrDoc = $this->validateInput('xmlStructure', $ebsrSub, $doc, $xmlName, $xmlName, $xmlDocContext);

        if ($ebsrDoc === false) {
            return $this->result;
        }

        $busRegInputContext = [
            'submissionType' => $ebsrSub->getEbsrSubmissionType()->getId(),
            'organisation' => $organisation
        ];

        //do some pre-doctrine data processing
        $ebsrData = $this->validateInput('busReg', $ebsrSub, $doc, $xmlName, $ebsrDoc, $busRegInputContext);

        if ($ebsrData === false) {
            return $this->result;
        }

        //we now have xml data we can add to our ebsr submission record
        $ebsrSub = $this->addXmlDataToEbsrSubmission($ebsrSub, $ebsrData);

        //get the parts of the data we need doctrine for
        $ebsrData = $this->getDoctrineInformation($ebsrData);

        /**
         * @var LicenceRepo $repo
         * @var LicenceEntity $licence
         * @var BusRegEntity $previousBusReg
         */
        $repo = $this->getRepo('Licence');
        $licence = $repo->fetchByLicNoWithoutAdditionalData($ebsrData['licNo']);
        $previousBusReg = $licence->getLatestBusVariation($ebsrData['existingRegNo']);
        $previousBusRegNoExclusions = $licence->getLatestBusVariation($ebsrData['existingRegNo'], []);

        //we now have the data from doctrine, so validate this additional data
        $processedContext = [
            'busReg' => $previousBusReg,
            'busRegNoExclusions' => $previousBusRegNoExclusions,
        ];

        $ebsrData = $this->validateInput('processedData', $ebsrSub, $doc, $xmlName, $ebsrData, $processedContext);

        if ($ebsrData === false) {
            return $this->result;
        }

        //we have valid data, so build a bus reg record
        $busReg = $this->createBusReg($ebsrData, $previousBusReg, $licence);

        //we can only validate short notice data once we've created the bus reg
        if (!$this->validateInput('shortNotice', $ebsrSub, $doc, $xmlName, $ebsrData, ['busReg' => $busReg])) {
            return $this->result;
        }

        //short notice has passed validation
        if ($busReg->getIsShortNotice() === 'Y') {
            $busReg->getShortNotice()->fromData($ebsrData['busShortNotice']);
        }

        //we've finished validating
        $ebsrSub->finishValidating(
            $this->getRepo()->getRefdataReference(EbsrSubmissionEntity::PROCESSING_STATUS),
            $this->getSubmissionResultData([], $ebsrData, $ebsrSub)
        );

        //save the submission and the bus reg
        $this->getRepo('EbsrSubmission')->save($ebsrSub);
        $busReg->setEbsrSubmissions(new ArrayCollection([$ebsrSub]));
        $this->getRepo()->save($busReg);

        //set the TransXChange schema version
        $ebsrSub->setTransXChangeVersion($ebsrData['transXChangeVersion']);

        //update submission status to processed
        $ebsrSub->setBusReg($busReg);
        $this->getRepo('EbsrSubmission')->save($ebsrSub);

        //trigger side effects (persist docs, txc inbox, create task, request a route map, create fee, send email)
        $sideEffects = $this->getSideEffects($ebsrData, $busReg, $ebsrSub, dirname($xmlName));
        $this->handleSideEffects($sideEffects);

        //we've finished processing
        $ebsrSub->finishProcessing($this->getRepo()->getRefdataReference(EbsrSubmissionEntity::PROCESSED_STATUS));

        $this->result->addMessage(
            $doc->getDescription() . '(' . basename($xmlName) . '): file processed successfully'
        );

        return $this->result;
    }
}
