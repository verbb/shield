<?php
namespace verbb\shield\base;

use verbb\shield\Shield;
use verbb\shield\services\Logs;
use verbb\shield\services\Service;
use verbb\shield\web\assets\cp\ShieldAsset;

use verbb\base\LogTrait;
use verbb\base\helpers\Plugin;
use verbb\base\services\Templates;

use craft\helpers\App;

use nystudio107\pluginvite\services\VitePluginService;

trait PluginTrait
{
    // Properties
    // =========================================================================

    public static ?Shield $plugin = null;


    // Traits
    // =========================================================================

    use LogTrait;


    // Static Methods
    // =========================================================================

    public static function config(): array
    {
        Plugin::bootstrapPlugin('shield');

        return [
            'components' => [
                'logs' => Logs::class,
                'service' => Service::class,
                'templates' => [
                    'class' => Templates::class,
                    'pluginClass' => Shield::class,
                ],
                'vite' => [
                    'class' => VitePluginService::class,
                    'assetClass' => ShieldAsset::class,
                    // The dev server is opt-in, so production requests never probe localhost. Set
                    // SHIELD_USE_VITE_DEV_SERVER=true while running `npm run dev`.
                    'useDevServer' => filter_var(App::parseEnv('$SHIELD_USE_VITE_DEV_SERVER') ?: false, FILTER_VALIDATE_BOOL),
                    'devServerPublic' => rtrim(App::parseEnv('$SHIELD_CP_DEV_SERVER_PUBLIC') ?: 'http://localhost:4046/', '/') . '/',
                    'errorEntry' => 'cp/src/js/shield.ts',
                    'cacheKeySuffix' => '',
                    'devServerInternal' => rtrim(App::parseEnv('$SHIELD_CP_DEV_SERVER_INTERNAL') ?: 'http://localhost:4046/', '/') . '/',
                    'checkDevServer' => true,
                    'includeReactRefreshShim' => false,
                ],
            ],
        ];
    }


    // Public Methods
    // =========================================================================

    public function getLogs(): Logs
    {
        return $this->get('logs');
    }

    public function getService(): Service
    {
        return $this->get('service');
    }

    public function getTemplates(): Templates
    {
        return $this->get('templates');
    }

    public function getVite(): VitePluginService
    {
        return $this->get('vite');
    }

}
