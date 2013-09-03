<?php
define('COMPETITION_MANAGER_CRON_VERSION', '0.1');

require_once __DIR__.'/libraries/autoload.php';

\CompetitionManager\Cron\Setup::getInstance()->run();

$cronTab = \CompetitionManager\Cron\CronTab::getInstance();
$cronTab->addCron(\CompetitionManager\Cron\ServersMaintenance::getInstance(), 60);
$cronTab->addCron(\CompetitionManager\Cron\CompetitionHandling::getInstance(), 30);
$cronTab->addCron(\CompetitionManager\Cron\Payments::getInstance(), 300);
$cronTab->run();
?>