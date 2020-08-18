<?php

Route::post('pagseguro/notification', array(
	'as' => 'notification.api.post', 
	'uses' => 'Dmrch\PagSeguro\Components\Pagseguro@onNotification'
));
