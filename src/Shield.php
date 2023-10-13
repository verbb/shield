<?php
namespace verbb\shield;

use verbb\shield\base\PluginTrait;
use verbb\shield\models\Settings;
use verbb\shield\variables\ShieldVariable;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;

use craft\contactform\Mailer;
use craft\contactform\events\SendEvent;

use craft\guestentries\controllers\SaveController;
use craft\guestentries\events\SaveEvent;

use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;

use yii\base\Event;

class Shield extends Plugin
{
    // Properties
    // =========================================================================

    public string $schemaVersion = '1.0.0';
    public bool $hasCpSection = true;


    // Traits
    // =========================================================================

    use PluginTrait;


    // Public Methods
    // =========================================================================

    public function init(): void
    {
        parent::init();

        self::$plugin = $this;

        $this->_registerVariables();
        $this->_registerEventHandlers();

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            $this->_registerCpRoutes();
        }
    }

    public function getPluginName(): string
    {
        return Craft::t('shield', 'Shield');
    }


    // Protected Methods
    // =========================================================================

    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }


    // Private Methods
    // =========================================================================

    private function _registerCpRoutes(): void
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'shield' => 'shield/logs',
            ]);
        });
    }

    private function _registerVariables(): void
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $event->sender->set('shield', ShieldVariable::class);
        });
    }

    private function _registerEventHandlers(): void
    {
        $settings = $this->getSettings();
        $pluginsService = Craft::$app->getPlugins();

        if ($settings->enableContactFormSupport && $pluginsService->isPluginInstalled('contact-form')) {
            Event::on(Mailer::class, Mailer::EVENT_BEFORE_SEND, function(SendEvent $event) {
                $event->isSpam = $this->getService()->detectContactFormSpam($event->submission);
            });
        }

        if ($settings->enableGuestEntriesSupport && $pluginsService->isPluginInstalled('guest-entries')) {
            Event::on(SaveController::class, SaveController::EVENT_BEFORE_SAVE_ENTRY, function(SaveEvent $event) {
                $event->isSpam = $this->getService()->detectDynamicFormSpam($event->entry);
            });
        }
    }
}
