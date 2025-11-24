<?php

namespace Modules\problemcal\Actions;

use CController;
use CControllerResponseData;
use API;

class ProblemCalendar extends CController {

    public function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        return true;
    }

    protected function checkPermissions(): bool {
        return true;
    }

    protected function doAction(): void {
        $problemData = $this->getProblems();
        
        $data = ['problems' => $problemData];
        $response = new CControllerResponseData($data);
        $this->setResponse($response);
    }

    private function getProblems(): array {
        $response = API::Problem()->get([
            'output' => ['eventid', 'name', 'severity', 'acknowledged', 'r_eventid', 'clock', 'ns', 'value', 'tags'],
            'selectHosts' => ['hostid', 'name'],
            'selectGroups' => ['groupid', 'name'],
            'selectTags' => 'extend',
            'sortfield' => ['clock'],
            'sortorder' => 'DESC',
            'recent' => true,
        ]);

        $problems = [];

        foreach ($response as $problem) {

            // Collect HOSTS (if present)
            $hosts = [];
            if (!empty($problem['hosts'])) {
                $hosts = array_column($problem['hosts'], 'name');
            }

            // Collect GROUPS (if present)
            $groups = [];
            if (!empty($problem['groups'])) {
                $groups = array_column($problem['groups'], 'name');
            }

            $problems[] = [
                'id' => $problem['eventid'],
                'name' => $problem['name'],
                'clock' => $problem['clock'], // UNIX timestamp
                'ns' => $problem['ns'],       // nanoseconds
                'value' => $problem['value'], // problem state
                'severity' => $problem['severity'],
                'acknowledged' => $problem['acknowledged'],
                'hosts' => implode(', ', $hosts),
                'groups' => implode(', ', $groups),
                'tags' => $problem['tags'] ?? []
            ];
        }

        return $problems;
    }
}

?>
