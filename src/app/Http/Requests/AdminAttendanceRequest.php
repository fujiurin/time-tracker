<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in' => ['required'],
            'clock_out' => ['required', 'after:clock_in'],
            'note' => ['required'],

            'breaks.*.break_start' => ['nullable'],
            'breaks.*.break_end' => ['nullable', 'after:breaks.*.break_start'],
        ];
    }

    public function messages()
    {
        return [
            'clock_out.after' =>
                '出勤時間もしくは退勤時間が不適切な値です',

            'note.required' =>
                '備考を記入してください',

            'breaks.*.break_end.after' =>
                '休憩時間もしくは退勤時間が不適切な値です',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $clockIn = $this->clock_in;
            $clockOut = $this->clock_out;

            foreach ($this->breaks ?? [] as $index => $break) {

                // 休憩開始が勤務時間外
                if (
                    !empty($break['break_start']) &&
                    (
                        $break['break_start'] < $clockIn ||
                        $break['break_start'] > $clockOut
                    )
                ) {
                    $validator->errors()->add(
                        'breaks.' . $index . '.break_start',
                        '休憩時間が不適切な値です'
                    );
                }

                // 休憩終了が退勤後
                elseif (
                    !empty($break['break_end']) && $break['break_end'] > $clockOut
                ) {
                    $validator->errors()->add(
                        'breaks.' . $index . '.break_end',
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            }
        });
    }
}
