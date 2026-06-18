<?php

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;
use Common\Form\Form;
use Dvsa\Olcs\Api\Entity;

/**
 * UploadEvidence
 */
class UploadEvidence implements MapperInterface
{
    /**
     * Mapped data from Query result into form data
     *
     * @param array $data Api data
     *
     * @return array
     */
    #[\Override]
    public static function mapFromResult(array $data)
    {
        $formData = [];
        $counter = count($data['operatingCentres']);
        for ($i = 0; $i < $counter; ++$i) {
            $formData['operatingCentres'][$i]['adPlacedIn'] = $data['operatingCentres'][$i]['adPlacedIn'];
            $formData['operatingCentres'][$i]['aocId'] = $data['operatingCentres'][$i]['id'];

            if (!empty($data['operatingCentres'][$i]['adPlacedDate'])) {
                $adPlacedDate = new \DateTime($data['operatingCentres'][$i]['adPlacedDate']);
                $formData['operatingCentres'][$i]['adPlacedDate']['day'] = $adPlacedDate->format('j');
                $formData['operatingCentres'][$i]['adPlacedDate']['month'] = $adPlacedDate->format('n');
                $formData['operatingCentres'][$i]['adPlacedDate']['year'] = $adPlacedDate->format('Y');
            }
        }

        return $formData;
    }

    /**
     * Map data from API result onto the form
     *
     * @param array $data API data
     * @param Form  $form The form
     */
    public static function mapFromResultForm(array $data, Form $form): void
    {
        $form->setData(self::mapFromResult($data));
        $counter = count($data['operatingCentres']);

        for ($i = 0; $i < $counter; ++$i) {
            /** @var \Laminas\Form\InputFilterProviderFieldset $fieldset */
            $fieldset = $form->get('operatingCentres')->getFieldsets()[$i];

            // Set the label of each operating centre fieldset
            $label = $data['operatingCentres'][$i]['operatingCentre']['address']['town'];
            if (!empty($data['operatingCentres'][$i]['operatingCentre']['address']['postcode'])) {
                $label .= ', ' . $data['operatingCentres'][$i]['operatingCentre']['address']['postcode'];
            }

            $fieldset->setLabel($label);
        }
    }

    /**
     * Prepare form data for Save command
     *
     * @param array $data form data
     */
    public static function mapFromForm(array $data): array
    {

        $apiData['supportingEvidence'] = false;

        if (!empty($data['operatingCentres'])) {
            foreach ($data['operatingCentres'] as $operatingCentreData) {
                $apiData['operatingCentres'][$operatingCentreData['aocId']] = [
                    'adPlacedIn' => $operatingCentreData['adPlacedIn'],
                    'adPlacedDate' => $operatingCentreData['adPlacedDate'],
                    'aocId' => $operatingCentreData['aocId']
                ];
            }
        }

        if (!empty($data['supportingEvidence'])) {
            $apiData['supportingEvidence'] = true;
        }

        return $apiData;
    }
}
