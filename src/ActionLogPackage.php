<?php

declare(strict_types=1);

namespace Lyrasoft\ActionLog;

use Lyrasoft\ActionLog\Entity\ActionLog;
use Lyrasoft\ActionLog\Service\ActionLogService;
use Windwalker\Core\Package\AbstractPackage;
use Windwalker\Core\Package\PackageInstaller;
use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;

class ActionLogPackage extends AbstractPackage implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->prepareSharedObject(ActionLogService::class);
    }

    public function install(PackageInstaller $installer): void
    {
        $installer->installConfig(static::path('etc/*.php'), 'config');
        $installer->installLanguages(static::path('resources/languages/**/*.ini'), 'lang');
        $installer->installMigrations(static::path('resources/migrations/**/*'), 'migrations');
        $installer->installRoutes(static::path('routes/**/*.php'), 'routes');

        $installer->installMVCModules(
            ActionLog::class,
            ['Admin'],
            true
        );
    }
}
