<?php

$vehicleSafetyForm = include(dirname(__FILE__) . '/vehicle-safety.form.php');

//reconfigure form for psv licence type
$vehicleSafetyForm['vehicle-safety-psv'] = $vehicleSafetyForm['vehicle-safety'];
$vehicleSafetyForm['vehicle-safety-psv']['name']='vehicle-safety-psv';
unset($vehicleSafetyForm['vehicle-safety']);
unset($vehicleSafetyForm['vehicle-safety-psv']['fieldsets'][0]['elements']['licence.safetyInsTrailers']);

$vehicleSafetyForm['vehicle-safety-psv']['fieldsets'][0]['elements']['licence.safetyInsVaries']['label'] =
    'selfserve-app-vehicle-safety-safety-moreFrequentInspectionsNoTrailer';

return $vehicleSafetyForm;
