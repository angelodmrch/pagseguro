<?php namespace Dmrch\PagSeguro;

use Backend;
use Event;
use System\Classes\PluginBase;

/**
 * PagSeguro Plugin Information File
 */
class Plugin extends PluginBase
{

    public $require = [
        'RainLab.User',
        'Dmrch.UserExtension'
    ];
    
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'PagSeguro',
            'description' => 'No description provided yet...',
            'author'      => 'Angelo Demarchi',
            'icon'        => 'icon-credit-card-alt'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {

    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Dmrch\PagSeguro\Components\Pagseguro' => 'PagSeguro',
            'Dmrch\PagSeguro\Components\Cart' => 'Cart',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'dmrch.pagseguro.navigation' => [
                'tab'   => 'PagSeguro',
                'label' => 'PagSeguro'
            ],
            'dmrch.pagseguro.settings' => [
                'tab'   => 'PagSeguro',
                'label' => 'Configurações'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [
            'pagseguro' => [
                'label'       => 'PagSeguro',
                'url'         => Backend::url('dmrch/pagseguro/pagseguro'),
                'icon'        => 'icon-credit-card-alt',
                'permissions' => ['dmrch.pagseguro.navigation'],
                'order'       => 500,
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'PagSeguro',
                'description' => 'Manage PagSeguro settings.',
                'category'    => 'Geral',
                'icon'        => 'icon-cog',
                'class'       => 'Dmrch\PagSeguro\Models\Settings',
                'order'       => 500,
                'permissions' => ['dmrch.pagseguro.settings'],
            ]
        ];
    }
}
