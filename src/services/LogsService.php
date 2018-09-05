<?php
namespace selvinortiz\shield\services;

use craft\base\Component;

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

    public function create(array $data)
    {
    }

    public function delete(int $id = null)
    {
    }

    public function one()
    {
        return [];
    }

    public function all()
    {
        return [];
    }
}
