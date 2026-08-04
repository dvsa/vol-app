<?php

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;
use Common\Form\Elements\Custom\OlcsCheckbox;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Form\Form;
use Common\RefData;

/**
 * Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentre implements MapperInterface
{
    public const VALUE_OPTION_AD_PLACED_NOW = 'adPlaced';

    public const VALUE_OPTION_AD_UPLOAD_LATER = 'adPlacedLater';

    public const LOC_INTERNAL = 'internal';

    public const LOC_EXTERNAL = 'external';

    /**
     * Map from result
     *
     * @param array $data data
     *
     * @return array
     */
    #[\Override]
    public static function mapFromResult(array $data)
    {
        $adPlaceMapping = [
            RefData::AD_UPLOAD_NOW => self::VALUE_OPTION_AD_PLACED_NOW,
            RefData::AD_UPLOAD_LATER => self::VALUE_OPTION_AD_UPLOAD_LATER,
        ];
        $mappedData = [
            'version' => $data['version'],
            'data' => [
                'noOfVehiclesRequired' => $data['noOfVehiclesRequired'],
                'noOfTrailersRequired' => $data['noOfTrailersRequired'],
                'permission' => [
                    'permission' => $data['permission']
                ],
            ],
            'operatingCentre' => $data['operatingCentre'],
            'address' => $data['operatingCentre']['address'],
            'advertisements' => [
                'adPlacedContent' => [
                    'adPlacedIn' => $data['adPlacedIn'],
                    'adPlacedDate' => $data['adPlacedDate']
                ],
                'radio' => $adPlaceMapping[$data['adPlaced']]
            ]
        ];

        $mappedData['address']['countryCode'] = $mappedData['address']['countryCode']['id'];

        return $mappedData;
    }

    /**
     * Map from form
     *
     * @param array $data data
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $overridden = (isset($data['isTaOverridden']) && $data['isTaOverridden'] === "1") ? 'Y' : 'N';

        $mappedData = [
            'version' => $data['version'],
            'address' => $data['address'] ?? null,
            'noOfVehiclesRequired' => null,
            'noOfTrailersRequired' => null,
            'permission' => null,
            'adPlaced' => null,
            'adPlacedIn' => null,
            'adPlacedDate' => null,
            'taIsOverridden' =>  $overridden
        ];

        $mappedData = array_merge($mappedData, $data['data']);
        if (isset($data['advertisements'])) {
            $adv = $data['advertisements'];
            $adPlaceMapping = [
                self::VALUE_OPTION_AD_PLACED_NOW => RefData::AD_UPLOAD_NOW,
                self::VALUE_OPTION_AD_UPLOAD_LATER => RefData::AD_UPLOAD_LATER
            ];

            if (isset($adv['radio'])) {
                $mappedData['adPlaced'] = $adPlaceMapping[$adv['radio']];
            }

            if (isset($adv['adPlacedContent']['adPlacedIn'])) {
                $mappedData['adPlacedIn'] = $adv['adPlacedContent']['adPlacedIn'];
            }

            if (isset($adv['adPlacedContent']['adPlacedDate'])) {
                $mappedData['adPlacedDate'] = $adv['adPlacedContent']['adPlacedDate'];
            }
        }

        if (isset($data['data']['permission']['permission'])) {
            $mappedData['permission'] = $data['data']['permission']['permission'];
        }

        return $mappedData;
    }

    /**
     * Map from post
     *
     * @param array $data data
     *
     * @return array
     */
    public static function mapFromPost(array $data)
    {
        if (!isset($data['advertisements']) || !is_array($data['advertisements'])) {
            $data['advertisements'] = [];
        }

        $data['advertisements']['uploadedFileCount'] =
            isset($data['advertisements']['adPlacedContent']['file']['list'])
                ? count($data['advertisements']['adPlacedContent']['file']['list'])
                : 0;

        return $data;
    }

    /**
     * Map from errors
     *
     * @param Form                        $form        form
     * @param array                       $errors      errors
     * @param FlashMessengerHelperService $fm          flash messenger helper
     * @param TranslationHelperService    $translator  translator service
     * @param string                      $location    location
     * @param string                      $taGuidesUrl guides url
     */
    public static function mapFormErrors(
        Form $form,
        array $errors,
        FlashMessengerHelperService $fm,
        TranslationHelperService $translator,
        $location,
        $taGuidesUrl
    ): void {
        $formMessages = [];

        if (isset($errors['noOfVehiclesRequired'])) {
            foreach ($errors['noOfVehiclesRequired'] as $message) {
                $formMessages['data']['noOfVehiclesRequired'][] = $message;
            }

            unset($errors['noOfVehiclesRequired']);
        }

        if (isset($errors['noOfTrailersRequired'])) {
            foreach ($errors['noOfTrailersRequired'] as $message) {
                $formMessages['data']['noOfTrailersRequired'][] = $message;
            }

            unset($errors['noOfTrailersRequired']);
        }

        if (isset($errors['adPlacedIn'])) {
            foreach ($errors['adPlacedIn'] as $message) {
                $formMessages['advertisements']['adPlacedIn'][] = $message;
            }

            unset($errors['adPlacedIn']);
        }

        if (isset($errors['adPlacedDate'])) {
            foreach ($errors['adPlacedDate'] as $message) {
                $formMessages['advertisements']['adPlacedDate'][] = $message;
            }

            unset($errors['adPlacedDate']);
        }

        if (isset($errors['file'])) {
            foreach ($errors['file'] as $message) {
                $formMessages['advertisements']['file']['upload'][] = $message;
            }

            unset($errors['file']);
        }

        $isExternal = ($location === self::LOC_EXTERNAL);

        if (isset($errors['postcode'])) {
            foreach ($errors['postcode'] as $message) {
                foreach ($message as $k => $v) {
                    if ($k === 'ERR_OC_PC_TA_GB') {
                        $message[$k] = $translator->translateReplace($k, [$taGuidesUrl]);
                        $form->get('form-actions')->setOption('shouldEscapeMessages', false);
                        self::setConfirmation($form, $translator, $isExternal, $k);
                    } elseif (in_array($k, OperatingCentres::API_ERR_KEYS)) {
                        $message[$k] = $translator->translateReplace($k . '_' . strtoupper($location), [$v]);
                    }
                }

                if (!$isExternal) {
                    $formMessages['form-actions'][] = $message;
                } else {
                    $formMessages['address']['postcode'][] = $message;
                }
            }

            unset($errors['postcode']);
        }

        if (isset($errors['permission'])) {
            foreach ($errors['permission'] as $message) {
                $formMessages['data']['permission']['permission'][] = $message;
            }

            unset($errors['permission']);
        }

        foreach ($errors as $error) {
            $fm->addCurrentErrorMessage($error);
        }

        $form->setMessages($formMessages);
    }

    /**
     * @param                          $isExternal
     */
    protected static function setConfirmation(
        Form $form,
        TranslationHelperService $translator,
        $isExternal,
        string $k
    ): void {
        if (!$isExternal) {
            $confirm = new OlcsCheckbox(
                'confirm-add',
                ['label' => $translator->translate($k . '-confirm')]
            );
            $confirm->setMessages([$translator->translate($k . "-internalwarning")]);
            $form->get('form-actions')->add($confirm, ['priority' => 20]);
        }
    }
}
