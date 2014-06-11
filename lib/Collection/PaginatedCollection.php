<?php

namespace eResults\Unity\Api\Collection;

use eResults\Unity\Api\Client;

/**
 * Description of PaginatedCollection
 *
 * @author niels
 */
class PaginatedCollection
	implements
		Countable,
		Traversable,
		Iterator
{
	protected $items = array();
	
	/**
	 *
	 * @var Client 
	 */
	protected $client;
	
	protected $pages;
	protected $page;
	protected $limit;
	protected $total;
	
	public function __construct ( Client $client, array $collection )
	{
		$this->links = $collection['_links'];
		$this->client = $client;
		$this->pages = $collection['pages'];
		$this->page = $collection['page'];
		$this->limit = $collection['limit'];
		$this->total = $collection['total'];
		
		$this->items = reset( $collection['_embedded'] );
	}

	public function count()
	{
		return $this->total;
	}
	
	public function getPageCount ()
	{
		return $this->pages;
	}

	protected $step = 0;

	/**
	 * Return the current item.
	 * 
	 * @return mixed
	 */
	public function current()
	{
		return $this->items[ $this->step ];
	}

	/**
	 * Return the key of the current item.
	 * 
	 * @return int
	 */
	public function key()
	{
		return $this->step;
	}

	/**
	 * Step to the next item and return it.
	 * 
	 * @return mixed
	 */
	public function next()
	{
		if( $this->valid() )
			$this->load();
		
		return $this->items[ $this->step++ ];
	}

	/**
	 * Rewind the iterator.
	 */
	public function rewind()
	{
		$this->step = 0;
	}

	/**
	 * Checks wether the next item is a valid item.
	 * 
	 * @return boolean
	 */
	public function valid()
	{
		return !isset( $this->items[ $this->step + 1 ] ) && $this->step < $this->total;
	}
	
	/**
	 * Load the next page in the collection and populate the current items.
	 * 
	 * @return boolean
	 * @throws \Exception
	 */
	public function load ()
	{
		if( !isset( $this->links['next'] ) )
			throw new \Exception('No more pages to load.');
			
		$collection = $this->client->get( $this->links['next'] );
		
		if( !$collection instanceof self )
			throw new \Exception('Expected ' . __CLASS__ . ', but got ' . ( is_object( $collection ) ? get_class( $collection ) : (string) $collection ) );
		
		$this->items += $collection->getItems();
		
		return true;
	}
	
	public function getItems ()
	{
		return $this->items;
	}
}
