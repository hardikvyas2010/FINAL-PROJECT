<?php


$sheet = $this->excel->setActiveSheetIndex(0);
$sheet->setTitle(mb_strimwidth(lang('hr_export_employees_title'), 0, 28, "..."));  //Maximum 31 characters allowed in sheet title.
$sheet->setCellValue('A1', lang('hr_export_employees_thead_id'));
$sheet->setCellValue('B1', lang('hr_export_employees_thead_firstname'));
$sheet->setCellValue('C1', lang('hr_export_employees_thead_lastname'));
$sheet->setCellValue('D1', lang('hr_export_employees_thead_email'));
$sheet->setCellValue('E1', lang('hr_export_employees_thead_entity'));
$sheet->setCellValue('F1', lang('hr_export_employees_thead_contract'));
$sheet->setCellValue('G1', lang('hr_export_employees_thead_manager'));
$sheet->getStyle('A1:G1')->getFont()->setBold(true);
$sheet->getStyle('A1:G1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$employees = $this->users_model->employeesOfEntity($id, $children);

$line = 2;
foreach ($employees as $employee) {
    $sheet->setCellValue('A' . $line, $employee->id);
    $sheet->setCellValue('B' . $line, $employee->firstname);
    $sheet->setCellValue('C' . $line, $employee->lastname);
    $sheet->setCellValue('D' . $line, $employee->email);
    $sheet->setCellValue('E' . $line, $employee->entity);
    $sheet->setCellValue('F' . $line, $employee->contract);
    $sheet->setCellValue('G' . $line, $employee->manager_name);
    $line++;
}


foreach(range('A', 'G') as $colD) {
    $sheet->getColumnDimension($colD)->setAutoSize(TRUE);
}

$filename = 'employees.xls';
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
$objWriter->save('php://output');
