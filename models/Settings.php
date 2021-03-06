<?php namespace Dmrch\PagSeguro\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code
    public $settingsCode = 'dmrch_pagseguro_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';
}