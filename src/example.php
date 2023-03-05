<?php

require_once(dirname(__DIR__) . '/vendor/autoload.php');

// 定义系统字体目录
define("_SYSTEM_TTFONTS", 'C:\\Windows\\Fonts\\');

$mapping = [
    // 模板名称
    'name' => 'template.pdf',

    // 模板路径
    'path' => __DIR__,

    // 模板所使用的字体
    // 注：下面要使用的字体都必须在这里先定义
    'fonts' => [
        // font-family => ...
        'PMingLiU' => [
            // font-style => font file
            '' => 'PMingLiU-CN.ttf',
        ],
        'msyh' => [
            '' => 'MicrosoftYaHei.ttf',
        ],
        'DejaVuSans' => [
            '' => 'DejaVuSans.ttf',
            'B' => 'DejaVuSans-Bold.ttf',
        ],
        'times' => [
            '' => 'times.ttf',
            'B' => 'timesbd.ttf',
            'I' => 'timesi.ttf',
            'BI' => 'timesbi.ttf',
        ],
        // 内部字体前面加 @ 表示
        '@symbol' => [
            '' => '',
        ]
    ],

    // 模板元信息
    'title'    => 'Simple Example',                 // 设置文档标题（可选）
    'author'   => 'fengxue145',                     // 设置文档作者（可选）
    'subject'  => 'This is a simple use example',   // 设置文档主题（可选）
    'keywords' => 'PDF FPDF TFPDF',                 // 设置文档关键字（可选）
    'creator'  => '2022/02/16 09:57',               // 设置文档创建时间（可选）

    // 模板页
    'pages' => [
        // 页码(从1开始) => ...
        // 1 => [
        //     // 标识(随便起) => ...
        //     'example' => [
        //         'type'   => 'text',                                         // 类型（可选: text | plain | line | rect | link | image，默认 text）
        //         'value'  => 'Hello Word!',                                  // 写入的内容（可选，未设置该属性的会被跳过）
        //         'x'      => 20,                                             // 写入位置的 X 坐标（可选，类型为 line 时，需使用数组提供两个坐标：[x1, x2]）
        //         'y'      => 20,                                             // 写入位置的 Y 坐标（可选，类型为 line 时，需使用数组提供两个坐标：[y1, y2]）
        //         'border' => 'LTRB',                                         // 边框（可选：L | T | R | B | 1 | 0；其中 LTRB 可组合，1 表示全边框，0 表示无边框，默认 0；仅限 text、rect 类型）
        //         'link'   => 'https://www.baidu.com/',                       // 链接（可选，仅限 text、image 类型）
        //         'inherit_styl' => false,                                    // 是否允许 styl 样式被继承（可选，默认 true）
        //
        //         // 注：
        //         // 1. 以下样式都是可选的，且部分样式会被后面的继承。
        //         // 2. 颜色只支持三种方式 #000 | #000000 | rgb(0,0,0)
        //         // 3. 边框大小不支持单位
        //         'styl' => [
        //             'width'            => 0,                                // 单元格宽度（仅限 link、rect、image、text 类型，要显示边框，必须同时定义 width 和 height）
        //             'height'           => 0,                                // 单元格高度（同上）
        //             'line-height'      => 0,                                // 行高（仅限 text 类型，多行文本建议设置）
        //             'font-family'      => 'PMingLiU',                       // 字体（可继承，必须事先在 fonts 中定义）
        //             'font-style'       => '',                               // 字体样式（同上）
        //             'font-size'        => 12,                               // 字体大小（可继承）
        //             'color'            => '#000000',                        // 字体颜色（可继承，仅限 plain、text 类型）
        //             'background-color' => 'rgb(255,0,0)',                   // 背景颜色（可继承，仅限 rect、text 类型）
        //             'border'           => 1,                                // 边框大小（可继承，仅限 line、rect、text 类型）
        //             // 'border'        => '1 solid',                        // 边框大小及样式（同上）
        //             // 'border'        => '1 solid #000000',                // 边框大小、样式及颜色（同上）
        //             'border-width'     => 1,                                // 边框大小（同上）
        //             'border-color'     => '#000000',                        // 边框颜色（同上）
        //             'text-align'       => 'L',                              // 文本对齐方式（可选：L | C | R | J）
        //             'word-break'       => 'break-word',                     // 文本换行（可选：break-word | break-all）
        //         ]
        //     ],
        // ],


        1 => [
            // 页面定位 X 坐标起点（默认 0 左上角）
            'startX' => 0,

            // 页面定位 Y 坐标起点（默认 0 左上角）
            'startY' => 0,

            // 页面内容
            'content' => [
                // ------------------------- Plain Text ----------------------
                // 纯文本（不换行，可在任何位置写）
                // 'plain 1' => [
                //     'type' => 'plain',
                //     'value' => '这是一个简单文本',
                //     'x' => 5,
                //     'y' => 15,
                //     'styl'  => [
                //         'font-family'   => 'PMingLiU',
                //         'font-style'    => '',
                //         'font-size'     => 8,
                //         'color'         => 'rgb(0,0,0)',
                //     ],
                // ],
                // 'plain text 2' => [
                //     'type'  => 'plain',
                //     'value' => '这是一个纯文本',
                //     'y' => 20,
                //     'inherit_styl' => false, // 申明当前的“styl”是独立的，不允许被后面继承
                //     'styl' => [
                //         'font-family' => 'msyh',
                //         'font-style' => '',
                //         'font-size' => 10,
                //         'color' => 'rgb(255,0,0)',
                //     ],
                // ],
                // 'plain text 3' => [
                //     'type' => 'plain',
                //     'value' => '这是一个纯文本',
                //     'y' => 25,
                //     'styl' => [ // 这里继承 “plain text 1” 的styl属性，“plain text 2”因为设置了“inherit_styl => false”，导致属性不能被继承
                //         'font-size' => 12,
                //     ],
                // ],
                // 'plain text 4' => [
                //     'type' => 'plain',
                //     'value' => '这是一个很长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长的纯文本',
                //     'y' => 30,
                // ],
                // ------------------------- /Plain Text ----------------------



                // ------------------------- Simple Text ----------------------
                // 简单文本，遇到右边距才会换行
                // 注：换行后的文本 x 坐标会从左边距开始
                // 'Simple text 1' => [
                //     'type' => 'text',
                //     'value' => '这是一个简单文本',
                //     'y' => 40,
                //     'styl' => [
                //         'line-height' => 4 // 必须定义行高，否则多行文字会重叠
                //     ]
                // ],
                // 'Simple text 2' => [
                //     'type' => 'text',
                //     'value' => '这是一个很长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长长的简单文本',
                //     'x' => 30,
                //     'y' => 45,
                //     'styl' => [
                //         'font-family' => 'PMingLiU',
                //         'line-height' => 4
                //     ]
                // ],
                // ------------------------- /Simple Text ----------------------



                // // ------------------------- Single-line Text ----------------------
                // // 单行文本
                // // 注：x 坐标会变更到上一次位置的x坐标+宽度
                // 'Single-line text 1' => [
                //     'type' => 'text',
                //     'value' => '这是一个单行文本',
                //     'x' => 5,
                //     'y' => 60,
                //     'border' => 1,
                //     'styl' => [
                //         'width' => 50,
                //         'height' => 6
                //     ]
                // ],
                // 'Single-line text 2' => [
                //     'type'  => 'text',
                //     'value' => '这是一个单行文本',
                //     'border' => 'TRB',
                //     'styl' => [
                //         'width' => 50,
                //         'height' => 6
                //     ]
                // ],
                // 'Single-line text 3' => [
                //     'type'  => 'text',
                //     'value' => '这是一个超过单行限制的文本',
                //     'border' => 'TRB',
                //     'styl' => [
                //         'width' => 50,
                //         'height' => 6
                //     ]
                // ],
                // 'Single-line text 4' => [
                //     'type'  => 'text',
                //     'value' => '这是一个单行文本, 支持内部链接',
                //     'x' => 5,
                //     'y' => 66,
                //     'border' => 'RBL',
                //     'link' => 'http://fpdf.org/',
                //     'styl' => [
                //         'width' => 80,
                //         'height' => 6
                //     ]
                // ],
                // // ------------------------- /Single-line Text ----------------------



                // // ------------------------- /Multi-line Text ----------------------
                // // 多行文本（styl 必须包含 width, height, word-break 属性）
                'Multi-line text 1' => [
                    'type' => 'text',
                    'value' => "22\n这是一个多行文本\n内部可以使用\\n换行这是一个多行文本\n内部可以使用\\n换行这是一个多行文本\n内部可以使用\\n换行",
                    'x' => 15,
                    'y' => 80,
                    // 'border' => 'L',
                    'link' => 'https://www.baidu.com/',
                    'styl' => [
                        // 'font-family' => 'msyh',
                        'font-family' => 'PMingLiU',
                        'width' => 50,
                        'height' => 0,
                        'line-height' => 3,
                        'line-offset' => '2 0 0 5 5 -1 0',
                        'word-break' => 'break-word',
                        'border-width' => 1,
                        'border-color' => '#000000',
                        'background-color' => '#eb4c4c',
                    ]
                ],

                // 'Multi-line text 1' => [
                //     'type' => 'text',
                //     'value' => "这是一个多行文本\n内部可以使用\\n换行",
                //     'x' => 5,
                //     'y' => 80,
                //     'border' => 1,
                //     'styl' => [
                //         'width' => 50,
                //         'height' => 6,
                //         'word-break' => 'break-word'
                //     ]
                // ],
                // 'Multi-line text 2' => [
                //     'type' => 'text',
                //     'value' => "这是一个多行文本\n多行文本不支持内部链接，可以使用 Link 设置可点击区域",
                //     'x' => 60,
                //     'y' => 80,
                //     'border' => 1,
                //     'styl' => [
                //         'width' => 50,
                //         'height' => 6,
                //         'word-break' => 'break-word'
                //     ]
                // ],
                // 'Multi-line text 3' => [
                //     'type' => 'link',
                //     'value' => "http://fpdf.org/",
                //     'x' => 60,
                //     'y' => 80,
                //     'styl' => [
                //         'width' => 50,
                //         'height' => 24
                //     ]
                // ],
                // // ------------------------- /Multi-line Text ----------------------



                // // ------------------------- Line ----------------------
                // // 线条
                // // 注意：线条必须提供 x 和 y 的两个坐标
                // 'Line 1' => [
                //     'type' => 'line',
                //     'value' => 'ABCDEFGHIJKLMN',
                //     'x' => [5, 100],
                //     'y' => [118, 118],
                //     'styl' => [
                //         'border' => '1 solid #00ff00'
                //     ]
                // ],
                // 'Line 2' => [
                //     'type' => 'line',
                //     'value' => 'ABCDEFGHIJKLMN',
                //     'x' => [5, 100],
                //     'y' => [120, 120],
                //     'styl' => [
                //         'border' => '0.5 solid #0000ff'
                //     ]
                // ],
                // // ------------------------- /Line ----------------------



                // // ------------------------- Rect ----------------------
                // // 矩形
                // 'Rect 1' => [
                //     'type' => 'rect',
                //     'x' => 5,
                //     'y' => 130,
                //     'styl' => [
                //         'width' => 10,
                //         'height' => 10,
                //         'background-color' => 'rgb(255, 0, 0)'
                //     ]
                // ],
                // 'Rect 2' => [
                //     'type' => 'rect',
                //     'x' => 20,
                //     'y' => 124.5,
                //     'border' => 1,
                //     'styl' => [
                //         'width' => 15,
                //         'height' => 15,
                //         'border' => '1 solid rgb(0, 255, 0)',
                //         'background-color' => 'rgb(255, 0, 0)'
                //     ]
                // ],
                // // ------------------------- /Rect ----------------------



                // // ------------------------- Link ----------------------
                // // 设置可点击区域
                // 'Link 1' => [
                //     'type' => 'link',
                //     'value' => 'http://fpdf.org/',
                //     'x' => 5,
                //     'y' => 150,
                //     'styl' => [
                //         'width' => 50,
                //         'height' => 6,
                //     ]
                // ],
                // 'Link 1 background' => [
                //     'type' => 'rect',
                //     'styl' => [
                //         'width' => 50,
                //         'height' => 6,
                //         'background-color' => '#ff0000'
                //     ]
                // ],
                // 'Link 1 text' => [
                //     'type' => 'text',
                //     'value' => '可点击: http://fpdf.org/',
                //     'styl' => [
                //         'font-family' => 'PMingLiU',
                //         'width' => 50,
                //         'height' => 6,
                //     ]
                // ],
                // 'Link 2' => [
                //     'type' => 'text',
                //     'value' => '可点击2: http://fpdf.org/',
                //     'x' => 70,
                //     'border' => 1,
                //     'link' => 'http://fpdf.org/',
                //     'styl' => [
                //         // 'font-family'      => 'PMingLiU', // 默认继承上一次设置的字体
                //         'width' => 50,
                //         'height' => 6,
                //         'border' => '0.1 solid #000000',
                //         'background-color' => 'rgb(255, 0, 0)',
                //         'word-break' => 'break-word'
                //     ]
                // ],
                // // ------------------------- /Link ----------------------



                // // ------------------------- Image ----------------------
                // // 图片
                // 'Image 1' => [
                //     'type' => 'image',
                //     'value' => __DIR__ . '/example.png',
                //     'x' => 5,
                //     'y' => 180,
                //     'styl' => [
                //         'width' => '20',
                //     ]
                // ],
                // 'Image 2' => [
                //     'type' => 'image',
                //     'value' => __DIR__ . '/example.png',
                //     'x' => 30,
                //     'styl' => [
                //         'width' => '50',
                //     ]
                // ],
                // 'Image 3' => [
                //     'type' => 'image',
                //     'value' => __DIR__ . '/example.png',
                //     'x' => 85,
                //     'link' => 'http://fpdf.org/',
                //     'styl' => [
                //         'width' => '50',
                //         'height' => '50',
                //     ]
                // ],
                // // ------------------------- Image ----------------------
            ]
        ],
    ]
];


$pdf = new fengxue145\pdf\PDF();
$pdf->writeMapping($mapping);
$pdf->Output(__DIR__ . '/example.pdf', 'F');
