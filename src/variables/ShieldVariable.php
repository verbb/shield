<?php
namespace selvinortiz\shield\variables;

use function selvinortiz\shield\shield;

class ShieldVariable
{
    public function version()
    {
        return shield()->version;
    }

    public function settings()
    {
        return shield()->getSettings();
    }

    public function isKeyValid()
    {
        return shield()->service->isKeyValid();
    }
}
