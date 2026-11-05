<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

class AuditLog extends Model
{
    /** Columns that must never reach the log. */
    private const SECRET = ['password', 'remember_token'];

    protected $fillable = [
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Write one row for a model event. Updated rows carry the old and new
     * values of the fields that actually changed.
     */
    public static function record(string $event, Model $model): void
    {
        $changes = match ($event) {
            'updated' => [
                'old' => Arr::only($model->getOriginal(), array_keys($model->getChanges())),
                'new' => $model->getChanges(),
            ],
            default => ['new' => $model->getAttributes()],
        };

        $changes = self::redact($changes);

        if ($event === 'updated' && $changes['new'] === []) {
            return;
        }

        self::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'changes' => $changes,
            'ip_address' => self::ip(),
            'user_agent' => self::userAgent(),
        ]);
    }

    /**
     * Authentication events do not have a model change around them.
     */
    public static function recordAuth(string $event, ?User $user, array $changes = []): void
    {
        self::create([
            'user_id' => $user?->id,
            'event' => $event,
            'auditable_type' => $user ? $user::class : null,
            'auditable_id' => $user?->id,
            'changes' => $changes === [] ? null : $changes,
            'ip_address' => self::ip(),
            'user_agent' => self::userAgent(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<string, mixed>
     */
    private static function redact(array $changes): array
    {
        foreach (['new', 'old'] as $side) {
            if (! isset($changes[$side]) || ! is_array($changes[$side])) {
                continue;
            }

            $changes[$side] = Arr::except($changes[$side], [
                ...self::SECRET,
                'created_at',
                'updated_at',
            ]);
        }

        return $changes;
    }

    private static function ip(): ?string
    {
        return app()->runningInConsole() ? null : request()->ip();
    }

    private static function userAgent(): ?string
    {
        return app()->runningInConsole() ? null : mb_substr((string) request()->userAgent(), 0, 1000);
    }
}
