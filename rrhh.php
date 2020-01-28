<?php

defined('_JEXEC') or die;

JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

class RRHHsModelRrhh extends JModelLegacy
{
	
	protected $_item;

	public function click()
	{
		$id = $this->getState('rrhh.id');

		// Update click count
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->update('#__rrhhs')
			->set('clicks = (clicks + 1)')
			->where('id = ' . (int) $id);

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			JError::raiseError(500, $e->getMessage());
		}

		$item = $this->getItem();

		// Track clicks
		$trackClicks = $item->track_clicks;

		if ($trackClicks < 0 && $item->cid)
		{
			$trackClicks = $item->client_track_clicks;
		}

		if ($trackClicks < 0)
		{
			$config = JComponentHelper::getParams('com_rrhh');
			$trackClicks = $config->get('track_clicks');
		}

		if ($trackClicks > 0)
		{
			$trackDate = JFactory::getDate()->format('Y-m-d H');

			$query->clear()
				->select($db->quoteName('count'))
				->from('#__rrhh_tracks')
				->where('track_type=2')
				->where('rrhh_id=' . (int) $id)
				->where('track_date=' . $db->quote($trackDate));

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				JError::raiseError(500, $e->getMessage());
			}

			$count = $db->loadResult();

			$query->clear();

			if ($count)
			{
				// Update count
				$query->update('#__rrhh_tracks')
					->set($db->quoteName('count') . ' = (' . $db->quoteName('count') . ' + 1)')
					->where('track_type=2')
					->where('rrhh_id=' . (int) $id)
					->where('track_date=' . $db->quote($trackDate));
			}
			else
			{
				// Insert new count
				$query->insert('#__rrhh_tracks')
					->columns(
						array(
							$db->quoteName('count'), $db->quoteName('track_type'),
							$db->quoteName('rrhh_id'), $db->quoteName('track_date')
						)
					)
					->values('1, 2,' . (int) $id . ',' . $db->quote($trackDate));
			}

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				JError::raiseError(500, $e->getMessage());
			}
		}
	}

	public function &getItem()
	{
		if (!isset($this->_item))
		{
			$cache = JFactory::getCache('com_rrhh', '');

			$id = $this->getState('rrhh.id');

			$this->_item = $cache->get($id);

			if ($this->_item === false)
			{

				$db = $this->getDbo();
				$query = $db->getQuery(true)
					->select(
						'a.clickurl as clickurl,' .
							'a.cid as cid,' .
							'a.track_clicks as track_clicks'
					)
					->from('#__rrhhs as h')
					->where('h.id = ' . (int) $id)

					->join('LEFT', '#__rrhh_clients AS cl ON cl.id = h.cid')
					->select('cl.track_clicks as client_track_clicks');

				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (RuntimeException $e)
				{
					JError::raiseError(500, $e->getMessage());
				}

				$this->_item = $db->loadObject();
				$cache->store($this->_item, $id);
			}
		}

		return $this->_item;
	}

	public function getUrl()
	{
		$item = $this->getItem();
		$url = $item->clickurl;

		// Check for links
		if (!preg_match('#http[s]?://|index[2]?\.php#', $url))
		{
			$url = "http://$url";
		}

		return $url;
	}
}
