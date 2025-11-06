<?php

declare(strict_types=1);

namespace Lyrasoft\ActionLog\Service;

use Lyrasoft\ActionLog\Entity\ActionLog;
use Lyrasoft\ActionLog\Event\FormatEntityEvent;
use Lyrasoft\ActionLog\Event\FormatTaskEvent;
use Lyrasoft\Luna\Entity\User;
use Lyrasoft\Luna\User\UserService;
use Psr\Http\Message\ResponseInterface;
use Windwalker\Core\Application\Context\AppContextInterface;
use Windwalker\Core\Application\Context\AppRequestInterface;
use Windwalker\Core\Event\CoreEventAwareTrait;
use Windwalker\Core\Http\AppRequest;
use Windwalker\Core\Http\BrowserNext;
use Windwalker\Event\EventAwareInterface;
use Windwalker\ORM\ORM;
use Windwalker\Session\Session;
use Windwalker\Utilities\Str;
use Windwalker\Utilities\TypeCast;

use function Windwalker\chronos;

class ActionLogService implements EventAwareInterface
{
    use CoreEventAwareTrait;

    public function __construct(
        protected AppContextInterface $app,
        protected ORM $orm,
        protected UserService $userService,
        protected BrowserNext $browser,
        protected ?Session $session = null,
    ) {
    }

    public function log(AppRequestInterface $appRequest, ?ResponseInterface $response = null): ActionLog
    {
        $log = $this->createLogItem($appRequest, $response);

        return $this->saveLog($log);
    }

    public function saveLog(ActionLog $log): ActionLog
    {
        return $this->orm->createOne($log);
    }

    /**
     * @param  AppRequestInterface  $appRequest
     * @param  ResponseInterface|null  $response
     *
     * @return  ActionLog
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function createLogItem(AppRequestInterface $appRequest, ?ResponseInterface $response): ActionLog
    {
        /** @var User $user */
        $user = $this->userService->getUser();
        $input = $appRequest->input();

        $route = $appRequest->getMatchedRoute();
        $request = $appRequest->getServerRequest();
        $stage = $route->getNamespace();

        $log = new ActionLog();
        $log->userId = (string) ($user->id ?: '');
        $log->email = $user->email;
        $log->name = $user->name;
        $log->stage = $stage;
        $log->ip = $appRequest->getClientIP();
        $log->controller = $this->getController();
        $log->route = $route->getName();

        if ($appRequest instanceof AppRequest) {
            $log->method = $appRequest->getOverrideMethod();
        }

        if (method_exists($user, 'getUsername')) {
            $log->username = $user->username;
        }

        if ($this->session->isStarted()) {
            $log->sessionId = (string)$this->session->getId();
        }

        if ($response) {
            $log->status = $response->getStatusCode();
        }

        $log->device = sprintf(
            '%s (%s)',
            $this->browser->deviceString(),
            $this->browser->osString()
        );
        $log->url = $appRequest->getSystemUri()->full();
        $log->task = TypeCast::forceString($appRequest->input('task'));
        $log->ids = json_encode(
            $input['id'] ?? $input['ids'] ?? $input['item']['id'] ?? null
        );
        $log->ua = $appRequest->getHeader('user-agent');
        $log->referrer = $request->getServerParams()['HTTP_REFERER'] ?? '';
        $log->body = $appRequest->input();
        $log->time = 'now';

        return $log;
    }

    public function clearExpiredIfTriggered(
        ?string $reserveTime = null,
        ?int $chance = null,
        ?int $changeBase = null
    ): void {
        $chance ??= $this->app->config('action_log.auto_clear.chance') ?? 1;
        $changeBase ??= $this->app->config('action_log.auto_clear.chance_base') ?? 100;

        if (random_int(1, (int) $changeBase) > (int) $chance) {
            return;
        }

        $this->clearExpired($reserveTime ?? $this->app->config('action_log.reserve_max_time') ?? '3months');
    }

    public function clearExpired(string $reserveTime = '3months'): void
    {
        $reserveTime = Str::ensureLeft(trim($reserveTime), '-');

        $maxTime = chronos($reserveTime);

        $this->orm->delete(ActionLog::class)
            ->where('time', '<', $maxTime)
            ->execute();
    }

    public function formatTask(ActionLog $log): string
    {
        $taskText = $log->task ?: $log->getControllerTask();

        $event = $this->emit(
            new FormatTaskEvent(
                log: $log,
                taskText: $taskText
            )
        );

        return $event->taskText;
    }

    public function formatEntity(ActionLog $log): string
    {
        $entityText = Str::removeRight($log->getControllerShortClass(), 'Controller');

        $event = $this->emit(
            new FormatEntityEvent(
                log: $log,
                entityText: $entityText
            )
        );

        return $event->entityText;
    }

    protected function getController(): string
    {
        $controller = $this->app->getController();

        if (is_string($controller)) {
            return $controller;
        }

        if (is_array($controller)) {
            $class = TypeCast::forceString($controller[0]);
            $action = TypeCast::forceString($controller[1] ?? '');

            if ($action) {
                $class .= '::' . $action;
            }

            return $class;
        }

        if ($controller instanceof \Closure) {
            $ref = new \ReflectionFunction($controller);

            return sprintf(
                'Closure: @(%s)',
                $ref->getClosureScopeClass()?->getName() ?? ''
            );
        }

        return TypeCast::forceString($controller);
    }
}
