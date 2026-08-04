<?php

namespace Common\Data\Mapper\Lva;

use Common\Service\Table\Formatter\VehicleDiscNo;
use Laminas\Form\FormInterface;

/**
 * Goods Vehicles Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GoodsVehiclesVehicle
{
    public function __construct(protected VehicleDiscNo $vehicleDiscNo)
    {
    }

    /**
     * @return array
     */
    public function mapFromResult(array $data)
    {
        $licenceVehicle = $data;
        unset($licenceVehicle['vehicle']);

        $licenceVehicle['discNo'] = $this->vehicleDiscNo->format($licenceVehicle);
        unset($licenceVehicle['goodsDiscs']);

        $dataFieldset = $data['vehicle'];
        $dataFieldset['version'] = $data['version'];

        return [
            'licence-vehicle' => $licenceVehicle,
            'data' => $dataFieldset
        ];
    }

    /**
     * @return array
     */
    public static function mapFromErrors($errors, FormInterface $form)
    {
        $dataFields = ['vrm', 'platedWeight'];
        $licenceVehicleFields = [
            'receivedDate', 'specifiedDate', 'removalDate', 'warningLetterSeedDate', 'discNo', 'warningLetterSentDate'
        ];
        $formMessages = [];
        foreach ($errors as $key => $error) {
            if (in_array($key, $dataFields, false)) {
                foreach ($error as $subError) {
                    $formMessages['data'][$key][] = $subError;
                }

                unset($errors[$key]);
            }

            if (in_array($key, $licenceVehicleFields, false)) {
                foreach ($error as $subError) {
                    $formMessages['licenceVehicle'][$key][] = $subError;
                }

                unset($errors[$key]);
            }
        }

        $form->setMessages($formMessages);
        return $errors;
    }
}
