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
 * Class Shield
 *
 * @package selvinortiz\shield
 *
 * @property ShieldService $service
 */
class Shield extends Plugin
{
    public $hasCpSection  = true;
    public $hasCpSettings = false;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            [$this, 'registerTemplateComponent']
        );

        if ($this->shouldEnableContactFormSupport())
        {
            Event::on(
                \craft\contactform\Mailer::class,
                \craft\contactform\Mailer::EVENT_BEFORE_SEND,
                function(\craft\contactform\events\SendEvent $event)
                {
                    $event->isSpam = shield()->service->detectContactFormSpam($event->submission);
                }
            );
        }

        if ($this->shouldEnableGuestEntriesSupport())
        {
            Event::on(
                craft\guestentries\controllers\SaveController::class,
                craft\guestentries\controllers\SaveController::EVENT_BEFORE_SAVE_ENTRY,
                function(craft\guestentries\events\SaveEvent $event)
                {
                    $event->isSpam = shield()->service->detectDynamicFormSpam($event->entry);
                }
            );
        }

        if ($this->shouldEnableSproutFormsSupport())
        {

        }

        $this->set('service', ShieldService::class);
    }

    public function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @param Event $event
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function registerTemplateComponent(Event $event)
    {
        /**
         * @var CraftVariable $variable
         */
        $variable = $event->sender;

        $variable->set('shield', ShieldVariable::class);
    }

    /**
     * @return bool
     */
    protected function shouldEnableContactFormSupport()
    {
        if (!$this->getSettings()->enableContactFormSupport)
        {
            return false;
        }

        if (!Craft::$app->plugins->isPluginInstalled('contact-form'))
        {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function shouldEnableGuestEntriesSupport()
    {
        if (!$this->getSettings()->enableGuestEntriesSupport)
        {
            return false;
        }

        if (!Craft::$app->plugins->isPluginInstalled('guest-entries'))
        {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function shouldEnableSproutFormsSupport()
    {
        if (!$this->getSettings()->enableSproutFormsSupport)
        {
            return false;
        }

        if (!Craft::$app->plugins->isPluginInstalled('sprout-forms'))
        {
            return false;
        }

        return true;
    }
}

/**
 * @return Shield
 */
function shield()
{
    return Craft::$app->loadedModules[Shield::class] ?? null;
}
