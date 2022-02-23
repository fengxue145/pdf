
这是一个PHP库，基于 [mPDF](https://github.com/mpdf/mpdf) 。在其基础上稍微增加一些功能。


## 安装
```
$ composer require fengxue145/pdf
```


## 用法
参见 [mPDF](https://github.com/mpdf/mpdf)



## 库修改

使用 `PHP::Reflection` 替换了 mPDF 库的 `\Mpdf\Tag` 类，重写了 `getTagClassName` 方法，并新增 `setTagClassName` 方法，允许加入自定义标签处理类。

案例：
``` php
<?php
require_once __DIR__ . '/vendor/autoload.php';

class Red extends \Mpdf\Tag\InlineTag
{
    public function open($attr, &$ahtml, &$ihtml)
	{
        $attr += ['STYLE' => ''];
        $attr['STYLE'] .= 'color:red;';
        parent::open($attr, $ahtml, $ihtml);
    }
}

$pdf = new \fengxue145\pdf\PDF();
$pdf->RegisterTag('<red>', '\Red');
$pdf->WriteHTML('<red>Hello World</red>');
$pdf->Output();
```


## 新增标签

### &lt;template&gt;

作用：允许使用PDF模板。继承于 &lt;pagebreak&gt; 标签，在其基础上增加两个 `src` 和 `pageno` 两个属性。

属性：
- `src`: PDF模板文件路径（可选）
- `pageno`: 使用的PDF模板文件页码（可选，默认最后一页）

案例：
``` html
<template src="./template.pdf" pageno="1">
    <div>Hello World</div>
</template>
```

### &lt;include&gt;

作用：引入其他html文件。

属性：
- `src`: html文件的路径

案例：
``` html
<include src="./header.html"/>
<div>Hello World</div>
<include src="./footer.html"/>
```


## 新增方法

### RegisterTag($tag, $className)

作用：注册/覆盖标签的处理类。

参数：
- `$tag`: string

    标签名称。

- `$className`: string

    标签处理类名称。如果是内部（mPDF）的标签处理类，只需要填写类名即可(不含命名空间); 若是外部的类，需填写完整的类名称（含命名空间）。

案例：
``` php
<?php
require_once __DIR__ . '/vendor/autoload.php';

class Red extends \Mpdf\Tag\InlineTag
{
    public function open($attr, &$ahtml, &$ihtml)
	{
        $attr += ['STYLE' => ''];
        $attr['STYLE'] .= 'color:red;';
        parent::open($attr, $ahtml, $ihtml);
    }
}

$pdf = new \fengxue145\pdf\PDF();
$pdf->RegisterTag('<red>', '\Red');
$pdf->WriteHTML('<red>Hello World</red>');
$pdf->Output();
```


### SetMeta($meta)

作用：设置PDF文档元信息。

参数：
- `$meta`: array

    PDF元信息数组 `title` `author` `subject` `keywords` `creator`。
    详见 `mPDF::SetAuthor()` `mPDF::SetCreator()` `mPDF::SetKeywords()` `mPDF::SetSubject()` `mPDF::SetTitle()`


### SetStyleFile($file)

作用：添加默认样式。

参数：
- `$file`: string

    CSS样式文件路径。详见 `mPDF::WriteHTML()`


### SetStyle($style)

作用：添加默认样式。

参数：
- `$style`: string|array

    CSS样式。详见 `mPDF::WriteHTML()`


### SetFonts($fonts)

作用：添加字体定义

参数：
- `$fonts`: array

    字体定义数组。详见 `mPDF::AddFontDirectory()` `mPDF::AddFont()`

案例：
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$pdf = new \fengxue145\pdf\PDF();
$pdf->SetFonts([
    '/usr/local/fonts' => [
        'frutiger' => [
            'R' => 'Frutiger-Normal.ttf',
            'I' => 'FrutigerObl-Normal.ttf',
        ]
    ]
]);
$pdf->WriteHTML('<span style="font-family: frutiger">Hello World</span>');
$pdf->Output();
```


### WriteMap($map)

作用：将HTML抽象化成数组，并将其写入PDF。

详细用法参考：example/mapping/index.php


### Mapping2HTML(array $mapping, $pos = array())

作用：将 mapping 结构数组转成 `HTML`。
