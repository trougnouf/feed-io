<?php
/*
 * This file is part of the feed-io package.
 *
 * (c) Alexandre Debril <alex.debril@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FeedIo;


use FeedIo\Feed\Item;
use FeedIo\Rule\DateTimeBuilder;
use Psr\Log\NullLogger;

class ParserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \FeedIo\ParserAbstract
     */
    protected $object;

    public function setUp()
    {
        $date = new DateTimeBuilder();
        $date->addDateFormat(\DateTime::ATOM);
        $standard = $this->getMockForAbstractClass(
            '\FeedIo\StandardAbstract',
            array($date)
        );
        $standard->expects($this->any())->method('canHandle')->will($this->returnValue(true));
        $standard->expects($this->any())->method('buildFeedRuleSet')->will($this->returnValue(new RuleSet()));
        $standard->expects($this->any())->method('parseBody')->will($this->returnValue(new Feed()));
        $standard->expects($this->any())->method('getMainElement')->will($this->returnValue(new \DOMElement('test')));

        $this->object = new Parser($standard, new NullLogger());
    }

    public function testParse()
    {
        $document = new \DOMDocument();
        $document->loadXML('<feed><items></items></feed>');
        $feed = $this->object->parse($document, new Feed());
        $this->assertInstanceOf('FeedIo\Feed', $feed);
    }

    public function testParseNode()
    {
        $document = new \DOMDocument();
        $xml = <<<XML
        <channel>
            <title>feed-io</title>
            <link>https://github.com/alexdebril/feed-io</link>
            <description>feed-io is a library</description>
        </channel>
XML;
        $document->loadXML($xml);
        $feed = new Feed();
        $this->object->parseNode($feed, $document->documentElement, new RuleSet());

        $this->assertInstanceOf('\Iterator', $feed->getElementIterator('description'));
        $iterator = $feed->getElementIterator('description');
        $count = 0;
        foreach ( $iterator as $element ) {
            $this->assertInstanceOf('\FeedIo\Feed\Node\ElementInterface', $element);
            $this->assertEquals('feed-io is a library', $element->getValue());
            $count++;
        }
        $this->assertEquals(1, $count);
    }

    /**
     * @expectedException \FeedIo\Parser\UnsupportedFormatException
     */
    public function testParseBadDocument()
    {
        $document = new \DOMDocument();
        $document->loadXML('<feed><items></items></feed>');

        $standard = $this->getMockForAbstractClass(
            '\FeedIo\StandardAbstract',
            array(new DateTimeBuilder())
        );
        $standard->expects($this->any())->method('canHandle')->will($this->returnValue(false));
        $parser = new Parser($standard, new NullLogger());

        $parser->parse($document, new Feed());
    }

    public function testIsValid()
    {
        $item = new Item();
        $item->setLastModified(new \DateTime('-1day'));

        $this->object->addFilter($this->getFilterMock(true));
        $this->assertTrue($this->object->isValid($item));
    }

    public function testIsNotValid()
    {
        $item = new Item();

        $this->object->addFilter($this->getFilterMock(false));
        $this->assertFalse($this->object->isValid($item));
    }

    public function testCheckStructure()
    {
        $rss = <<<RSS
<rss version="2.0">
    <channel>
        <title>RSS Title</title>
    </channel>
</rss>
RSS;
        $document = new \DOMDocument();
        $document->loadXML($rss);
        $this->assertInstanceOf(
            '\FeedIo\Parser',
            $this->object->checkBodyStructure($document, array('channel', 'title'))
        );
    }

    /**
     * @expectedException \FeedIo\Parser\MissingFieldsException
     */
    public function testCheckBadStructure()
    {
        $document = new \DOMDocument();
        $document->loadXML('<rss></rss>');
        $this->assertInstanceOf(
            '\FeedIo\Parser',
            $this->object->checkBodyStructure($document, array('channel'))
        );
    }

    /**
     * @param boolean $returnValue
     * @return \FeedIo\FilterInterface
     */
    protected function getFilterMock($returnValue)
    {
        $filter = $this->getMockForAbstractClass('\FeedIo\FilterInterface');
        $filter->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue($returnValue));

        return $filter;
    }

}
