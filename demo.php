<?php

require_once __DIR__ . '/vendor/autoload.php';

$pdf = new fengxue145\pdf\PDF();
// // 使用模板
// $pdf->setSourceFile('./example/template/sample_basic.pdf');
// // 添加字体并使用字体
// $pdf->AddFont('DejaVuSans', '', 'DejaVuSans.ttf', true);
// $pdf->SetFont('DejaVuSans', '', 16);
// // 设置PDF元数据
// $pdf->SetTitle('PDF Example');
// $pdf->SetAuthor('fengxue145');
// $pdf->SetSubject('This is a simple EXAMPLE of PDF editing.');
// $pdf->SetKeywords('pdf,fpdf');
// $pdf->SetCreator('2023/01/22 15:26');
// // 添加页面
// $pdf->AddPage();
// $pageId = $pdf->importPage(1);
// $pdf->useImportedPage($pageId, 0, 0, null, null, true);
// // 写入内容
// $pdf->Text(100, 10, 'This is a line of text.');
// $pdf->Link(100, 10, 100, 20, 'http://fpdf.org/');
// $pdf->Image('./example/mapping/example.png', 10, 100, 100, 0);
// $pdf->Cell(100, 30, 'Hello PDF.');


$mapping = [
    // // use template
    // 'name' => 'sample_basic.pdf',
    // 'path' => './example/template/',

    // // defined and use font
    // 'fonts' => [
    //     'DejaVuSans' => [
    //         '' => 'DejaVuSans.ttf',
    //     ],
    // ],

    // // pdf meta data
    // 'title'    => 'PDF Example',
    // 'author'   => 'fengxue145',
    // 'subject'  => 'This is a simple EXAMPLE of PDF editing.',
    // 'keywords' => 'pdf,fpdf',
    // 'creator'  => '2023/01/22 15:26',

    // // default style
    // 'style' => [
    //     'body' => [
    //         'font-family'   => 'DejaVuSans',
    //         'font-style'    => '',
    //         'font-size'     => 10,
    //         'color'         => '#000000',
    //     ]
    // ],

    'pages' => [
        // 1 => [
        //     'content' => [
        //         'any keys 1' => [
        //             'x' => 100,
        //             'y' => 10,
        //             'type' => 'plain',
        //             'value' => 'This is a line of text.',
        //         ],
        //         'any keys 2' => [
        //             'x' => 100,
        //             'y' => 10,
        //             'type' => 'link',
        //             'value' => 'http://fpdf.org/',
        //             'style' => [
        //                 'width' => 100,
        //                 'height' => 20,
        //             ]
        //         ],
        //         'any keys 3' => [
        //             'x' => 10,
        //             'y' => 100,
        //             'type' => 'image',
        //             'value' => './example/mapping/example.png',
        //             'style' => [
        //                 'width' => 100,
        //             ]
        //         ],
        //         'any keys 4' => [
        //             'x' => 100,
        //             'y' => 60,
        //             'type' => 'text',
        //             'value' => 'Hello PDF.',
        //             'style' => [
        //                 'width' => 100,
        //                 'height' => 30
        //             ]
        //         ]
        //     ]
        // ]
    ]
];
$pdf->WriteMapping($mapping);
$pdf->Output(__DIR__ . '/example.pdf', 'F');
