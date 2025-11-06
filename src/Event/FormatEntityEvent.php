<?php

declare(strict_types=1);

namespace Lyrasoft\ActionLog\Event;

use Lyrasoft\ActionLog\Entity\ActionLog;
use Windwalker\Event\AbstractEvent;
use Windwalker\Event\BaseEvent;

class FormatEntityEvent extends BaseEvent
{
    public function __construct(public ActionLog $log, public string $entityText = '')
    {
    }

    /**
     * @deprecated  Use public property instead
     */
    public function &getEntityText(): string
    {
        return $this->entityText;
    }
}
