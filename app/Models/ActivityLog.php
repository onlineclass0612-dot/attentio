<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'module',
        'action',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a new activity log entry cleanly
     */
    public static function record(string $module, string $action, string $description, ?array $properties = null): self
    {
        $user = Auth::user();
        $roleName = $user ? ($user->roles->first()?->name ?? 'User') : 'System';

        return self::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System Automated',
            'user_role' => $roleName,
            'module' => $module,
            'action' => strtoupper($action),
            'description' => $description,
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
