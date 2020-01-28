<?php

jimport('joomla.application.component.view');

if (!class_exists('RHViewBase')) {
    if (interface_exists('JView')) {
        abstract class RHViewBase extends JViewLegacy {}
    } else {
        abstract class RHViewBase extends JView {}
    }
}

class RHViewPopup extends RHViewBase {

    public function display($tpl = null) {
        $app = JFactory::getApplication();

        $this->document->addScript(JURI::root(true) . '/components/com_rrhh/media/js/popup.js');
        $this->document->addStylesheet(JURI::root(true) . '/components/com_rrhh/media/css/popup.css');

        // Get variables
        $img = JRequest::getVar('img');
        $title = JRequest::getWord('title');
        $mode = JRequest::getInt('mode', '0');
        $click = JRequest::getInt('click', '0');
        $print = JRequest::getInt('print', '0');

        $dim = array('', '');

        if (strpos('http', $img) === false) {
            $path = JPATH_SITE . '/' . trim(str_replace(JURI::root(), '', $img), '/');
            if (is_file($path)) {
                $dim = @getimagesize($path);
            }
        }

        $width = JRequest::getInt('w', JRequest::getInt('width', ''));
        $height = JRequest::getInt('h', JRequest::getInt('height', ''));

        if (!$width) {
            $width = $dim[0];
        }

        if (!$height) {
            $height = $dim[1];
        }

        // Cleanup img variable
        $img = preg_replace('/[^a-z0-9\.\/_-]/i', '', $img);

        $title = isset($title) ? str_replace('_', ' ', $title) : basename($img);
        // img src must be passed
        if ($img) {
            $features = array(
                'img' => str_replace(JURI::root(), '', $img),
                'title' => $title,
                'alt' => $title,
                'mode' => $mode,
                'click' => $click,
                'print' => $print,
                'width' => $width,
                'height' => $height
            );
            
            $this->document->addScriptDeclaration('(function(){RHWindowPopup.init(' . $width . ', ' . $height . ', ' . $click . ');})();');

            $this->assign('features', $features);
        } else {
            $app->redirect('index.php');
        }

        parent::display($tpl);
    }

}

?>
