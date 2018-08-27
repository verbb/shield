<?php
namespace selvinortiz\shield\services;

use GuzzleHttp\Client;

use yii\base\UserException;

use Craft;
use craft\base\Model;
use craft\base\Component;

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
     * @var Client
     */

    /**
     *
     * @var Client
     */
    protected $httpClient;

    /**
     * Initializes this component, Guzzle client, and plugin settings
     */
    public function init()
    {
        parent::init();

        $this->params = [
            'blog'  => $this->getOriginUrl(),
            'user_ip' => $this->getRequestingIp(),
            'user_agent' => $this->getUserAgent(),
            'comment_type' => 'Entry'
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
     * Ensures that a valid origin URL is set and returns it
     *
     * @return string
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
     * Checks whether the API key is valid
     *
     * @return bool
     */
    public function isKeyValid()
    {
        $params = array(
            'key'  => $this->getApiKey(),
            'blog' => $this->getOriginUrl(),
        );

        $response = $this->httpClient->post($this->getKeyEndpoint(), ['form_params' => $params]);
        $response = (string) $response->getBody();

        return $response == 'valid';
    }

    /**
     * Validates potential spam against the Akismet API
     *
     * @param array $data
     *
     * @return bool
     */
    public function detectSpam(array $data=array())
    {
        $params = array_merge($this->params, [
            'comment_author' => $data['author'] ?? null,
            'comment_content' => $data['content'] ?? null,
            'comment_author_email' => $data['email'] ?? null,
        ]);

        if ($this->isKeyValid())
        {
            $response = $this->httpClient->post($this->getContentEndpoint(), ['form_params' => $params]);
            $response = (string) $response->getBody();

            return 'true' == $response;
        }

        throw new UserException('Your akismet api key is invalid.');
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function submitSpam(array $data=array())
    {
        $params = array_merge($this->params, [
            'comment_author' => isset($data['author']) ? $data['author'] : null,
            'comment_content' => isset($data['content']) ? $data['content'] : null,
            'comment_author_email' => isset($data['email']) ? $data['email'] : null,
        ]);

        if ($this->isKeyValid())
        {
            $request = $this->httpClient->post($this->getSpamEndpoint(), ['form_params' => $params]);
            $response = (string) $request->send()->getBody();

            return (bool) ('Thanks for making the web a better place.' == $response);
        }

        throw new UserException('Your akismet api key is invalid.');
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function submitHam(array $data=array())
    {
        $params = array_merge($this->params, [
            'comment_author' => isset($data['author']) ? $data['author'] : null,
            'comment_content' => isset($data['content']) ? $data['content'] : null,
            'comment_author_email' => isset($data['email']) ? $data['email'] : null,
        ]);

        if ($this->isKeyValid())
        {
            $request = $this->httpClient->post($this->getHamEndpoint(),['form_params' => $params]);
            $response = (string) $request->send()->getBody();

            return (bool) ('Thanks for making the web a better place.' == $response);
        }

        throw new UserException('Your akismet api key is invalid.');
    }

    /**
     * Checks whether the content is considered spam as far as akismet is concerned
     *
     * @param array $data The array containing the key/value pairs to validate
     *
     * @example
     * $data = array(
     *  'email' => 'john@smith.com',
     *  'author' => 'John Smith',
     *  'content' => 'We are Smith & Co, one of the best companies in the world.'
     * )
     *
     * @note $data[content] is required
     *
     * @return bool
     */
    public function isSpam(array $data = [])
    {
        $isKeyValid = true;
        $flaggedAsSpam = false;

        try
        {
            $flaggedAsSpam = $this->detectSpam($data);
        }
        catch(UserException $e)
        {
            throw $e;
            // if (craft()->userSession->isAdmin())
            // {
            //     craft()->userSession->setError($e->getMessage());
            //     craft()->request->redirect(sprintf('/%s/settings/plugins/shield/', craft()->config->get('cpTrigger')));
            // }
            // else
            // {
            //     $isKeyValid = false;

            //     Craft::log($e->getMessage(), LogLevel::Warning);
            // }
        }

        $params = array_merge($data, [
            'isKeyValid' => $isKeyValid,
            'flaggedAsSpam' => $flaggedAsSpam
        ]);

        // $this->addLog($params);

        return $flaggedAsSpam;
    }

    /**
     * Allows you to use Shield alongside the Contact Form plugin by P&T
     *
     * @since 1.0.0
     * @param Model $submission
     *
     * @return boolean
     */
    public function detectContactFormSpam(Model $submission)
    {
        $data = [
            'email'   => $submission->fromEmail,
            'author'  => $submission->fromName,
            'content' => $submission->message,
        ];

        return $this->isSpam($data);
    }

    /**
     * Implements support for the Guest Entries
     *
     * @since 0.6.0
     *
     * @param BaseModel $model
     * @return bool
     */
    public function detectDynamicFormSpam(BaseModel $model)
    {
        $data = [
            'email' => Craft::$app->request->getPost('shield.emailField'),
            'author' => Craft::$app->request->getPost('shield.authorField'),
            'content' => Craft::$app->request->getPost('shield.contentField'),
        ];

        $data = $this->renderObjectFields($data, $model);

        if (false == $data)
        {
            // @todo: Add log for this?
            return false;
        }

        return $this->isSpam($data);
    }

    /**
     * Comments onBeforeSaveComment()
     *
     * Allows you to use Shield alongside the Comments plugin
     *
     * @since 0.6.0
     * @param array $form
     * @return boolean
     */
    public function detectCommentsSpam(BaseModel $comment)
    {
        $data = array(
            'email' => $comment->author->email,
            'author' => $comment->author->fullName,
            'content' => $comment->comment,
        );

        return $this->isSpam($data);
    }

    /**
     * Deletes a log by id
     *
     * @param $id
     *
     * @return bool
     * @throws \CDbException
     */
    public function deleteLog($id)
    {
        $log = SpamGuardRecord::model()->findById($id);

        if ($log)
        {
            $log->delete();

            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function deleteLogs()
    {
        return SpamGuardRecord::model()->deleteAll();
    }

    /**
     * Returns an array of logs if any are found
     *
     * @param array $attributes
     *
     * @return array
     */
    public function getLogs(array $attributes=array())
    {
        $models = array();
        $records = SpamGuardRecord::model()->findAllByAttributes($attributes);

        if ($records)
        {
            foreach ($records as $record)
            {
                $models[] = SpamGuardModel::populateModel($record->getAttributes());
            }
        }

        return $models;
    }

    /**
     * Creates a new submission log if logging is enabled
     *
     * @param $data
     *
     * @return bool
     */
    public function addLog($data)
    {
        if ($this->pluginSettings->getAttribute('logSubmissions'))
        {
            $record = new SpamGuardRecord;

            $record->setAttributes($data, false);

            if ($record->validate())
            {
                $record->save();
            }
        }

        return false;
    }

    /**
     * Returns an array of variables to be used by the index or settings templates
     *
     * @param bool $includeLogs
     * @return array
     */
    public function getTemplateVariables($includeLogs=false)
    {
        $plugin = craft()->plugins->getPlugin('Shield');
        $settings = $this->pluginSettings->getAttributes();
        $variables = array();

        $variables['name']  = $plugin->getName(true);
        $variables['alias']  = $plugin->getName();
        $variables['version'] = $plugin->getVersion();
        $variables['developer'] = $plugin->getDeveloper();
        $variables['developerUrl'] = $plugin->getDeveloperUrl();
        $variables['settings'] = $settings;

        if ($includeLogs)
        {
            $variables['logs'] = $this->getLogs();
        }

        return $variables;
    }

    /**
     * Parses fields with twig support used in mappings
     *
     * @param array $fields
     * @param BaseModel $object
     *
     * @return array|false
     */
    protected function renderObjectFields(array $fields, BaseModel $object)
    {
        try
        {
            foreach ($fields as $field => $value)
            {
                $fields[$field] = craft()->templates->renderObjectTemplate($value, $object);
            }
        }
        catch (\Exception $e)
        {
            SpamGuardPlugin::log($e->getMessage(), LogLevel::Error);

            return false;
        }

        return $fields;
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
}
