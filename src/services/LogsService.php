<?php
namespace selvinortiz\shield\services;

use craft\base\Component;

use selvinortiz\shield\records\LogRecord;

use function selvinortiz\shield\shield;

/**
 * Class LogsService
 *
 * @package selvinortiz\shield\services
 */
class LogsService extends Component
{
    protected $logSubmissions = false;

    public function init()
    {
        $this->logSubmissions = shield()->getSettings()->logSubmissions;
    }

    public function create(array $data, $flagged = false)
    {
        if ($this->logSubmissions)
        {
            $log          = new LogRecord();
            $log->type    = $data['type'] ?? null;
            $log->email   = $data['email'] ?? null;
            $log->author  = $data['author'] ?? null;
            $log->content = $data['content'] ?? null;
            $log->flagged = (bool)$flagged;

            if (!$log->save())
            {
                $message = [
                    'errors'     => $log->getErrors(),
                    'properties' => $log->getAttributes(),
                    'message'    => \Craft::t('shield', 'Unable to save submission log')
                ];

                shield()->error($message);
            }
        }
    }

    /**
     * @param int|null $id
     *
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(int $id = null)
    {
        if ($id && ($log = LogRecord::find()->where(['id' => $id])->one()))
        {
            if (!$log->delete())
            {
                $message = [
                    'errors'     => $log->getErrors(),
                    'properties' => $log->getAttributes(),
                    'message'    => \Craft::t('shield', 'Unable to delete submission log')
                ];

                shield()->error($message);
            }
        }
        else
        {
            LogRecord::deleteAll();
        }
    }
}
