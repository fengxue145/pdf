<?php

namespace fengxue145\pdf;

class PDF extends \setasign\Fpdi\Tfpdf\Fpdi
{
    static public function DefaultStyle()
    {
        return array(
            'body' => [
                'font-family' => 'helvetica',
                'font-style' => '',
                'font-size' => 12,
                'color' => '#000',
                'text-align' => 'left',
            ],
            'text' => [
                'padding'        => 0.6,
                'border-width'   => 0,
                'text-indent'    => 0,
                'autosize'       => 0,
                'min-font-size'  => 5,
                'word-break'     => 'none', // 可选：break-word | break-all
                'text-align'     => 'left', // 可选：left | center | right
                'vertical-align' => 'top', // 可选：top | middle | bottom
            ]
        );
    }

    /**
     * Sets the coordinate points to draw
     *
     * @param array $point   Draws information about an object.
     * @param int   $startX  X offset.
     * @param int   $startY  Y offset.
     * @return array [$x1, $y1, $x2, $y2]
     */
    protected function _position(&$point, $startX = 0, $startY = 0)
    {
        if (isset($point['x'])) {
            if (!is_array($point['x']))
                $point['x'] = array($point['x']);
            array_walk($point['x'], function (&$v, $_, $d) {
                $v += $d;
            }, $startX);
        } else $point['x'] = [$this->x];

        if (isset($point['y'])) {
            if (!is_array($point['y']))
                $point['y'] = array($point['y']);
            array_walk($point['y'], function (&$v, $_, $d) {
                $v += $d;
            }, $startY);
        } else $point['y'] = [$this->y];


        $point['x'][] = $this->x;
        $point['y'][] = $this->y;
        list($x1, $x2) = $point['x'];
        list($y1, $y2) = $point['y'];
        $this->SetXY($x1, $y1);
        return [$x1, $y1, $x2, $y2];
    }

    /**
     * Draws cells, supporting row offsets
     *
     * @see Cell()
     * @see MultiCell()
     */
    public function WriteCell($txt, $link = '', $css = array())
    {
        $cMargin = $this->cMargin;
        $this->cMargin = 0;

        // 默认样式
        $css += [
            'width' => 0,
            'height' => 0,
            'line-height' => $this->FontSize
        ];

        // 基准 x/y
        $x = $this->x;
        $y = $this->y;
        // 单元格宽度
        $w = $css['width'];
        // 单元格高度
        $h = $css['height'];
        // 行高
        $lh = $css['line-height'];
        // 文本水平对齐方式
        $align = strtoupper(substr($css['text-align'], 0, 1));
        // 文本垂直对齐方式
        $valign = strtoupper(substr($css['vertical-align'], 0, 1));
        // 首行缩进
        $indent = $css['text-indent'];
        // 内边距
        $tp = $css['padding-top'];
        $rp = $css['padding-right'];
        $bp = $css['padding-bottom'];
        $lp = $css['padding-left'];
        // 是否换行
        $break = in_array($css['word-break'], ['break-word', 'break-all']);
        // 字符串过滤
        $txt = str_replace("\r", '', (string)$txt);
        // 字符串分割
        $lines = $break ? explode("\n", $txt) : [str_replace("\n", '', $txt)];
        // 每次缩减字体大小的值
        $step = 0.2;
        // 当前字体大小
        $fs = $css['font-size'];
        // 自适应后的最小字体
        $mfs = max($css['min-font-size'], 2);
        // 内容自适应
        $autosize = $css['autosize'] == 1;
        // 存储需要绘制的文本
        $draws = [];
        // 盒子对象
        $box = [
            'w'  => 0, // 盒子的宽度
            'h'  => 0, // 盒子的高度
            'ow' => 0, // 内容的宽度
            'oh' => 0, // 内容的高度
        ];


        if ($break) {
            while (1) {
                $draws = [];
                $clone_lines = $lines;

                while (($line = array_shift($clone_lines)) !== null) {
                    if ($w > 0 && $line !== '') {
                        // 行文本的字符长度
                        $line_len = mb_strlen($line, 'UTF-8');
                        // 每行以第一个字符的宽度为基准
                        $first_cw = max(1, $this->GetStringWidth(mb_substr($line, 0, 1, 'UTF-8')));
                        $first_cw_redundancy = $first_cw / 5;
                        // 最小宽度为第一个字符串的宽度
                        if ($w < $first_cw) {
                            $w = $first_cw;
                        }
                        // 如果定义了text-indent，则最小宽度为第一个字符的宽度+text-indent
                        if ($align === 'L' && $w < $indent) {
                            $w = $first_cw + $indent;
                        }
                        // 预估当前行的文本长度
                        $end = intval(ceil($w / $first_cw));
                        // 空格位置
                        $sep = -1;
                        // 当前文本是否超长
                        $long = false;

                        do {
                            $str = mb_substr($line, 0, $end, 'UTF-8');
                            // 首行缩进的时候，增加缩进长度
                            $str_width = $this->GetStringWidth($str) + (empty($draws) && $align === 'L' ? $indent : 0);
                            if ($str_width - $first_cw_redundancy > $w) {
                                // 最低一个字符
                                if ($end === 1) break;
                                // 字符串过长，需要裁切
                                if ($css['word-break'] === 'break-all' || !$sep) {
                                    // 任意字符都可以切断
                                    $end--;
                                } else {
                                    // 只有空格可以切断
                                    $sep = mb_strrpos($str, ' ', 0, 'UTF-8');
                                    $end = $sep ? min($sep, $end - 1) : $end - 1;
                                }
                                $long = true;
                            } else if (!$long && $str_width < $w && $end < $line_len) {
                                // 字符太短，添加字符
                                $end++;
                            } else {
                                break;
                            }
                        } while (1);

                        // 超出的部分文本，转为下一行
                        if ($end < $line_len) {
                            array_unshift($clone_lines, mb_substr($line, $end, null, 'UTF-8'));
                        }
                        $draws[] = ['w' => $str_width, 'txt' => $str];
                    } else {
                        $_w = $line === '' ? 0 : $this->GetStringWidth($line);
                        if (empty($draws) && $align === 'L') {
                            $_w += $indent;
                        }
                        $draws[] = ['w' => $_w, 'txt' => $line];
                    }
                }

                // 需要自适应大小
                if (!$autosize || $fs <= $mfs || $h <= 0 || count($draws) * $this->FontSize <= $h) {
                    break;
                }
                $fs = max($mfs, $fs - $step);
                $this->SetFontSize($fs);
            }

            // 如果未定义宽度，则使用最长行的宽度
            if ($w <= 0) {
                $w = max(array_column($draws, 'w'));
            }
            // 计算每一行所占用的高度
            if ($autosize && $h > 0) {
                $lh = count($draws) * $lh > $h ? $h / count($draws) : $lh;
            }
            // 重置每行的宽度和高度
            foreach ($draws as &$v) {
                $v['w'] = $w;
                $v['h'] = $lh;
            }
            // 如果未定义宽度，则计算所有行高度的总和
            $oh = array_sum(array_column($draws, 'h'));
            if ($h <= 0) {
                $h = $oh;
            }

            $box['w'] = $box['ow'] = $w;
            $box['h'] = $h;
            $box['oh'] = $oh;
        }
        // no line break
        else {
            $line = current($lines);

            // 如果高度是0，则高度是line-height
            if ($h <= 0) $h = $lh;

            // 自适应大小需要定义宽度
            if ($autosize && $w > 0) {
                while (1) {
                    $sw = $this->GetStringWidth($line);
                    if ($fs <= $mfs || ($sw < $w && $this->FontSize < $h)) {
                        break;
                    }
                    $fs = max($mfs, $fs - $step);
                    $this->SetFontSize($fs);
                }
                $box['ow'] = $sw;
            }
            // 非自适应且未定义宽度时，使用字符串的长度
            else if ($w <= 0) {
                $box['ow'] = $w = $this->GetStringWidth($line);
            } else {
                $box['ow'] = $this->GetStringWidth($line);
            }

            $box['w'] = $w;
            $box['h'] = $h;
            $box['oh'] = $this->FontSize;
            $draws[] = ['w' => max($box['w'], $box['ow']), 'h' => $box['oh'], 'txt' => $line];
        }

        // 1. 确定整体大小
        $x      = $this->x;
        $y      = $this->y;
        $bw     = isset($css['border-width']) ? $css['border-width'] : 0;
        $rect_w = $box['w'] + $lp + $rp + $bw * 2;
        $rect_h = $box['h'] + $tp + $bp + $bw * 2;

        // 2. 绘制背景及边框
        $this->Cell($rect_w, $rect_h, '', $bw > 0, 0, '', isset($css['background-color']));
        if ($link) {
            $this->Link($x, $y, $rect_w, $rect_h, $link);
        }

        // 3. 绘制内容
        $by = $y + $tp + $bw;
        if ($box['h'] > $box['oh']) {
            if ($valign === 'M') {
                $by += ($box['h'] - $box['oh']) / 2;
            } else if ($valign === 'B') {
                $by += $box['h'] - $box['oh'];
            }
        }
        foreach ($draws as $k => $item) {
            $bx = $x + $lp + $bw;
            // 4. 第一行在居左的情况下，允许 text-indent
            if ($k === 0) {
                if ($align === 'L') {
                    $bx += $indent;
                }
            }

            $this->SetXY($bx, $by);
            $this->Cell($item['w'], $item['h'], $item['txt'], 0, 0, $item['w'] > $box['w'] ? 'L' : $align);
            $by += $item['h'];
        }


        $this->cMargin = $cMargin;
    }

    /**
     * Draw the mapping
     */
    public function WriteMapping(&$mapping)
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
            'pages'    => [],
        ];


        // Load Template
        $templatePages = 0;
        if (isset($mapping['name'])) {
            $template_file = rtrim($mapping['path'], '\\/') . DIRECTORY_SEPARATOR . $mapping['name'];
            if (file_exists($template_file)) {
                $templatePages = $this->setSourceFile($template_file);
            }
        }


        // Load the font you want to use
        foreach ($mapping['fonts'] as $family => $item) {
            $uni = $family[0] !== '@';
            foreach ($item as $style => $file) {
                $this->AddFont(ltrim($family, '@'), $style, $file, $uni);
            }
        }


        // Set Meta Info
        $this->SetTitle($mapping['title'], true);
        $this->SetAuthor($mapping['author'], true);
        $this->SetSubject($mapping['subject'], true);
        $this->SetKeywords($mapping['keywords'], true);
        $this->SetCreator($mapping['creator'], true);


        // Set Margin Info
        if (isset($mapping['margin'])) {
            $margin = [
                'top'    => $this->tMargin,
                'right'  => $this->rMargin,
                'bottom' => $this->bMargin,
                'left'   => $this->lMargin,
            ];
            if (is_numeric($mapping['margin'])) {
                array_walk($margin, function (&$v, $_, $m) {
                    $v = $m;
                }, (float)$mapping['margin']);
            } else if (is_array($mapping['margin'])) {
                $margin = array_merge($margin, $mapping['margin']);
            }

            $this->SetMargins($margin['left'], $margin['top'], $margin['right']);
            $this->SetAutoPageBreak(true, $margin['bottom']);
        }


        // Set Default Style
        $cssManager = new CssManager($this);
        $cssManager->ReadCss(self::DefaultStyle());
        if (isset($mapping['style'])) {
            $cssManager->ReadCss($mapping['style']);
        }

        $count = $mapping['pages'] ? max(array_keys($mapping['pages'])) : 0;
        $count = max($count, $templatePages);
        for ($i = 1; $i <= $count; $i++) {
            // Load and edit the template
            $this->AddPage();
            if ($templatePages > 0 && $i <= $templatePages) {
                $pageId = $this->importPage($i);
                $this->useImportedPage($pageId, 0, 0, null, null, true);
            }

            if (!isset($mapping['pages'][$i])) {
                continue;
            }

            // Write custom content
            $page = &$mapping['pages'][$i];
            $page += ['startX' => 0, 'startY' => 0, 'content' => []];
            $content = &$mapping['pages'][$i]['content'];
            foreach ($content as $node) {
                if (!isset($node['value'])) {
                    continue;
                }

                $node += ['type' => 'text'];
                $cssManager->LoadCss($node);
                $css = $cssManager->PreviewCss($node);

                // Set the coordinates
                list($x, $y, $x2, $y2) = $this->_position($node, $page['startX'], $page['startY']);

                switch ($node['type']) {
                    case 'line':
                        $this->Line($x, $y, $x2, $y2);
                        break;
                    case 'link':
                        $this->Link($x, $y, $css['width'], $css['height'], $node['value']);
                        break;
                    case 'rect':
                        $fill = isset($css['background-color']) ? 'F' : '';
                        $border = isset($css['border-width']) && $css['border-width'] ? 'D' : '';
                        $this->Rect($x, $y, $css['width'], $css['height'], $border . $fill);
                        break;
                    case 'checkbox':
                        if ($node['value']) {
                            $this->Text($x, $y, is_string($node['value']) ? $node['value'] : '√');
                        }
                        break;
                    case 'image':
                        $imagesize = getimagesize($node['value']);
                        if (!$imagesize) {
                            trigger_error('FPDF warning: ' . $node['value'] . ' Unable to find file or not a valid image file', E_USER_WARNING);
                        } else {
                            $link = isset($node['link']) ? $node['link'] : '';
                            $this->Image($node['value'], $x, $y, $css['width'] ?? 0, $css['height'] ?? 0, $node['ext'] ?? image_type_to_extension($imagesize[2], false), $link);
                        }
                        break;
                    case 'plain': // plain text
                        $this->Text($x, $y, $node['value']);
                        break;
                    default: // text
                        if ($node['value'] !== '') {
                            $link = isset($node['link']) ? $node['link'] : '';
                            $this->WriteCell($node['value'], $link, $css);
                        }
                }
            }
        }
    }
}
