<?php
namespace verbb\shield\services;

use verbb\shield\Shield;
use verbb\shield\models\Log;
use verbb\shield\records\Log as LogRecord;

use Craft;
use craft\base\Component;
use craft\base\MemoizableArray;
use craft\db\Query;

use Throwable;
use Exception;

class Logs extends Component
{
    // Properties
    // =========================================================================

    private ?MemoizableArray $_logs = null;


    // Public Methods
    // =========================================================================

    public function getAllLogs(): array
    {
        return $this->_logs()->all();
    }

    public function getLogById(int $id): ?Log
    {
        return $this->_logs()->firstWhere('id', $id);
    }

    public function saveLog(Log $log, bool $runValidation = true): bool
    {
        $isNewLog = !$log->id;

        if ($runValidation && !$log->validate()) {
            Shield::info('Log not saved due to validation error.');
            return false;
        }

        $logRecord = $this->_getLogRecordById($log->id);
        $logRecord->id = $log->id;
        $logRecord->type = $log->type;
        $logRecord->email = $log->email;
        $logRecord->author = $log->author;
        $logRecord->content = $log->content;
        $logRecord->flagged = $log->flagged;
        $logRecord->ham = $log->ham;
        $logRecord->spam = $log->spam;
        $logRecord->data = $log->data;

        $logRecord->save(false);

        if (!$log->id) {
            $log->id = $logRecord->id;
        }

        return true;
    }

    public function deleteLogById(int $logId): bool
    {
        $log = $this->getLogById($logId);

        if (!$log) {
            return false;
        }

        return $this->deleteLog($log);
    }

    public function deleteLog(Log $log): bool
    {
        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();

        try {
            $db->createCommand()
                ->delete('{{%shield_logs}}', ['id' => $log->id])
                ->execute();

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    public function deleteAllLogs(): bool
    {
        LogRecord::deleteAll();

        return true;
    }


    // Private Methods
    // =========================================================================

    private function _logs(): MemoizableArray
    {
        if (!isset($this->_logs)) {
            $logs = [];

            foreach ($this->_createLogQuery()->all() as $result) {
                $logs[] = new Log($result);
            }

            $this->_logs = new MemoizableArray($logs);
        }

        return $this->_logs;
    }

    private function _createLogQuery(): Query
    {
        return (new Query())
            ->select([
                'id',
                'type',
                'email',
                'author',
                'content',
                'flagged',
                'ham',
                'spam',
                'data',
                'dateCreated',
                'dateUpdated',
                'uid',
            ])
            ->from(['{{%shield_logs}}']);
    }

    private function _getLogRecordById(int $logId = null): ?LogRecord
    {
        if ($logId !== null) {
            $logRecord = LogRecord::findOne(['id' => $logId]);

            if (!$logRecord) {
                throw new Exception(Craft::t('shield', 'No log exists with the ID “{id}”.', ['id' => $logId]));
            }
        } else {
            $logRecord = new LogRecord();
        }

        return $logRecord;
    }
}
