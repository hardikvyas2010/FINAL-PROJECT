<?php


$sheet = $this->excel->setActiveSheetIndex(0);
$sheet->setTitle(mb_strimwidth(lang('hr_export_overtime_title'), 0, 28, "..."));  
$sheet->setCellValue('A3', lang('hr_export_overtime_thead_id'));
$sheet->setCellValue('B3', lang('hr_export_overtime_thead_status'));
$sheet->setCellValue('C3', lang('hr_export_overtime_thead_date'));
$sheet->setCellValue('D3', lang('hr_export_overtime_thead_duration'));
$sheet->setCellValue('E3', lang('hr_export_overtime_thead_cause'));
$sheet->getStyle('A3:E3')->getFont()->setBold(true);
$sheet->getStyle('A3:E3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


$requests = $this->overtime_model->getExtrasOfEmployee($id);

$fullname = $this->users_model->getName($id);
$sheet->setCellValue('A1', $fullname);

$line = 4;
foreach ($requests as $request) {
    $date = new DateTime($request['date']);
    $startdate = $date->format(lang('global_date_format'));
    $sheet->setCellValue('A' . $line, $request['id']);
    $sheet->setCellValue('B' . $line, lang($request['status_name']));
    $sheet->setCellValue('C' . $line, $startdate);
    $sheet->setCellValue('D' . $line, $request['duration']);
    $sheet->setCellValue('E' . $line, $request['cause']);
    $line++;
}


foreach(range('A', 'E') as $colD) {
    $sheet->getColumnDimension($colD)->setAutoSize(TRUE);
}

$filename = 'overtime.xls';
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
$objWriter->save('php://output');
