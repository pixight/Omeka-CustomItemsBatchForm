<?php
/**
* @version $Id$
* @copyright pxg, 2014
* @license http://www.gnu.org/licenses/gpl-3.0.txt
* @package CustomItemsBatchForm
*/


/**
* *
* @package Omeka\Plugins\CustomItemsBatchForm
*/

class CustomItemsBatchFormPlugin extends Omeka_Plugin_AbstractPlugin
{
    
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array('install', 'uninstall','admin_head','admin_items_batch_edit_form','items_batch_edit_custom','define_routes');
   
    
    protected $_filters = array();
        

    
    /**
    * Installs 
    *
    */
    public function hookInstall()
    {
        
        
    }
    
    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    { 
        
    }
    
    
    /**
     * Configure admin theme header.
     *
     * @param array $args
     */
    public function hookAdminHead($args)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        if (is_admin_theme() && $request->getControllerName() == 'items' && $request->getActionName() == 'batch-edit') {
            queue_js_file('elements-custom');
        }
    }
    
    function hookDefineRoutes($args)
    {
        
        $router = $args['router'];
        
        $router->addRoute('elementscustomAction',
            new Zend_Controller_Router_Route(
                'elements-custom/element-form',
                array(
                    'module' => 'custom-items-batch-form',
                    'controller' => 'Elementscustom',
                    'action' => 'elementForm'
                )
            )
        );
    }
    
    public function hookAdminItemsBatchEditForm()
    {        
        $elementSets = $this->_getItemElementSets();
        $item = new Item();        
        
        include 'form.php';
    }
    public function hookItemsBatchEditCustom($args)
    {
        $currentItem = $args['item'];
        $customData = $args['custom'];        
        
        
        $elementTexts = array();
        $elementTextsTmp = array();
        
        //traitement des elements dublin core
        foreach($customData as $key => $value){   
            
            var_dump($key);
            $element = $this->_getElement($key);
            $elementname = $element->name;
            
            $elementSet = $this->_getElementElementSet($element);
            $elementSetname = $elementSet->name;
            
            
            $i = 0;
            $multichamps = false;
            if(count($value)>1){
                $multichamps = true;
            }
            foreach($value as $idelement => $indextext){
               if($indextext['text'] != ''){  
                   if($i==0){
                       $elementTexts[$elementSetname][$elementname] = array();
                       $i++;
                   }                   
                   $boolhtml = ($indextext['html'] === '1');
                   array_push($elementTexts[$elementSetname][$elementname],array('text' => $indextext['text'], 'html' => $boolhtml));
                }else if($multichamps){
                    if($i==0){
                       $elementTexts[$elementSetname][$elementname] = array();
                       $i++;
                   }             
                   //on récupère la valeur du champs DC $elementname qui était déjà enregistrée pour l'index $idelement de l'element $key du $currentItem                   
                   $textelement = metadata($currentItem, array($elementSetname, $elementname),array('index' => $idelement));
                   if($textelement == null){
                       $textelement = '';
                   }
                   array_push($elementTexts[$elementSetname][$elementname],array('text' => $textelement, 'html' => ''));
                   
                    
                }
            }
        }
        
        update_item($currentItem->id, array('overwriteElementTexts'=>true), $elementTexts, array());
         
       
    }
   
     public static function renameItem(array $components, $args)
     {     

        //remplacement de element par custom dans le bouton "ajouter une entrée"
        $addinput = $components['add_input'];
        $pattern = '/element_(\d+)/i';
        $replacement = 'custom_$1';
        $addinput = preg_replace($pattern, $replacement, $addinput);        
        $components['add_input'] = $addinput;
        
        $inputs = $components['inputs'];
        $pattern = '/Elements\[/i';
        $replacement = 'custom[';
        $inputs = preg_replace($pattern, $replacement, $inputs);
        $pattern = '/Elements-(\d+)/i';
        $replacement = 'custom-$1';
        $inputs = preg_replace($pattern, $replacement, $inputs);
        $components['inputs'] = $inputs;
        
       
        return $components;

     }
    
    /**
     * Gets the element sets for the 'Item' record type.
     * 
     * @return array The element sets for the 'Item' record type
     */
    protected function _getItemElementSets()
    {
        return get_db()->getTable('ElementSet')->findByRecordType('Item');
    }
    
    /**
     * Gets the element from his id.
     * 
     * @return Element The element 
     */
    protected function _getElement($id)
    {
        return get_db()->getTable('Element')->find($id);
    }
    
    /**
     * Gets the element set of an Element.
     * 
     * @return Elementset 
     */
    protected function _getElementElementSet($element)
    {
        return $element->getElementSet();
    }
}