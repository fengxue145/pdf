<?php

namespace fengxue145\pdf;

class PDF
{
    protected $mpdf;
    protected static $inline_tags = ['SPAN', 'A', 'SUB', 'SUP', 'ACRONYM', 'BIG', 'SMALL', 'INS', 'S', 'STRIKE', 'DEL', 'STRONG', 'CITE', 'Q', 'EM', 'B', 'I', 'U', 'SAMP', 'CODE', 'KBD', 'TT', 'VAR', 'FONT', 'TIME', 'MARK', 'IMG'];
    protected static $position_keys = ['LEFT', 'RIGHT', 'TOP', 'BOTTOM'];

    public function __construct(array $config = array())
    {
        $this->mpdf = new \Mpdf\Mpdf($config);
        $reflect = new \ReflectionObject($this->mpdf);
        $reflectProperty = $reflect->getProperty('tag');
        $reflectProperty->setAccessible(true);

        $tag = $reflectProperty->getValue($this->mpdf);
        $properties = [
            'mpdf' => null,
            'cache' => null,
            'cssManager' => null,
            'form' => null,
            'otl' => null,
            'tableOfContents' => null,
            'sizeConverter' => null,
            'colorConverter' => null,
            'imageProcessor' => null,
            'languageToFont' => null,
        ];
        foreach ($properties as $name => &$value) {
            $value = get_object_property($tag, $name);
        }

        // Replace the default tag handling class
        //  - Allows new/replacement tag classes
        $reflectProperty->setValue($this->mpdf, new Tag($properties));

        // Add custom tag
        $this->RegisterTag('<include>', 'include_');
        $this->RegisterTag('<template>', 'template');
    }

    public function RegisterTag($tag, $className)
    {
        Tag::setTagClassName(trim($tag, '<>'), $className);

        if (strpos($this->mpdf->enabledtags, $tag) === false) {
            $this->mpdf->enabledtags .= $tag;
        }
    }

    public function SetMeta(array $meta)
    {
        $meta += ['title' => '', 'author' => '', 'subject' => '', 'keywords' => '', 'creator' => ''];
        $this->SetTitle($meta['title']);
        $this->SetAuthor($meta['author']);
        $this->SetSubject($meta['subject']);
        $this->SetKeywords($meta['keywords']);
        $this->SetCreator($meta['creator']);
    }

    public function SetStyleFile(string $file)
    {
        $this->mpdf->WriteHTML('<link rel="stylesheet" type="text/css" href="' . $file . '">', \Mpdf\HTMLParserMode::HEADER_CSS);
    }

    public function SetStyle($style)
    {
        if (is_string($style)) {
            $this->mpdf->WriteHTML($style, \Mpdf\HTMLParserMode::HEADER_CSS);
        } else if (is_array($style)) {
            $str = '';
            foreach ($style as $k => $v) {
                $str .= sprintf("%s{ %s }\n", $k, style2str($v));
            }
            $this->mpdf->WriteHTML($str, \Mpdf\HTMLParserMode::HEADER_CSS);
        }
    }

    public function SetFonts(array $fonts)
    {
        foreach ($fonts as $dir => $fs) {
            $this->mpdf->AddFontDirectory($dir);
            $this->mpdf->fontdata = array_merge($this->mpdf->fontdata, $fs);
            foreach ($fs as $family => $config) {
                if (isset($config['R']) && $config['R']) {
                    $this->mpdf->AddFont($family, 'R');
                }
                if (isset($config['B']) && $config['B']) {
                    $this->mpdf->AddFont($family, 'B');
                }
                if (isset($config['I']) && $config['I']) {
                    $this->mpdf->AddFont($family, 'I');
                }
                if (isset($config['BI']) && $config['BI']) {
                    $this->mpdf->AddFont($family, 'BI');
                }
            }
        }
    }

    public function WriteMap(Map $map)
    {
        // Set document metad information
        $this->mpdf->SetTitle($map->title);
        $this->mpdf->SetAuthor($map->author);
        $this->mpdf->SetSubject($map->subject);
        $this->mpdf->SetKeywords($map->keywords);
        $this->mpdf->SetCreator($map->creator);

        // Fonts that can be used by setting documents
        if (!empty($map->fonts)) {
            $this->SetFonts($map->fonts);
        }

        // Set the default style (1)
        if (!empty($map->default_style_file)) {
            $this->SetStyleFile($map->default_style_file);
        }

        // Set the default style (2)
        if (!empty($map->default_style)) {
            $this->SetStyle($map->default_style);
        }

        foreach ($map->__toArray() as $page) {
            // Set Template
            if (isset($page['template'])) {
                list('sourcefile' => $sourcefile, 'pageno' => $pageno) = $page['template'] + ['sourcefile' => null, 'pageno' => 0];
                if ($sourcefile && is_file($sourcefile)) {
                    $pagecount = $this->mpdf->SetSourceFile($sourcefile);
                    $pageno = max(1, min(intval($pageno) ?: $pagecount, $pagecount));
                    $tplIdx = $this->mpdf->ImportPage($pageno);
                    list('w' => $w, 'h' => $h) = $this->mpdf->GetTemplateSize($tplIdx);
                    if (!isset($page['newformat']) && !isset($page['sheet-size'])) {
                        $page['sheet-size'] = array(min($w, $h), max($w, $h));
                    }
                    if (!isset($page['orientation'])) {
                        $page['orientation'] = $w > $h ? 'L' : 'P';
                    }
                    $this->mpdf->SetPageTemplate($tplIdx);
                }
            }

            // Add Page
            $this->mpdf->AddPageByArray($page);

            // Draw content
            if (isset($page['body'])) {
                $body = $page['body'];
                if (is_array($body)) {
                    $body = $this->Mapping2HTML($body);
                }
                $this->mpdf->WriteHTML($body, \Mpdf\HTMLParserMode::HTML_BODY);
            }
        }
    }

    public function Mapping2HTML(array $mapping, array $pos = array())
    {
        $html = '';
        foreach ($mapping as $item) {
            $item     += ['tag' => 'div', 'attrs' => [], 'children' => [], 'text' => ''];
            $tag      = strtoupper($item['tag']);
            $attrs    = [];
            $children = [];
            $prev_pos = $pos;

            // Elements that are not displayed are ignored
            if (isset($item['hidden']) && $item['hidden'] == true) {
                continue;
            }

            /* --------------- ATTR PRE-PROCESSING --------------- */
            foreach ($item['attrs'] as $k => $v) {
                if (is_array($v)) {
                    $v = $k === 'style' ? style2str($v) : implode(' ', $v);
                } else if (!is_scalar($v) || (is_bool($v) && $v == false)) {
                    continue;
                }
                $k = strtoupper($k);
                if ($k === 'ID' || $k === 'CLASS' || preg_match('/^(HEADER|FOOTER)-STYLE/i', $k)) {
                    $v = trim(strtoupper($v));
                }
                $attrs[$k] = $v;
            }
            /* --------------- END ATTR PRE-PROCESSING --------------- */

            switch ($tag) {
                case 'CHECKBOX':
                    $tag = 'DIV';
                    $item += ['options' => [], 'value' => []];
                    if (!empty($item['options'])) {
                        $value = (array)$item['value'];
                        foreach ($item['options'] as $ck => $cv) {
                            if (in_array($ck, $value) || (isset($cv['checked']) && $cv['checked'] == true)) {
                                $children[$ck] = $cv;
                            }
                        }
                    }
                    $item['children'] = $children;
                    break;

                case 'RADIO':
                    $tag = 'DIV';
                    $item += ['options' => [], 'value' => ''];
                    if (!empty($item['options'])) {
                        $value = $item['value'];
                        foreach ($item['options'] as $ck => $cv) {
                            if ($ck === $value || (isset($cv['checked']) && $cv['checked'] == true)) {
                                $children[$ck] = $cv;
                                break; // Radio has only one
                            }
                        }
                    }
                    $item['children'] = $children;
                    break;

                case 'SELECT':
                    $tag = 'DIV';
                    $item += ['options' => [], 'value' => '', 'column_key' => 'value', 'index_key' => 'key'];
                    if (!empty($item['options'])) {
                        $value = $item['value'];
                        $options = array_column($item['options'], $item['column_key'], $item['index_key']);
                        if (isset($options[$value])) {
                            $item['text'] = (string)$options[$value];
                        }
                    }
                    break;
            }


            $cssManager = get_object_property($this->mpdf, 'cssManager');
            $css = $cssManager->PreviewBlockCSS($tag, $attrs);

            // Elements that are not displayed are ignored
            if (isset($css['DISPLAY']) && $css['DISPLAY'] == 'none') {
                continue;
            }

            if (isset($css['POSITION'])) {
                if ($css['POSITION'] === 'relative') {
                    foreach (self::$position_keys as $k) {
                        if (isset($css[$k])) {
                            if (!isset($pos[$k])) {
                                $pos[$k] = $css[$k];
                            } else {
                                $pos[$k] = $this->MergeSize($k, $pos[$k], $css[$k]);
                            }
                        }
                    }
                    // MPDF only supports absolute and fixed
                    $css['POSITION'] = 'absolute';
                    $attrs['STYLE'] .= 'position:absolute;';
                } else if ($css['POSITION'] === 'absolute' && !empty($pos)) {
                    foreach (self::$position_keys as $k) {
                        if (!isset($pos[$k])) {
                            continue;
                        } else if (!isset($css[$k])) {
                            $size = $pos[$k];
                        } else {
                            $size = $this->MergeSize($k, $pos[$k], $css[$k]);
                        }
                        // reset position
                        $css[$k] = $size;
                        $attrs['STYLE'] .= sprintf('%s:%s;', strtolower($k), $size);
                    }
                }

                // not block
                if (in_array($tag, self::$inline_tags)) {
                    $block_css = 'width:auto;height:auto;border:none;background:none;';
                    $block_css .= 'position:' . $css['POSITION'] . ';';
                    if (isset($css['TOP'])) {
                        $block_css .= 'top:' . $css['TOP'] . ';';
                    }
                    if (isset($css['RIGHT'])) {
                        $block_css .= 'right:' . $css['RIGHT'] . ';';
                    }
                    if (isset($css['BOTTOM'])) {
                        $block_css .= 'bottom:' . $css['BOTTOM'] . ';';
                    }
                    if (isset($css['LEFT'])) {
                        $block_css .= 'left:' . $css['LEFT'] . ';';
                    }
                    $html .= sprintf('<DIV style="%s"><%s%s>%s</%s></DIV>', $block_css, $tag, attr2str($attrs), (string)$item['text'], $tag);
                } else {
                    $html .= sprintf('<%s%s>%s</%s>', $tag, attr2str($attrs), (string)$item['text'], $tag);
                }
            } else {
                $html .= sprintf('<%s%s>%s</%s>', $tag, attr2str($attrs), (string)$item['text'], $tag);
            }
            $html .= PHP_EOL;

            // Recursively process child nodes
            if (!empty($item['children'])) {
                $html .= $this->Mapping2HTML($item['children'], $pos);
                $pos = $prev_pos;
            }
        }
        return $html;
    }

    protected function MergeSize($direction, $sizeA, $sizeB)
    {
        $pattern = '/^(?P<size>[-0-9.,]+)?(?P<unit>[%a-z-]+)?$/';
        $resA = preg_match($pattern, $sizeA, $partsA);
        $resB = preg_match($pattern, $sizeB, $partsB);
        if (!$resA || $sizeA == 'auto') {
            return $sizeB;
        } else if (!$resB || $sizeB == 'auto') {
            return $sizeA;
        } else {
            $unitA = !empty($partsA['unit']) ? $partsA['unit'] : null;
            $unitB = !empty($partsB['unit']) ? $partsB['unit'] : null;
            $sizeA = !empty($partsA['size']) ? (float)$partsA['size'] : 0.0;
            $sizeB = !empty($partsB['size']) ? (float)$partsB['size'] : 0.0;
            if ($unitA == $unitB) {
                return sprintf('%.2f%s', $sizeA + $sizeB, $unitA);
            } else {
                $sizeConverter = get_object_property($this->mpdf, 'sizeConverter');
                $cont_w = $this->mpdf->w;
                $cont_h = $this->mpdf->h;
                if ($direction === 'LEFT' || $direction === 'RIGHT') {
                    $a = $sizeConverter->convert($sizeA, $cont_w, $this->mpdf->FontSize, false);
                    $b = $sizeConverter->convert($sizeB, $cont_w, $this->mpdf->FontSize, false);
                } else {
                    $a = $sizeConverter->convert($sizeA, $cont_h, $this->mpdf->FontSize, false);
                    $b = $sizeConverter->convert($sizeB, $cont_h, $this->mpdf->FontSize, false);
                }
                return sprintf('%.2fmm', $a + $b);
            }
        }
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->mpdf, $name), $arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array(array('\Mpdf\Mpdf', $name), $arguments);
    }
}
