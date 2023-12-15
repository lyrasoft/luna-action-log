<?php

declare(strict_types=1);

namespace Lyrasoft\ActionLog\Event;

use Lyrasoft\ActionLog\Entity\ActionLog;
use Windwalker\Event\AbstractEvent;

class FormatTaskEvent extends AbstractEvent
{
    protected ActionLog $log;

    protected string $taskText = '';

    public function getLog(): ActionLog
    {
        return $this->log;
    }

    public function setLog(ActionLog $log): static
    {
        $this->log = $log;

        return $this;
    }

    public function &getTaskText(): string
    {
        return $this->taskText;
    }

    public function setTaskText(string $taskText): static
    {
        $this->taskText = $taskText;

        return $this;
    }
}
