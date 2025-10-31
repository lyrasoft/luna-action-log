# LYRASOFT Action Log Package

![Action Log](https://github.com/lyrasoft/luna-action-log/assets/1639206/8b24f4f0-ba2f-4010-877c-161b3ad66275)

## Installation

Install from composer

```shell
composer require lyrasoft/action-log
```

Then copy files to project

```shell
php windwalker pkg:install lyrasoft/action-log -t routes -t migrations
```

And run migrtions.

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
        options: [
            'methods' => ['POST', 'DELETE'],
            'enabled' => (bool) env('ACTION_LOG_ENABLE'),
            'max_time' => '7days',
            // ...
        ]
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

## Custom Task (Action) and Entity Render

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
        $text = &$event->getTaskText();
        $log = $event->getLog();
        $task = $log->task;

        // Custom you task text
        
        // Same examples
        if ($task === 'relativeContracts') {
            $text = '相關合約操作';
        }

        if ($task === 'relativeRentals') {
            $text = '相關委託操作';
        }

        if ($task === 'addToCart') {
            $text = '加入購物車';
        }
    }

    #[ListenTo(FormatEntityEvent::class)]
    public function formatEntity(FormatEntityEvent $event): void
    {
        $text = &$event->getEntityText();
        $log = $event->getLog();

        // Custom you entity text
    }
}
```

Register this subscriber to `etc/app/main.php`:

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
