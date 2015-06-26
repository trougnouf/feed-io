<?php
/*
 * This file is part of the feed-io package.
 *
 * (c) Alexandre Debril <alex.debril@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FeedIo\Feed;

use FeedIo\Feed\Node\Element;
use FeedIo\Feed\Item\Media;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \FeedIo\Feed\Item
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new Item();
    }

    public function testGetElementIterator()
    {
        $element = new Element();
        $element->setName('foo');

        $this->object->addElement($element);

        $element2 = new Element();
        $element2->setName('bar');

        $this->object->addElement($element2);
        $iterator = $this->object->getElementIterator('foo');

        $this->assertInstanceOf('\FeedIo\Feed\Node\ElementIterator', $iterator);
        $this->assertTrue($iterator->count() > 0);

        $count = 0;
        foreach ($iterator as $element) {
            $count++;
            $this->assertEquals('foo', $element->getName());
        }

        $this->assertEquals(1, $count);
    }

    public function testNewElement()
    {
        $this->assertInstanceOf('\FeedIo\Feed\Node\ElementInterface', $this->object->newElement());
    }

    public function testSet()
    {
        $this->object->set('foo', 'bar');
        $this->assertEquals('bar', $this->object->getValue('foo'));
    }

    public function testGetValue()
    {
        $this->assertNull($this->object->getValue('null'));
        $this->object->set('name', 'value');

        $this->assertEquals('value', $this->object->getValue('name'));
    }

    public function testSetValue()
    {
        $this->object->set('foo', 'bar');

        $element = new Element();
        $element->setName('foo');
        $element->setValue('bar');

        $this->assertAttributeContainsOnly($element, 'elements', $this->object);
    }

    public function testHasElement()
    {
        $this->assertFalse($this->object->hasElement('foo'));
        $this->object->set('name', 'value');

        $this->assertFalse($this->object->hasElement('foo'));
        $this->assertTrue($this->object->hasElement('name'));
    }

    public function testGetAllElements()
    {
        $element = new Element();
        $element->setName('foo');

        $this->object->addElement($element);

        $element2 = new Element();
        $element2->setName('bar');

        $this->object->addElement($element2);

        $iterator = $this->object->getAllElements();

        $this->assertInstanceOf('\ArrayIterator', $iterator);
        $this->assertEquals(2, $iterator->count());
    }

    public function testListElements()
    {
        $element = new Element();
        $element->setName('foo');

        $this->object->addElement($element);

        $element2 = new Element();
        $element2->setName('bar');

        $this->object->addElement($element2);

        $this->assertEquals(array('foo', 'bar'), $this->object->listElements());
    }

    public function testNewMedia()
    {
        $this->assertInstanceOf('\FeedIo\Feed\Item\MediaInterface', $this->object->newMedia());
    }

    public function testAddMedia()
    {
        $media = new Media();
        $media->setType('audio/mp3');

        $this->assertInstanceOf('FeedIo\Feed\Item', $this->object->addMedia($media));

        $this->assertAttributeContains($media, 'medias', $this->object);
    }

    public function testHasMedia()
    {
        $this->assertFalse($this->object->hasMedia());

        $this->object->addMedia(new Media());

        $this->assertTrue($this->object->hasMedia());
    }

    public function testGetMedias()
    {
        $this->object->addMedia(new Media());

        $iterator = $this->object->getMedias();
        $this->assertInstanceOf('\ArrayIterator', $iterator);
        $count = 0;
        foreach ($iterator as $media) {
            $count++;
            $this->assertInstanceOf('FeedIo\Feed\Item\MediaInterface', $media);
        }

        $this->assertEquals(1, $count);
    }
}
