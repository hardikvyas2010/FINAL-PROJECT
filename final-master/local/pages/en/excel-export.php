<?php

$title = 'Types';
$chartTitle = 'Distribution of leave types';
$label = 'Leave type';
$value = 'Number of days';
$requests = 'Requests';

$ci = get_instance();
$ci->load->library('excel');
$sheet = $ci->excel->setActiveSheetIndex(0);
$sheet->setTitle($title);
$sheet->setCellValue('A1', $label);
$sheet->setCellValue('B1', $value);
$sheet->setCellValue('C1', $requests);
$sheet->getStyle('A1:C1')->getFont()->setBold(true);
$sheet->getStyle('A1:C1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$ci->load->model('organization_model');
$entity = ($this->input->get('txtEntityID', TRUE) != FALSE)? $this->input->get('txtEntityID', TRUE) : 0;
$include_children = TRUE;
$include_children = filter_var($this->input->get('chkIncludeChildren'), FILTER_VALIDATE_BOOLEAN);
$users = $ci->organization_model->allEmployees($entity, $include_children);
$ids = array(0);
foreach ($users as $user) {
    array_push($ids, (int) $user->id);
}

$this->db->select('count(*) as number, sum(duration) as duration', FALSE);
$this->db->select('types.name as type_name');
$this->db->from('leaves');
$this->db->join('types', 'leaves.type = types.id');
$this->db->where('leaves.status', 3);
if ($this->input->get('cboYear', TRUE) === FALSE) {
    $this->db->where('YEAR(startdate) = YEAR(CURDATE())');
} else {
    $this->db->where('YEAR(startdate) = ' . $this->db->escape($this->input->get('cboYear', TRUE)));
}
$this->db->where_in('leaves.employee', $ids);
$this->db->group_by('type'); 
$this->db->order_by('number', 'desc');
$rows = $this->db->get()->result_array();

$line = 2;
foreach ($rows as $row) {
    $sheet->setCellValue('A' . $line, $row['type_name']);
    $sheet->setCellValue('B' . $line, $row['duration']);
    $sheet->setCellValue('C' . $line, $row['number']);
    $line++;
}
//Autofit
foreach(range('A', 'C') as $colD) {
    $sheet->getColumnDimension($colD)->setAutoSize(TRUE);
}

$dataseriesLabels1 = array(
	new PHPExcel_Chart_DataSeriesValues('String', $title . '!$A$1', null, 1),
);

$xAxisTickValues1 = array(
	new PHPExcel_Chart_DataSeriesValues('String', $title . '!$A$2:$A$' . $line, null, 4),
);

$dataSeriesValues1 = array(
	new PHPExcel_Chart_DataSeriesValues('Number', $title . '!$B$2:$B$' . $line, null, 4),
);

$series1 = new PHPExcel_Chart_DataSeries(
	PHPExcel_Chart_DataSeries::TYPE_PIECHART,			
	PHPExcel_Chart_DataSeries::GROUPING_STANDARD,	
	range(0, count($dataSeriesValues1)-1),			
	$dataseriesLabels1,					
	$xAxisTickValues1,					
	$dataSeriesValues1						
);

$layout1 = new PHPExcel_Chart_Layout();
$layout1->setShowVal(TRUE);
$layout1->setShowPercent(TRUE);
$plotarea1 = new PHPExcel_Chart_PlotArea($layout1, array($series1));
$legend1 = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_RIGHT, null, false);
$title1 = new PHPExcel_Chart_Title($chartTitle);

$chart1 = new PHPExcel_Chart(
	'chart1',		
	$title1,		
	$legend1,		
	$plotarea1,	
	true,		
	0,		
	null,		
	null		
);

$chart1->setTopLeftPosition('E3');
$chart1->setBottomRightPosition('K20');
$sheet->addChart($chart1);

$filename = 'excel-export.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($ci->excel, 'Excel2007');
$objWriter->setIncludeCharts(TRUE);
$objWriter->save('php://output');
