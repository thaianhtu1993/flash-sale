<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Slack Authentication
    |--------------------------------------------------------------------------
    |
    |   Below is slack api key and channel need to push notification
    |
    */
    'auth' => [
        'token' => 'xoxp-107399059749-107988760726-149915379265-888c1a0220bd9ed0da50608d1a4f0dd6',
        'channel' => [
            'staging' => '#flash_sale_log',
            'product' => '#',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Message
    |--------------------------------------------------------------------------
    |
    |   Below is error message for this application
    |
    */
    'error' => [
        'db' => [
            'status' => 0,
            'message' => 'Lỗi hệ thống xin vui lòng thử lại sau'
        ],
        'email_not_exists' => [
            'status' => 0,
            'message' => 'Email không tồn tại trong hệ thống vui lòng thử lại'
        ],
        'password_wrong' => [
            'status' => 0,
            'message' => 'Mật khẩu không đúng vui lòng thử lại'
        ],
        'role' => [
            'status' => 0,
            'message' => 'Bạn không có quyền truy cập'
        ],
        'order_different_shipper_route' => [
            'status' => 0,
            'message' => 'Đơn hàng không nằm trong lộ trình này'
        ],
        'ship_code_not_exists' => [
            'status' => 0,
            'message' => 'Mã vận đơn không tồn tại hãy xem kĩ và thử lại'
        ]
    ],

    /*
   |--------------------------------------------------------------------------
   | Success Message
   |--------------------------------------------------------------------------
   |
   |   Below is error message for this application
   |
   */

    'success' => [
        'register' => [
            'status' => 1,
            'messsage' => 'Bạn đã đăng ký thành công'
        ],
        'logout' => [
            'status' => 1,
            'message' => 'Bạn đã đăng xuất thành công'
        ],
        'order_change_status' => [
            'status' => 1,
            'message' => 'Thay đổi trạng thái đơn hàng thành công'
        ],
        'create' => [
            'status' => 1,
            'message' => 'Thêm mới thành công'
        ],
        'update' => [
            'status' => 1,
            'message' => 'Cập nhật thành công'
        ],
        'delete' => [
            'status' => 1,
            'message' => 'Xoá thành công'
        ]

    ]


];
