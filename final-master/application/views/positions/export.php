<?php
/

$sheet = $this->excel->setActiveSheetIndex(0);
$sheet->setTitle(mb_strimwidth(lang('positions_export_title'), 0, 28, "...")); 
$sheet->setCellValue('A1', lang('positions_export_thead_id'));
$sheet->setCellValue('B1', lang('positions_export_thead_name'));
$sheet->setCellValue('C1', lang('positions_export_thead_description'));
$sheet->getStyle('A1:C1')->getFont()->setBold(true);
$sheet->getStyle('A1:C1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$positions = $this->positions_model->getPositions();
$line = 2;
foreach ($positions as $position) {
    $sheet->setCellValue('A' . $line, $position['id']);
    $sheet->setCellValue('B' . $line, $position['name']);
    $sheet->setCellValue('C' . $line, $position['description']);
    $line++;
}


foreach(range('A', 'C') as $colD) {
    $sheet->getColumnDimension($colD)->setAutoSize(TRUE);
}

$filename = 'positions.xls';
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
$objWriter->save('php://output');
