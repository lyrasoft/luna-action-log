<?php

declare(strict_types=1);

namespace Lyrasoft\ActionLog\Event;

use Lyrasoft\ActionLog\Entity\ActionLog;
use Windwalker\Event\AbstractEvent;
use Windwalker\Event\BaseEvent;

class FormatTaskEvent extends BaseEvent
{
    public function __construct(public ActionLog $log, public string $taskText = '')
    {
    }

    /**
     * @deprecated  Use public property instead
     */
    public function &getTaskText(): string
    {
        return $this->taskText;
    }
}
