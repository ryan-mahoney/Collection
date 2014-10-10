<?php
/*
 * @version .3
 * @link https://raw.github.com/Opine-Org/Collection/master/available/Profiles.php
 * @mode upgrade
 *
 * .3 wrong singular
 */
namespace Collection;

class Profiles {
    public $publishable = true;
    public $singular = 'profile';

    public function index ($document) {
        return [
            'title' => $document['title'], 
            'description' => $document['description'], 
            'image' => isset($document['image']) ? $document['image'] : '', 
            'tags' => isset($document['tags']) ? $document['tags'] : [], 
            'categories' => isset($document['categories']) ? $document['categories']: [],
            'date' => date('c', $document['created_date']->sec) 
        ];
    }
}