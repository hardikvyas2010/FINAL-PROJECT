<?php

$sheet = $this->excel->setActiveSheetIndex(0);
$sheet->setTitle(mb_strimwidth(lang('hr_export_leaves_title'), 0, 28, "..."));  
$sheet->setCellValue('A3', lang('hr_export_leaves_thead_id'));
$sheet->setCellValue('B3', lang('hr_export_leaves_thead_status'));
$sheet->setCellValue('C3', lang('hr_export_leaves_thead_start'));
$sheet->setCellValue('D3', lang('hr_export_leaves_thead_end'));
$sheet->setCellValue('E3', lang('hr_export_leaves_thead_duration'));
$sheet->setCellValue('F3', lang('hr_export_leaves_thead_type'));

$sheet->getStyle('A3:F3')->getFont()->setBold(true);
$sheet->getStyle('A3:F3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$leaves = $this->leaves_model->getLeavesOfEmployee($id);
$fullname = $this->users_model->getName($id);
$sheet->setCellValue('A1', $fullname);

$line = 4;
foreach ($leaves as $leave) {
    $date = new DateTime($leave['startdate']);
    $startdate = $date->format(lang('global_date_format'));
    $date = new DateTime($leave['enddate']);
    $enddate = $date->format(lang('global_date_format'));
    $sheet->setCellValue('A' . $line, $leave['id']);
    $sheet->setCellValue('B' . $line, lang($leave['status_name']));
    $sheet->setCellValue('C' . $line, $startdate);
    $sheet->setCellValue('D' . $line, $enddate);
    $sheet->setCellValue('E' . $line, $leave['duration']);
    $sheet->setCellValue('F' . $line, $leave['type_name']);
    $line++;
}


foreach(range('A', 'F') as $colD) {
    $sheet->getColumnDimension($colD)->setAutoSize(TRUE);
}

$filename = 'leaves.xls';
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
$objWriter->save('php://output');
