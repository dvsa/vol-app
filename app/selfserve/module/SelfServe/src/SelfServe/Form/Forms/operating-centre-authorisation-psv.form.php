<?php

/**
 * <i>THIS ALWAYS MUST BE UNIT TESTED</i>
 */

$form = include(dirname(__FILE__) . '/operating-centre-authorisation.form.php');
$form['operating-centre-authorisation-psv'] = $form['operating-centre-authorisation'];
unset($form['operating-centre-authorisation']);
unset($form['operating-centre-authorisation-psv']['fieldsets'][0]['elements']['totAuthTrailers']);

return $form;