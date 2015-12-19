<?php 


CI_Controller::get_instance()->load->helper('language');
$this->lang->load('menu', $language);?>

<?php if ($this->config->item('ldap_enabled') == FALSE) { ?>
<div id="frmChangeMyPwd" class="modal hide fade">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
         <h3><?php echo lang('menu_password_popup_title');?></h3>
    </div>
    <div class="modal-body" id="frmChangeMyPwdBody">
        <img src="<?php echo base_url();?>assets/images/loading.gif">
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal"><?php echo lang('menu_password_popup_button_cancel');?></button>
    </div>
</div>

<script type="text/javascript">
    $(function () {
    
        $("#cmdChangePassword").click(function() {
            $("#frmChangeMyPwd").modal('show');
            $("#frmChangeMyPwdBody").load('<?php echo base_url();?>users/reset/<?php echo $user_id; ?>');
        });
        
    });
</script>
<?php } ?>

<div id="wrap">
<div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
      
            <div class="nav-responsive">
                <ul class="nav">
                    <li><a href="<?php echo base_url();?>leaves" title="<?php echo lang('menu_leaves_list_requests');?>"><i class="icon-list icon-white"></i></a></li>

              <?php if ($is_hr == TRUE) { ?>
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo lang('menu_admin_title');?> <b class="caret"></b></a>
                  <ul class="dropdown-menu">
                    <li><a href="<?php echo base_url();?>users"><?php echo lang('menu_admin_list_users');?></a></li>
                    <li><a href="<?php echo base_url();?>users/create"><?php echo lang('menu_admin_add_user');?></a></li>
                    <li class="divider"></li>
                    <li class="nav-header"><?php echo lang('menu_hr_leaves_type_divider');?></li>
                    <li><a href="<?php echo base_url();?>leavetypes"><?php echo lang('menu_hr_list_leaves_type');?></a></li>
                    <li class="divider"></li>
                    <li class="nav-header"><?php echo lang('menu_admin_settings_divider');?></li>
                    <li><a href="<?php echo base_url();?>admin/settings"><?php echo lang('menu_admin_settings');?></a></li>
                  </ul>
                </li>
              <?php } ?>

              <?php if ($is_hr == TRUE) { ?>
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo lang('menu_hr_title');?> <b class="caret"></b></a>
                  <ul class="dropdown-menu">
                    <li class="nav-header"><?php echo lang('menu_hr_employees_divider');?></li>
                    <li><a href="<?php echo base_url();?>hr/employees"><?php echo lang('menu_hr_list_employees');?></a></li>
                    <li><a href="<?php echo base_url();?>organization"><?php echo lang('menu_hr_list_organization');?></a></li>
                    <!--<li><a href="<?php echo base_url();?>entitleddays/organization"><?php echo lang('menu_hr_list_entitlements');?></a></li>//-->
                    <li class="divider"></li>
                    <li class="nav-header"><?php echo lang('menu_hr_contracts_divider');?></li>
                  
                    <li><a href="<?php echo base_url();?>positions"><?php echo lang('menu_hr_list_positions');?></a></li>
                    <li class="divider"></li>
                    <li class="nav-header"><?php echo lang('menu_hr_reports_divider');?></li>
                   
                    <li><a href="<?php echo base_url();?>reports/leaves"><?php echo lang('menu_hr_report_leaves');?></a></li>
                    <li><a href="<?php echo base_url();?>reports"><?php echo lang('menu_hr_reports_divider');?></a></li>
                  </ul>
                </li>
              <?php } ?>

             

                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo lang('menu_requests_title');?> <b class="caret"></b></a>
                  <ul class="dropdown-menu">
                    <li class="nav-header"><?php echo lang('menu_requests_leaves');?></li>
                    <li><a href="<?php echo base_url();?>leaves/counters"><?php echo lang('menu_leaves_counters');?></a></li>
                    <li><a href="<?php echo base_url();?>leaves"><?php echo lang('menu_leaves_list_requests');?></a></li>
                    <li><a href="<?php echo base_url();?>leaves/create"><?php echo lang('menu_leaves_create_request');?></a></li>
                    <?php if ($this->config->item('disable_overtime') == FALSE) { ?>
                    <li class="divider"></li>
                    <li class="nav-header"><?php echo lang('menu_requests_overtime');?></li>
                    <li><a href="<?php echo base_url();?>extra"><?php echo lang('menu_requests_list_extras');?></a></li>
                    <li><a href="<?php echo base_url();?>extra/create"><?php echo lang('menu_requests_request_extra');?></a></li>
                    <?php } ?>
                  </ul>
                </li>

                
              </ul>

                <ul class="nav pull-right">
                    <a href="<?php echo base_url();?>users/myprofile" class="brand"><?php echo $fullname;?></a>
                    <li><a href="<?php echo base_url();?>users/myprofile" title="<?php echo lang('menu_banner_tip_myprofile');?>"><i class="icon-user icon-white"></i></a></li>
                    <?php if ($this->config->item('ldap_enabled') == FALSE) { ?>
                    <li><a href="#" id="cmdChangePassword" title="<?php echo lang('menu_banner_tip_reset');?>"><i class="icon-lock icon-white"></i></a></li>
                    <?php } ?>
                    <li><a href="<?php echo base_url();?>session/logout" title="<?php echo lang('menu_banner_logout');?>"><i class="icon-off icon-white"></i></a></li>
                </ul>
            </div>
      </div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid"><div class="span12">


                <div class="row-fluid"><div class="span12">&nbsp;</div></div>
                <div class="row-fluid"><div class="span12">&nbsp;</div></div>