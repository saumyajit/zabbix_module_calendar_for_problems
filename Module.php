<?php
namespace Modules\problemcal;

use Zabbix;
use APP;
use CMenuItem;

/**
 * Incident Calendar Module
 */
class Module extends CModule
{
    public function init(): void
    {
        // Add Incident Calendar item to the main menu, under "Reports".
        APP\Component::get('menu.main')
            ->findOrAdd('reports')
            ->getSubmenu()
            ->insertAfter(
                'notifications',
                new CMenuItem('Incident Calendar')
                    ->setAction('incident.calendar')
            );
    }
}
