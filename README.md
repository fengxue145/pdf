
这是一个PHP库，基于 [Tfpdf](https://github.com/Setasign/tFPDF) 进行开发。


## 安装
```
$ composer require fengxue145/pdf:v1.0.1
```


## 用法
参见 [FPDF](http://fpdf.org/)



## 新增方法

### WriteCell($txt, $link = '', $css = array())

参数：
- `$txt`: string

    单元格文本内容，支持 "\n" 换行；

- `$link`: string

    文本链接；

- `$css`: array

    单元格样式数组；


参见 Cell()、MultiCell() 方法;



### WriteMapping(&$mapping)

作用：此方法只是将 Mapping 结构数据转换成常规操作并执行的方法；

参数：

- `$mapping`: array

    Mapping 结构数组；


案例：
``` php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$mapping = [
    $mapping = [
      // 模板名称（可选）
      'name' => 'example.pdf',
      // 模板路径（可选）
      'path' => './web/public/template/',
      // 模板所使用的字体（注：下面要使用的字体都必须在这里先定义）
      'fonts' => [
        // font-family => ...
        'DejaVuSans' => [
             // font-style => font file
          '' => 'DejaVuSans.ttf',
          'B' => 'DejaVuSans-Bold.ttf',
        ],
      ],
      // 设置文档标题（可选）
      'title'    => 'Derivative Knowledge Training and Questionnaire 6.2018',
      // 设置文档作者（可选）
      'author'   => 'Transaction Technologies',
      // 设置文档主题（可选）
      'subject'  => '',
      // 设置文档关键字（可选）
      'keywords' => '',
      // 设置文档创建时间（可选）
      'creator'  => '2022/02/16 09:57',
      // 文档边距（可选）
      'margin' => [
        'top'    => 10,
        'right'  => 10,
        'bottom' => 10,
        'left'   => 10,
      ],
      // 默认样式
      'style' => [
        'body' => [
          'font-family'   => 'PMingLiU',
          'font-style'    => '',
          'font-size'     => 10,
          'color'         => '#000000',
        ],
        'checkbox' => [
          'font-family'   => 'DejaVuSans',
          'font-style'    => 'B',
          'font-size'     => 10,
        ],
        'text' => [
          // 'border' => '0.1 solid #FF0000',
          'autosize' => 1,
          'min-font-size' => 4,
        ]
      ],

      'pages' => self::pages($context)
    ];
];

$pdf = new \fengxue145\pdf\PDF();
$pdf->WriteMapping($mapping);
$pdf->Output();
```


## Mapping 数组结构

原操作PDF的方式：
```php
require_once __DIR__ . '/vendor/autoload.php';

$pdf = new fengxue145\pdf\PDF();
// 使用模板
$pdf->setSourceFile('./example/template/sample_basic.pdf');
// 添加字体并使用字体
$pdf->AddFont('DejaVuSans', '', 'DejaVuSans.ttf', true);
$pdf->SetFont('DejaVuSans', '', 16);
// 设置PDF元数据
$pdf->SetTitle('PDF Example');
$pdf->SetAuthor('fengxue145');
$pdf->SetSubject('This is a simple EXAMPLE of PDF editing.');
$pdf->SetKeywords('pdf,fpdf');
$pdf->SetCreator('2023/01/22 15:26');
// 添加页面
$pdf->AddPage();
$pageId = $pdf->importPage(1);
$pdf->useImportedPage($pageId, 0, 0, null, null, true);
// 写入内容
$pdf->Text(100, 10, 'This is a line of text.');
$pdf->Link(100, 10, 100, 20, 'http://fpdf.org/');
$pdf->Image('./example/mapping/example.png', 10, 100, 100);
$pdf->SetXY(100, 60);
$pdf->Cell(100, 30, 'Hello PDF.');
$pdf->Output(__DIR__ . '/example.pdf', 'F');
```

将其转换为 Mapping 结构：
```php
require_once __DIR__ . '/vendor/autoload.php';

$pdf = new fengxue145\pdf\PDF();
$mapping = [
    // use template
    'name' => 'sample_basic.pdf',
    'path' => './example/template/',

    // defined and use font
    'fonts' => [
        'DejaVuSans' => [
            '' => 'DejaVuSans.ttf',
        ],
    ],

    // pdf meta data
    'title'    => 'PDF Example',
    'author'   => 'fengxue145',
    'subject'  => 'This is a simple EXAMPLE of PDF editing.',
    'keywords' => 'pdf,fpdf',
    'creator'  => '2023/01/22 15:26',

    // default style
    'style' => [
        'body' => [
            'font-family'   => 'DejaVuSans',
            'font-style'    => '',
            'font-size'     => 10,
            'color'         => '#000000',
        ]
    ],

    'pages' => [
        // page no
        1 => [
            'content' => [
                'any keys 1' => [
                    'x' => 100,
                    'y' => 10,
                    'type' => 'plain',
                    'value' => 'This is a line of text.',
                ],
                'any keys 2' => [
                    'x' => 100,
                    'y' => 10,
                    'type' => 'link',
                    'value' => 'http://fpdf.org/',
                    'style' => [
                        'width' => 100,
                        'height' => 20,
                    ]
                ],
                'any keys 3' => [
                    'x' => 10,
                    'y' => 100,
                    'type' => 'image',
                    'value' => './example/mapping/example.png',
                    'style' => [
                        'width' => 100,
                    ]
                ],
                'any keys 4' => [
                    'x' => 100,
                    'y' => 60,
                    'type' => 'text',
                    'value' => 'Hello PDF.',
                    'style' => [
                        'width' => 100,
                        'height' => 30
                    ]
                ]
            ]
        ]
    ]
];
$pdf->WriteMapping($mapping);
$pdf->Output(__DIR__ . '/example.pdf', 'F');
```

完整结构：
```php
$mapping = [
    // 模板名称（可选）
    'name' => 'example.pdf',
    // 模板路径（可选）
    'path' => './web/public/template/',

    // 模板所使用的字体（注：下面要使用的字体都必须在这里先定义）
    'fonts' => [
        // font-family => ...
        'DejaVuSans' => [
            // font-style => font file
            '' => 'DejaVuSans.ttf',
            'B' => 'DejaVuSans-Bold.ttf',
        ],
        // 在 font-family 前面加上 @ 符号，表示使用内部字体
        '@courier' => [
            '' => 'courier'
        ]
    ],

    // 设置文档标题（可选）
    'title'    => 'Derivative Knowledge Training and Questionnaire 6.2018',
    // 设置文档作者（可选）
    'author'   => 'Transaction Technologies',
    // 设置文档主题（可选）
    'subject'  => '',
    // 设置文档关键字（可选）
    'keywords' => '',
    // 设置文档创建时间（可选）
    'creator'  => '2022/02/16 09:57',

    // 文档边距（可选）
    'margin' => [
        'top'    => 10,
        'right'  => 10,
        'bottom' => 10,
        'left'   => 10,
    ],

    // 默认样式（可选）
    'style' => [
        // 全局样式
        'body' => [
            'font-family'   => 'PMingLiU',
            'font-style'    => '',
            'font-size'     => 10,
            'color'         => '#000000',
        ],
        // 类型的默认样式
        'checkbox' => [
            'font-family'   => 'DejaVuSans',
            'font-style'    => 'B',
            'font-size'     => 10,
        ],
        'text' => [
            // 'border' => '0.1 solid #FF0000',
            'autosize' => 1,
            'min-font-size' => 4,
        ]
    ],

    // 页面数组
    'pages' => [
        // 页码（需要写入数据的页码）
        // 比如：使用模板总共有10页，只需要在第9页绘制内容，则页码设为 9 即可。
        1 => [
            // X坐标的偏移值（默认 0）
            'startX' => 0,
            // Y坐标的偏移值（默认 0）
            'startY' => 0,
            // 操作数组列表
            'content' => [
                // 键名任意（可有可无）
                'key' => [
                    // 写入的X坐标（自动加上 startX）
                    'x' => 0,
                    // 写入的Y坐标（自动加上 startY）
                    'y' => 0,
                    // 操作类型，支持：line、link、checkbox、image、plain、text
                    'type' => 'text',
                    // 写入的内容（NULL值跳过）
                    'value' => 'hello world.',
                    // 写入样式（不同的操作支持的样式都不一样）
                    'style' => [

                    ]
                ]
            ]
        ]
    ]
];
```
