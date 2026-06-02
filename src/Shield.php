<?php
namespace verbb\shield;

use verbb\shield\base\PluginTrait;
use verbb\shield\models\Settings;
use verbb\shield\variables\ShieldVariable;

use Craft;
use craft\base\Plugin;
use craft\elements\User;
use craft\events\ModelEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\Request;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;

use verbb\base\helpers\Plugin as PluginHelper;

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

        if ($settings->enableContactFormSupport && PluginHelper::isPluginInstalledAndEnabled('contact-form')) {
            Event::on(Mailer::class, Mailer::EVENT_BEFORE_SEND, function(SendEvent $event) {
                $event->isSpam = $this->getService()->detectContactFormSpam($event->submission);
            });
        }

        if ($settings->enableGuestEntriesSupport && PluginHelper::isPluginInstalledAndEnabled('guest-entries')) {
            Event::on(SaveController::class, SaveController::EVENT_BEFORE_SAVE_ENTRY, function(SaveEvent $event) {
                $event->isSpam = $this->getService()->detectDynamicFormSpam($event->entry);
            });
        }

        if ($settings->enableUserRegistrationSupport) {
            Event::on(User::class, User::EVENT_BEFORE_SAVE, function(ModelEvent $event) {
                /** @var User $user */
                $user = $event->sender;

                if (!$event->isNew || !$this->_isPublicUserRegistration()) {
                    return;
                }

                if ($this->getService()->detectUserRegistrationSpam($user)) {
                    $user->addError('email', Craft::t('shield', 'Unable to register this account.'));

                    $event->isValid = false;
                }
            });
        }
    }

    private function _isPublicUserRegistration(): bool
    {
        $request = Craft::$app->getRequest();

        if (!$request instanceof Request || !$request->getIsPost() || $request->getIsCpRequest()) {
            return false;
        }

        if ($request->getBodyParam('userId')) {
            return false;
        }

        return !Craft::$app->getUser()->getIdentity();
    }
}
