<?php
/**
 * @package   Soulex
 * @copyright Copyright (C) 2010 - Present, miholeus
 * @author    miholeus <me@miholeus.com> {@link http://miholeus.com}
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    $Id: $
 */

/**
  * Soulex_View_Helper_LoadSkin loads skins which are placed in /skins/
  * folder.
  *
  */
class Soulex_View_Helper_LoadSkin extends Zend_View_Helper_Abstract
{
     public function loadSkin ($skin)
     {
         // load the skin config file
         if(APPLICATION_ENV == 'testing') { // otherwise path can not be found during testing
             $skinPath = APPLICATION_PATH . '/../public/skins/';
         } else {
             $skinPath = './skins/';
         }
         $skinData = new Zend_Config_Xml($skinPath . $skin . '/skin.xml');
         $skinDataArray = $skinData->stylesheets->toArray();
         $stylesheets = array_pop($skinDataArray);

         if(!is_array($stylesheets) || count($stylesheets) == 0) {
             return false;
         }
         $version = 1;
         try {
             $appVersion = \Zend_Registry::get('App_Version');

             $version = $appVersion->css;
         } catch (\Exception $e) {
             // nothing here
         }
         // append each stylesheet
         foreach ($stylesheets as $stylesheet) {
             if(is_array($stylesheet)) {
                 if(!isset($stylesheet['condition'])) {
                     $this->view->headLink()->appendStylesheet('/skins/' . $skin .
                         '/css/' . $stylesheet['style'] . '?v=' . $version);
                 } else {
                     $this->view->headLink()->appendStylesheet('/skins/' . $skin .
                         '/css/' . $stylesheet['style'] . '?v=' . $version, 'screen', $stylesheet['condition']);
                 }
             } else { // string type
                 $this->view->headLink()->appendStylesheet('/skins/' . $skin .
                         '/css/' . $stylesheet . '?v=' . $version);
             }
         }
     }
}
