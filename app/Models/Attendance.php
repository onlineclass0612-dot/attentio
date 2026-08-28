<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'branch_id',
        'shift_id',
        'date',
        'clock_in',
        'in_latitude',
        'in_longitude',
        'in_photo',
        'in_distance_meters',
        'clock_out',
        'out_latitude',
        'out_longitude',
        'out_photo',
        'out_distance_meters',
        'status',
        'late_minutes',
        'early_leave_minutes',
        'work_duration_minutes',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'in_latitude' => 'float',
        'in_longitude' => 'float',
        'in_distance_meters' => 'float',
        'out_latitude' => 'float',
        'out_longitude' => 'float',
        'out_distance_meters' => 'float',
        'late_minutes' => 'integer',
        'early_leave_minutes' => 'integer',
        'work_duration_minutes' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function correction(): HasOne
    {
        return $this->hasOne(AttendanceCorrection::class);
    }

    public function getInPhotoUrlAttribute(): ?string
    {
        return $this->in_photo ? asset('storage/' . $this->in_photo) : null;
    }

    public function getOutPhotoUrlAttribute(): ?string
    {
        return $this->out_photo ? asset('storage/' . $this->out_photo) : null;
    }
}
