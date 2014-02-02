<?php
/*
 * @version .5
 * @link https://raw.github.com/Opine-Org/Collection/master/available/photo_galleries.php
 * @mode upgrade
 *
 * .4 remove dead code
 * .5 tag view
 */
namespace Collection;

class photo_galleries {
    public $publishable = true;
    public $singular = 'photo_gallery';

    public function index ($document) {
        $depth = substr_count($document['dbURI'], ':');
        if ($depth > 1) {
            return false;
        }
        return [
            'title' => $document['title'], 
            'description' => $document['description'], 
            'image' => isset($document['image']) ? $document['image'] : '',
            'tags' => [], 
            'categories' => [],
            'date' => date('c', $document['created_date']->sec) 
        ];
    }

    public function tagsView ($mode, $id, $document) {
        $this->queue->add('CollectionTags', ['collection' => 'photo_galleries']);
    }
}