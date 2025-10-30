<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceShowRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'clock_in' => ['required', 'date_format:H:i', 'before:clock_out'],
            'clock_out' => ['required', 'date_format:H:i', 'after:clock_in'],
            'remarks' => ['required', 'string', 'max:255'],
        ];

        $rules['break_start'] = ['nullable', 'date_format:H:i', 'after_or_equal:clock_in', 'before_or_equal:clock_out'];
        $rules['break_end']   = ['nullable', 'date_format:H:i', 'after_or_equal:break_start', 'before_or_equal:clock_out'];

        foreach ($this->all() as $key => $value) {
            if (preg_match('/^break_start_(\d+)$/', $key, $matches)) {
                $index = $matches[1];
                $rules["break_start_{$index}"] = ['nullable', 'date_format:H:i', 'after_or_equal:clock_in', 'before_or_equal:clock_out'];
                $rules["break_end_{$index}"]   = ['nullable', 'date_format:H:i', "after_or_equal:break_start_{$index}", 'before_or_equal:clock_out'];
            }
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'clock_in.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            '*.after_or_equal' => '休憩時間が不適切な値です',
            '*.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
            'remarks.required' => '備考を記入してください',
        ];
    }
}
