<?php

/**
 * Psv Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;
use Common\Service\Helper\FlashMessengerHelperService;
use Laminas\Form\Form;

/**
 * Psv Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PsvVehicles implements MapperInterface
{
    /**
     * @return (mixed|null|string)[][]
     *
     * @psalm-return array{data: array{version: mixed, hasEnteredReg: 'Y'|mixed}, shareInfo: array{shareInfo: mixed|null}}
     */
    #[\Override]
    public static function mapFromResult(array $data)
    {
        return [
            'data' => [
                'version' => $data['version'],
                // @NOTE: licences don't have this flag, but we haven't defined their behaviour
                // on PSV pages yet. As such, this just prevents a PHP error
                'hasEnteredReg' => $data['hasEnteredReg'] ?? 'Y'
            ],
            'shareInfo' => [
                'shareInfo' => $data['organisation']['confirmShareVehicleInfo'] ?? null
            ]
        ];
    }

    public static function mapFormErrors(Form $form, array $errors, FlashMessengerHelperService $fm): void
    {
        $formMessages = [];

        if (isset($errors['hasEnteredReg'])) {
            foreach ($errors['hasEnteredReg'] as $message) {
                $formMessages['data']['hasEnteredReg'][] = $message;
            }

            unset($errors['hasEnteredReg']);
        }

        foreach ($errors as $error) {
            $fm->addCurrentErrorMessage($error);
        }

        $form->setMessages($formMessages);
    }
}
