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
    public ?int $id = null;

    #[Column('session_id')]
    public string $sessionId = '';

    #[Column('user_id')]
    public string $userId = '' {
        set(string|int $value) => $this->userId = (string) $value;
    }

    #[Column('email')]
    public string $email = '';

    #[Column('username')]
    public string $username = '';

    #[Column('name')]
    public string $name = '';

    #[Column('stage')]
    public string $stage = '';

    #[Column('device')]
    public string $device = '';

    #[Column('ip')]
    public string $ip = '';

    #[Column('ua')]
    public string $ua = '';

    #[Column('referrer')]
    public string $referrer = '';

    #[Column('route')]
    public string $route = '';

    #[Column('url')]
    public string $url = '';

    #[Column('controller')]
    public string $controller = '';

    #[Column('status')]
    public int $status = 0;

    #[Column('method')]
    public string $method = '';

    #[Column('task')]
    public string $task = '';

    #[Column('ids')]
    public string $ids = '';

    #[Column('body')]
    #[Cast(JsonCast::class)]
    public mixed $body = '';

    #[Column('time')]
    #[CastNullable(ServerTimeCast::class)]
    public ?Chronos $time = null {
        set(\DateTimeInterface|string|null $value) => $this->time = Chronos::wrapOrNull($value);
    }

    #[EntitySetup]
    public static function setup(EntityMetadata $metadata): void
    {
        //
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
        $controller = $this->controller;

        return explode('::', $controller);
    }
}
