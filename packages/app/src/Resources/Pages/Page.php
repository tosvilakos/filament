<?php

namespace Filament\Resources\Pages;

use Filament\Context;
use Filament\Pages\Page as BasePage;
use Filament\Resources\Resource;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;

abstract class Page extends BasePage
{
    protected static ?string $breadcrumb = null;

    protected static string $resource;

    public static string $formActionsAlignment = 'left';

    public static bool $hasInlineFormLabels = false;

    public static function route(string $path): PageRegistration
    {
        return new PageRegistration(
            page: static::class,
            route: fn (Context $context): Route => RouteFacade::get($path, static::class)
                ->middleware(static::getRouteMiddleware($context)),
        );
    }

    public static function getTenantSubscribedMiddleware(Context $context): string
    {
        return static::getResource()::getTenantSubscribedMiddleware($context);
    }

    public static function isTenantSubscriptionRequired(Context $context): bool
    {
        return static::getResource()::isTenantSubscriptionRequired($context);
    }

    public function getBreadcrumb(): ?string
    {
        return static::$breadcrumb ?? static::getTitle();
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $breadcrumb = $this->getBreadcrumb();

        return array_merge(
            [$resource::getUrl() => $resource::getBreadcrumb()],
            (filled($breadcrumb) ? [$breadcrumb] : []),
        );
    }

    public static function authorizeResourceAccess(): void
    {
        abort_unless(static::getResource()::canViewAny(), 403);
    }

    public function getModel(): string
    {
        return static::getResource()::getModel();
    }

    /**
     * @return class-string<resource>
     */
    public static function getResource(): string
    {
        return static::$resource;
    }

    protected function callHook(string $hook): void
    {
        if (! method_exists($this, $hook)) {
            return;
        }

        $this->{$hook}();
    }

    public static function alignFormActionsLeft(): void
    {
        static::$formActionsAlignment = 'left';
    }

    public static function alignFormActionsCenter(): void
    {
        static::$formActionsAlignment = 'center';
    }

    public static function alignFormActionsRight(): void
    {
        static::$formActionsAlignment = 'right';
    }

    public function getFormActionsAlignment(): string
    {
        return static::$formActionsAlignment;
    }

    public function hasInlineFormLabels(): bool
    {
        return static::$hasInlineFormLabels;
    }
}