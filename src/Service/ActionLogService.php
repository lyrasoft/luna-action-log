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
use Windwalker\Core\Http\Browser;
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
        protected Browser $browser,
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
        $log->setUserId((string) ($user->getId() ?: ''));
        $log->setEmail($user->getEmail());
        $log->setName($user->getName());
        $log->setStage($stage);
        $log->setIp($appRequest->getClientIP());
        $log->setController($this->getController());
        $log->setRoute($route->getName());

        if ($appRequest instanceof AppRequest) {
            $log->setMethod($appRequest->getOverrideMethod());
        }

        if (method_exists($user, 'getUsername')) {
            $log->setUsername($user->getUsername());
        }

        if ($this->session->isStarted()) {
            $log->setSessionId((string) $this->session->getId());
        }

        if ($response) {
            $log->setStatus($response->getStatusCode());
        }

        $log->setDevice(
            sprintf(
                '%s (%s)',
                $this->browser->device(),
                $this->browser->platform()
            )
        );
        $log->setUrl($appRequest->getSystemUri()->full());
        $log->setTask(TypeCast::forceString($appRequest->input('task')));
        $log->setIds(
            json_encode(
                $input['id'] ?? $input['ids'] ?? $input['item']['id'] ?? null
            )
        );
        $log->setUa($appRequest->getHeader('user-agent'));
        $log->setReferrer($request->getServerParams()['HTTP_REFERER'] ?? '');
        $log->setBody($appRequest->input());
        $log->setTime('now');

        return $log;
    }

    public function clearExpiredIfTriggered(
        ?string $reserveTime = null,
        ?int $chance = null,
        ?int $changeBase = null
    ): void {
        $chance ??= $this->app->config('action_log.auto_clear.chance') ?? 1;
        $changeBase ??= $this->app->config('action_log.auto_clear.chance_base') ?? 100;

        if (random_int(1, (int) $changeBase) <= (int) $chance) {
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
        $taskText = $log->getTask() ?: $log->getControllerTask();

        $event = $this->emit(
            FormatTaskEvent::class,
            compact(
                'log',
                'taskText'
            )
        );

        return $event->getTaskText();
    }

    public function formatEntity(ActionLog $log): string
    {
        $entityText = Str::removeRight($log->getControllerShortClass(), 'Controller');

        $event = $this->emit(
            FormatEntityEvent::class,
            compact(
                'log',
                'entityText'
            )
        );

        return $event->getEntityText();
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
