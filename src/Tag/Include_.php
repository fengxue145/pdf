<?php

namespace fengxue145\pdf\Tag;

class Include_ extends \Mpdf\Tag\Tag
{
	public function open($attr, &$ahtml, &$ihtml)
	{
		$attr += ['SRC' => ''];
		if (empty($attr['SRC']) || !file_exists($attr['SRC'])) {
			throw new \Mpdf\MpdfException('File "' . $attr['SRC'] . '" not found.');
		}
		$content = file_get_contents($attr['SRC']);
		if ($content !== false) {
			$this->mpdf->WriteHTML($content);
		}
	}

	public function close(&$ahtml, &$ihtml)
	{
	}
}
