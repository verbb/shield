<?php
namespace verbb\shield\services;

use verbb\shield\Shield;
use verbb\shield\enums\CommentType;
use verbb\shield\models\Log;

use Craft;
use craft\base\Model;
use craft\base\Component;

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

    const ENDPOINT = 'rest.akismet.com/1.1';


    // Properties
    // =========================================================================

    /**
     * Default parameters
     *
     * @var array
     */
    protected $params;

    /**
     *
     * @var Client
     */
    protected $httpClient;


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
            'user_ip' => $this->getRequestingIp(),
            'user_agent' => $this->getUserAgent(),
            'comment_type' => CommentType::ContactForm,
        ];

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
            'key' => $this->getApiKey(),
            'blog' => $this->getOriginUrl(),
        ];

        $response = $this->httpClient->post($this->getKeyEndpoint(), ['form_params' => $params]);
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
        $isKeyValid = true;
        $flaggedAsSpam = false;

        try {
            $flaggedAsSpam = $this->detectSpam($data);
        } catch (UserException $e) {
            $message = array_merge($data, [
                'error' => $e,
                'isKeyValid' => $isKeyValid,
                'flaggedAsSpam' => $flaggedAsSpam,
            ]);

            Shield::error($message);
        }

        Shield::error($data);

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
            'comment_type' => $data['type'] ?? $this->params['comment_type'] ?? null,
            'comment_author' => $data['author'] ?? null,
            'comment_content' => $data['content'] ?? null,
            'comment_author_email' => $data['email'] ?? null,
        ]);

        if ($this->isKeyValid()) {
            $response = $this->httpClient->post($this->getContentEndpoint(), ['form_params' => $params]);
            $response = (string)$response->getBody();

            return 'true' == $response;
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
    public function submitSpam(array $data = []): bool
    {
        $params = array_merge($this->params, [
            'comment_author' => $data['author'] ?? null,
            'comment_content' => $data['content'] ?? null,
            'comment_author_email' => $data['email'] ?? null,
        ]);

        if ($this->isKeyValid()) {
            $response = $this->httpClient->post($this->getSpamEndpoint(), ['form_params' => $params]);
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
            'comment_author' => $data['author'] ?? null,
            'comment_content' => $data['content'] ?? null,
            'comment_author_email' => $data['email'] ?? null,
        ]);

        if ($this->isKeyValid()) {
            $response = $this->httpClient->post($this->getHamEndpoint(), ['form_params' => $params]);
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

    /**
     * Ensures that we get the right IP address even if behind CloudFlare
     *
     * @return string
     */
    public function getRequestingIp(): string
    {
        return !empty($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : Craft::$app->getRequest()->getUserIP();
    }

    protected function getUserAgent(): string
    {
        $craftInfo = 'Craft ' . Craft::$app->getEditionName() . ' ' . Craft::$app->getVersion();
        $pluginInfo = 'Shield ' . Shield::$plugin->getVersion();

        return Craft::$app->getRequest()->getUserAgent() ?? ($craftInfo . ' | ' . $pluginInfo);
    }

    protected function getKeyEndpoint(): string
    {
        return sprintf('http://%s/verify-key', self::ENDPOINT);
    }

    protected function getContentEndpoint(): string
    {
        return sprintf('http://%s.%s/comment-check', $this->getApiKey(), self::ENDPOINT);
    }

    protected function getSpamEndpoint(): string
    {
        return sprintf('http://%s.%s/submit-spam', $this->getApiKey(), self::ENDPOINT);
    }

    protected function getHamEndpoint(): string
    {
        return sprintf('http://%s.%s/submit-ham', $this->getApiKey(), self::ENDPOINT);
    }

    /**
     * @param array $fields
     * @param object $object
     *
     * @return array
     * @throws Throwable
     * @throws Throwable
     */
    protected function renderObjectFields(array $fields, $object): array
    {
        try {
            foreach ($fields as $field => $value) {
                $fields[$field] = Craft::$app->getView()->renderObjectTemplate($value, $object);
            }
        } catch (Exception $e) {
            Shield::error($e);

            return [];
        }

        return $fields;
    }
}
