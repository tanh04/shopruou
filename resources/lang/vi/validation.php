<?php

return [
    'required' => ':attribute không được để trống.',
    'string'   => ':attribute phải là chuỗi ký tự.',
    'numeric'  => ':attribute phải là số.',
    'integer'  => ':attribute phải là số nguyên.',
    'min' => [
        'string'  => ':attribute phải có ít nhất :min ký tự.',
        'numeric' => ':attribute phải lớn hơn hoặc bằng :min.',
    ],
    'max' => [
        'string'  => ':attribute không được vượt quá :max ký tự.',
        'numeric' => ':attribute không được lớn hơn :max.',
    ],
    'between' => [
        'numeric' => ':attribute phải nằm trong khoảng :min - :max.',
        'string'  => ':attribute phải có từ :min đến :max ký tự.',
    ],
    'in'        => ':attribute không hợp lệ.',
    'regex'     => ':attribute không được chỉ chứa khoảng trắng.',
    'image'     => ':attribute phải là hình ảnh.',
    'mimes'     => ':attribute chỉ chấp nhận định dạng: :values.',
    'mimetypes' => ':attribute chỉ chấp nhận định dạng: :values.',
    'email'     => ':attribute phải là email hợp lệ.',
    'unique'    => ':attribute đã tồn tại.',
    'confirmed' => ':attribute xác nhận không khớp.',
    'boolean'   => ':attribute phải là đúng hoặc sai.',

    // Rule ngày
    'after'          => ':attribute phải sau :date.',
    'after_or_equal' => ':attribute phải sau hoặc bằng :date.',
    'before'         => ':attribute phải trước :date.',
    'date'           => ':attribute không phải là ngày hợp lệ.',

    // Map tên hiển thị
    'attributes' => [
        // Product
        'product_name'        => 'Tên sản phẩm',
        'product_image'       => 'Hình ảnh',
        'product_description' => 'Mô tả sản phẩm',
        'product_price'       => 'Giá sản phẩm',
        'product_capacity'    => 'Dung tích',
        'product_stock'       => 'Số lượng',
        'category_id'         => 'Danh mục',
        'brand_id'            => 'Thương hiệu',
        'product_status'      => 'Trạng thái',

        // Category
        'category_name'        => 'Tên danh mục',
        'category_description' => 'Mô tả danh mục',
        'category_status'      => 'Trạng thái danh mục',

        // Brand
        'brand_name'        => 'Tên thương hiệu',
        'brand_description' => 'Mô tả thương hiệu',
        'brand_status'      => 'Trạng thái thương hiệu',

        // Coupon
        'coupon_code'      => 'Mã giảm giá',
        'coupon_quantity'  => 'Số lượng',
        'start_date'       => 'Ngày bắt đầu',
        'end_date'         => 'Ngày kết thúc',
        'discount_percent' => 'Phần trăm giảm',
        'discount_amount'  => 'Số tiền giảm',
        'min_order_value'  => 'Giá trị đơn hàng tối thiểu',
        'status'           => 'Trạng thái',

        // User
        'name'                  => 'Họ và tên',
        'email'                 => 'Email',
        'password'              => 'Mật khẩu',
        'password_confirmation' => 'Xác nhận mật khẩu',
        'address'               => 'Địa chỉ',
        'phone'                 => 'Số điện thoại',
        'role'                  => 'Vai trò',
    ],
];
