<?php

?>
<h2><?php echo lang('Leave Management System');?></h2>

<p>This page is the action page.</p>

Content passed by the form:
<?php 

echo $this->input->get('txtContent');
?>
<br />


<a href="<?php echo base_url();?>sample-page">Back to the form</a><br />
<a href="<?php echo base_url();?>custom-report">Try the report</a>
