<?php
/*
 * @version .3
 * @link https://raw.github.com/Opine-Org/Collection/master/available/Pages.php
 * @mode upgrade
 *
 * .3 tag view
 */
namespace Collection;

class Pages {
    public $publishable = false;
    public $singular = 'page';


    public function index ($document) {
        return [
            'title' => $document['title'], 
            'description' => $document['metadata_description'], 
            'image' => null, 
            'tags' => isset($document['tags']) ? $document['tags'] : [], 
            'categories' => isset($document['categories']) ? $document['categories']: [],
            'date' => date('c', $document['created_date']->sec) 
        ];
    }

    public function tagsView ($mode, $id, $document) {
        $this->queue->add('CollectionTags', ['collection' => 'pages']);
    }
}