<?php
namespace verbb\shield\helpers;

use verbb\shield\Shield;
use verbb\shield\web\assets\cp\ShieldAsset;

class Plugin
{
    // Static Methods
    // =========================================================================

    public static function registerAsset(string $path): void
    {
        $viteService = Shield::$plugin->getVite();

        $scriptOptions = [
            'depends' => [
                ShieldAsset::class,
            ],
            'onload' => null,
        ];

        $styleOptions = [
            'depends' => [
                ShieldAsset::class,
            ],
        ];

        $viteService->register($path, false, $scriptOptions, $styleOptions);

        // Provide nice build errors, only in Vite dev.
        if ($viteService->devServerRunning()) {
            $viteService->register('@vite/client', false);
        }
    }

    public static function registerCpAssets(): void
    {
        self::registerAsset('cp/src/js/shield.ts');
    }
}
