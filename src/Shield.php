<?php
namespace selvinortiz\shield;

use yii\base\Event;

use Craft;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;

use selvinortiz\shield\models\Settings;
use selvinortiz\shield\services\ShieldService;
use selvinortiz\shield\variables\ShieldVariable;

/**
 * @property ShieldService
 */
class Shield extends Plugin
{
    public $hasCpSection  = true;
    public $hasCpSettings = false;

    public function init()
    {
        parent::init();

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            [$this, 'registerTemplateComponent']
        );

        $this->set('service', ShieldService::class);
    }

    public function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @param Event $event
     *
     * @return void
     */
    public function registerTemplateComponent(Event $event)
    {
        /**
         * @var CraftVariable $variable
         */
        $variable = $event->sender;

        $variable->set('shield', ShieldVariable::class);
    }
}

/**
 * @return Shield
 */
function shield()
{
    return Craft::$app->loadedModules[Shield::class] ?? null;
}
