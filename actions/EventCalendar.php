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
        $events = $this->getEvents();
        $data = ['problems' => $events];
        $response = new CControllerResponseData($data);
        $this->setResponse($response);
    }

	private function getEvents(): array {
		$eventsResponse = API::Event()->get([
			'output' => ['eventid', 'name', 'severity', 'acknowledged', 'clock', 'ns', 'value'],
			'source' => 0,      // trigger events
			'object' => 0,      // event is from a trigger
			'value' => [0,1],   // resolved (0) or problem (1)
			'selectHosts' => ['hostid', 'host'],
			'selectTags' => 'extend',
			'selectAcknowledges' => 'extend',
			'sortfield' => ['clock'],
			'sortorder' => 'DESC',
		]);

		$events = [];

		if (!is_array($eventsResponse)) {
			return $events;
		}

		foreach ($eventsResponse as $event) {
			$hosts = [];
			$groups = [];

			if (!empty($event['hosts'])) {
				$hostIds = [];
				foreach ($event['hosts'] as $host) {
					$hosts[] = $host['host'];
					$hostIds[] = $host['hostid'];
				}

				// Fetch host groups safely
				$hostsGroups = API::Host()->get([
					'output' => [],
					'hostids' => $hostIds,
					'selectGroups' => ['name']
				]);

				foreach ($hostsGroups as $hostGroup) {
					if (!empty($hostGroup['groups']) && is_array($hostGroup['groups'])) {
						foreach ($hostGroup['groups'] as $group) {
							$groups[] = $group['name'];
						}
					}
				}
			}

			$events[] = [
				'id' => $event['eventid'],
				'name' => $event['name'],
				'clock' => $event['clock'],
				'ns' => $event['ns'],
				'value' => $event['value'],
				'severity' => $event['severity'],
				'acknowledged' => $event['acknowledged'],
				'hosts' => implode(', ', $hosts),
				'groups' => implode(', ', array_unique($groups)),
				'tags' => $event['tags'] ?? []
			];
		}

		return $events;
	}

}
