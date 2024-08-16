<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStoreWithMultipleAddresses as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\Licence;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

final class GenerateAndStoreWithMultipleAddresses extends AbstractCommandHandler implements
    TransactionedInterface,
    AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Document';

    protected $extraRepos = ['Licence', 'CompaniesHouseCompany'];

    protected $defaultSendToAddresses = [
        'correspondenceAddress'           => true,
        'insolvencyPractitionerAddresses' => false
    ];

    /**
     * @param Cmd $command
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $data = $command->getGenerateCommandData();
        $description = $data['description'];
        $addressBookmark = (string)$command->getAddressBookmark();
        $bookmarkBundle = $command->getBookmarkBundle();
        /** @var Licence $licenceRepo */
        $licenceRepo = $this->getRepo('Licence');
        $licence = $licenceRepo->fetchWithAddressesUsingId($data['licence']);

        $addresses = $this->retrieveAddresses($command, $licence);

        foreach ($addresses as $addressName => $addressValue) {
            $data['description'] = "$description ($addressName)";
            $data = $this->addAddressToKnownValues($bookmarkBundle, $addressValue, $data, $addressBookmark);
            $data = $this->addAddressToMetadata($data, $addressName);
            $result = $this->handleSideEffect(GenerateAndStore::create($data));
            $documentId = $result->getId('document');
            $this->result->addId('documents', $documentId, true);
            $this->result->addId($addressName, $documentId);
        }
        return $this->result;
    }

    /**
     * @param Cmd $command
     * @param LicenceEntity $licence
     * @return array
     */
    private function retrieveAddresses(CommandInterface $command, $licence)
    {
        $sendToAddresses = array_merge($this->defaultSendToAddresses, $command->getSendToAddresses());

        $addresses = [];

        if (
            $sendToAddresses['correspondenceAddress'] &&
            $licence->getCorrespondenceCd() !== null &&
            $licence->getCorrespondenceCd()->getAddress() !== null
        ) {
            $addresses['correspondenceAddress'] = $licence->getCorrespondenceCd()->getAddress()->serialize();
        }

        if ($sendToAddresses['insolvencyPractitionerAddresses']) {
            $companyOrLlpNo = $licence->getOrganisation()->getCompanyOrLlpNo();
            $company = $this->getRepo('CompaniesHouseCompany')->getLatestByCompanyNumber($companyOrLlpNo);
            $n = 1;
            foreach ($company->getInsolvencyPractitioners() as $insolvencyPractitioner) {
                $addresses['insolvencyPractitionerAddress' . $n] = [
                    'addressLine1' => $insolvencyPractitioner->getName(),
                    'addressLine2' => $insolvencyPractitioner->getAddressLine1(),
                    'addressLine3' => $insolvencyPractitioner->getAddressLine2(),
                    'addressLine4' => '',
                    'town' => $insolvencyPractitioner->getLocality(),
                    'postcode' => $insolvencyPractitioner->getPostalCode(),
                    'countryCode' => ''
                ];
                $n++;
            }
        }

        $addresses = $this->validateAddresses($addresses);

        return $addresses;
    }

    private function isEmptyAddress($values)
    {
        return ($values['addressLine1'] === null || $values['addressLine1'] === "") &&
            ($values['addressLine2'] === null || $values['addressLine2'] === "") &&
            ($values['addressLine3'] === null || $values['addressLine3'] === "") &&
            ($values['addressLine4'] === null || $values['addressLine4'] === "");
    }

    private function validateAddresses($addresses)
    {
        $validatedAddresses = [];
        foreach ($addresses as $key => $values) {
            if ($this->isEmptyAddress($values)) {
                continue;
            }

            foreach ($validatedAddresses as $validatedAddressKey => $validatedAddressValues) {
                if (
                    $validatedAddressValues['addressLine1'] === $values['addressLine1'] &&
                    $validatedAddressValues['addressLine2'] === $values['addressLine2'] &&
                    $validatedAddressValues['addressLine3'] === $values['addressLine3'] &&
                    $validatedAddressValues['addressLine4'] === $values['addressLine4'] &&
                    $validatedAddressValues['town'] === $values['town'] &&
                    $validatedAddressValues['postcode'] === $values['postcode'] &&
                    $validatedAddressValues['countryCode'] === $values['countryCode']
                ) {
                    continue 2;
                }
            }

            $validatedAddresses[$key] = $values;
        }

        return $validatedAddresses;
    }

    private function addAddressToMetadata($data, $addressName)
    {
        if (!array_key_exists('metadata', $data)) {
            $data['metadata'] = json_encode([]);
        }
        $metadata = json_decode((string) $data['metadata'], true);
        if (!array_key_exists('details', $metadata)) {
            $metadata['details'] = [];
        }
        $metadata['details']['sendToAddress'] = $addressName;
        $data['metadata'] = json_encode($metadata);
        return $data;
    }

    private function addAddressToKnownValues($bookmarkBundle, $addressValue, $data, $addressBookmark)
    {
        if (!array_key_exists('knownValues', $data)) {
            $data['knownValues'] = [];
        }
        $knownValues = [];
        foreach ($bookmarkBundle as $bundleKey => $bundleValues) {
            foreach ($bundleValues as $bundleValue) {
                $knownValues[$bundleKey] = [$bundleValue => $addressValue];
            }
        }
        $data['knownValues'][$addressBookmark] = $knownValues;
        return $data;
    }
}
