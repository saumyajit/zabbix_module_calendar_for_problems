<?php
namespace Modules\problemcal\Actions;

use CController;
use CControllerResponseData;
use API;

/**
 * Controller for Incident Calendar (Problem Event Calendar)
 */
class ProblemCalendar extends CController
{
    public function init(): void
    {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool
    {
        // Accept all request input for now.
        return true;
    }

    protected function checkPermissions(): bool
    {
        // Restrict as needed; open for now.
        return true;
    }

    protected function doAction(): void
    {
        // Fetch incidents (Zabbix problems) with filtering support
        $data = $this->getProblems();
        $response = new CControllerResponseData([
            'incidents' => $data
        ]);
        $this->setResponse($response);
    }

    private function getProblems(): array
    {
        $params = [
            'output' => [
                'eventid', 'name', 'severity', 'clock', 'acknowledged', 'hostid'
            ],
            'selectHosts' => ['hostid', 'name'],
            'selectGroups' => ['groupid', 'name'],
            'recent' => true
        ];

        // Optional: filter by HTTP GET/POST parameters
        if ($this->hasInput('severity')) {
            $params['severity'] = $this->getInput('severity');
        }
        if ($this->hasInput('hostid')) {
            $params['hostids'] = [$this->getInput('hostid')];
        }
        if ($this->hasInput('groupid')) {
            $params['groupids'] = [$this->getInput('groupid')];
        }

        // Call Zabbix API to get problems (incidents)
        $result = API::Problem()->get($params);

        // Map problem events to calendar format
        $events = [];
        foreach ($result as $problem) {
            $date = date('Y-m-d', $problem['clock']);
            $title = $problem['name'] . " (" . $problem['severity'] . ")";
            $events[] = [
                'title' => $title,
                'date'  => $date,
                'severity' => $problem['severity'],
                'acknowledged' => $problem['acknowledged'],
                'host' => !empty($problem['hosts']) ? $problem['hosts'][0]['name'] : '',
                'group' => !empty($problem['groups']) ? $problem['groups'][0]['name'] : ''
            ];
        }
        return $events;
    }
}
