<?php 

?>

<div class="input-append">
<input type="text" placeholder="<?php echo lang('organization_select_field_search_placeholder');?>" id="txtSearch" />
<button id="cmdSearchOrg" class="btn btn-primary"><?php echo lang('organization_select_button_search');?></button>
</div>

<div style="text-align: left;" id="organization"></div>

<link rel="stylesheet" href='<?php echo base_url(); ?>assets/jsTree/themes/default/style.css' type="text/css" media="screen, projection" />
<script type="text/javascript" src="<?php echo base_url(); ?>assets/jsTree/jstree.min.js"></script>

<script type="text/javascript">
    $(function () {
        
        $("#cmdSearchOrg").click(function () {
            $("#organization").jstree("search", $("#txtSearch").val());
        });
        
        $('#organization').jstree({
            rules : {
                deletable  : false,
                creatable  : false,
                draggable  : false,
                dragrules  : false,
                renameable : false
              },
            core : {
              multiple : false,
              data : {
                url : function (node) {
                  return node.id === '#' ? 
                    '<?php echo base_url(); ?>organization/root' : 
                    '<?php echo base_url(); ?>organization/children';
                },
                'data' : function (node) {
                  return { 'id' : node.id };
                }
              },
              'check_callback' : true
            },
            plugins: [ "search", "state", "sort" ]
        });
    });
</script>
