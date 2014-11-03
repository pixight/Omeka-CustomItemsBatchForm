<?php
/**
 * Omeka
 * 
 * @copyright Copyright 2014
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * @package CustomItemsBatchForm\Controller
 */
class CustomItemsBatchForm_ElementscustomController extends Omeka_Controller_AbstractActionController
{
    public function elementFormAction()
    {        
        $elementId = (int)$_POST['element_id'];
        $recordType = $_POST['record_type'];
        $recordId  = (int)$_POST['record_id'];
        $fieldCount = (int)$_POST['field_count'];
                         
        // Re-index the element form posts so that they are displayed in the correct order
        // when one is removed.
        $_POST['custom'][$elementId] = array_merge($_POST['custom'][$elementId]);

        $element = $this->_helper->db->getTable('Element')->find($elementId);
        $record = false;
        
        if (!$record) {
            $record = new Item();            
        }
        
        $this->view->assign(compact('element', 'record','fieldCount'));
    }
}
?>