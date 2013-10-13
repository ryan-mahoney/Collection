<?php
namespace Collection;

trait Collection {
	public $collection;
	public $criteria = [];
	public $tagCacheCollection;
	public $sort = [];
	public $limit = 100;
	public $skip = 0;
	public $total = 0;
	public $name = null;
	public $transform = 'document';
	public $myTransform = 'myDocument';
	private $local = false;
	public $db;

	public function __construct ($db, $limit=20, $page=1, $sort=[]) {
		$this->db = $db;
		$this->collection = get_class($this);
		$this->tagCacheCollection = $this->collection . 'Tags';
		$this->limit = $limit;
		$this->skip = ($page - 1) * $limit;
		if (is_string($sort)) {
			$this->sort = json_decode($sort);
		} else {
			$this->sort = $sort;
		}
	}

	public function totalGet () {
		return $this->total;
	}

	public function localSet() {
		$this->local = true;
	}

	private function decorate (&$document) {
		$document['_id'] = (string)$document['_id'];
		if (method_exists($this, $this->transform)) {
			$method = $this->transform;
			$this->$method($document);
		}
		if (method_exists($this, $this->myTransform)) {
			$method = $this->myTransform;
			$this->$method($document);
		}
		$template = '';
		if (isset($document['template_separation'])) {
			$template = '-' . $document['template_separation'];
		}
		if (property_exists($this, 'path')) {
			if ($this->path === false) {
				return;
			}
		}
		if ($this->local) {
			$key = '_id';
			if (!empty($this->pathKey)) {
				$key = $this->pathKey;
			}
			$path = $this::$singular . $template . '.html#{"Sep":"' . $this->collection . '", "a": {"id":"' . (string)$document[$key] . '"}}';
		} else {
			if (!property_exists($this, 'path')) {
				$path = '/' . $this::$singular . $template;
				if (isset($document['code_name'])) {
					$path .= '/' . $document['code_name'] . '.html';
				} else {
					$path .= '/id/' . (string)$document['_id'] . '.html';
				}
			} else {
				$path =	$this->path . $document[$this->pathKey] . '.html';
			}
		}
		$document['path'] = $path;
	}

	private function fetchAll ($collection, $cursor) {
		$rows = [];
		while ($cursor->hasNext()) {
			$document = $cursor->getNext();
			$this->decorate($document);
			$rows[] = $document;
		}
		return $rows;
	}

	public function all () {
		$this->name = $this->collection;
		if ($this->publishable) {
			$this->criteria['status'] = 'published';
		}
		$this->total = $this->db->collection($this->collection)->find($this->criteria)->count();
		return $this->fetchAll($this->collection, $this->db->collection($this->collection)->find($this->criteria)->sort($this->sort)->limit($this->limit)->skip($this->skip));
	}

	public function byId ($id) {
		$this->name = $this::$singular;
		$document = $this->db->collection($this->collection)->findOne(['_id' => $this->db->id($id)]);
		if (!isset($document['_id'])) {
			return [];
		}
		self::decorate($document);
		return $document;
	}

	public function bySlug ($slug) {
		$this->name = $this::$singular;
		$document = $this->db->collection($this->collection)->findOne(['code_name' => $slug]);
		if (!isset($document['_id'])) {
			return [];
		}
		self::decorate($document);
		return $document;
	}

	public function featured () {
		$this->criteria['featured'] = 't';
		return $this->all();
	}

	public function byCategoryId ($categoryId) {
		$this->criteria['category'] = $this->db->id($categoryId);
		return $this->all();
	}

	public function byCategory ($category) {
		$category = self::categoryIdFromTitle($category);
		if (!isset($category['_id'])) {
			return $this->all();
		}
		$this->criteria['categories'] = ['$in' => [$category['_id'], (string)$category['_id']]];
		return $this->all();
	}

	private static function categoryIdFromTitle ($title) {
		return $this->db->collection('categories')->findOne(['title' => urldecode($title)], ['id']);
	}

	public function byCategoryFeatured ($category) {
		$category = self::categoryIdFromTitle($category);
		if (!isset($category['_id'])) {
			return $this->all();
		}
		$this->criteria['categories'] = $category['_id'];
		$this->criteria['featured'] = 't';
		return $this->all();
	}

	public function byTag ($tag) {
		$this->criteria['tags'] = $tag;
		return $this->all();
	}

	public function byCategoryIdFeatured ($categoryId) {
		$this->criteria['categories'] = $this->db->id($categoryId);
		$this->criteria['featured'] = 't';
		return $this->all();
	}

	public function byTagFeatured ($tag) {
		$this->criteria['tags'] = $tag;
		$this->criteria['featured'] = 't';
		return $this->all();
	}

	private function dateFieldValidate() {
		if (isset($this->dateField)) {
			throw new \Exception('Model configuration mmissing dateField');
		}
	}

	public function byDateUpcoming () {
		$this->dateFieldValidate();
		$this->criteria[$this->dateField] = ['$gte' => new \MongoDate(strtorime('today'))];
		$this->all();
	}

	public function byDatePast () {
		$this->dateFieldValidate();
		$this->criteria[$this->dateField] = ['$lt' => new \MongoDate(strtorime('today'))];
		$this->all();
	}

	public function byAuthorId ($id) {
		$this->criteria['author'] = $this->db->id($id);
	}

	public function byAuthorSlug ($slug) {
		$this->criteria['author'] = $this->db->id($id);
	}

	public function tags () {
		if (!isset($this->tagCacheCollection)) {
			throw new \Exception('Model configuration missing tagCacheCollection field');
		}
		$this->path = '/' . $this->collection . '/byTag/';
		$this->pathKey = 'tag';
		$this->collection = $this->tagCacheCollection;
		$this->publishable = false;
		$this->transform = 'documentTags';
		$this->myTransform = 'myDocumentTags';
		return $this->all();
	}

	public function document (&$document) {
		//format date
		if (isset($document['display_date'])) {
			$document['display_date__MdY'] = date('M d, Y', $document['display_date']->sec);
		}

		//lookup authors		

		//lookup categories
	}

	public function documentTags (&$document) {
		$document['tag'] = $document['_id'];
		$document['count'] = $document['value'];
		unset($document['_id']);
		unset($document['value']);
	}

//Todo: wrap up additional functions
	public function tagsRandom () {

	}

	public function popular () {

	}

	public function search () {
		//solr integration
	}
}