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
use Windwalker\Core\Attributes\Assert;
use Windwalker\Core\Attributes\Controller;
use Windwalker\Core\Attributes\Request\Input;
use Windwalker\Core\DateTime\ChronosService;
use Windwalker\Core\Http\Browser;
use Windwalker\DI\Attributes\Autowire;
use Windwalker\Http\Response\AttachmentResponse;
use Windwalker\ORM\ORM;
use Windwalker\Stream\Stream;

use function Windwalker\now;
use function Windwalker\response;

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

                    $row->setRowCell('id', $item->id);
                    $row->setRowCell('time', $item->time->format('Y/m/d H:i:s'));
                    $row->setRowCell('session_id', $item->sessionId);
                    $row->setRowCell('user_id', $item->userId);
                    $row->setRowCell('username', $user?->username ?? '');
                    $row->setRowCell('name', $item->name);
                    $row->setRowCell('email', $item->email);
                    $row->setRowCell('ip', $item->ip);
                    $row->setRowCell('device', $item->device ?: $getDevice($item->ua));
                    $row->setRowCell('ua', $item->ua);
                    $row->setRowCell('url', $item->url);
                    $row->setRowCell('status', $item->status);
                    $row->setRowCell('method', $item->method);
                    $row->setRowCell('controller', $item->controller);
                    $row->setRowCell('task', $item->task);
                    $row->setRowCell('ids', $item->ids);
                }
            );
        }

        $excel->finish();
    }

    public function download(
        #[Input, Assert('required')] string $id,
        ORM $orm,
        ChronosService $chronosService
    ): AttachmentResponse {
        $log = $orm->mustFindOne(ActionLog::class, $id);
        $time = $chronosService->toLocalFormat($log->time, 'Y-m-d-H-i-s');

        return response()
            ->attachment(
                Stream::fromString(
                    json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                )
            )
            ->withFilename("action-log-[{$log->id}]-{$time}.json")
            ->withContentType('application/json');
    }
}
