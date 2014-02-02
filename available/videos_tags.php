<?php
/*
 * @version .1
 * @link https://raw.github.com/Opine-Org/Collection/master/available/videos_tags.php
 * @mode upgrade
 *
 * .1 initial load
 */
namespace Collection;

class videos_tags {
    public $publishable = false;
    public $singular = 'videos_tag';
    public $path = false;

    public function document (&$document) {
        $tmp = [
            'tag' => $document['_id'],
            'count' => $document['value']
        ];
        $document = $tmp;
    }
}