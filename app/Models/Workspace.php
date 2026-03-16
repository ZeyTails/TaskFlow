<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Workspace extends Model
{
    use HasFactory;

    public const THEMES = [
        'briefcase' => [
            'label' => 'Organisation',
            'badge' => 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-900/60 dark:bg-sky-950/30 dark:text-sky-300',
            'icon' => 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-900/60 dark:bg-sky-950/30 dark:text-sky-300',
        ],
        'users' => [
            'label' => 'Equipe',
            'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300',
            'icon' => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300',
        ],
        'folder' => [
            'label' => 'Dossiers',
            'badge' => 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300',
            'icon' => 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300',
        ],
        'rocket' => [
            'label' => 'Lancement',
            'badge' => 'border-fuchsia-200 bg-fuchsia-50 text-fuchsia-700 dark:border-fuchsia-900/60 dark:bg-fuchsia-950/30 dark:text-fuchsia-300',
            'icon' => 'border-fuchsia-200 bg-fuchsia-50 text-fuchsia-700 dark:border-fuchsia-900/60 dark:bg-fuchsia-950/30 dark:text-fuchsia-300',
        ],
        'bolt' => [
            'label' => 'Prioritaire',
            'badge' => 'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-900/60 dark:bg-violet-950/30 dark:text-violet-300',
            'icon' => 'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-900/60 dark:bg-violet-950/30 dark:text-violet-300',
        ],
        'target' => [
            'label' => 'Objectif',
            'badge' => 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300',
            'icon' => 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300',
        ],
        'calendar' => [
            'label' => 'Planning',
            'badge' => 'border-cyan-200 bg-cyan-50 text-cyan-700 dark:border-cyan-900/60 dark:bg-cyan-950/30 dark:text-cyan-300',
            'icon' => 'border-cyan-200 bg-cyan-50 text-cyan-700 dark:border-cyan-900/60 dark:bg-cyan-950/30 dark:text-cyan-300',
        ],
        'layers' => [
            'label' => 'Structure',
            'badge' => 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-800 dark:bg-slate-950/30 dark:text-slate-300',
            'icon' => 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-800 dark:bg-slate-950/30 dark:text-slate-300',
        ],
    ];

    public const ROLE_OWNER = 'owner';
    public const ROLE_MEMBER = 'member';
    public const ROLE_VIEWER = 'viewer';

    public const ROLES = [
        self::ROLE_OWNER,
        self::ROLE_MEMBER,
        self::ROLE_VIEWER,
    ];

    public const MEMBER_STATUS_ACTIVE = 'active';
    public const MEMBER_STATUS_SUSPENDED = 'suspended';

    public const MEMBER_STATUSES = [
        self::MEMBER_STATUS_ACTIVE,
        self::MEMBER_STATUS_SUSPENDED,
    ];

    public const ICON_KEYS = [
        'briefcase',
        'users',
        'folder',
        'rocket',
        'bolt',
        'target',
        'calendar',
        'layers',
    ];

    public const JOIN_CODE_PATTERN = '/^[A-Z0-9]{3}-[A-Z0-9]{3}-[A-Z0-9]{3}$/';

    protected $fillable = [
        'name',
        'icon_key',
        'join_code',
        'owner_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (Workspace $workspace): void {
            $workspace->join_code = filled($workspace->join_code)
                ? static::normalizeJoinCode($workspace->join_code)
                : static::generateUniqueJoinCode();
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_user')
            ->withPivot('role', 'job_title', 'status', 'suspended_at')
            ->withTimestamps();
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(WorkspaceInvitation::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public static function themeFor(?string $iconKey): array
    {
        $iconKey = $iconKey ?: 'briefcase';

        return self::THEMES[$iconKey] ?? self::THEMES['briefcase'];
    }

    public function theme(): array
    {
        return self::themeFor($this->icon_key);
    }

    public static function normalizeJoinCode(string $joinCode): string
    {
        $characters = preg_replace('/[^A-Z0-9]/', '', Str::upper($joinCode)) ?? '';

        return implode('-', str_split(substr($characters, 0, 9), 3));
    }

    public static function generateUniqueJoinCode(): string
    {
        do {
            $code = static::formatJoinCode(static::randomJoinCodeCharacters());
        } while (static::query()->where('join_code', $code)->exists());

        return $code;
    }

    private static function randomJoinCodeCharacters(): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $characters = '';

        for ($index = 0; $index < 9; $index++) {
            $characters .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $characters;
    }

    private static function formatJoinCode(string $characters): string
    {
        return implode('-', str_split(substr(Str::upper($characters), 0, 9), 3));
    }
}
