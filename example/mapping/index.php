<?php

require_once '../../vendor/autoload.php';

class DemoMap extends fengxue145\pdf\Map 
{
    public function __construct()
    {
        $this->title = 'MAPPING EXAMPLE';
        $this->creator = date('Y/m/d H:i');

        $this->fonts = [
            // font dir => font data
            __DIR__ . '/ttf' => [
                'cascadia-code' => [
                    'R' => 'CascadiaCode.ttf',
                    'I' => 'CascadiaCodeItalic.ttf'
                ]
            ]
        ];

        $this->default_style = [
            'body' => [
                'font-family' => 'cascadia-code, Sun-ExtA, Sun-ExtB',
                'font-size' => '16px'
            ],
        ];

        // customize
        $this->template = dirname(__DIR__) . '/template/sample_basic.pdf';
    }

    public function select()
    {
        return [
            'a' => [
                'tag' => 'div',
                'text' => 'Apple',
                'attrs' => [
                    'style' => [
                        'position' => 'absolute',
                        'left' => '20px',
                        'top' => '30px',
                    ],
                ],
            ],
            'b' => [
                'tag' => 'div',
                'text' => 'Banana',
                'attrs' => [
                    'style' => [
                        'position' => 'absolute',
                        'left' => '120px',
                        'top' => '30px',
                    ],
                ],
            ],
            'c' => [
                'tag' => 'div',
                'text' => 'Orange',
                'attrs' => [
                    'style' => [
                        'position' => 'absolute',
                        'left' => '220px',
                        'top' => '30px',
                    ],
                ],
            ],
            'd' => [
                'tag' => 'div',
                'text' => 'Other:',
                'attrs' => [
                    'style' => [
                        'position' => 'absolute',
                        'left' => '320px',
                        'top' => '30px',
                    ],
                ],
            ]
        ];
    }

    public function page_1()
    {
        $mapping = [
            // For more attributes, see Mpdf::AddPageByArray()
            'orientation' => 'L',
            'margin-left' => 0,
            'margin-right' => 0,
            'margin-top' => 0,
            'margin-bottom' => 0,


            // Template
            // 'template' => [
            //     'sourcefile' => $this->template,
            //     'pageno' => 1,
            // ],

            // Main content
            'body' => [
                'wrapper' => [
                    // Text tag
                    'tag' => 'div',
                    'attrs' => [
                        'style' => [
                            'position' => 'relative',
                            'left' => '0px',
                            'top' => '0px'
                        ]
                    ],
                    'children' => [
                        'value1' => [
                            'tag' => 'div',
                            'text' => 'This is a text content',
                            'attrs' => [
                                'style' => [
                                    'position' => 'absolute',
                                    'left' => '50px',
                                    'top' => '50px',
                                    'border' => '1px solid red',
                                ],
                            ],
                        ],
                        'value2' => [
                            'tag' => 'img',
                            'attrs' => [
                                'style' => [
                                    'position' => 'absolute',
                                    'left' => '50px',
                                    'top' => '100px',
                                    'border' => '1px solid red',
                                ],
                                'src' => __DIR__ . '/example.png',
                            ],
                        ],
                        'value3' => [
                            'tag' => 'div',
                            'attrs' => [
                                'style' => [
                                    'position' => 'relative',
                                    'left' => '50px',
                                    'top' => '500px',
                                ],
                            ],
                            'children' => [
                                'question' => [
                                    'tag' => 'div',
                                    'text' => 'What are your favorite fruits? (Multiple options)',
                                    'attrs' => [
                                        'style' => [
                                            'position' => 'absolute',
                                            'left' => '0px',
                                            'top' => '0px',
                                        ],
                                    ],
                                    'children' => $this->select(),
                                ],
                                'answer' => [
                                    'tag' => 'checkbox',
                                    'value' => ['a', 'b'],
                                    'options' => [
                                        'a' => [
                                            'tag' => 'span',
                                            'text' => '✔',
                                            'attrs' => [
                                                'style' => 'position:absolute;left:0px;top:30px;',
                                            ]
                                        ],
                                        'b' => [
                                            'tag' => 'span',
                                            'text' => '✔',
                                            'attrs' => [
                                                'style' => 'position:absolute;left:100px;top:30px;',
                                            ]
                                        ],
                                        'c' => [
                                            'tag' => 'span',
                                            'text' => '✔',
                                            'attrs' => [
                                                'style' => 'position:absolute;left:200px;top:30px;',
                                            ]
                                        ],
                                        'd' => [
                                            'tag' => 'span',
                                            'text' => '✔',
                                            'checked' => true,
                                            'attrs' => [
                                                'style' => 'position:absolute;left:300px;top:30px;',
                                            ],
                                            'children' => [
                                                [
                                                    'tag' => 'span',
                                                    'text' => 'litchi',
                                                    'attrs' => [
                                                        'style' => [
                                                            'position' => 'absolute',
                                                            'left' => '370px',
                                                            'top' => '30px',
                                                            'border-bottom' => '1px solid #000'
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'value4' => [
                            'tag' => 'div',
                            'attrs' => [
                                'style' => [
                                    'position' => 'relative',
                                    'left' => '50px',
                                    'top' => '600px',
                                ],
                            ],
                            'children' => [
                                'question' => [
                                    'tag' => 'div',
                                    'text' => 'What are your favorite fruits? (Single option)',
                                    'attrs' => [
                                        'style' => [
                                            'position' => 'absolute',
                                            'left' => '0px',
                                            'top' => '0px',
                                        ],
                                    ],
                                    'children' => $this->select(),
                                ],
                                'answer' => [
                                    'tag' => 'checkbox',
                                    'value' => 'd',
                                    'options' => [
                                        'a' => [
                                            'tag' => 'span',
                                            'text' => '✔',
                                            'attrs' => [
                                                'style' => 'position:absolute;left:0px;top:30px;',
                                            ]
                                        ],
                                        'b' => [
                                            'tag' => 'span',
                                            'text' => '✔',
                                            'attrs' => [
                                                'style' => 'position:absolute;left:100px;top:30px;',
                                            ]
                                        ],
                                        'c' => [
                                            'tag' => 'span',
                                            'text' => '✔',
                                            'attrs' => [
                                                'style' => 'position:absolute;left:200px;top:30px;',
                                            ]
                                        ],
                                        'd' => [
                                            'tag' => 'span',
                                            'text' => '✔',
                                            'attrs' => [
                                                'style' => 'position:absolute;left:300px;top:30px;',
                                            ],
                                            'children' => [
                                                [
                                                    'tag' => 'span',
                                                    'text' => 'litchi',
                                                    'attrs' => [
                                                        'style' => [
                                                            'position' => 'absolute',
                                                            'left' => '370px',
                                                            'top' => '30px',
                                                            'border-bottom' => '1px solid #000'
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'value5' => [
                            'tag' => 'div',
                            'attrs' => [
                                'style' => [
                                    'position' => 'relative',
                                    'left' => '50px',
                                    'top' => '700px',
                                ],
                            ],
                            'children' => [
                                'question' => [
                                    'tag' => 'div',
                                    'text' => 'What are your favorite fruits?',
                                    'attrs' => [
                                        'style' => [
                                            'position' => 'absolute',
                                        ],
                                    ]
                                ],
                                'answer' => [
                                    'tag' => 'select',
                                    'text' => 'pear', // default text
                                    'value' => 'a',
                                    'index_key' => 'value', // default 'key'
                                    'column_key' => 'text', // default 'value'
                                    'options' => [
                                        [ 'value' => 'a', 'text' => 'Apple' ],
                                        [ 'value' => 'b', 'text' => 'Banana' ],
                                        [ 'value' => 'c', 'text' => 'Orange' ],
                                        [ 'value' => 'd', 'text' => 'Other' ],
                                    ],
                                    'attrs' => [
                                        'style' => [
                                            'position' => 'absolute',
                                            'left' => '250px',
                                            'border-bottom' => '1px solid #000'
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $mapping;
    }

    public function __toArray()
    {
        return [
            $this->page_1(),
            [
                'margin-left' => '10mm',
                'margin-right' => '10mm',
                'margin-top' => '10mm',
                'margin-bottom' => '10mm',

                'template' => [
                    'sourcefile' => $this->template,
                    'pageno' => 2,
                ],
                'body' => [
                    'any' => [
                        'tag' => 'div',
                        'text' => 'This is a text content',
                        'attrs' => [
                            'style' => [
                                'position' => 'fixed',
                                'top' => '80px',
                                'right' => '0px',
                                'font-size' => '30px',
                                'color' => 'red'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'template' => [
                    'sourcefile' => $this->template,
                    'pageno' => 3,
                ],
            ]
        ];
    }
}


$pdf = new \fengxue145\pdf\PDF();
$pdf->SetImportUse();
$pdf->WriteMap(new DemoMap());
$pdf->Output();
