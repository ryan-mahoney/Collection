<?php
/*
 * @version .2
 * @link https://raw.github.com/Opine-Org/Collection/master/available/Users.php
 * @mode upgrade
 */
namespace Collection;

class Users {
    public $publishable = true;
    public $singular = 'user';

    public function index ($document) {
        return [
            'title' => $document['first_name'] . ' ' . $document['last_name'], 
            ///'description' => $document['description'], 
            //'image' => isset($document['image']) ? $document['image'] : '', 
            //'tags' => isset($document['tags']) ? $document['tags'] : [], 
            //'categories' => isset($document['categories']) ? $document['categories']: [],
            'date' => date('c', $document['created_date']->sec) 
        ];
    }
}