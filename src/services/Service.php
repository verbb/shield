<?php
namespace verbb\shield\services;

use verbb\shield\Shield;
use verbb\shield\enums\CommentType;
use verbb\shield\models\Log;

use Craft;
use craft\base\Model;
use craft\base\Component;
use craft\base\Element;
use craft\elements\User;
use craft\helpers\Json;

use yii\base\InvalidConfigException;
use yii\base\UserException;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

use Exception;
use Throwable;

class Service extends Component
{
    // Constants
    // =========================================================================

    public const ENDPOINT = 'rest.akismet.com/1.1';

    private const CONNECT_TIMEOUT = 3;
    private const REQUEST_TIMEOUT = 5;


    // Properties
    // =========================================================================

    protected array $params = [];
    protected ?Client $httpClient = null;


    // Public Methods
    // =========================================================================

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->params = [
            'blog' => $this->getOriginUrl(),
            'user_agent' => $this->getUserAgent(),
            'comment_type' => CommentType::ContactForm,
        ];

        $userIp = $this->getRequestingIp();

        if ($userIp !== null) {
            $this->params['user_ip'] = $userIp;
        }

        $this->httpClient = Craft::createGuzzleClient();
    }

    /**
     * @param Client $httpClient
     */
    public function setHttpClient(Client $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return Shield::$plugin->getSettings()->akismetApiKey;
    }

    /**
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function getOriginUrl(): string
    {
        $originUrl = Shield::$plugin->getSettings()->akismetOriginUrl;
        $originUrl = trim($originUrl);

        if (empty($originUrl) || '{siteUrl}' === $originUrl) {
            return Craft::$app->getRequest()->getUrl();
        }

        return $originUrl;
    }

    /**
     * @return bool
     *
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function isKeyValid(): bool
    {
        $params = [
            'api_key' => $this->getApiKey(),
            'blog' => $this->getOriginUrl(),
        ];

        $response = $this->httpClient->post($this->getKeyEndpoint(), $this->_requestOptions($params));
        $response = (string)$response->getBody();

        return $response == 'valid';
    }

    /**
     * @param array $data
     *
     * @return bool
     *
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function isSpam(array $data = []): bool
    {
        $flaggedAsSpam = false;

        try {
            $flaggedAsSpam = $this->detectSpam($data);
        } catch (GuzzleException) {
            Shield::warning('Unable to check this submission with Akismet. The submission was allowed.');
        } catch (UserException $e) {
            $message = array_merge($data, [
                'error' => $e,
                'flaggedAsSpam' => $flaggedAsSpam,
            ]);

            Shield::error(Json::encode($message));
        }

        Shield::info(Json::encode($data));

        // Should we save the log?
        if (Shield::$plugin->getSettings()->logSubmissions) {
            $log = new Log();
            $log->type = $data['type'] ?? null;
            $log->email = $data['email'] ?? null;
            $log->author = $data['author'] ?? null;
            $log->content = $data['content'] ?? null;
            $log->flagged = $flaggedAsSpam;

            Shield::$plugin->getLogs()->saveLog($log);
        }

        return $flaggedAsSpam;
    }

    /**
     * @param array $data
     *
     * @return bool
     *
     * @throws UserException
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function detectSpam(array $data = []): bool
    {
        $params = array_merge($this->params, [
            'api_key' => $this->getApiKey(),
            'comment_type' => $data['type'] ?? $this->params['comment_type'] ?? null,
            'comment_author' => $data['author'] ?? null,
            'comment_content' => $data['content'] ?? null,
            'comment_author_email' => $data['email'] ?? null,
        ]);

        $response = $this->httpClient->post($this->getContentEndpoint(), $this->_requestOptions($params));
        $body = trim((string)$response->getBody());

        if ($body === 'true') {
            return true;
        }

        if ($body === 'false') {
            return false;
        }

        $message = $response->getHeaderLine('X-akismet-debug-help') ?: 'Akismet returned an invalid response.';

        throw new UserException($message);
    }

    /**
     * @param array $data
     *
     * @return bool
     *
     * @throws UserException
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function submitSpam(array $data = []): bool
    {
        $params = array_merge($this->params, [
            'api_key' => $this->getApiKey(),
            'comment_author' => $data['author'] ?? null,
            'comment_content' => $data['content'] ?? null,
            'comment_author_email' => $data['email'] ?? null,
        ]);

        if ($this->isKeyValid()) {
            $response = $this->httpClient->post($this->getSpamEndpoint(), $this->_requestOptions($params));
            $response = (string)$response->getBody();

            return 'Thanks for making the web a better place.' == $response;
        }

        throw new UserException('Your akismet api key is invalid.');
    }

    /**
     * @param array $data
     *
     * @return bool
     *
     * @throws UserException
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function submitHam(array $data = []): bool
    {
        $params = array_merge($this->params, [
            'api_key' => $this->getApiKey(),
            'comment_author' => $data['author'] ?? null,
            'comment_content' => $data['content'] ?? null,
            'comment_author_email' => $data['email'] ?? null,
        ]);

        if ($this->isKeyValid()) {
            $response = $this->httpClient->post($this->getHamEndpoint(), $this->_requestOptions($params));
            $response = (string)$response->getBody();

            return 'Thanks for making the web a better place.' == $response;
        }

        throw new UserException('Your akismet api key is invalid.');
    }

    /**
     * Allows you to use Shield alongside the Contact Form plugin by P&T
     *
     * @param Model $submission
     *
     * @return boolean
     *
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function detectContactFormSpam(Model $submission): bool
    {
        $data = [
            'type' => CommentType::ContactForm,
            'email' => $submission->fromEmail,
            'author' => $submission->fromName,
            'content' => $submission->message,
        ];

        return $this->isSpam($data);
    }

    /**
     * Allows you to use Shield alongside Guest Entries plugin by P&T
     * It also allows you to use Shield with other dynamic forms
     *
     * @param Model $model
     *
     * @return bool
     *
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function detectDynamicFormSpam(Model $model): bool
    {
        $data = [
            'type' => CommentType::ContactForm,
            'email' => Craft::$app->getRequest()->post('shield.emailHandle'),
            'author' => Craft::$app->getRequest()->post('shield.authorHandle'),
            'content' => Craft::$app->getRequest()->post('shield.contentHandle'),
        ];

        $data = $this->renderObjectFields($data, $model);

        if ($data) {
            return $this->isSpam($data);
        }

        return false;
    }

    public function detectUserRegistrationSpam(User $user): bool
    {
        $data = [
            'type' => CommentType::Signup,
            'email' => $user->email,
            'author' => $user->fullName ?: $user->username,
            'content' => $user->username,
        ];

        return $this->isSpam($data);
    }

    public function getRequestingIp(): ?string
    {
        $request = Craft::$app->getRequest();
        $trustedHosts = $request->trustedHosts;

        // Craft trusts forwarded IP headers by default, so only use them after the site restricts trusted proxies.
        if (in_array('any', $trustedHosts, true) || array_key_exists('any', $trustedHosts)) {
            return $request->getRemoteIP();
        }

        return $request->getUserIP();
    }

    protected function getUserAgent(): string
    {
        $craftInfo = 'Craft ' . Craft::$app->getEditionName() . ' ' . Craft::$app->getVersion();
        $pluginInfo = 'Shield ' . Shield::$plugin->getVersion();

        return Craft::$app->getRequest()->getUserAgent() ?? ($craftInfo . ' | ' . $pluginInfo);
    }

    protected function getKeyEndpoint(): string
    {
        return sprintf('https://%s/verify-key', self::ENDPOINT);
    }

    protected function getContentEndpoint(): string
    {
        return sprintf('https://%s/comment-check', self::ENDPOINT);
    }

    protected function getSpamEndpoint(): string
    {
        return sprintf('https://%s/submit-spam', self::ENDPOINT);
    }

    protected function getHamEndpoint(): string
    {
        return sprintf('https://%s/submit-ham', self::ENDPOINT);
    }

    /**
     * @param array $fields
     * @param object $object
     *
     * @return array
     * @throws Throwable
     * @throws Throwable
     */
    protected function renderObjectFields(array $fields, object $object): array
    {
        try {
            $fieldValues = $this->_getTokenFieldValues($object);

            foreach ($fields as $field => $value) {
                $fields[$field] = Shield::$plugin->getTemplates()->renderTokens((string)$value, $fieldValues);
            }
        } catch (Exception $e) {
            Shield::error($e->getMessage());

            return [];
        }

        return $fields;
    }


    // Private Methods
    // =========================================================================

    private function _requestOptions(array $params): array
    {
        // Apply the bounds per request so an injected HTTP client cannot remove them.
        return [
            'connect_timeout' => self::CONNECT_TIMEOUT,
            'timeout' => self::REQUEST_TIMEOUT,
            'form_params' => $params,
        ];
    }

    private function _getTokenFieldValues(object $object): array
    {
        // Element metadata and service getters are not submitted form fields.
        if ($object instanceof Element) {
            $names = ['title'];

            foreach ($object->getFieldLayout()?->getCustomFields() ?? [] as $field) {
                $names[] = $field->handle;
            }
        } elseif ($object instanceof Model) {
            $names = $object->safeAttributes();
        } else {
            return [];
        }

        $values = [];

        foreach ($names as $name) {
            $value = $object->$name;

            if (is_scalar($value) || $value === null) {
                $values[$name] = $value;
            }
        }

        return $values;
    }
}
