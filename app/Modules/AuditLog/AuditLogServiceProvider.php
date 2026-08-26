<?php

namespace App\Modules\AuditLog;

use App\Modules\AuditLog\Http\Controllers\AuditLogFeedController;
use App\Modules\AuditLog\Http\Controllers\AuditLogNoteController;
use App\Modules\AuditLog\Http\Controllers\AuditLogPageController;
use App\Platform\ConceptRegistration;
use App\Platform\ConceptRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AuditLogServiceProvider extends ServiceProvider
{
    public const SLUG = 'audit-log';

    public function boot(ConceptRegistry $registry): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'audit-log');

        $registry->register(new ConceptRegistration(
            slug: self::SLUG,
            name: 'Audit Log',
            description: 'Performs CRUD actions on a demo note, writing an actor/action/subject audit log entry for every write and watching it land in a live activity feed.',
            category: 'DevOps / Observability',
            demoRoute: 'audit-log.demo',
        ));

        Route::middleware('web')->group(function () {
            Route::get('/concepts/audit-log/demo', [AuditLogPageController::class, 'show'])
                ->name('audit-log.demo');

            Route::post('/concepts/audit-log/notes', [AuditLogNoteController::class, 'store'])
                ->name('audit-log.notes.store');

            Route::put('/concepts/audit-log/notes/{note}', [AuditLogNoteController::class, 'update'])
                ->name('audit-log.notes.update');

            Route::delete('/concepts/audit-log/notes/{note}', [AuditLogNoteController::class, 'destroy'])
                ->name('audit-log.notes.destroy');

            Route::get('/concepts/audit-log/feed', [AuditLogFeedController::class, 'show'])
                ->name('audit-log.feed');
        });
    }
}
