<?php

declare(strict_types=1);

use App\Command\CommissionCalculatorCommand;
use App\Service\Commission\CommissionConfig;
use App\Service\Commission\CommissionFacade;
use App\Service\Commission\CommissionFactory;
use App\Service\Config\ConfigRepository;
use App\Service\Logger\NullLogger;
use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/vendor/autoload.php';

$dotenv = new Dotenv();
if (file_exists(APP_ROOT . '/.env')) {
    $dotenv->load(APP_ROOT . '/.env');
}

$configRepository = new ConfigRepository(APP_ROOT . '/config');

$logger = new NullLogger();

$commissionFacade = new CommissionFacade(
    new CommissionFactory(
        new CommissionConfig($configRepository)
    )
);

$application = new Application();

$application->add(
    new CommissionCalculatorCommand(
        commissionFacade: $commissionFacade,
        logger: $logger,
    )
);

return $application;
