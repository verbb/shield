<?php
namespace selvinortiz\shield;

use yii\base\Event;

use craft\contactform\Mailer;

use Craft;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;
use selvinortiz\shield\models\Settings;
use selvinortiz\shield\services\ShieldService;
use selvinortiz\shield\variables\ShieldVariable;
use selvinortiz\shield\controllers\ShieldController;

/**
 * @property ShieldService $service
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

        Event::on(
            Mailer::class,
            Mailer::EVENT_BEFORE_SEND,
            function(Event $event)
            {
                $event->isSpam = shield()->service->detectContactFormSpam($event->submission);
            }
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
