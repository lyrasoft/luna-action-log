# LYRASOFT Action Log Package

![Action Log](https://github.com/lyrasoft/luna-action-log/assets/1639206/8b24f4f0-ba2f-4010-877c-161b3ad66275)

<!-- TOC -->
* [LYRASOFT Action Log Package](#lyrasoft-action-log-package)
  * [Installation](#installation)
  * [Register Admin Menu](#register-admin-menu)
  * [Getting Started](#getting-started)
    * [Auto Clear](#auto-clear)
  * [Configure Middleware](#configure-middleware)
    * [Prepare Log Handler](#prepare-log-handler)
  * [Manually Log](#manually-log)
  * [View](#view)
    * [Pagination](#pagination)
    * [Limit Per-Page](#limit-per-page)
    * [Custom Task (Action) and Entity Display](#custom-task-action-and-entity-display)
<!-- TOC -->

## Installation

Install from composer

```shell
composer require lyrasoft/action-log
```

Then copy files to project

```shell
php windwalker pkg:install lyrasoft/action-log -t routes -t migrations
```

And run migrations.

Next, add Middleware to `routes/admin.route.php`

```php
use Lyrasoft\ActionLog\Middleware\ActionLogMiddleware;

// ...

$router->group('admin')
    // ...
    ->middleware(ActionLogMiddleware::class)
    // ...
```

> If you want to log front and admin, you may put middleware to `main.route.php`

Languages

Add this line to admin & front middleware to load language from package automatically:

```php
use Lyrasoft\ActionLog\ActionLogPackage;

// ...

    $this->lang->loadAllFromVendor(ActionLogPackage::class, 'ini');
```

If you want to copy language file to project, run this:

```shell
php windwalker pkg:install lyrasoft/action-log -t lang
```

## Register Admin Menu

Edit `resources/menu/admin/sidemenu.menu.php`

```php
// Action Log
$menu->link('操作記錄')
    ->to($nav->to('action_log_list'))
    ->icon('fal fa-shoe-prints');
```

## Getting Started

If you have setup ActionLog package, now try to save any item or filter any list, you can see a new log shows on
`action_logs` table:

![log](https://github.com/lyrasoft/luna-banner/assets/1639206/1f007d14-82dd-4034-93b3-cb828f2a9fe7)

### Auto Clear

Bt default, ActionLog only reserve last 3 months logs. It has 1/100 chance to trigger clear action.

You can configure the reserve time and clear chance at config file or using env to configure them:

```php
return [
    'action_log' => [
        'reserve_max_time' => env('ACTION_LOG_MAX_TIME') ?: '3months',
        'auth_clear' => [
            'chance' => env('ACTION_LOG_CLEAR_CHANCE', 1),
            'chance_base' => env('ACTION_LOG_CLEAR_CHANCE_BASE', 100)
        ],

        // ...
    ]
];
```

## Configure Middleware

Add options to middleware:

```php
$router->group('admin')
    // ...
    ->middleware(
        ActionLogMiddleware::class,
        methods: ['POST', 'DELETE'],
        enabled: (bool) env('ACTION_LOG_ENABLE'),
        maxTime: '7days',
        // ...
    )
    // ...
```

| Config Name         | Type                | Default                              | Description                                                                      |
|---------------------|---------------------|--------------------------------------|----------------------------------------------------------------------------------|
| `methods`           | array, string, null | `['POST', 'PUT', 'PATCH', 'DELETE']` | Allowed method, use NULL to allow all                                            |
| `enabled`           | bool                | `true`                               | Enabled log, you may add your env to configure this.                             |
| `max_time`          | string, null        | `null`                               | Max reserve time, default using config and env, can be datetime string.          |
| `clear_chance`      | int, null           | `null`                               | Auto clear chance, default using config and env.                                 |
| `clear_chance_base` | int, null           | `null`                               | Auto clear chance base, default using config and env.                            |
| `prepare_log`       | callable, null      | `null`                               | The handler to handle log item, can be callable or closure, can inject services. |

### Prepare Log Handler

Add a custom handler to configure every log, must use `$log` argument to inject log item.

This is an example to override user name.

```php
use Lyrasoft\ActionLog\Entity\ActionLog;
use Lyrasoft\Luna\User\UserService;

$router->group('admin')
    // ...
    ->middleware(
        ActionLogMiddleware::class,
        options: [
            'prepare_log' => function (ActionLog $log, UserService $userService) {
                $user = $userService->getUser();
                
                $log->name = $user->firstName . ' ' . $user->lastName;
            }
        ]
    )
    // ...
```

If you want to ignore some actions, just return FALSE in prepare log handler:

```php
    'prepare_log' => function (ActionLog $log, UserService $userService) {
        // ...
        
        if ($log->getTask() === '...') {
            // This log will not save
            return false;
        }
    }
```

## Manually Log

Just inject `ActionLogService` to do this.

```php
/** @var \Lyrasoft\ActionLog\Service\ActionLogService $actionLogService */
$actionLogService = $app->retrieve(\Lyrasoft\ActionLog\Service\ActionLogService::class);
$appRequest = $app->retrieve(\Windwalker\Core\Http\AppRequest::class);

$actionLogService->clearExpiredIfTriggered();

$actionLogService->log($appRequest, $response ?? null);

// Or just create entity item.
$log = $actionLogService->createLogItem($appRequest, $response ?? null);
```

## View

### Pagination

By default, ActionLog will display total pages:

![Image](https://github.com/user-attachments/assets/94904489-d15a-4b1c-b23c-5cdd4fd16a47)

However, if you have a large number of logs, counting total rows may impact performance. 
You can disable total count by setting `ACTION_LOG_COUNT_PAGES=0`in the `.env` or `.env.base` files.

This will display a simple pagination without total pages:

![Image](https://github.com/user-attachments/assets/cfa234f3-9692-4545-b3bf-c1235a2a0705)

### Limit Per-Page

Use `ACTION_LOG_DISPLAY_LIMIT=xx` in `.env` or `.env.base` to configure the number of items displayed per page.
Default is `100`.

### Custom Task (Action) and Entity Display

By default, the admin list table shows task and entity by english programaticlly name.

You can custom the render name by events. Create a subscriber:


```php
namespace App\Subscriber;

use Lyrasoft\ActionLog\Event\FormatEntityEvent;
use Lyrasoft\ActionLog\Event\FormatTaskEvent;
use Windwalker\Event\Attributes\EventSubscriber;
use Windwalker\Event\Attributes\ListenTo;

#[EventSubscriber]
class ActionLogSubscriber
{
    #[ListenTo(FormatTaskEvent::class)]
    public function formatTask(FormatTaskEvent $event): void
    {
        $log = $event->log;
        $task = $log->task;

        // Custom you task text
        
        // Same examples
        if ($task === 'relativeContracts') {
            $event->taskText = '相關合約操作';
        }

        if ($task === 'relativeRentals') {
            $event->taskText = '相關委託操作';
        }

        if ($task === 'addToCart') {
            $event->taskText = '加入購物車';
        }
    }

    #[ListenTo(FormatEntityEvent::class)]
    public function formatEntity(FormatEntityEvent $event): void
    {
        $log = $event->getLog();

        // Custom you entity text
        $event->entityText = '...';
    }
}
```

Register this subscriber to `etc/app/main.config.php`:

```php
// ...

        'listeners' => [
            \Lyrasoft\ActionLog\Service\ActionLogService::class => [
                \App\Subscriber\ActionLogSubscriber::class
            ]
        ],
```

The text you return from event will show at table list:

![tasks](https://github.com/lyrasoft/luna-action-log/assets/1639206/6c47f458-9432-4336-89bd-61ad3e776bf0)
