<?php $view = get_view(); ?>
<style type="text/css">
.hide-boxes {
    text-align: center;
}
.ui-accordion .ui-accordion-content{
    padding: 1em 5px;
}
</style>
<?php echo js_tag('vendor/tiny_mce/tiny_mce'); ?>
<?php echo js_tag('items'); ?>
<script type="text/javascript" charset="utf-8">
//<![CDATA[
// TinyMCE hates document.ready.
jQuery(window).load(function () {

    Omeka.Items.tagDelimiter = <?php echo js_escape(get_option('tag_delimiter')); ?>;
    Omeka.Items.enableTagRemoval();
    Omeka.Items.makeFileWindow();
    Omeka.Items.enableSorting();
    Omeka.Items.tagChoices('#tags', <?php echo js_escape(url(array('controller'=>'tags', 'action'=>'autocomplete'), 'default', array(), true)); ?>);

    Omeka.wysiwyg({
        mode: "none",
        forced_root_block: ""
    });

    // Must run the element form scripts AFTER reseting textarea ids.
    jQuery(document).trigger('omeka:elementformload');

    jQuery(function() {
        if(jQuery('#accordion fieldset').length > 1){
            jQuery( "#accordion" ).accordion({
                heightStyle: "content",
                collapsible: true
            });
        }
    });
                            
    <?php
    /*
    Omeka.Items.enableAddFiles(<?php echo js_escape(__('Add Another File')); ?>);
    Omeka.Items.changeItemType(<?php echo js_escape(url("items/change-type")) ?><?php if ($id = metadata('item', 'id')) echo ', '.$id; ?>);
    * 
    */ ?>
});

jQuery(document).bind('omeka:elementformload', function (event) {
    Omeka.ElementsCustom.makeElementControls(event.target, <?php echo js_escape(url('custom-items-batch-form/elements-custom/element-form')); ?>,'Item');
    Omeka.ElementsCustom.enableWysiwyg(event.target);
});
//]]>
</script>

    
    <?php
    $current_element_set = null;
    $tabs = array();
    ?>
    <div id="accordion">
    <?php
    foreach ($elementSets as $key => $elementSet):
        
        $current_element_set = $elementSet->name;        
       
        if($current_element_set != ElementSet::ITEM_TYPE_NAME){

            $tabName = $current_element_set;
            
            $tabContent = '<fieldset id="item-fields-'.$elementSet->id.'">';

            $recordType = get_class($item);
            $elements = get_db()->getTable('Element')->findBySet($current_element_set);

            $filterName = array('ElementSetForm', $recordType, $current_element_set);
            $elements = apply_filters(
                $filterName, 
                $elements,
                array('record_type' => $recordType, 'record' => $item, 'element_set_name' => $current_element_set)
            );

            $options = array();

            if (is_array($elements)) {
                foreach ($elements as $key => $e) {
                    //add_filter(array('ElementInput', $recordType, $current_element_set, $e->name),array('CustomItemsBatchFormPlugin','renameItem'));
                    add_filter(array('ElementForm', $recordType, $current_element_set, $e->name),array('CustomItemsBatchFormPlugin','renameItem'));
                    $tabContent .= get_view()->elementForm($e, $item, $options);
                }
            } else {
                add_filter(array('ElementForm', $recordType, $current_element_set, $elements->name),array('CustomItemsBatchFormPlugin','renameItem'));
                $tabContent = get_view()->elementForm($elements, $item, $options);
            }

            $tabContent .= '</fieldset>';
            $tabs[$tabName] = $tabContent;
        }            

    endforeach; ?>
        
<?php $tabs = apply_filters('custom_items_batch_form_tabs', $tabs, array('item' => $item)); ?>
<?php
    foreach($tabs as $tabName => $tabContent){
        if($tabName == 'Dublin Core'){
            $tabName = 'Dublin Core Metadatas standards';
        }
        ?>
        <h2><?php echo __($tabName); ?></h2>
        <?php
        echo $tabContent;
    }            
?>         
</div>
