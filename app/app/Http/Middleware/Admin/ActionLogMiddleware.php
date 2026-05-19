<?php

namespace App\Http\Middleware\Admin;

use App\Contracts\ActionLogInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActionLogMiddleware
{
    private const IGNORED_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), self::IGNORED_METHODS, true)) {
            return $next($request);
        }

        $response = $next($request);

        if ($request->user()) {
            app(ActionLogInterface::class)->store(
                action: $this->resolveAction($request, $response),
                userId: $request->user('admin')->id,
                ip: $request->ip(),
                userAgent: $request->userAgent(),
            );
        }

        return $response;
    }

    /**
     * Определяем тип действия
     */
    private function resolveAction(Request $request, Response $response): string
    {
        $method = $request->method();

        $object = $this->resolveObject($request);

        $itemId = $response->headers->get('X-ITEM-ID') ?? 'unknown';

        $result = match ($method) {
            'POST' => "CREATED:{$object}:ITEM_ID:{$itemId}",
            'PUT', 'PATCH' => "UPDATED:{$object}:ITEM_ID:{$itemId}",
            'DELETE' => "DELETED:{$object}:ITEM_ID:{$itemId}",
            default => "UNKNOWN:{$object}:ITEM_ID:{$itemId}",
        };

        return $result;
    }

    private function resolveObject(Request $request): string
    {
        if (str_contains($request->path(), 'users')) {
            return 'USER';
        }

        if (str_contains($request->path(), 'texts')) {
            return 'TEXT';
        }

        return 'UNKNOWN';
    }
}
