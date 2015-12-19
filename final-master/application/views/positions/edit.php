<?php 

?>

<h2><?php echo lang('positions_edit_title');?><?php echo $position['id']; ?></h2>

<?php echo validation_errors(); ?>

<?php echo form_open('positions/edit/' . $position['id']) ?>

    <label for="name"><?php echo lang('positions_edit_field_name');?></label>
    <input type="text" name="name" id="name" value="<?php echo $position['name']; ?>" autofocus required /><br />

    <label for="description"><?php echo lang('positions_edit_field_description');?></label>
    <textarea type="input" name="description" id="description" /><?php echo $position['description']; ?></textarea>

    <br /><br />
    <button type="submit" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;<?php echo lang('positions_edit_button_update');?></button>
    &nbsp;
    <a href="<?php echo base_url();?>positions" class="btn btn-danger"><i class="icon-remove icon-white"></i>&nbsp;<?php echo lang('positions_edit_button_cancel');?></a>
</form>
