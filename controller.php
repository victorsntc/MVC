<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class RRHHComponentController extends JControllerLegacy
{

	public function display($cachable = false, $urlparams = false)
	{
		JRequest::setVar('view','default');

		return parent::display($cachable, $urlparams);
	}

}
