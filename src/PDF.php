<?php
namespace fengxue145\pdf;


class PDF extends \setasign\Fpdi\Tfpdf\Fpdi
{
    /**
     * Parse colors, RGB and HEX only
     *
     * @param string $color
     * @return array [$r, $g, $b]
     * @throws Exception
     *
     * @example
     *  $this->_color('#fff');
     *  $this->_color('#fff000');
     *  $this->_color('rgb(0,0,0)');
     */
    protected function _color($color)
    {
        if (preg_match('/^rgb\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)$/', $color, $matches))
        {
            array_shift($matches);
            return $matches;
        }
        else if (preg_match('/^#([0-9A-Fa-f]{3,6})$/', $color, $matches))
        {
            $color = $matches[1];
            if (strlen($color) === 3)
            {
                $r = $color[0] . $color[0];
                $g = $color[1] . $color[1];
                $b = $color[2] . $color[2];
            }
            else
            {
                $r = $color[0] . $color[1];
                $g = $color[2] . $color[3];
                $b = $color[4] . $color[5];
            }
            return [hexdec($r), hexdec($g), hexdec($b)];
        }
        $this->Error('Unsupported color format: ' . $color);
    }

    /**
     * Decompose the CSS border style (units not supported)
     *
     * @param string|float $width  Border size, not supported in units.
     * @param string       $style  Border style, solid by default.
     * @param string       $color  Border color, null by default. see PDF::_color();
     * @return array [$width, $style, $color|null];
     * @throws Exception
     *
     * @example
     *  $this->_border(1);                           // [1, 'solid', null]
     *  $this->_border('1 solid');                   // [1, 'solid', null]
     *  $this->_border('1 solid #fff');              // [1, 'solid', [255,255,255]]
     *  $this->_border('1 solid #ffff00');           // [1, 'solid', [255,255,0]]
     *  $this->_border('1 solid rgb(255,255,255)');  // [1, 'solid', [255,255,255]]
     */
    protected function _border($width, $style='solid', $color=null)
    {
        if (is_string($width))
        {
            $property = explode(' ', trim($width), 3);
            $width = $property[0] * 1;
            $style = isset($property[1]) ? $property[1] : $style;
            $color = isset($property[2]) ? $property[2] : $color;
        }
        return [$width, $style, $color];
    }

    /**
     * Set the drawing Style
     *
     * @param array $styl
     */
    protected function _styl(&$styl)
    {
        if (isset($styl['font-family']))
        {
            $this->SetFontFamily($styl['font-family']);
        }
        if (isset($styl['font-style']))
        {
            $this->SetFontStyle($styl['font-style']);
        }
        if (isset($styl['font-size']))
        {
            $this->SetFontSize($styl['font-size']*1);
        }
        if (isset($styl['color']))
        {
            list($r, $g, $b) = $this->_color($styl['color']);
            $this->SetTextColor($r, $g, $b);
        }
        if (isset($styl['border']))
        {
            list($border_width, ,$border_color) = $this->_border($styl['border']);
            $styl['border-width'] = $border_width;
            $styl['border-color'] = $border_color;
        }
        if (isset($styl['border-width']))
        {
            $this->SetLineWidth($styl['border-width']);
        }
        if (isset($styl['border-color']))
        {
            list($r, $g, $b) = $this->_color($styl['border-color']);
            $this->SetDrawColor($r, $g, $b);
        }
        if (isset($styl['background-color']))
        {
            list($r, $g, $b) = $this->_color($styl['background-color']);
            $this->SetFillColor($r, $g, $b);
        }
    }

    /**
     * Sets the coordinate points to draw
     *
     * @param array $point   Draws information about an object.
     * @param int   $startX  X offset.
     * @param int   $startY  Y offset.
     * @return array [$x1, $y1, $x2, $y2]
     */
    protected function _position(&$point, $startX=0, $startY=0)
    {
        if (isset($point['x']))
        {
            if (!is_array($point['x']))
                $point['x'] = array($point['x']);
            array_walk($point['x'], function(&$v, $_, $d) { $v += $d; }, $startX);
        }
        else $point['x'] = [$this->x];

        if (isset($point['y']))
        {
            if (!is_array($point['y']))
                $point['y'] = array($point['y']);
            array_walk($point['y'], function(&$v, $_, $d) { $v += $d; }, $startY);
        }
        else $point['y'] = [$this->y];


        $point['x'][] = $this->x;
        $point['y'][] = $this->y;
        list($x1, $x2) = $point['x'];
        list($y1, $y2) = $point['y'];
        $this->SetXY($x1, $y1);
        return [$x1, $y1, $x2, $y2];
    }

    /**
     * Returns a substring of a string
     * @return string
     *
     * @see substr()
     * @see mb_substr()
     */
    protected function _substr($str, $offset, $length=null)
    {
        if ($this->unifontSubset)
        {
            return mb_substr($str, $offset, $length, 'UTF-8');
        }
        else
        {
            return substr($str, $offset, $length);
        }
    }



    public function SetFontFamily($family)
    {
        $this->SetFont($family, $this->FontStyle);
    }

    public function SetFontStyle($style)
    {
        $this->SetFont($this->FontFamily, $style);
    }

    /**
     * Draws cells, supporting row offsets
     *
     * @see Cell()
     * @see MultiCell()
     */
    public function WriteCell($txt='', $border=0, $link='', $styl=array())
    {
        // Output text with automatic or explicit line breaks
        if(!isset($this->CurrentFont))
            $this->Error('No font has been set');

        // 默认样式
        $styl += [
            'width'       => 0,
            'height'      => 0,
            'line-indent' => '0',
            'text-align'  => 'left',
            'padding'     => $this->cMargin
        ];

        // 基准 x/y
        $bx = $this->x;
        $by = $this->y;
        // 单元格宽度
        $w = $styl['width'];
        // 单元格高度
        $h = $styl['height'];
        // 文本行高
        $lh = $styl['line-height'] ?: ceil($this->FontSize);
        // 文本对齐方式
        $align = strtoupper(substr($styl['text-align'], 0, 1));
        // 行缩进
        $indents = array_map('intval', explode(' ', $styl['line-indent']));
        // 内边距
        $padding = $this->cMargin = $styl['padding'] * 1;
        // 是否填充单元格
        $fill = isset($styl['background-color']);
        // 是否换行
        $break = isset($styl['word-break']) && in_array($styl['word-break'], ['break-word', 'break-all']);
        // 字符串过滤
        $txt = str_replace("\r", '', (string)$txt);
        // 字符串分割
        $lines = $break ? explode("\n", $txt) : [str_replace("\n", '', $txt)];
        // 总行数
        $n = 0;
        // 基础行宽（去除padding）
        $line_w = 0;
        // 单元格左右内边距
        $padding2 = 2 * $padding;
        // 存储需要绘制的文本
        $draws = [];

        while(!empty($lines))
        {
            $line = array_shift($lines);
            if ($break)
            {
                if ($w == 0) {
                    $w = $this->w - $this->rMargin - $this->x;
                    $line_w = $w - $padding2;
                }

                // 行文本的绘制起点
                $text_x = $bx + $padding;
                $text_y = $by + $padding + $lh * $n;
                // 行文本的字符长度
                $line_len = $this->unifontSubset ? mb_strlen($line, 'UTF-8') : strlen($line);
                // 行缩进
                $line_indent = abs(isset($indents[$n]) ? $indents[$n] : end($indents));
                // 行文本
                $str = '';
                // 行文本宽度
                $str_width = $line_w;
                // 行文本实际宽度
                $str_w = $line_w - ($align !== 'C' ? $line_indent : 0);


                if ($line !== '')
                {
                    // 每行以第一个字符的宽度为基准
                    $first_cw = $this->GetStringWidth($this->_substr($line, 0, 1));
                    $first_cw_redundancy = $first_cw / 5;
                    if ($str_w < $first_cw) {
                        $str_w = $first_cw; // 最小宽度为第一个字符串的宽度
                    }
                    // 预估当前行的文本长度
                    $end = ceil($str_w / $first_cw);
                    // 空格位置
                    $sep = -1;
                    // 当前文本是否超长
                    $long = false;

                    do {
                        $str = $this->_substr($line, 0, $end);
                        $str_width = $this->GetStringWidth($str);
                        if ($str_width - $first_cw_redundancy > $str_w)
                        {
                            // 最低一个字符
                            if ($end === 1) break;
                            // 字符串过长，需要裁切
                            if ($styl['word-break'] === 'break-all' || !$sep)
                            {
                                // 任意字符都可以切断
                                $end--;
                            }
                            else
                            {
                                // 只有空格可以切断
                                $sep = strrpos($str, ' ');
                                $end = $sep ? $sep : $end-1;
                            }
                            $long = true;
                        }
                        else if (!$long && $str_width < $str_w && $end < $line_len)
                        {
                            // 字符太短，添加字符
                            $end++;
                        }
                        else
                        {
                            break;
                        }
                    } while(1);

                    // 超出的部分文本，转为下一行
                    if ($end < $line_len)
                    {
                        array_unshift($lines, $this->_substr($line, $end));
                    }

                    switch($align)
                    {
                        case 'C':
                            $text_x = $bx + $padding + ($str_w - $str_width) / 2;
                            break;
                        case 'R':
                            $text_x = $bx + $padding + ($str_w - $str_width);
                            break;
                        default:
                            $text_x = $bx + $padding + $line_indent;
                    }
                }

                $draws[] = [
                    'x'     => $text_x,
                    'y'     => $text_y,
                    'w'     => $str_w,
                    'value' => $str,
                ];
            }
            else
            {
                if ($w <= 0) $w = $this->GetStringWidth($line) + $padding2;
                if ($h <= 0) $h = $this->FontSize + $padding2;

                $this->Cell($w, $h, $line, $border, $lh, $align, $fill, $link);
            }
            $n++;
        }

        if (!empty($draws))
        {
            // 绘制背景及边框
            $rect_w = $w;
            $rect_h = $h <= 0 ? $lh * $n + $padding2 : $h;
            $this->Cell($rect_w, $rect_h, '', $border, 0, '', $fill, $link);
            if ($link) {
                $this->Link($bx, $by, $rect_w, $rect_h, $link);
            }

            // 绘制文本
            $this->cMargin = 0;
            foreach ($draws as $item)
            {
                $this->SetXY($item['x'], $item['y']);
                $this->Cell($item['w'], $lh, $item['value']);
            }
            $this->cMargin = $padding;
        }

        return $n;
    }

    /**
     * Draw the mapping
     */
    public function writeMapping(&$mapping)
    {
        // Setting default Properties
        $mapping += [
            'path'     => '',
            'title'    => '-',
            'author'   => '-',
            'subject'  => '-',
            'keywords' => '-',
            'creator'  => date('Y/m/d H:i:s'),
            'fonts'    => [],
            'pages'    => []
        ];
        $mapping['pages'] += [
            'startX'   => 0,
            'startY'   => 0,
            'content'  => []
        ];


        // Load Template
        $count = max(array_keys($mapping['pages']));
        $hasTemplate = false;
        if (isset($mapping['name']))
        {
            $template_file = rtrim($mapping['path'], '\\/') . DIRECTORY_SEPARATOR . $mapping['name'];
            if (file_exists($template_file))
            {
                $count = $this->setSourceFile($template_file);
                $hasTemplate = true;
            }
        }


        // Load the font you want to use
        $fonts = $mapping['fonts'] ?: [];
        foreach ($fonts as $family => $item)
        {
            $uni = $family[0] !== '@';
            foreach ($item as $style => $file)
            {
                $this->AddFont(ltrim($family, '@'), $style, $file, $uni);
            }
        }


        // Set Meta Info
        $this->SetTitle($mapping['title'], true);
        $this->SetAuthor($mapping['author'], true);
        $this->SetSubject($mapping['subject'], true);
        $this->SetKeywords($mapping['keywords'], true);
        $this->SetCreator($mapping['creator'], true);


        // A styl that can be inherited
        $flush_styl = true;
        $inherit_styl = [
            'font-family'      => $this->FontFamily,
            'font-style'       => $this->FontStyle,
            'font-size'        => 8,
            'color'            => '#000000',
            'border-width'     => 0.1,
            'border-color'     => '#000000',
            'background-color' => null
        ];


        $pages = &$mapping['pages'];
        for($i = 1; $i <= $count; $i++)
        {
            // Load and edit the template
            $this->AddPage();
            if ($hasTemplate)
            {
                $pageId = $this->importPage($i);
                $this->useImportedPage($pageId, 0, 0, null, null, true);
            }


            // Write custom content
            if (isset($pages[$i]))
            {
                $page = &$pages[$i];
                $content = &$page['content'];
                foreach ($content as $point)
                {
                    $point += [
                        'type' => 'text',
                        'styl' => []
                    ];

                    if (isset($point['value']) || $point['type'] === 'line' || $point['type'] === 'rect')
                    {
                        // Sets the current writing style
                        $styl = $point['styl'] + [
                            'width'       => 0,
                            'height'      => 0,
                        ] + ($flush_styl ? $inherit_styl : []);
                        $this->_styl($styl);


                        // Set the coordinates
                        list($x, $y, $x2, $y2) = $this->_position($point, $page['startX'], $page['startY']);


                        $fill   = isset($styl['background-color']);
                        $link   = isset($point['link']) ? $point['link'] : '';
                        $border = isset($point['border']) ? $point['border'] : 0;
                        switch($point['type'])
                        {
                            case 'line':
                                $this->Line($x, $y, $x2, $y2);
                                break;
                            case 'link':
                                $this->Link($x, $y, $styl['width'], $styl['height'], $point['value']);
                                break;
                            case 'rect':
                                $this->Rect($x, $y, $styl['width'], $styl['height'], ($border === 1 ? 'D' : '') . ($fill ? 'F' : ''));
                                break;
                            case 'checkbox':
                                if ($point['value'])
                                {
                                    $this->Text($x, $y, is_string($point['value']) ? $point['value'] : '√');
                                }
                                break;
                            case 'image':
                                if (!file_exists($point['value']))
                                {
                                    $this->Error($point['value'] . ' File not found');
                                }
                                $this->Image($point['value'], $x, $y, $styl['width'], $styl['height'], '', $link);
                                break;
                            case 'plain': // plain text
                                $this->Text($x, $y, $point['value']);
                                break;
                            default: // text
                                $this->WriteCell($point['value'], $border, $link, $styl);
                        }


                        $flush_styl = isset($point['inherit_styl']) && !$point['inherit_styl'];
                        if (!$flush_styl)
                        {
                            // Allow inheritance properties
                            foreach ($inherit_styl as $key => $_)
                            {
                                if (isset($styl[$key]))
                                {
                                    $inherit_styl[$key] = $styl[$key];
                                }
                            }
                        }
                    }

                }
            }
        }

        return true;
    }
}
