<?php

/**
 * Interim
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;
use Common\Service\Helper\FlashMessengerHelperService;
use Laminas\Form\Form;

/**
 * Interim
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Interim implements MapperInterface
{
    /**
     * map data from result array
     *
     * @param array $data data
     *
     * @return array
     */
    #[\Override]
    public static function mapFromResult(array $data)
    {
        return [
            'version' => $data['version'],
            'data' => [
                'interimReason' => $data['interimReason'],
                'interimStart' => $data['interimStart'],
                'interimEnd' => $data['interimEnd'],
                'interimAuthHgvVehicles' => $data['interimAuthHgvVehicles'],
                'interimAuthLgvVehicles' => $data['interimAuthLgvVehicles'],
                'interimAuthTrailers' => $data['interimAuthTrailers']
            ],
            'requested' => [
                'interimRequested' => (empty($data['interimStatus']['id']) ? 'N' : 'Y')
            ],
            'interimStatus' => [
                'status' => $data['interimStatus']['id']
            ]
        ];
    }

    /**
     * map data from form
     *
     * @param array $data data
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $defaultDataData = [
            'version' => null,
            'interimReason' => null,
            'interimStart' => null,
            'interimEnd' => null,
            'interimAuthHgvVehicles' => null,
            'interimAuthLgvVehicles' => null,
            'interimAuthTrailers' => null
        ];
        $dataData = array_merge($defaultDataData, $data['data']);

        return [
            'version' => $data['version'],
            'requested' => $data['requested']['interimRequested'],
            'reason' => $dataData['interimReason'],
            'startDate' => $dataData['interimStart'],
            'endDate' => $dataData['interimEnd'],
            'authHgvVehicles' => $dataData['interimAuthHgvVehicles'],
            'authLgvVehicles' => $dataData['interimAuthLgvVehicles'],
            'authTrailers' => $dataData['interimAuthTrailers'],
            'operatingCentres' => $data['operatingCentres']['id'] ?? [],
            'vehicles' => $data['vehicles']['id'] ?? [],
            'status' => $data['interimStatus']['status'] ?? null
        ];
    }

    /**
     * map form errors
     *
     * @param Form                        $form   form
     * @param array                       $errors array of errors
     * @param FlashMessengerHelperService $fm     flash messenger
     */
    public static function mapFormErrors(Form $form, array $errors, FlashMessengerHelperService $fm): void
    {
        $formMessages = [];

        if (isset($errors['reason'])) {
            foreach ($errors['reason'] as $message) {
                $formMessages['data']['interimReason'][] = $message;
            }

            unset($errors['reason']);
        }

        if (isset($errors['startDate'])) {
            foreach ($errors['startDate'] as $message) {
                $formMessages['data']['interimStart'][] = $message;
            }

            unset($errors['startDate']);
        }

        if (isset($errors['endDate'])) {
            foreach ($errors['endDate'] as $message) {
                $formMessages['data']['interimEnd'][] = $message;
            }

            unset($errors['endDate']);
        }

        if (isset($errors['authHgvVehicles'])) {
            foreach ($errors['authHgvVehicles'] as $message) {
                $formMessages['data']['interimAuthHgvVehicles'][] = $message;
            }

            unset($errors['authHgvVehicles']);
        }

        if (isset($errors['authLgvVehicles'])) {
            foreach ($errors['authLgvVehicles'] as $message) {
                $formMessages['data']['interimAuthLgvVehicles'][] = $message;
            }

            unset($errors['authLgvVehicles']);
        }

        if (isset($errors['authTrailers'])) {
            foreach ($errors['authTrailers'] as $message) {
                $formMessages['data']['interimAuthTrailers'][] = $message;
            }

            unset($errors['authTrailers']);
        }

        foreach ($errors as $error) {
            $fm->addCurrentErrorMessage($error);
        }

        $form->setMessages($formMessages);
    }
}
