<?php

namespace Packages\QueryLogging\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Packages\QueryLogging\Contracts\ActionLoggerInterface;
use Packages\QueryLogging\Contracts\LastCreatedModelStoreInterface;
use Symfony\Component\HttpFoundation\Response;

class ActionLogMiddleware
{
    public function __construct(
        private ActionLoggerInterface $logger
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldIgnore($request)) {
            return $next($request);
        }

        $response = $next($request);

        if ($request->user()) {

            $this->logger->store(
                action: $this->resolveAction($request, $response),
                userId: $request->user()->id,
                ip: $request->ip(),
                userAgent: $request->userAgent(),
            );
        }

        return $response;
    }

    private function shouldIgnore(Request $request): bool
    {
        return in_array(
            $request->method(),
            config('query-logging.ignored_methods', []),
            true
        );
    }

    private function resolveAction(
        Request $request,
        Response $response
    ): string {

        $method = $request->method();

        $action = $this->resolveActionType($method);

        $object = $this->resolveObject($request);

        $itemId = $this->resolveItemId($request, $response);

        return "{$action}:{$object}:ITEM_ID:{$itemId}";
    }

    private function resolveActionType(string $method): string
    {
        return config(
            "query-logging.actions.{$method}",
            $method
        );
    }

    private function resolveObject(Request $request): string
    {
        $segment = $request->segment(1);

        return config(
            "query-logging.objects.{$segment}",
            strtoupper($segment ?? 'UNKNOWN')
        );
    }

    private function resolveItemId(
        Request $request,
        Response $response
    ): string|int {

        $header = config(
            'query-logging.item_id_header',
            'X-ITEM-ID'
        );

        $itemId = $response
            ->headers
            ->get($header);

        if ($itemId) {
            return $itemId;
        }

        foreach (
            config('query-logging.route_id_parameters', []) as $parameter
        ) {

            $value = $request->route($parameter);

            if ($value) {
                return $value;
            }
        }

        $storedId = app(
            LastCreatedModelStoreInterface::class
        )->get();

        if ($storedId) {
            return $storedId;
        }

        return 'unknown';
    }
}
