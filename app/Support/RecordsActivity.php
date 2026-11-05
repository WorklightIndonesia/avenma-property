<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Writes an audit row whenever a model is created, updated or deleted. Apply
 * only to models the client cares about; logging every pivot would bury them.
 */
trait RecordsActivity
{
    public static function bootRecordsActivity(): void
    {
        static::created(fn (Model $model) => AuditLog::record('created', $model));
        static::updated(fn (Model $model) => AuditLog::record('updated', $model));
        static::deleted(fn (Model $model) => AuditLog::record('deleted', $model));
    }
}
