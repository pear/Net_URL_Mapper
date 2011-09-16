<?php
/**
 * Unit tests for Net_URL_Mapper package
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006, Bertrand Mansion <golgote@mamasam.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * The names of the authors may not be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Net
 * @package    Net_URL_Mapper
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    CVS: $Id: GenerationTest.php 296826 2010-03-25 22:57:49Z kguest $
 * @link       http://pear.php.net/package/Net_URL_Mapper
 */

/**
 * Mapper class
 */
require_once 'Net/URL/Mapper.php';

/**
 * PHPUnit Test Case
 */
require_once 'PHPUnit/Framework/TestCase.php';


/**
 * URL generation tests for Net_URL_Mapper class
 */
class GenerationTest extends PHPUnit_Framework_TestCase
{
    public function testAllStaticNoReqs()
    {
        $m = Net_URL_Mapper::getInstance();
        $m->connect('hello/world');
        $this->assertEquals('/hello/world', $m->generate());
    }

    public function testBasicDynamic()
    {
        $m = Net_URL_Mapper::getInstance();
        $m->reset();
        $paths = array('first' => 'hi/:fred', 'second' => 'hi/:(fred)');
        foreach ($paths as $alias => $path) {
            $m->connect($path, array(), array(), $alias);
            $this->assertEquals('/hi/index', $m->generate(array('fred'=>'index')));
            $this->assertEquals('/hi/show', $m->generate(array('fred'=>'show')));
            $this->assertEquals('/hi/list+people', $m->generate(array('fred'=>'list people')));
            $this->assertEquals(false, $m->generate());

            $this->assertEquals('/hi/index', $m->generateFromAlias($alias, array('fred'=>'index')));
            $this->assertEquals('/hi/show', $m->generateFromAlias($alias, array('fred'=>'show')));
            $this->assertEquals('/hi/list+people', $m->generateFromAlias($alias, array('fred'=>'list people')));
            $this->assertEquals(false, $m->generateFromAlias($alias));

        }
    }

    public function testDynamicWithDefault()
    {
        $m = Net_URL_Mapper::getInstance();
        $m->reset();
        $paths = array('first' => 'hi/:action', 'second' => 'hi/:(action)');
        foreach ($paths as $alias => $path) {
            $m->connect($path, array('action'=>'test'), array(), $alias);
            $this->assertEquals('/hi/index', $m->generate(array('action'=>'index')));
            $this->assertEquals('/hi/show', $m->generate(array('action'=>'show')));
            $this->assertEquals('/hi/list+people', $m->generate(array('action'=>'list people')));
            $this->assertEquals('/hi/test', $m->generate());
            $this->assertEquals('/hi/test', $m->generate(array('action'=>'test')));

            $this->assertEquals('/hi/index', $m->generateFromAlias($alias, array('action'=>'index')));
            $this->assertEquals('/hi/show', $m->generateFromAlias($alias, array('action'=>'show')));
            $this->assertEquals('/hi/list+people', $m->generateFromAlias($alias, array('action'=>'list people')));
            $this->assertEquals('/hi/test', $m->generateFromAlias($alias));
            $this->assertEquals('/hi/test', $m->generateFromAlias($alias, array('action'=>'test')));
        }
    }

    public function testDynamicWithFalseEquivs()
    {
        $m = Net_URL_Mapper::getInstance();
        $m->reset();
        $m->connect('article/:page', array('page' => false), array(), 'article');
        $m->connect('view/:home/:area', array('home'=>'austere', 'area'=>null), array(), 'home');
        $m->connect(':controller/:action/:id', array(), array(), 'controller');

        $this->assertEquals('/blog/view/0', $m->generate(array('controller'=>'blog', 'action'=>'view', 'id'=>'0')));
        $this->assertEquals('/blog/view/0', $m->generate(array('controller'=>'blog', 'action'=>'view', 'id'=>0)));
        $this->assertEquals('/blog/view', $m->generate(array('controller'=>'blog', 'action'=>'view', 'id'=>false)));
        $this->assertEquals('/blog/view', $m->generate(array('controller'=>'blog', 'action'=>'view', 'id'=>null)));
        $this->assertEquals('/article', $m->generate(array('page'=>null)));
        $this->assertEquals('/article', $m->generate(array('page'=>false)));
        $this->assertEquals('/article/0', $m->generate(array('page'=>0)));

        $this->assertEquals('/blog/view/0', $m->generateFromAlias('controller', array('controller'=>'blog', 'action'=>'view', 'id'=>'0')));
        $this->assertEquals('/blog/view/0', $m->generateFromAlias('controller', array('controller'=>'blog', 'action'=>'view', 'id'=>0)));
        $this->assertEquals('/blog/view', $m->generateFromAlias('controller', array('controller'=>'blog', 'action'=>'view', 'id'=>false)));
        $this->assertEquals('/blog/view', $m->generateFromAlias('controller', array('controller'=>'blog', 'action'=>'view', 'id'=>null)));
        $this->assertEquals('/article', $m->generateFromAlias('article', array('page'=>null)));
        $this->assertEquals('/article', $m->generateFromAlias('article', array('page'=>false)));
        $this->assertEquals('/article/0', $m->generateFromAlias('article', array('page'=>0)));

        $this->assertEquals('/view/sumatra', $m->generate(array('home'=>'sumatra')));
        $this->assertEquals('/view/austere/chicago', $m->generate(array('area'=>'chicago')));

        $this->assertEquals('/view/sumatra', $m->generateFromAlias('home', array('home'=>'sumatra')));
        $this->assertEquals('/view/austere/chicago', $m->generateFromAlias('home', array('area'=>'chicago')));

        $this->assertEquals(array('home'=>'austere', 'area'=>'chicago'), $m->match('/view/austere/chicago'));
        $this->assertEquals(array('home'=>'sumatra', 'area'=>null), $m->match('/view/sumatra'));

        $m->reset();

        $m->connect('view/:home/:area', array('home'=>null, 'area'=>null), array(), 'city');
        $this->assertEquals('/view/chicago', $m->generate(array('home'=>null, 'area'=>'chicago')));
        $this->assertEquals('/view/chicago', $m->generate(array('area'=>'chicago')));

        $this->assertEquals('/view/chicago', $m->generateFromAlias('city', array('home'=>null, 'area'=>'chicago')));
        $this->assertEquals('/view/chicago', $m->generateFromAlias('city', array('area'=>'chicago')));

        $this->assertEquals(array('home'=>'chicago', 'area'=>null), $m->match('/view/chicago'));
    }


    public function testDynamicWithFalseEquivsAndSplits()
    {
        $m = Net_URL_Mapper::getInstance();
        $m->reset();
        $m->connect('article/:(page)', array('page' => false), array(), 'article');
        $m->connect('view/:(home)/:(area)', array('home'=>'austere', 'area'=>null), array(), 'view');
        $m->connect(':(controller)/:(action)/:(id)', array(), array(), 'controller');

        $this->assertEquals('/blog/view/0', $m->generate(array('controller'=>'blog', 'action'=>'view', 'id'=>'0')));
        $this->assertEquals('/blog/view/0', $m->generate(array('controller'=>'blog', 'action'=>'view', 'id'=>0)));
        $this->assertEquals('/blog/view', $m->generate(array('controller'=>'blog', 'action'=>'view', 'id'=>false)));
        $this->assertEquals('/blog/view', $m->generate(array('controller'=>'blog', 'action'=>'view', 'id'=>null)));
        $this->assertEquals('/article', $m->generate(array('page'=>null)));
        $this->assertEquals('/article', $m->generate(array('page'=>false)));
        $this->assertEquals('/article/0', $m->generate(array('page'=>0)));

        $this->assertEquals('/blog/view/0', $m->generateFromAlias('controller', array('controller'=>'blog', 'action'=>'view', 'id'=>'0')));
        $this->assertEquals('/blog/view/0', $m->generateFromAlias('controller', array('controller'=>'blog', 'action'=>'view', 'id'=>0)));
        $this->assertEquals('/blog/view', $m->generateFromAlias('controller', array('controller'=>'blog', 'action'=>'view', 'id'=>false)));
        $this->assertEquals('/blog/view', $m->generateFromAlias('controller', array('controller'=>'blog', 'action'=>'view', 'id'=>null)));
        $this->assertEquals('/article', $m->generateFromAlias('article', array('page'=>null)));
        $this->assertEquals('/article', $m->generateFromAlias('article', array('page'=>false)));
        $this->assertEquals('/article/0', $m->generateFromAlias('article', array('page'=>0)));

        $this->assertEquals('/view/sumatra', $m->generate(array('home'=>'sumatra')));
        $this->assertEquals('/view/austere/chicago', $m->generate(array('area'=>'chicago')));

        $this->assertEquals('/view/sumatra', $m->generateFromAlias('view', array('home'=>'sumatra')));
        $this->assertEquals('/view/austere/chicago', $m->generateFromAlias('view', array('area'=>'chicago')));

        $this->assertEquals(array('home'=>'austere', 'area'=>'chicago'), $m->match('/view/austere/chicago'));
        $this->assertEquals(array('home'=>'sumatra', 'area'=>null), $m->match('/view/sumatra'));

        $m->reset();

        $m->connect('view/:home/:area', array('home'=>null, 'area'=>null), array(), 'view');
        $this->assertEquals('/view/chicago', $m->generate(array('home'=>null, 'area'=>'chicago')));
        $this->assertEquals('/view/chicago', $m->generate(array('area'=>'chicago')));

        $this->assertEquals('/view/chicago', $m->generateFromAlias('view', array('home'=>null, 'area'=>'chicago')));
        $this->assertEquals('/view/chicago', $m->generateFromAlias('view', array('area'=>'chicago')));

        $this->assertEquals(array('home'=>'chicago', 'area'=>null), $m->match('/view/chicago'));
    }

    public function testDynamicWithRegexpCondition()
    {
        $paths = array('last' => 'hi/:name', 'first' => 'hi/:(name)');
        foreach ($paths as $alias => $path) {
            $m = Net_URL_Mapper::getInstance();
            $m->reset();
            $m->connect($path, null, array('name'=>'[a-z]+'), $alias);
            $this->assertEquals('/hi/index', $m->generate(array('name'=>'index')));
            $this->assertEquals(false, $m->generate(array('name'=>'fox5')));
            $this->assertEquals(false, $m->generate(array('name'=>'something_is_up')));
            $this->assertEquals(false, $m->generate(array('name'=>'list people')));
            $this->assertEquals('/hi', $m->generate(array('name'=>null)));
            $this->assertEquals(false, $m->generate());

            $this->assertEquals('/hi/index', $m->generateFromAlias($alias, array('name'=>'index')));
            $this->assertEquals(false, $m->generateFromAlias($alias, array('name'=>'fox5')));
            $this->assertEquals(false, $m->generateFromAlias($alias, array('name'=>'something_is_up')));
            $this->assertEquals(false, $m->generateFromAlias($alias, array('name'=>'list people')));
            $this->assertEquals('/hi', $m->generateFromAlias($alias, array('name'=>null)));
            $this->assertEquals(false, $m->generateFromAlias($alias));
        }
    }

    public function testPath()
    {
        $paths = array('hi/*file', 'hi/*(file)');
        foreach ($paths as $alias => $path) {
            $m = Net_URL_Mapper::getInstance();
            $m->reset();
            $m->connect($path, null, null, $alias);
            $this->assertEquals('/hi', $m->generate(array('file'=>null)));
            $this->assertEquals('/hi/books/learning_php.pdf', $m->generate(array('file'=>'books/learning_php.pdf')));
            $this->assertEquals('/hi/books/development%26whatever/learning_php.pdf', $m->generate(array('file'=>'books/development&whatever/learning_php.pdf')));

            $this->assertEquals('/hi', $m->generateFromAlias($alias, array('file'=>null)));
            $this->assertEquals('/hi/books/learning_php.pdf', $m->generateFromAlias($alias, array('file'=>'books/learning_php.pdf')));
            $this->assertEquals('/hi/books/development%26whatever/learning_php.pdf', $m->generateFromAlias($alias, array('file'=>'books/development&whatever/learning_php.pdf')));
        }
    }

    public function testPathBackwards()
    {
        $paths = array('*file/hi', '*(file)/hi');
        foreach ($paths as $alias => $path) {
            $m = Net_URL_Mapper::getInstance();
            $m->reset();
            $m->connect($path, null, null, $alias);
            $this->assertEquals('/hi', $m->generate(array('file'=>null)));
            $this->assertEquals('/books/learning_php.pdf/hi', $m->generate(array('file'=>'books/learning_php.pdf')));
            $this->assertEquals('/books/development%26whatever/learning_php.pdf/hi', $m->generate(array('file'=>'books/development&whatever/learning_php.pdf')));

            $this->assertEquals('/hi', $m->generateFromAlias($alias, array('file'=>null)));
            $this->assertEquals('/books/learning_php.pdf/hi', $m->generateFromAlias($alias, array('file'=>'books/learning_php.pdf')));
            $this->assertEquals('/books/development%26whatever/learning_php.pdf/hi', $m->generateFromAlias($alias, array('file'=>'books/development&whatever/learning_php.pdf')));
        }
    }

    public function testController()
    {
        $paths = array('hi/:controller', 'hi/:(controller)');
        foreach ($paths as $alias => $path) {
            $m = Net_URL_Mapper::getInstance();
            $m->reset();
            $m->connect($path, null, null, $alias);
            $this->assertEquals('/hi/content', $m->generate(array('controller'=>'content')));
            // There is no point in accepting this since it wouldn't be recognized because of the /
            // Python routes package accepts this...
            $this->assertEquals(false, $m->generate(array('controller'=>'admin/user')));

            $this->assertEquals('/hi/content', $m->generateFromAlias($alias, array('controller'=>'content')));
            // There is no point in accepting this since it wouldn't be recognized because of the /
            // Python routes package accepts this...
            $this->assertEquals(false, $m->generateFromAlias($alias, array('controller'=>'admin/user')));
        }
    }

    public function testStandardRoute()
    {
        $paths = array(':controller/:action/:id', ':(controller)/:(action)/:(id)');
        foreach ($paths as $alias => $path) {
            $m = Net_URL_Mapper::getInstance();
            $m->reset();
            $m->connect($path, array('action'=>null, 'id'=>null), null, $alias);
            $this->assertEquals('/content', $m->generate(array('controller'=>'content')));
            $this->assertEquals('/content/list', $m->generate(array('controller'=>'content', 'action'=>'list')));
            $this->assertEquals('/content/show/10', $m->generate(array('controller'=>'content','action'=>'show','id'=>'10')));

            $this->assertEquals('/content', $m->generateFromAlias($alias, array('controller'=>'content')));
            $this->assertEquals('/content/list', $m->generateFromAlias($alias, array('controller'=>'content', 'action'=>'list')));
            $this->assertEquals('/content/show/10', $m->generateFromAlias($alias, array('controller'=>'content','action'=>'show','id'=>'10')));
        }
    }

    public function testMultiroute()
    {
        $m = Net_URL_Mapper::getInstance();
        $m->reset();
        $m->connect('archive/:year/:month/:day', array('controller'=>'blog', 'action'=>'view', 'month'=>null, 'day'=>null),
            array('month'=>'\d{1,2}', 'day'=>'\d{1,2}'), 'archive');
        $m->connect('viewpost/:id', array('controller'=>'post', 'action'=>'view'), null, 'post');
        $m->connect(':controller/:action/:id', null, null, 'controller');

        $this->assertEquals('/archive/2004/11', $m->generate(array('controller'=>'blog', 'action'=>'view', 'year'=>'2004', 'month'=>'11')));
        $this->assertEquals('/archive/2004/11', $m->generate(array('controller'=>'blog', 'action'=>'view', 'year'=>2004, 'month'=>11)));
        $this->assertEquals('/archive/2004', $m->generate(array('controller'=>'blog', 'action'=>'view', 'year'=>2004)));
        $this->assertEquals('/viewpost/3', $m->generate(array('controller'=>'post', 'action'=>'view', 'id'=>3)));

        $this->assertEquals('/archive/2004/11', $m->generateFromAlias('archive', array('controller'=>'blog', 'action'=>'view', 'year'=>'2004', 'month'=>'11')));
        $this->assertEquals('/archive/2004/11', $m->generateFromAlias('archive', array('controller'=>'blog', 'action'=>'view', 'year'=>2004, 'month'=>11)));
        $this->assertEquals('/archive/2004', $m->generateFromAlias('archive', array('controller'=>'blog', 'action'=>'view', 'year'=>2004)));
        $this->assertEquals('/viewpost/3', $m->generateFromAlias('post', array('controller'=>'post', 'action'=>'view', 'id'=>3)));
    }

    public function testBigMultiroute()
    {
        $m = Net_URL_Mapper::getInstance();
        $m->reset();
        $m->connect('admin', array('controller'=>'admin/general', 'action'=>'index'), null, 'admin');

        $m->connect('admin/comments/article/:article_id/:action/:id', array('controller'=>'admin/comments', 'action'=>null, 'id'=>null), null, 'article_comments');
        $m->connect('admin/trackback/article/:article_id/:action/:id', array('controller'=>'admin/trackback', 'action'=>null, 'id'=>null), null, 'article_trackback');
        $m->connect('admin/content/:action/:id', array('controller'=>'admin/content'), null, 'article_action');

        $m->connect('xml/:action/feed.xml', array('controller'=>'xml'), null, 'action_feed');
        $m->connect('xml/articlerss/:id/feed.xml', array('controller'=>'xml', 'action'=>'articlerss'), null, 'articles_rss');
        $m->connect('index.rdf', array('controller'=>'xml', 'action'=>'rss'), null, 'rdf');

        $m->connect('articles', array('controller'=>'articles', 'action'=>'index'), null, 'articles');
        $m->connect('articles/page/:page', array('controller'=>'articles', 'action'=>'index'), array('page'=>'\d+'), null, 'articles_paginated');

        $m->connect('articles/:year/:month/:day/page/:page', array('controller'=>'articles', 'action'=>'find_by_date', 'month'=>null, 'day'=>null),
            array('year'=>'\d{4}', 'month'=>'\d{1,2}', 'day'=>'\d{1,2}'), 'articles_history');

        $m->connect('articles/category/:id', array('controller'=>'articles', 'action'=>'category'), null, 'article_category');
        $m->connect('pages/*name', array('controller'=>'articles', 'action'=>'view_page'), null, 'page');

        $m->connect('/', array('controller'=>'articles', 'action'=>'index'), null, 'home');


        $this->assertEquals('/pages/the/idiot/has/spoken', $m->generate(array('controller'=>'articles', 'action'=>'view_page', 'name'=>'the/idiot/has/spoken')));
        $this->assertEquals('/articles', $m->generate(array('controller'=>'articles', 'action'=>'index')));
        $this->assertEquals('/', $m->generate());
        $this->assertEquals('/admin', $m->generate(array('controller'=>'admin/general')));
        $this->assertEquals('/xml/articlerss/4/feed.xml', $m->generate(array('controller'=>'xml', 'action'=>'articlerss', 'id'=>4)));
        $this->assertEquals('/xml/rss/feed.xml', $m->generate(array('controller'=>'xml', 'action'=>'rss')));
        $this->assertEquals('/admin/comments/article/4/view/2', $m->generate(array('controller'=>'admin/comments', 'action'=>'view', 'article_id'=>4, 'id'=>2)));
        $this->assertEquals('/admin/comments/article/4/index', $m->generate(array('controller'=>'admin/comments', 'action'=>'index', 'article_id'=>4)));
        $this->assertEquals('/admin/comments/article/4', $m->generate(array('controller'=>'admin/comments', 'article_id'=>4, 'action'=>null)));
        $this->assertEquals('/articles/2004/2/20/page/1', $m->generate(array('controller'=>'articles', 'action'=>'find_by_date', 'year'=>2004, 'month'=>2, 'day'=>20, 'page'=>1)));
        $this->assertEquals('/articles/category/bingo', $m->generate(array('controller'=>'articles', 'action'=>'category', 'id' => 'bingo')));
        $this->assertEquals('/xml/index/feed.xml', $m->generate(array('controller'=>'xml', 'action'=>'index')));
        $this->assertEquals('/xml/articlerss/feed.xml', $m->generate(array('controller'=>'xml', 'action'=>'articlerss')));

        $this->assertEquals('/pages/the/idiot/has/spoken', $m->generateFromAlias('page', array('name'=>'the/idiot/has/spoken')));
        $this->assertEquals('/articles', $m->generateFromAlias('articles'));
        $this->assertEquals('/', $m->generateFromAlias('home'));
        $this->assertEquals('/admin', $m->generateFromAlias('admin'));
        $this->assertEquals('/xml/articlerss/4/feed.xml', $m->generateFromAlias('articles_rss', array('id'=>4)));
        $this->assertEquals('/xml/rss/feed.xml', $m->generateFromAlias('action_feed', array('action'=>'rss')));
        $this->assertEquals('/admin/comments/article/4/view/2', $m->generateFromAlias('article_comments', array('action'=>'view', 'article_id'=>4, 'id'=>2)));
        $this->assertEquals('/admin/comments/article/4/index', $m->generateFromAlias('article_comments', array('action'=>'index', 'article_id'=>4)));
        $this->assertEquals('/admin/comments/article/4', $m->generateFromAlias('article_comments', array('article_id'=>4, 'action'=>null)));
        $this->assertEquals('/articles/2004/2/20/page/1', $m->generateFromAlias('articles_history', array('year'=>2004, 'month'=>2, 'day'=>20, 'page'=>1)));
        $this->assertEquals('/articles/category/bingo', $m->generateFromAlias('article_category', array('id' => 'bingo')));
        $this->assertEquals('/xml/index/feed.xml', $m->generateFromAlias('action_feed', array('action'=>'index')));
        $this->assertEquals('/xml/articlerss/feed.xml', $m->generateFromAlias('action_feed', array('action'=>'articlerss')));

        $this->assertEquals(false, $m->generate(array('controller'=>'admin/comments', 'id'=>2)));
        $this->assertEquals(false, $m->generate(array('controller'=>'articles', 'action'=>'find_by_date', 'year'=>2004)));
        
        $this->assertEquals(false, $m->generateFromAlias('article_comments', array('id'=>2)));
        $this->assertEquals(false, $m->generateFromAlias('article_history', array('year'=>2004)));

    }

    public function testBothRequirementsAndOptional()
    {
        $m = Net_URL_Mapper::getInstance();
        $m->reset();
        $m->connect('test/:year', array('controller'=>'post', 'action'=>'show', 'year'=>null), array('year'=>'\d{4}'), 'test');
        $this->assertEquals('/test', $m->generate(array('controller'=>'post', 'action'=>'show')));
        $this->assertEquals('/test', $m->generate(array('controller'=>'post', 'action'=>'show', 'year'=>null)));
        $this->assertEquals('/test/2004', $m->generate(array('controller'=>'post', 'action'=>'show', 'year'=>2004)));
        $this->assertEquals(false, $m->generate(array('controller'=>'post', 'action'=>'show', 'year'=>'abcd')));

        $this->assertEquals('/test', $m->generateFromAlias('test'));
        $this->assertEquals('/test', $m->generateFromAlias('test', array('year'=>null)));
        $this->assertEquals('/test', $m->generateFromAlias('test', array('year'=>false)));
        $this->assertEquals('/test/2004', $m->generateFromAlias('test', array('year'=>2004)));
        $this->assertEquals(false, $m->generateFromAlias('test', array('year'=>'abcd')));
    }

    public function testQstring()
    {
        $m = Net_URL_Mapper::getInstance();
        $m->reset();
        $m->connect('', array('controller'=>'articles', 'action'=>'index'), null, 'root');
        $m->connect('articles/category/:id', array('controller'=>'articles', 'action'=>'category'), null, 'article_category');

        $this->assertEquals('/articles/category/bingo?test=x%2Fy', $m->generate(array('controller'=>'articles', 'action'=>'category', 'id' => 'bingo'), array('test'=>'x/y')));
        $this->assertEquals('/?test=x%2Fy', $m->generate(null, array('test'=>'x/y')));
        $this->assertEquals('/?test=x%2Fy', $m->generate(array('controller'=>'articles', 'action'=>'index'), array('test'=>'x/y')));

        $this->assertEquals('/articles/category/bingo?test=x%2Fy', $m->generateFromAlias('article_category', array('id' => 'bingo'), array('test'=>'x/y')));
        $this->assertEquals('/?test=x%2Fy', $m->generateFromAlias('root', null, array('test'=>'x/y')));
        $this->assertEquals('/?test=x%2Fy', $m->generateFromAlias('root', array('controller'=>'articles', 'action'=>'index'), array('test'=>'x/y')));
    }

    public function testUrlWithPrefix()
    {
        $m = Net_URL_Mapper::getInstance();
        $m->reset();
        $m->setPrefix('/en');
        $m->connect(':controller/:action/:id', null, null, 'action');
        $this->assertEquals('/en/content/view', $m->generate(array('controller'=>'content', 'action'=>'view', 'id'=>null)));
        $this->assertEquals('/en/content/view/3', $m->generate(array('controller'=>'content', 'action'=>'view', 'id'=>3)));

        $this->assertEquals('/en/content/view', $m->generateFromAlias('action', array('controller'=>'content', 'action'=>'view', 'id'=>null)));
        $this->assertEquals('/en/content/view/3', $m->generateFromAlias('action', array('controller'=>'content', 'action'=>'view', 'id'=>3)));
    }

    public function testUrlWithPrefixDefault()
    {
        $m = Net_URL_Mapper::getInstance();
        $m->reset();
        $m->setPrefix('/en');
        $m->connect('', array('controller'=>'test'), null, 'root');
        $this->assertEquals('/en/', $m->generate(array('controller'=>'test')));
        $this->assertEquals('/en/', $m->generate());
        $this->assertEquals(false, $m->generate(array('controller'=>'false')));

        $this->assertEquals('/en/', $m->generateFromAlias('root'));
        $this->assertEquals(false, $m->generateFromAlias('root', array('controller'=>'false')));

    }

    public function testRouteWithEndExtension()
    {
        $m = Net_URL_Mapper::getInstance();
        $m->reset();
        $m->connect(':controller/:(action)-:(id).html', null, null, 'html_file');
        $this->assertEquals(false, $m->generate(array('controller'=>'content', 'action'=>'view')));
        $this->assertEquals(false, $m->generate(array('controller'=>'content')));
        $this->assertEquals('/content/view-3.html', $m->generate(array('controller'=>'content', 'action'=>'view', 'id'=>3)));
        $this->assertEquals('/content/index-2.html', $m->generate(array('controller'=>'content', 'action'=>'index', 'id'=>2)));

        $this->assertEquals(false, $m->generateFromAlias('html_file', array('controller'=>'content', 'action'=>'view')));
        $this->assertEquals(false, $m->generateFromAlias('html_file', array('controller'=>'content')));
        $this->assertEquals('/content/view-3.html', $m->generateFromAlias('html_file', array('controller'=>'content', 'action'=>'view', 'id'=>3)));
        $this->assertEquals('/content/index-2.html', $m->generateFromAlias('html_file', array('controller'=>'content', 'action'=>'index', 'id'=>2)));
    }

    public function testMultipleInstances()
    {
        $en = Net_URL_Mapper::getInstance('en');
        $en->setPrefix('/en');
        $en->connect('pictures/by_category/:id', array('controller'=>'pictures', 'action'=>'by_category'), null, 'pictures_category');

        $fr = Net_URL_Mapper::getInstance('fr');
        $fr->setPrefix('/fr');
        $fr->connect('photos/par_categorie/:id', array('controller'=>'pictures', 'action'=>'by_category'), null, 'pictures_category');

        $this->assertEquals('/en/pictures/by_category/bingo', $en->generate(array('controller'=>'pictures', 'action'=>'by_category', 'id' => 'bingo')));
        $this->assertEquals('/fr/photos/par_categorie/bingo', $fr->generate(array('controller'=>'pictures', 'action'=>'by_category', 'id' => 'bingo')));

        $this->assertEquals('/en/pictures/by_category/bingo', $en->generateFromAlias('pictures_category', array('id' => 'bingo')));
        $this->assertEquals('/fr/photos/par_categorie/bingo', $fr->generateFromAlias('pictures_category', array('id' => 'bingo')));
    }


    public function testSharp()
    {
        $m = Net_URL_Mapper::getInstance();
        $m->reset();
        $m->connect('/account/:action/:handle*(section)',
            array('module'=>'account', 'section'=>'#default'),
            array('handle'=>'[a-zA-Z0-9]{3,12}',
                  'section'=>'#\w+'),
            'account');

        $this->assertEquals('/account/edit/mansion#password',
            $m->generate(array('module'=>'account',
                'action'=>'edit',
                'handle'=>'mansion',
                'section'=>'#password')));

        $this->assertEquals('/account/edit/mansion#default',
            $m->generate(array(
                'module'=>'account',
                'action'=>'edit',
                'handle'=>'mansion')));

        $this->assertEquals('/account/edit/mansion#password',
            $m->generateFromAlias('account', array(
                'action'=>'edit',
                'handle'=>'mansion',
                'section'=>'#password')));

        $this->assertEquals('/account/edit/mansion#default',
            $m->generateFromAlias('account', array(
                'action'=>'edit',
                'handle'=>'mansion')));
    }


    // TODO:
    // Ajoute validation avec visitor










}

















?>
