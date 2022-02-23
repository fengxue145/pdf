<?php

require_once '../../vendor/autoload.php';

$pdf = new \fengxue145\pdf\PDF();
$pdf->SetImportUse();
$pdf->WriteHTML(file_get_contents('./index.html'));
$pdf->DeletePages(1);
$pdf->Output();
