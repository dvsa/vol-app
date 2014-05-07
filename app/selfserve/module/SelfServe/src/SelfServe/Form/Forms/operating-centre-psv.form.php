<?php

$operatingCentreForm = include(dirname(__FILE__) . '/operating-centre.form.php');

//reconfigure form for psv licence type
$operatingCentreForm['operating-centre-psv'] = $operatingCentreForm['operating-centre'];
unset($operatingCentreForm['operating-centre']);
unset($operatingCentreForm['operating-centre-psv']['fieldsets']['authorised-vehicles']['elements']['no-of-trailers']);
$operatingCentreForm['operating-centre-psv']['fieldsets']['authorised-vehicles']['options']['label'] = 'Vehicles';
$operatingCentreForm['operating-centre-psv']['fieldsets']['authorised-vehicles']['elements']['no-of-vehicles']['type'] = 'vehiclesNumberPsv';
$operatingCentreForm['operating-centre-psv']['fieldsets']['authorised-vehicles']['elements']['parking-spaces-confirmation']['label'] =
    'I have enough parking spaces available for the '.
    'total number of vehicles that I want '.
    'to keep at this address';

$operatingCentreForm['operating-centre-psv']['fieldsets']['authorised-vehicles']['elements']['permission-confirmation']['label'] =
    'I am either the site owner or have permission from '.
    'the site owner to use the premises to park the number '.
    'of vehicles stated';

return $operatingCentreForm;