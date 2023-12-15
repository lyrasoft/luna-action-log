<?php

declare(strict_types=1);

namespace Lyrasoft\ActionLog\Module\Admin\ActionLog;

use Lyrasoft\ActionLog\Entity\ActionLog;
use Lyrasoft\ActionLog\Repository\ActionLogRepository;
use Lyrasoft\Luna\Entity\User;
use Lyrasoft\Toolkit\Spreadsheet\SpoutWriter;
use Lyrasoft\Toolkit\Spreadsheet\SpreadsheetKit;
use Unicorn\Controller\CrudController;
use Unicorn\Controller\GridController;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Attributes\Controller;
use Windwalker\Core\Http\Browser;
use Windwalker\DI\Attributes\Autowire;
use Windwalker\ORM\ORM;

use function Windwalker\now;

#[Controller()]
class ActionLogController
{
    public function delete(
        AppContext $app,
        #[Autowire] ActionLogRepository $repository,
        CrudController $controller
    ): mixed {
        return $app->call([$controller, 'delete'], compact('repository'));
    }

    public function filter(
        AppContext $app,
        #[Autowire] ActionLogRepository $repository,
        GridController $controller
    ): mixed {
        return $app->call([$controller, 'filter'], compact('repository'));
    }

    public function export(
        ORM $orm,
        #[Autowire] ActionLogRepository $repository,
        Browser $browser
    ): void {
        $items = $repository->getListSelector()
            ->limit(0)
            ->page(1)
            ->getIterator(ActionLog::class);

        $excel = SpreadsheetKit::createSpoutWriter();

        $excel->download(
            sprintf(
                'Action Log - %s.xlsx',
                now('Y-m-d-H-i-s')
            ),
            'xlsx'
        );

        $excel->addColumn('id', 'ID');
        $excel->addColumn('time', 'Time')->setWidth(20);
        $excel->addColumn('session_id', 'Session ID')->setWidth(30);
        $excel->addColumn('user_id', 'User ID');
        $excel->addColumn('username', 'Username');
        $excel->addColumn('name', 'User')->setWidth(20);
        $excel->addColumn('email', 'Email')->setWidth(30);
        $excel->addColumn('ip', 'IP')->setWidth(20);
        $excel->addColumn('device', 'Device')->setWidth(20);
        $excel->addColumn('ua', 'UserAgent')->setWidth(30);
        $excel->addColumn('url', 'URL')->setWidth(50);
        $excel->addColumn('status', 'Status');
        $excel->addColumn('method', 'Method');
        $excel->addColumn('controller', 'Controller')->setWidth(40);
        $excel->addColumn('task', 'Task')->setWidth(20);
        $excel->addColumn('ids', 'IDs')->setWidth(20);

        $getDevice = static function (string $ua) use ($browser) {
            return sprintf(
                '%s (%s)',
                $browser->device($ua),
                $browser->platform($ua),
            );
        };

        /** @var ActionLog $item */
        foreach ($items as $item) {
            $excel->addRow(
                function (SpoutWriter $row) use ($orm, $item, $getDevice) {
                    /** @var ?User $user */
                    $user = $orm->toEntityOrNull(User::class, $item->user);

                    $row->setRowCell('id', $item->getId());
                    $row->setRowCell('time', $item->getTime()->format('Y/m/d H:i:s'));
                    $row->setRowCell('session_id', $item->getSessionId());
                    $row->setRowCell('user_id', $item->getUserId());
                    $row->setRowCell('username', $user?->username ?? '');
                    $row->setRowCell('name', $item->getName());
                    $row->setRowCell('email', $item->getEmail());
                    $row->setRowCell('ip', $item->getIp());
                    $row->setRowCell('device', $item->getDevice() ?: $getDevice($item->getUa()));
                    $row->setRowCell('ua', $item->getUa());
                    $row->setRowCell('url', $item->getUrl());
                    $row->setRowCell('status', $item->getStatus());
                    $row->setRowCell('method', $item->getMethod());
                    $row->setRowCell('controller', $item->getController());
                    $row->setRowCell('task', $item->getTask());
                    $row->setRowCell('ids', $item->getIds());
                }
            );
        }

        $excel->finish();
    }
}
