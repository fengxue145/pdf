<?php

namespace fengxue145\pdf;

class CssManager
{
    /**
     * @var PDF
     */
    protected $pdf;

    /**
     * @var array
     */
    protected $css = [];


    public function __construct(PDF $pdf)
    {
        $this->pdf = $pdf;
    }


    public function ReadCss(array $styles)
    {
        foreach ($styles as $token => $style)
        {
            if (preg_match('/^(\.|#)?([a-zA-Z0-9-_]+)$/', strtoupper($token), $matches))
            {
                list(, $type, $name) = $matches;
                if ($type === '#') {
                    $name = 'ID>>' . $name;
                } else if ($type === '.') {
                    $name = 'CLASS>>' . $name;
                }

                $this->_preprocess($style);

                if (isset($this->css[$name])) {
                    $this->css[$name] = array_merge($this->css[$name], $style);
                } else {
                    $this->css[$name] = $style;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function PreviewCss(array $node)
    {
        $t = [];
        $tag = strtoupper($node['type']);
        $style = [];

        // 基础样式
        if (isset($this->css['BODY'])) {
            $style = array_merge($style, $this->css['BODY']);
        }
        if (isset($this->css[$tag])) {
            $style = array_merge($style, $this->css[$tag]);
        }

        // 附加样式
        if (isset($node['id'])) {
            $t[] = 'ID>>' . strtoupper($node['id']);
        }
        if (isset($node['class'])) {
            $class = strtoupper($node['class']);
            if (!is_array($class)) {
                $class = explode(' ', $class);
            }
            foreach ($class as $val) {
                $t[] = 'CLASS>>' . $val;
            }
        }
        if (!empty($t)) {
            $css = [];
            $tokens = array_keys($this->css);
            foreach ($t as $v) {
                $i = array_search($v, $tokens, true);
                if ($i !== false) {
                    $css[$i] = $v;
                }
            }
            ksort($css);
            foreach ($css as $name) {
                $style = array_merge($style, $this->css[$name]);
            }
        }

        // 内链样式
        if (isset($node['style'])) {
            $style = array_merge($style, $node['style']);
        }

        $this->_preprocess($style);
        return $style;
    }

    public function LoadCss(array $node)
    {
        $css = $this->PreviewCss($node);

        // font style
        $this->pdf->SetFont($css['font-family'], $css['font-style'], $css['font-size']*1);

        // font color
        list($r, $g, $b) = $this->_color($css['color']);
        $this->pdf->SetTextColor($r, $g, $b);

        // background color
        if (isset($css['background-color'])) {
            if ($css['background-color'] === 'transparent') {
                $this->FillColor = '0 g';
            } else {
                list($r, $g, $b) = $this->_color($css['background-color']);
                $this->pdf->SetFillColor($r, $g, $b);
            }
        }
        if (isset($css['border-width'])) {
            $this->pdf->SetLineWidth($css['border-width']);
        }
        if (isset($css['border-color'])) {
            list($r, $g, $b) = $this->_color($css['border-color']);
            $this->pdf->SetDrawColor($r, $g, $b);
        }
    }


    protected function _preprocess(array &$style)
    {
        if (isset($style['border'])) {
            list($border_width, ,$border_color) = $this->_border($style['border']);
            $style['border-width'] = $border_width;
            $style['border-color'] = $border_color;
            unset($style['border']);
        }
        if (isset($style['padding'])) {
            $padding = explode(' ', $style['padding']);
            $style['padding-top']    = $padding[0];
            $style['padding-right']  = isset($padding[1]) ? $padding[1] : $style['padding-top'];
            $style['padding-bottom'] = isset($padding[2]) ? $padding[2] : $style['padding-top'];
            $style['padding-left']   = isset($padding[3]) ? $padding[3] : $style['padding-right'];
            unset($style['padding']);
        }
    }

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
        $this->pdf->Error('Unsupported color format: ' . $color);
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
}
