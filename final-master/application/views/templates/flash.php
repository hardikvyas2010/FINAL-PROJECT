<?php 

?>
<?php if($this->session->flashdata('msg')){ ?>
<div class="alert fade in" id="flashbox">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <?php echo $this->session->flashdata('msg'); ?>
</div>
 
<script type="text/javascript">

$(document).ready(function() {
    $("#flashbox").alert();
});
</script>
<?php } ?>
