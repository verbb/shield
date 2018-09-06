<?php
namespace selvinortiz\shield;

use yii\base\Event;

use Craft;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;

use craft\contactform\Mailer;
use craft\contactform\events\SendEvent;
use craft\guestentries\controllers\SaveController;
use craft\guestentries\events\SaveEvent;

use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;

use selvinortiz\shield\models\Settings;
use selvinortiz\shield\services\LogsService;
use selvinortiz\shield\services\ShieldService;
use selvinortiz\shield\variables\ShieldVariable;

/**
 * Class Shield
 *
 * @package selvinortiz\shield
 *
 * @property LogsService   $logs
 * @property ShieldService $service
 */
class Shield extends Plugin
{
    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @todo Make configurable
     *
     * @var bool
     */
    public $hasCpSection = true;

    /**
     * @todo Make configurable
     *
     * @var bool
     */
    public $hasCpSettings = false;

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
                Mailer::class,
                Mailer::EVENT_BEFORE_SEND,
                function(SendEvent $event) {
                    $event->isSpam = shield()->service->detectContactFormSpam($event->submission);
                }
            );
        }

        if ($this->shouldEnableGuestEntriesSupport())
        {
            Event::on(
                SaveController::class,
                SaveController::EVENT_BEFORE_SAVE_ENTRY,
                function(SaveEvent $event) {
                    $event->isSpam = shield()->service->detectDynamicFormSpam($event->entry);
                }
            );
        }

        if ($this->shouldEnableSproutFormsSupport())
        {
            Event::on(
                Entry::class,
                Entry::EVENT_BEFORE_SAVE,
                function(OnBeforeSaveEntryEvent $event) {
                    if (shield()->service->detectDynamicFormSpam($event->entry))
                    {
                        $event->fakeIt  = true;
                        $event->isValid = false;
                    }
                }
            );
        }

        // @todo Add support for Comments by Verbb (Josh Crawford)

        $this->setComponents([
            'logs'    => LogsService::class,
            'service' => ShieldService::class,
        ]);
    }

    /**
     * @return Settings
     */
    public function getSettings()
    {
        return parent::getSettings();
    }

    public function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @param string|array $message
     */
    public function info($message)
    {
        Craft::info($message, 'shield');
    }

    /**
     * @param string|array $message
     */
    public function error($message)
    {
        Craft::error($message, 'shield');
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

    /**
     * @todo Figure out what is up with the query exception on install
     *
     * @return bool
     */
    protected function shouldEnableCommentsSupport()
    {
        if (!$this->getSettings()->enableCommentsSupport)
        {
            return false;
        }

        if (!Craft::$app->plugins->isPluginInstalled('comments'))
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
