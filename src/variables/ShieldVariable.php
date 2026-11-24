<?php
namespace verbb\shield\variables;

use verbb\shield\Shield;
use verbb\shield\helpers\Plugin;

class ShieldVariable
{
    // Public Methods
    // =========================================================================

    public function getPluginName(): string
    {
        return Shield::$plugin->getPluginName();
    }

    public function registerCpAssets(): void
    {
        Plugin::registerCpAssets();
    }
}
