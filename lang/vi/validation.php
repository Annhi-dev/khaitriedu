<?php

return [
    'after' => ':attribute phải là thời điểm sau :date.',
    'after_or_equal' => ':attribute phải là thời điểm sau hoặc bằng :date.',
    'before' => ':attribute phải là thời điểm trước :date.',
    'before_or_equal' => ':attribute phải là thời điểm trước hoặc bằng :date.',
    'date' => ':attribute không phải là ngày hợp lệ.',
    'date_format' => ':attribute phải có định dạng :format.',
    'exists' => ':attribute không hợp lệ.',
    'gte' => [
        'numeric' => ':attribute phải lớn hơn hoặc bằng :value.',
    ],
    'integer' => ':attribute phải là số nguyên.',
    'in' => ':attribute không hợp lệ.',
    'max' => [
        'array' => ':attribute không được nhiều hơn :max phần tử.',
        'numeric' => ':attribute không được lớn hơn :max.',
        'string' => ':attribute không được dài hơn :max ký tự.',
    ],
    'min' => [
        'array' => ':attribute phải có ít nhất :min phần tử.',
        'numeric' => ':attribute phải lớn hơn hoặc bằng :min.',
        'string' => ':attribute phải có ít nhất :min ký tự.',
    ],
    'required' => ':attribute là bắt buộc.',
    'required_without' => ':attribute là bắt buộc khi không có :values.',
    'required_without_all' => ':attribute là bắt buộc khi không có bất kỳ giá trị nào trong :values.',
    'string' => ':attribute phải là chuỗi ký tự.',

    'custom' => [],

    'attributes' => [
        'start_time' => 'Giờ bắt đầu',
        'end_time' => 'Giờ kết thúc',
        'requested_start_time' => 'Giờ bắt đầu đề xuất',
        'requested_end_time' => 'Giờ kết thúc đề xuất',
        'registration_open_at' => 'Thời gian mở đăng ký',
        'registration_close_at' => 'Thời gian đóng đăng ký',
        'slot_date' => 'Ngày học',
        'start' => 'Giờ bắt đầu',
        'end' => 'Giờ kết thúc',
        'requested_start_at' => 'Thời điểm bắt đầu đề xuất',
        'requested_end_at' => 'Thời điểm kết thúc đề xuất',
    ],
];
