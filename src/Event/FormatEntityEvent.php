<?php

declare(strict_types=1);

namespace Lyrasoft\ActionLog\Event;

use Lyrasoft\ActionLog\Entity\ActionLog;
use Windwalker\Event\AbstractEvent;

class FormatEntityEvent extends AbstractEvent
{
    protected ActionLog $log;

    protected string $entityText = '';

    public function getLog(): ActionLog
    {
        return $this->log;
    }

    public function setLog(ActionLog $log): static
    {
        $this->log = $log;

        return $this;
    }

    public function &getEntityText(): string
    {
        return $this->entityText;
    }

    public function setEntityText(string $entityText): static
    {
        $this->entityText = $entityText;

        return $this;
    }
}
