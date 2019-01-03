<?php
namespace selvinortiz\shield\services;

use GuzzleHttp\Client;

use yii\base\UserException;

use Craft;
use craft\base\Model;
use craft\base\Component;

use selvinortiz\shield\enums\CommentType;

use function selvinortiz\shield\shield;

class ShieldService extends Component
{
    const ENDPOINT = 'rest.akismet.com/1.1';

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

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->params = [
            'blog'         => $this->getOriginUrl(),
            'user_ip'      => $this->getRequestingIp(),
            'user_agent'   => $this->getUserAgent(),
            'comment_type' => CommentType::ContactForm,
        ];

        $this->httpClient = Craft::createGuzzleClient();
    }

    /**
     * @param Client $httpClient
     */
    public function setHttpClient(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return shield()->getSettings()->akismetApiKey;
    }

    /**
     * @return string
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function getOriginUrl()
    {
        $originUrl = shield()->getSettings()->akismetOriginUrl;
        $originUrl = trim($originUrl);

        if (empty($originUrl) || '{siteUrl}' === $originUrl)
        {
            return Craft::$app->request->getUrl();
        }

        return $originUrl;
    }

    /**
     * @return bool
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function isKeyValid()
    {
        $params = [
            'key'  => $this->getApiKey(),
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
     * @throws \yii\base\InvalidConfigException
     */
    public function isSpam(array $data = [])
    {
        $isKeyValid    = true;
        $flaggedAsSpam = false;

        try
        {
            $flaggedAsSpam = $this->detectSpam($data);
        }
        catch (UserException $e)
        {
            $message = array_merge($data, [
                'error'         => $e,
                'isKeyValid'    => $isKeyValid,
                'flaggedAsSpam' => $flaggedAsSpam
            ]);

            shield()->error($message);
        }

        shield()->error($data);
        shield()->logs->create($data, $flaggedAsSpam);

        return $flaggedAsSpam;
    }

    /**
     * @param array $data
     *
     * @return bool
     *
     * @throws UserException
     * @throws \yii\base\InvalidConfigException
     */
    public function detectSpam(array $data = [])
    {
        $params = array_merge($this->params, [
            'comment_type'         => $data['type'] ?? $this->params['comment_type'] ?? null,
            'comment_author'       => $data['author'] ?? null,
            'comment_content'      => $data['content'] ?? null,
            'comment_author_email' => $data['email'] ?? null,
        ]);

        if ($this->isKeyValid())
        {
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
     * @throws \yii\base\InvalidConfigException
     */
    public function submitSpam(array $data = [])
    {
        $params = array_merge($this->params, [
            'comment_author'       => $data['author'] ?? null,
            'comment_content'      => $data['content'] ?? null,
            'comment_author_email' => $data['email'] ?? null,
        ]);

        if ($this->isKeyValid())
        {
            $response = $this->httpClient->post($this->getSpamEndpoint(), ['form_params' => $params]);
            $response = (string)$response->getBody();

            return (bool)('Thanks for making the web a better place.' == $response);
        }

        throw new UserException('Your akismet api key is invalid.');
    }

    /**
     * @param array $data
     *
     * @return bool
     *
     * @throws UserException
     * @throws \yii\base\InvalidConfigException
     */
    public function submitHam(array $data = [])
    {
        $params = array_merge($this->params, [
            'comment_author'       => $data['author'] ?? null,
            'comment_content'      => $data['content'] ?? null,
            'comment_author_email' => $data['email'] ?? null,
        ]);

        if ($this->isKeyValid())
        {
            $response = $this->httpClient->post($this->getHamEndpoint(), ['form_params' => $params]);
            $response = (string)$response->getBody();

            return (bool)('Thanks for making the web a better place.' == $response);
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
     * @throws \yii\base\InvalidConfigException
     */
    public function detectContactFormSpam(Model $submission)
    {
        $data = [
            'type'    => CommentType::ContactForm,
            'email'   => $submission->fromEmail,
            'author'  => $submission->fromName,
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
     * @throws \yii\base\InvalidConfigException
     */
    public function detectDynamicFormSpam(Model $model)
    {
        $data = [
            'type'    => Craft::$app->request->post('shield.typeFieldHandle', CommentType::ContactForm),
            'email'   => Craft::$app->request->post('shield.emailFieldHandle'),
            'author'  => Craft::$app->request->post('shield.authorFieldHandle'),
            'content' => Craft::$app->request->post('shield.contentFieldHandle'),
        ];

        $data = $this->renderObjectFields($data, $model);

        if ($data)
        {
            return $this->isSpam($data);
        }

        return false;
    }

    /**
     * @param Model $comment
     *
     * @return bool
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function detectCommentsSpam(Model $comment)
    {
        $data = [
            'type'    => CommentType::Comment,
            'email'   => $comment->author->email,
            'author'  => $comment->author->fullName,
            'content' => $comment->comment,
        ];

        return $this->isSpam($data);
    }

    /**
     * Ensures that we get the right IP address even if behind CloudFlare
     *
     * @return string
     */
    public function getRequestingIp()
    {
        return !empty($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : Craft::$app->request->getUserIP();
    }

    protected function getUserAgent()
    {
        return Craft::$app->request->getUserAgent() ?? 'Craft 3 | Shield 1';
    }

    protected function getKeyEndpoint()
    {
        return sprintf('http://%s/verify-key', self::ENDPOINT);
    }

    protected function getContentEndpoint()
    {
        return sprintf('http://%s.%s/comment-check', $this->getApiKey(), self::ENDPOINT);
    }

    protected function getSpamEndpoint()
    {
        return sprintf('http://%s.%s/submit-spam', $this->getApiKey(), self::ENDPOINT);
    }

    protected function getHamEndpoint()
    {
        return sprintf('http://%s.%s/submit-ham', $this->getApiKey(), self::ENDPOINT);
    }

    /**
     * @param array  $fields
     * @param object $object
     *
     * @return array
     */
    protected function renderObjectFields(array $fields, $object)
    {
        try
        {
            foreach ($fields as $field => $value)
            {
                $fields[$field] = Craft::$app->view->renderObjectTemplate($value, $object);
            }
        }
        catch (\Exception $e)
        {
            return shield()->error($e);
        }

        return $fields;
    }
}
