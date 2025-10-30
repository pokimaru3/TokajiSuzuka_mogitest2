<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectionRequest extends Model
{
    use HasFactory;

    protected $table = 'attendance_correction_requests';

    protected $fillable = [
        'attendance_id',
        'user_id',
        'requested_clock_in',
        'requested_clock_out',
        'requested_break_start',
        'requested_break_end',
        'remarks',
        'status',
    ];

    protected $casts = [
            'requested_clock_in' => 'datetime',
            'requested_clock_out' => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function correctionBreaks()
    {
        return $this->hasMany(AttendanceCorrectionBreak::class, 'correction_request_id');
    }
}
