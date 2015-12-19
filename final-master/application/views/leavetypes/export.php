<?php


$sheet = $this->excel->setActiveSheetIndex(0);
$sheet->setTitle(mb_strimwidth(lang('leavetypes_type_export_title'), 0, 28, "...")); 
$sheet->setCellValue('A1', lang('leavetypes_type_export_thead_id'));
$sheet->setCellValue('B1', lang('leavetypes_type_export_thead_name'));
$sheet->getStyle('A1:B1')->getFont()->setBold(true);
$sheet->getStyle('A1:B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$types = $this->types_model->getTypes();
$line = 2;
foreach ($types as $type) {
    $sheet->setCellValue('A' . $line, $type['id']);
    $sheet->setCellValue('B' . $line, $type['name']);
    $line++;
}


foreach(range('A', 'B') as $colD) {
    $sheet->getColumnDimension($colD)->setAutoSize(TRUE);
}

$filename = 'leave_types.xls';
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
$objWriter->save('php://output');
