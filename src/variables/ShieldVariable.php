<?php
namespace verbb\shield\variables;

use verbb\shield\Shield;

class ShieldVariable
{
    // Public Methods
    // =========================================================================

    public function getPluginName(): string
    {
        return Shield::$plugin->getPluginName();
    }
}
