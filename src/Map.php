<?php

namespace fengxue145\pdf;

abstract class Map
{
    /**
     * Set the document title (optional)
     * @var string
     */
    public $title = '';

    /**
     * Set the document author (optional)
     * @var string
     */
    public $author = '';

    /**
     * Set the document subject (optional)
     * @var string
     */
    public $subject = '';

    /**
     * Set the document keyword (optional)
     * @var string
     */
    public $keywords = '';

    /**
     * Set up document creation time (optional)
     * @var string
     */
    public $creator = '';

    /**
     * Fonts that can be used by setting documents (optional)
     * @var array
     */
    public $fonts = array();

    /**
     * Document default style file (optional)
     */
    public $default_style_file = '';

    /**
     * Document default style (optional)
     */
    public $default_style = array();

    /**
     * overwrite
     * Return to draw structure array
        {
            // return mapping file content property
            return [
                [
                    ...(optional) More attributes See MPDF::AddPageByArray()

                    // template (optional)
                    'template' => [
                        'sourcefile' => 'PDF template file path',
                        'pageno' => 1,
                    ],

                    // Main content (optional)
                    // string = html
                    // array  = mapping array
                    // object = object, you can use __tostring to get HTML content
                    'body' => '', // string | array | object
                ],
            ];

            // Mapping array structure
            [
                'tag' => 'div',                     // HTML tag name (option, default div)
                'hidden' => false,                  // Whether to hide (optional, default FALSE). If you are True, ignore the current node and all its sub-nodes.
                'attrs' => [                        // HTML tag attribute list (optional, default [])
                    'class' => 'class1 class2',     // Can be an array or string
                    'style' => ['color' => 'red'],  // Can be an array or string
                ],
                'text' => 'abc',                    // HTML tag text content (optional, default '')
                'children' => [                     // sub-node list (optional, default [])
                    [
                        'tag' => 'img',
                        'attrs' => [
                            'src' => './abc.png',
                        ]
                    ],
                    ...More sub-nodes
                ],
            ]
        }
     * 
     */
    abstract public function __toArray();
}
