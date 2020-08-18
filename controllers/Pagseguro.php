<?php namespace Dmrch\PagSeguro\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Pagseguro Back-end Controller
 */
class Pagseguro extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Dmrch.PagSeguro', 'pagseguro', 'pagseguro');
    }
}
