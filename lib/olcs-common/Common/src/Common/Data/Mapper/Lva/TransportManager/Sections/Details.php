<?php

namespace Common\Data\Mapper\Lva\TransportManager\Sections;

use Common\Category;
use Common\RefData;

class Details extends AbstractSection
{
    private $name;

    private $birthDate;

    private $birthPlace;

    private $emailAddress;

    private $certificate;

    private $lgvAcquiredRightsReferenceNumber;

    private $hasUndertakenTraining;

    private $homeCd;

    private $workCd;

    private $propertiesToShow = [
        'name',
        'birthDate',
        'birthPlace',
        'emailAddress',
        'certificate',
        'lgvAcquiredRightsReferenceNumber',
        'hasUndertakenTraining',
        'homeCd',
        'workCd',
    ];

    private $propertiesToSkip = [];

    /**
     * sectionSerialize
     * Replaces SectionSerializeTrait::sectionSerialize as one section is conditional
     * @return array
     */
    #[\Override]
    public function sectionSerialize()
    {
        $templatePrefix = $this->getTranslationTemplate();

        $properties = [];
        foreach ($this->propertiesToShow as $property) {
            if (in_array($property, $this->propertiesToSkip)) {
                continue;
            }

            $key = $templatePrefix . $property;
            $properties[$key] = $this->{$property};
        }

        return $properties;
    }

    /**
     * populate
     */
    public function populate(array $transportManagerApplication): static
    {
        $person = $transportManagerApplication['transportManager']['homeCd']['person'];
        $this->populatePersonDetails($person);
        $this->emailAddress = $transportManagerApplication['transportManager']['homeCd']['emailAddress'];
        $this->certificate = $this->processDocuments($transportManagerApplication);

        if ($transportManagerApplication['application']['vehicleType']['id'] === RefData::APP_VEHICLE_TYPE_LGV) {
            // LGV only - populate lgvAcquiredRightsReferenceNumber
            $this->lgvAcquiredRightsReferenceNumber = empty($transportManagerApplication['lgvAcquiredRightsReferenceNumber'])
                ? $this->getTranslationTemplate() . 'lgvAcquiredRightsReferenceNumberNotProvided'
                : $transportManagerApplication['lgvAcquiredRightsReferenceNumber'];
        } else {
            // skip lgvAcquiredRightsReferenceNumber
            $this->propertiesToSkip[] = 'lgvAcquiredRightsReferenceNumber';
        }

        $this->hasUndertakenTraining = $transportManagerApplication['hasUndertakenTraining'];

        foreach (['homeCd', 'workCd'] as $addresses) {
            $address = $this->processAddress($transportManagerApplication['transportManager'][$addresses]['address']);
            $address = $this->populateTemplate(
                'markup-' . $this->getTranslationTemplate() . "answer-address",
                $address
            );
            if ($this->birthDate && strtotime($this->birthDate) !== false) {
                $this->birthDate = (new \DateTime($this->birthDate))->format('d M Y');
            }
            $this->$addresses = $address;
        }

        return $this;
    }

    /**
     * populatePersonDetails
     *
     * @param $person
     */
    private function populatePersonDetails($person): void
    {
        $this->name = $person['forename'] . " " . $person['familyName'];
        $props = array_keys(get_object_vars($this));
        foreach ($props as $property) {
            if (array_key_exists($property, $person)) {
                $this->$property = $person[$property];
            }
        }
    }

    /**
     * processDocuments
     *
     *
     * @return mixed
     */
    private function processDocuments(array $transportManagerApplication)
    {
        $hasDocument = false;
        $documents = $transportManagerApplication['transportManager']['documents'];
        foreach ($documents as $document) {
            if ($document['category']['id'] !== Category::CATEGORY_TRANSPORT_MANAGER) {
                continue;
            }
            if ($document['subCategory']['id'] !== Category::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CPC_OR_EXEMPTION) {
                continue;
            }
            $hasDocument = true;
        }

        return $this->getTranslationTemplate() . ($hasDocument ? 'certificateAdded' : 'noCertificatesAttached');
    }

    /**
     * processAddress
     *
     * @param $data
     */
    private function processAddress($data): array
    {
        $formattedAddress = [];
        foreach ($data as $key => $value) {
            if (str_contains($key, 'address') || in_array($key, ['postcode', 'town'])) {
                $formattedAddress[$key] = $value;
            }
        }

        $formattedAddress['country'] = $data['countryCode']['countryDesc'];
        return array_diff($formattedAddress, [''])
            + array_intersect($formattedAddress, ['']);
    }
}
