<?php
/**
 * Opine\Collection\Controller
 *
 * Copyright (c)2013, 2014 Ryan Mahoney, https://github.com/Opine-Org <ryan@virtuecenter.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Opine\Collection;

class Controller {
	private $model;
	private $view;

	public function __construct ($model, $view) {
		$this->model = $model;
		$this->view = $view;
	}

    public function json ($collection, $method='all', $limit=20, $page=1, $sort=[], $fields=[]) {
        $collectionClass = '\Collection\\' . $collection;
        if (!class_exists($collectionClass)) {
            throw new Exception ('Collection not found: ' . $collectionClass);
        }
        $this->view->json($this->model->generate(new $collectionClass, $method, $limit, $page, $sort, $fields, $method));
    }

    public function jsonBundle ($bundle, $collection, $method='all', $limit=20, $page=1, $sort=[], $fields=[]) {
        $collectionClass = '\\' . $bundle . '\Collection\\' . $collection;
        if (!class_exists($collectionClass)) {
            throw new Exception ('Bundled Collection not found: ' . $collectionClass);
        }
        $this->view->json($this->model->generate(new $collectionClass, $method, $limit, $page, $sort, $fields, $method));
    }

    public function htmlIndex ($method='all', $limit=10, $page=1, $sort=[]) {
        $name = explode('/', trim($_SERVER['REQUEST_URI'], '/'))[0];
        if ($limit === null) {
            $limit = 10;
        }
        $args = [];
        if ($limit != null) {
            $args['limit'] = $limit;
        }
        $args['method'] = $method;
        $args['page'] = $page;
        $args['sort'] = json_encode($sort);
        foreach (['limit', 'page', 'sort'] as $option) {
            $key = $name . '-' . $method . '-' . $option;
            if (isset($_GET[$key])) {
                $args[$option] = $_GET[$key];
            }
        }
        $this->view->htmlIndex($name, $arguments);
    }

    public function html ($slug) {
        $name = explode('/', trim($_SERVER['REQUEST_URI'], '/'))[0];
        $this->view->html($name, $slug);
    }

    public function htmlCollectionIndex () {
        $this->view->htmlCollectionIndex($this->model->collections());
    }

    public function jsonCollectionIndex () {
        $collections = $this->model->collections();
        foreach ($collections as &$collection) {
            $collectionObj = $this->collection->factory($collection['p']);
            $reflection = new \ReflectionClass($collectionObj);
            $methods = $reflection->getMethods();
            foreach ($methods as $method) {
                if (in_array($method->name, ['document','__construct','totalGet','localSet','decorate','fetchAll'])) {
                    continue;
                }
                $collection['methods'][] = $method->name;
            }
        }
        $head = '';
        $tail = '';
        if (isset($_GET['callback'])) {
            if ($_GET['callback'] == '?') {
                $_GET['callback'] = 'callback';
            }
            $head = $_GET['callback'] . '(';
            $tail = ');';
        }
        echo $head . json_encode($collections) . $tail;
    }
}