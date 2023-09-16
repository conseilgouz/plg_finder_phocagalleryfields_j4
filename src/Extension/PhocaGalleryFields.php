<?php
/*
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
namespace Phoca\Plugin\Finder\PhocaGalleryFields\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseQuery;
use Joomla\Registry\Registry;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Event\DispatcherInterface;
require_once JPATH_SITE . '/plugins/finder/phocagalleryimage/phocagalleryimage.php';

defined('JPATH_BASE') or die;

class PhocaGalleryFields extends \PlgFinderPhocagalleryImage
{
    use DatabaseAwareTrait;
    /**
     * Constructor.
     *
     * @param   DispatcherInterface  $dispatcher  The dispatcher
     * @param   array                $config      An optional associative array of configuration settings
     *
     * @since   4.2.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config)
    {
        parent::__construct($dispatcher, $config);

    }	
	protected function index(Result $item, $format = 'html') {
		// bug in PlgFinderPhocagalleryImage
		$registry = new Registry;
		if (isset($item->metadata)) {
		    $registry->loadString($item->params);
		}
		$item->params = $registry;

		$registry = new Registry;
		if (isset($item->metadata)) {
		    $registry->loadString($item->metadata);
		}
		$item->metadata = $registry;

		parent::index($item, $format);
	}
	
	public function onPrepareFinderContent(\FinderIndexerResult &$row)     { 
        if (!$row->id) return; // no id => ignore
		$fields = $this->myquery($row);
		foreach ($fields as $field) {
			$row->addInstruction(\FinderIndexer::TEXT_CONTEXT, $field->id); 
			$alias = OutputFilter::stringURLSafe($field->label.' '.$field->value);			
			$row->setElement($field->id, $alias);
			//$row->setElement($field->id, $field->value);
		}
	}
	protected function myQuery($row)
	{
	    $db = $this->getDatabase();
		// Check if we can use the supplied SQL query.
		$query = $db->getQuery(true)
			->select('v.value as value, f.name as id,f.label as label');
		$query->from('#__phocagallery AS a')
			->join('INNER','#__fields_values as v on v.item_id = a.id')
			->join('INNER','#__fields as f on f.id = v.field_id')
			->where('f.context like "%com_phocagallery.image%" and a.published = 1 and a.id = '.$row->id);
		$db->setQuery($query);
		$results = $db->loadObjectList();
		return $results;
	}
}
