<?php

$employee_name =  $employee['firstname'] . ' ' . $employee['lastname'];
$contract = $this->contracts_model->getContracts($employee['contract']);
if (!empty($contract)) {
    $contract_name = $contract['name'];
} else {
    $contract_name = '';
}


if ($month == 0) $month = date('m', strtotime('last month'));
if ($year == 0) $year = date('Y', strtotime('last month'));
$total_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$start = sprintf('%d-%02d-01', $year, $month);
$lastDay = date("t", strtotime($start));    
$end = sprintf('%d-%02d-%02d', $year, $month, $lastDay);

$non_working_days = $this->dayoffs_model->lengthDaysOffBetweenDates($employee['contract'], $start, $end);
$opened_days = $total_days - $non_working_days;
$month_name = lang(date('F', strtotime($start)));


$linear = $this->leaves_model->linear($id, $month, $year, FALSE, FALSE, TRUE, FALSE);
$leave_duration = $this->leaves_model->monthlyLeavesDuration($linear);
$work_duration = $opened_days - $leave_duration;
$leaves_detail = $this->leaves_model->monthlyLeavesByType($linear);

$summary = $this->leaves_model->getLeaveBalanceForEmployee($id, FALSE, $end);


$sheet = $this->excel->setActiveSheetIndex(0);
$sheet->setTitle(mb_strimwidth(lang('hr_presence_title'), 0, 28, "..."));  

$sheet->setCellValue('A1', lang('hr_presence_employee'));
$sheet->setCellValue('A2', lang('hr_presence_month'));
$sheet->setCellValue('A3', lang('hr_presence_days'));
$sheet->setCellValue('A4', lang('hr_presence_contract'));
$sheet->setCellValue('A5', lang('hr_presence_working_days'));
$sheet->setCellValue('A6', lang('hr_presence_non_working_days'));
$sheet->setCellValue('A7', lang('hr_presence_work_duration'));
$sheet->setCellValue('A8', lang('hr_presence_leave_duration'));
$sheet->getStyle('A1:A8')->getFont()->setBold(true);

$sheet->setCellValue('B1', $employee_name);
$sheet->setCellValue('B2', $month_name);
$sheet->setCellValue('B3', $total_days);
$sheet->setCellValue('B4', $contract_name);
$sheet->setCellValue('B5', $opened_days);
$sheet->setCellValue('B6', $non_working_days);
$sheet->setCellValue('B7', $work_duration);
$sheet->setCellValue('B8', $leave_duration);

if (count($leaves_detail) > 0) {
    $line = 9;
    foreach ($leaves_detail as $leaves_type_name => $leaves_type_sum) {
        $sheet->setCellValue('A' . $line, $leaves_type_name);
        $sheet->setCellValue('B' . $line, $leaves_type_sum);
        $sheet->getStyle('A' . $line)->getAlignment()->setIndent(2);
        $line++;
    }
}


$start = $year . '-' . $month . '-' . '1';    
$lastDay = date("t", strtotime($start));   
for ($ii = 1; $ii <=$lastDay; $ii++) {
    $dayNum = date("N", strtotime($year . '-' . $month . '-' . $ii));
    $col = $this->excel->column_name(3 + $ii);

    $sheet->setCellValue($col . '11', $ii);
   
    switch ($dayNum)
    {
        case 1: $sheet->setCellValue($col . '10', lang('calendar_monday_short')); break;
        case 2: $sheet->setCellValue($col . '10', lang('calendar_tuesday_short')); break;
        case 3: $sheet->setCellValue($col . '10', lang('calendar_wednesday_short')); break;
        case 4: $sheet->setCellValue($col . '10', lang('calendar_thursday_short')); break;
        case 5: $sheet->setCellValue($col . '10', lang('calendar_friday_short')); break;
        case 6: $sheet->setCellValue($col . '10', lang('calendar_saturday_short')); break;
        case 7: $sheet->setCellValue($col . '10', lang('calendar_sunday_short')); break;
    }
}

$col = $this->excel->column_name(3 + $lastDay);
$sheet->getStyle('C8:' . $col . '9')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


$styleBox = array(
    'borders' => array(
        'top' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        ),
        'bottom' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        )
    )
  );

$dayBox =  array(
    'borders' => array(
        'left' => array(
            'style' => PHPExcel_Style_Border::BORDER_DASHDOT,
            'rgb' => '808080'
        ),
        'right' => array(
            'style' => PHPExcel_Style_Border::BORDER_DASHDOT,
            'rgb' => '808080'
        )
    )
 );


$styleBgPlanned = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => 'DDD')
    )
);
$styleBgRequested = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => 'F89406')
    )
);
$styleBgAccepted = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => '468847')
    )
);
$styleBgRejected = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => 'FF0000')
    )
);
$styleBgDayOff = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => '000000')
    )
);

$line = 12;
$col = $this->excel->column_name($lastDay + 3);
$sheet->getStyle('D' . $line . ':' . $col . ($line + 1))->applyFromArray($styleBox);


$dayNum = 0;
foreach ($linear->days as $day) {
    $dayNum++;
    $col = $this->excel->column_name(3 + $dayNum);
    if (strstr($day->display, ';')) {
        $statuses = explode(";", $day->status);
        $types = explode(";", $day->type);
           
          $sheet->getComment($col . $line)->getText()->createTextRun($types[0]);
          $sheet->getComment($col . ($line + 1))->getText()->createTextRun($types[1]);
          switch (intval($statuses[1]))
          {
            case 1: $sheet->getStyle($col . $line)->applyFromArray($styleBgPlanned); break;  
            case 2: $sheet->getStyle($col . $line)->applyFromArray($styleBgRequested); break;  
            case 3: $sheet->getStyle($col . $line)->applyFromArray($styleBgAccepted); break;  
            case 4: $sheet->getStyle($col . $line)->applyFromArray($styleBgRejected); break;  
            case '5': $sheet->getStyle($col . $line)->applyFromArray($styleBgDayOff); break;    
            case '6': $sheet->getStyle($col . $line)->applyFromArray($styleBgDayOff); break;   
          }
          switch (intval($statuses[0]))
          {
            case 1: $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgPlanned); break;  
            case 2: $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgRequested); break; 
            case 3: $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgAccepted); break;  
            case 4: $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgRejected); break;  
            case '5': $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgDayOff); break;    
            case '6': $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgDayOff); break;    
          }
    } else {
        switch ($day->display) {
            case '1':   
                    $sheet->getComment($col . $line)->getText()->createTextRun($day->type);
                    $sheet->getComment($col . ($line + 1))->getText()->createTextRun($day->type);
                    switch ($day->status)
                    {
                        
                        case 1: $sheet->getStyle($col . $line . ':' . $col . ($line + 1))->applyFromArray($styleBgPlanned); break;  
                        case 2: $sheet->getStyle($col . $line . ':' . $col . ($line + 1))->applyFromArray($styleBgRequested); break; 
                        case 3: $sheet->getStyle($col . $line . ':' . $col . ($line + 1))->applyFromArray($styleBgAccepted); break;  
                        case 4: $sheet->getStyle($col . $line . ':' . $col . ($line + 1))->applyFromArray($styleBgRejected); break; 
                    }
                    break;
            case '2':   
                $sheet->getComment($col . $line)->getText()->createTextRun($day->type);
                switch ($day->status)
                  {
                      case 1: $sheet->getStyle($col . $line)->applyFromArray($styleBgPlanned); break;  // Planned
                      case 2: $sheet->getStyle($col . $line)->applyFromArray($styleBgRequested); break;  // Requested
                      case 3: $sheet->getStyle($col . $line)->applyFromArray($styleBgAccepted); break;  // Accepted
                      case 4: $sheet->getStyle($col . $line)->applyFromArray($styleBgRejected); break;  // Rejected
                  }
                break;
            case '3':  
                $sheet->getComment($col . ($line + 1))->getText()->createTextRun($day->type);
                switch ($day->status)
                  {
                      case 1: $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgPlanned); break; 
                      case 2: $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgRequested); break; 
                      case 3: $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgAccepted); break; 
                      case 4: $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgRejected); break; 
                  }
                break;
            case '4': 
                $sheet->getStyle($col . $line . ':' . $col . ($line + 1))->applyFromArray($styleBgDayOff);
                $sheet->getComment($col . $line)->getText()->createTextRun($day->type);
                $sheet->getComment($col . ($line + 1))->getText()->createTextRun($day->type);
                break;
            case '5': 
                $sheet->getStyle($col . $line)->applyFromArray($styleBgDayOff);
                $sheet->getComment($col . $line)->getText()->createTextRun($day->type);
                break;
            case '6':   
                $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgDayOff);
                $sheet->getComment($col . ($line + 1))->getText()->createTextRun($day->type);
                break;
        }
      }
}


for ($ii = 1; $ii <=$lastDay; $ii++) {
    $col = $this->excel->column_name($ii + 3);
    $sheet->getStyle($col . '10:' . $col . '13')->applyFromArray($dayBox);
    $sheet->getColumnDimension($col)->setAutoSize(TRUE);
}
$sheet->getColumnDimension('A')->setAutoSize(TRUE);
$sheet->getColumnDimension('B')->setAutoSize(TRUE);


$sheet->setCellValue('C16', lang('hr_summary_thead_type'));
$sheet->setCellValue('J16', lang('hr_summary_thead_available'));
$sheet->setCellValue('P16', lang('hr_summary_thead_taken'));
$sheet->setCellValue('V16', lang('hr_summary_thead_entitled'));
$sheet->setCellValue('AB16', lang('hr_summary_thead_description'));
$sheet->getStyle('C16:AH16')->getFont()->setBold(true);
$sheet->mergeCells('C16:I16');
$sheet->mergeCells('J16:O16');
$sheet->mergeCells('P16:U16');
$sheet->mergeCells('V16:AA16');
$sheet->mergeCells('AB16:AK16');

$line = 17;
foreach ($summary as $key => $value) {
    $sheet->setCellValue('C' . $line, $key);
    $sheet->setCellValue('J' . $line, ((float) $value[1] - (float) $value[0]));
    if ($value[2] == '') {
        $sheet->setCellValue('P' . $line, ((float) $value[0]));
    } else {
        $sheet->setCellValue('P' . $line, '-');
    }
    if ($value[2] == '') {
        $sheet->setCellValue('V' . $line, ((float) $value[1]));
    } else {
        $sheet->setCellValue('V' . $line, '-');
    }
    $sheet->setCellValue('AB' . $line, $value[2]);

    $sheet->getStyle('C' . $line . ':AK' . $line)->applyFromArray($styleBox);
    $sheet->mergeCells('C' . $line . ':I' . $line);
    $sheet->mergeCells('J' . $line . ':O' . $line);
    $sheet->mergeCells('P' . $line . ':U' . $line);
    $sheet->mergeCells('V' . $line . ':AA' . $line);
    $sheet->mergeCells('AB' . $line . ':AK' . $line);

    $line++;
}


$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setFitToPage(true);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);

$filename = 'presence.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
$objWriter->save('php://output');
