<?php

namespace fengxue145\pdf\Tag;

class Template extends \Mpdf\Tag\FormFeed
{
	public function open($attr, &$ahtml, &$ihtml)
	{
        $attr += ['SRC' => '', 'PAGENO' => null];

        if ($attr['SRC'] !== '') {
            $pagecount = $this->mpdf->SetSourceFile($attr['SRC']);
            if (!isset($attr['PAGENO']) || !is_numeric($attr['PAGENO'])) {
                $attr['PAGENO'] = $pagecount;
            }
            
            $pageno = max(1, min(intval($attr['PAGENO']), $pagecount));
            $tplIdx = $this->mpdf->ImportPage($pageno);
            $pagesize = $this->mpdf->GetTemplateSize($tplIdx);
            $w = floor($pagesize['w']);
            $h = floor($pagesize['h']);

            if (!isset($attr['SHEET-SIZE'])) {
                $attr['SHEET-SIZE'] = sprintf("%dmm %dmm", min($w, $h), max($w, $h));
            }
            if (!isset($attr['ORIENTATION'])) {
                $attr['ORIENTATION'] = $w > $h ? 'L' : 'P';
            }

            $this->mpdf->SetPageTemplate($tplIdx);
        } else {
            $this->mpdf->SetPageTemplate('');
        }

        parent::open($attr, $ahtml, $ihtml);
	}
}
