<?php
/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Filter\Tests;

use Joomla\Filter\OutputFilter;
use PHPUnit\Framework\TestCase;

/**
 * FilterTestObject
 *
 * @since  1.0
 */
class FilterTestObject
{
	public $string1;

	public $string2;

	public $string3;

	/**
	 * Sets up a dummy object for the output filter to be tested against
	 */
	public function __construct()
	{
		$this->string1 = "<script>alert();</script>";
		$this->string2 = "This is a test.";
		$this->string3 = "<script>alert(3);</script>";
		$this->array1  = [1, 2, 3];
	}
}

/**
 * Test class for Joomla\Filter\OutputFilter
 */
class OutputFilterTest extends TestCase
{
	/**
	 * @var  OutputFilter
	 */
	protected $object;

	/**
	 * @var  FilterTestObject
	 */
	protected $safeObject;

	/**
	 * @var  FilterTestObject
	 */
	protected $safeObjectArrayTest;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp(): void
	{
		$this->object = new OutputFilter;
		$this->safeObject = new FilterTestObject;
		$this->safeObjectArrayTest = new FilterTestObject;
	}

	/**
	 * Sends the FilterTestObject to the object filter.
	 */
	public function testObjectHtmlSafe()
	{
		$this->object->objectHtmlSafe($this->safeObject, null, 'string3');
		$this->assertEquals('&lt;script&gt;alert();&lt;/script&gt;', $this->safeObject->string1, "Script tag should be defused");
		$this->assertEquals('This is a test.', $this->safeObject->string2, "Plain text should pass");
		$this->assertEquals('<script>alert(3);</script>', $this->safeObject->string3, "This Script tag should be passed");
	}

	/**
	 * Sends the FilterTestObject to the object filter.
	 */
	public function testObjectHtmlSafeWithArray()
	{
		$this->object->objectHtmlSafe($this->safeObject, null, array('string1', 'string3'));
		$this->assertEquals('<script>alert();</script>', $this->safeObject->string1, "Script tag should pass array test");
		$this->assertEquals('This is a test.', $this->safeObject->string2, "Plain text should pass array test");
		$this->assertEquals('<script>alert(3);</script>', $this->safeObject->string3, "This Script tag should pass array test");
	}

	/**
	 * Tests enforcing XHTML links.
	 */
	public function testLinkXhtmlSafe()
	{
		$this->assertEquals(
			'<a href="http://www.example.com/index.frd?one=1&amp;two=2&amp;three=3">This & That</a>',
			$this->object->linkXhtmlSafe('<a href="http://www.example.com/index.frd?one=1&two=2&three=3">This & That</a>'),
			'Should clean ampersands only out of link, not out of link text'
		);
	}

	/**
	 * Tests making strings safe for usage in JS
	 */
	public function testStringJSSafe()
	{
		$this->assertEquals(
			'\u0054\u0065\u0073\u0074\u0020\u0073\u0074\u0072\u0069\u006e\u0067\u0020\u0045\u0073\u0070\u0061\u00f1\u006f\u006c\u0020\u0420\u0443\u0441\u0441\u043a\u0438\u0439\u0020\ud55c\uad6d\uc5b4\u0020\u{1f910}',
			$this->object->stringJSSafe('Test string Español Русский 한국어 🤐'),
			'Should convert the string to unicode escaped string'
		);
	}

	/**
	 * Tests filtering strings down to ASCII-7 lowercase URL text
	 */
	public function testStringUrlSafeWithoutALanguageInstance()
	{
		$this->assertEquals(
			'1234567890--qwertyuiop-qwertyuiop-asdfghjkl-asdfghjkl-zxcvbnm-zxcvbnm',
			$this->object->stringUrlSafe('`1234567890-=~!@#$%^&*()_+	qwertyuiop[]\QWERTYUIOP{}|asdfghjkl;\'ASDFGHJKL:"zxcvbnm,./ZXCVBNM<>?'),
			'Should clean keyboard string down to ASCII-7'
		);
	}

	/**
	 * Tests converting strings to URL unicoded slugs.
	 */
	public function testStringUrlUnicodeSlug()
	{
		$this->assertEquals(
			'what-if-i-do-not-get_this-right',
			$this->object->stringUrlUnicodeSlug('What-if I do.not get_this right?'),
			'Should be URL unicoded'
		);
	}

	/**
	 * Tests replacing single ampersands with the entity, but leaving double ampersands and ampsersand-octothorpe combinations intact.
	 */
	public function testAmpReplace()
	{
		$this->assertEquals(
			'&&george&amp;mary&#3son',
			$this->object->ampReplace('&&george&mary&#3son'),
			'Should replace single ampersands with HTML entity'
		);

		$this->assertEquals(
			'index.php?&&george&amp;mary&#3son&amp;this=that',
			$this->object->ampReplace('index.php?&&george&mary&#3son&this=that'),
			'Should replace single ampersands with HTML entity'
		);

		$this->assertEquals(
			'index.php?&&george&amp;mary&#3son&&&this=that',
			$this->object->ampReplace('index.php?&&george&mary&#3son&&&this=that'),
			'Should replace single ampersands with HTML entity'
		);

		$this->assertEquals(
			'index.php?&amp;this="this &amp; and that"',
			$this->object->ampReplace('index.php?&this="this & and that"'),
			'Should replace single ampersands with HTML entity'
		);

		$this->assertEquals(
			'index.php?&amp;this="this &amp; &amp; &&amp; and that"',
			$this->object->ampReplace('index.php?&this="this &amp; & &&amp; and that"'),
			'Should replace single ampersands with HTML entity'
		);
	}

	/**
	 * dataSet for Clean text
	 *
	 * @return  \Generator
	 */
	public function dataSet(): \Generator
	{
		yield 'case_1' => [
			'',
			'',
		];
		yield 'script_0' => [
			'<script>alert(\'hi!\');</script>',
			'',
		];
	}

	/**
	 * Execute a cleanText test case.
	 *
	 * @param   string  $data    The original output
	 * @param   string  $expect  The expected result for this test.
	 *
	 * @dataProvider dataSet
	 */
	public function testCleanText($data, $expect)
	{
		$this->assertEquals($expect, OutputFilter::cleanText($data));
	}

	/**
	 * Tests stripping images.
	 */
	public function testStripImages()
	{
		$this->assertEquals(
			'Hello  I am waving at you.',
			$this->object->stripImages('Hello <img src="wave.jpg"> I am waving at you.'),
			'Should remove img tags'
		);
	}

	/**
	 * Tests stripping iFrames.
	 */
	public function testStripIframes()
	{
		$this->assertEquals(
			'Hello  I am waving at you.',
			$this->object->stripIframes(
				'Hello <iframe src="http://player.vimeo.com/video/37576499" width="500" height="281" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe> I am waving at you.'
			),
			'Should remove iFrame tags'
		);
	}
}
