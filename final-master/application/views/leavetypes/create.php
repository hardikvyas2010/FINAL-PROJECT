<?php 

?>

<?php $attributes = array('id' => 'frmCreateLeaveType');
echo form_open('leavetypes/create', $attributes); ?>
    <label for="name"><?php echo lang('leavetypes_popup_create_field_name');?></label>
    <input type="text" name="name" id="name" pattern=".{1,}" required />
    <br />
    <button id="send" class="btn btn-primary"><?php echo lang('leavetypes_popup_create_button_create');?></button>
</form>

