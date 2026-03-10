<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    use HasFactory;

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

    protected $fillable = [
        'name',
        'icon_key',
        'owner_id',
    ];

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
}
