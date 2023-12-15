<?php

declare(strict_types=1);

namespace Lyrasoft\ActionLog\Entity;

use Windwalker\Core\DateTime\Chronos;
use Windwalker\Core\DateTime\ServerTimeCast;
use Windwalker\ORM\Attributes\AutoIncrement;
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\CastNullable;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\EntitySetup;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Cast\JsonCast;
use Windwalker\ORM\EntityInterface;
use Windwalker\ORM\EntityTrait;
use Windwalker\ORM\Metadata\EntityMetadata;

#[Table('action_logs', 'action_log')]
#[\AllowDynamicProperties]
class ActionLog implements EntityInterface
{
    use EntityTrait;

    #[Column('id'), PK, AutoIncrement]
    protected ?int $id = null;

    #[Column('session_id')]
    protected string $sessionId = '';

    #[Column('user_id')]
    protected string $userId = '';

    #[Column('email')]
    protected string $email = '';

    #[Column('username')]
    protected string $username = '';

    #[Column('name')]
    protected string $name = '';

    #[Column('stage')]
    protected string $stage = '';

    #[Column('device')]
    protected string $device = '';

    #[Column('ip')]
    protected string $ip = '';

    #[Column('ua')]
    protected string $ua = '';

    #[Column('referrer')]
    protected string $referrer = '';

    #[Column('route')]
    protected string $route = '';

    #[Column('url')]
    protected string $url = '';

    #[Column('controller')]
    protected string $controller = '';

    #[Column('status')]
    protected int $status = 0;

    #[Column('method')]
    protected string $method = '';

    #[Column('task')]
    protected string $task = '';

    #[Column('ids')]
    protected string $ids = '';

    #[Column('body')]
    #[Cast(JsonCast::class)]
    protected mixed $body = '';

    #[Column('time')]
    #[CastNullable(ServerTimeCast::class)]
    protected ?Chronos $time = null;

    #[EntitySetup]
    public static function setup(EntityMetadata $metadata): void
    {
        //
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): static
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string|int $userId): static
    {
        $this->userId = (string) $userId;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStage(): string
    {
        return $this->stage;
    }

    public function setStage(string $stage): static
    {
        $this->stage = $stage;

        return $this;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): static
    {
        $this->ip = $ip;

        return $this;
    }

    public function getUa(): string
    {
        return $this->ua;
    }

    public function setUa(string $ua): static
    {
        $this->ua = $ua;

        return $this;
    }

    public function getReferrer(): string
    {
        return $this->referrer;
    }

    public function setReferrer(string $referrer): static
    {
        $this->referrer = $referrer;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function getTask(): string
    {
        return $this->task;
    }

    public function setTask(string $task): static
    {
        $this->task = $task;

        return $this;
    }

    public function getIds(): string
    {
        return $this->ids;
    }

    public function setIds(string $ids): static
    {
        $this->ids = $ids;

        return $this;
    }

    public function getBody(): mixed
    {
        return $this->body;
    }

    public function setBody(mixed $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getTime(): ?Chronos
    {
        return $this->time;
    }

    public function setTime(\DateTimeInterface|string|null $time): static
    {
        $this->time = Chronos::wrapOrNull($time);

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function setController(string $controller): static
    {
        $this->controller = $controller;

        return $this;
    }

    public function getControllerClass(): string
    {
        [$controller] = $this->getControllerCallable();

        return $controller;
    }

    public function getControllerShortClass(): string
    {
        [$controller] = $this->getControllerCallable();

        $segments = explode('\\', $controller);

        return array_pop($segments);
    }

    public function getControllerTask(): string
    {
        [, $task] = $this->getControllerCallable() + ['', ''];

        return $task;
    }

    public function getControllerCallable(): array
    {
        $controller = $this->getController();

        return explode('::', $controller);
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function setRoute(string $route): static
    {
        $this->route = $route;

        return $this;
    }

    public function getDevice(): string
    {
        return $this->device;
    }

    public function setDevice(string $device): static
    {
        $this->device = $device;

        return $this;
    }
}
