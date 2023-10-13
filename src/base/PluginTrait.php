<?php
namespace verbb\shield\base;

use verbb\shield\Shield;
use verbb\shield\services\Logs;
use verbb\shield\services\Service;

use verbb\base\LogTrait;
use verbb\base\helpers\Plugin;

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

}