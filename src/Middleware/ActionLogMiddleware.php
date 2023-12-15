<?php

declare(strict_types=1);

namespace Lyrasoft\ActionLog\Middleware;

use Lyrasoft\ActionLog\Service\ActionLogService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Windwalker\Core\Application\ApplicationInterface;
use Windwalker\Core\Http\AppRequest;
use Windwalker\Utilities\Options\OptionsResolverTrait;

use function Windwalker\collect;

class ActionLogMiddleware implements MiddlewareInterface
{
    use OptionsResolverTrait;

    public function __construct(
        protected AppRequest $appRequest,
        protected ActionLogService $actionLogService,
        protected ApplicationInterface $app,
        array $options = []
    ) {
        $this->resolveOptions($options, $this->configureOptions(...));
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->define('methods')
            ->default(['POST', 'PUT', 'PATCH', 'DELETE'])
            ->allowedTypes('string', 'array', 'null');

        $resolver->define('enabled')
            ->default(true)
            ->allowedTypes('bool');

        $resolver->define('max_time')
            ->default('3months')
            ->allowedTypes('string');

        $resolver->define('clear_chance')
            ->default(1)
            ->allowedTypes('int');

        $resolver->define('clear_chance_base')
            ->default(100)
            ->allowedTypes('int');

        $resolver->define('prepare_log')
            ->default(null)
            ->allowedTypes('callable', 'null');
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $enabled = $this->options['enabled'];

        $response = $handler->handle($request);

        if ($enabled && $this->isAllowedMethod()) {
            $this->actionLogService->clearExpiredIfTriggered(
                $this->options['max_time'],
                $this->options['clear_chance'],
                $this->options['clear_chance_base'],
            );

            $log = $this->actionLogService->createLogItem($this->appRequest, $response);

            $prepare = $this->options['prepare_log'];

            if ($prepare) {
                $log = $this->app->call($prepare, compact('log')) ?? $log;
            }

            $this->actionLogService->saveLog($log);
        }

        return $response;
    }

    public function isAllowedMethod(): bool
    {
        $allows = $this->options['methods'];

        if ($allows === null) {
            return true;
        }

        $method = strtoupper($this->appRequest->getMethod());

        return collect($allows)
            ->map('strtoupper')
            ->contains($method);
    }
}
