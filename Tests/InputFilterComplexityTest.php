<?php

/**
 * Part of the Joomla Framework Filter Package
 *
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Filter\Tests;

use Joomla\Filter\InputFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Regression tests guarding the runtime complexity of InputFilter.
 *
 * InputFilter::cleanTags() and InputFilter::escapeAttributeValues() used to copy the whole
 * remainder of the input on every single tag or attribute, which made them quadratic in the
 * length of the input. As every form field filtered as "string" or "html" is passed through
 * these methods, a few hundred kilobytes of malformed markup such as "<a x<a x<a x..." were
 * enough to keep a worker busy for seconds to minutes (denial of service).
 *
 * The tests below are deliberately written as scaling tests: they compare the runtime for an
 * input with the runtime for an input four times as large. A linear implementation needs
 * roughly four times as long, a quadratic one roughly sixteen times, so the limit in between
 * detects a reintroduction of the problem without depending on the speed of the machine.
 */
class InputFilterComplexityTest extends TestCase
{
    /**
     * Number of repetitions of the payload snippet for the small sample.
     */
    private const BASE_REPETITIONS = 20000;

    /**
     * The factor the input is grown by for the second sample.
     */
    private const GROWTH_FACTOR = 4;

    /**
     * The maximum runtime factor allowed when the input grows by GROWTH_FACTOR.
     *
     * Linear behaviour means a factor of about 4, quadratic behaviour a factor of about 16.
     */
    private const MAX_RUNTIME_FACTOR = 8.0;

    /**
     * Payloads which triggered quadratic behaviour.
     *
     * @return  array<string, array{0: string, 1: string}>
     */
    public static function dosPayloadProvider(): array
    {
        return [
            // Unterminated tag opens, the payload from the original report
            'unterminated tag opens (string)'  => ['<a x', 'string'],
            'unterminated tag opens (html)'    => ['<a x', 'html'],

            // Mal-formed tags: a second '<' before the first '>'
            'malformed tags'                   => ['a<b<c>', 'html'],
            'malformed tags without close'     => ['a<b<a x="1">', 'html'],

            // Nested tag opens
            'nested tag opens'                 => ['<a <b>', 'html'],

            // Well formed markup, hitting the closing tag lookup
            'plain tags'                       => ['<a>', 'html'],
            'closing tags'                     => ['</a>', 'html'],

            // Attribute parsing inside a tag
            'attributes without values'        => ['<a x x x x>', 'html'],
            'attributes with values'           => ['<a x="1" y z/ w=2>', 'html'],

            // escapeAttributeValues()
            'quoted attribute values'          => ['<a x="1">', 'html'],
            'unterminated attribute values'    => ['<a x="1', 'html'],

            // Blocked tags
            'blocked tags'                     => ['<script x', 'string'],
        ];
    }

    /**
     * @param   string  $snippet  The payload snippet which is repeated
     * @param   string  $type     The filter type to run the payload through
     */
    #[DataProvider('dosPayloadProvider')]
    #[Group('complexity')]
    public function testFilteringScalesLinearly(string $snippet, string $type): void
    {
        $small = str_repeat($snippet, self::BASE_REPETITIONS);
        $large = str_repeat($snippet, self::BASE_REPETITIONS * self::GROWTH_FACTOR);

        $smallRuntime = $this->runtime($small, $type);
        $largeRuntime = $this->runtime($large, $type);

        // Guard against a division by (almost) zero on very fast machines
        $smallRuntime = max($smallRuntime, 0.005);

        $factor = $largeRuntime / $smallRuntime;

        $this->assertLessThan(
            self::MAX_RUNTIME_FACTOR,
            $factor,
            sprintf(
                'Filtering %d KB took %.3fs and %d KB took %.3fs (factor %.1f), which indicates '
                . 'the filter is no longer linear in the length of its input.',
                \strlen($small) / 1024,
                $smallRuntime,
                \strlen($large) / 1024,
                $largeRuntime,
                $factor
            )
        );
    }

    /**
     * A one megabyte payload of unterminated tag opens, as an unauthenticated request could send
     * it to any field filtered as a string, must be filtered in well under a second.
     */
    #[Group('complexity')]
    public function testLargeMalformedPayloadIsFilteredQuickly(): void
    {
        $source = str_repeat('<a x', 256 * 1024);

        $this->assertLessThan(2.0, $this->runtime($source, 'string'));
    }

    /**
     * The rewrite of the scanning must not change the result of the filter.
     *
     * The expectations were taken from the implementation before the rewrite.
     *
     * @return  array<string, array{0: string, 1: string}>
     */
    public static function unchangedBehaviourProvider(): array
    {
        return [
            ['<a x<a x<a x', 'a xa x'],
            ['a<b<c>', 'a'],
            ['<a href="x" y z/ w=1>', '<a href="x" />'],
            ['<a <b>x</b>', 'a <b>x</b>'],
            ['<a x="1"  x  =  "2" >', '<a x="1" x="2" />'],
            ['</a><a>', '</a><a />'],
            ['<a x="<script>">', '<a x="&lt;script&gt;" />'],
            ["<a x='y' />", '<a x="y" />'],
            ['<', ''],
            ['<>', ''],
            ['<a', 'a'],
            ['text only', 'text only'],
        ];
    }

    /**
     * @param   string  $source    The string to filter
     * @param   string  $expected  The expected result
     */
    #[DataProvider('unchangedBehaviourProvider')]
    public function testBehaviourIsUnchanged(string $source, string $expected): void
    {
        $filter = new InputFilter(['a', 'b'], ['href', 'x']);

        $this->assertSame($expected, $filter->clean($source, 'html'));
        $this->assertSame($expected, $filter->clean($source, 'string'));
    }

    /**
     * Measure how long filtering the given source takes.
     *
     * @param   string  $source  The string to filter
     * @param   string  $type    The filter type
     *
     * @return  float  The runtime in seconds
     */
    private function runtime(string $source, string $type): float
    {
        $filter = new InputFilter(['a', 'b'], ['href', 'x', 'y', 'z', 'w']);

        $start = microtime(true);
        $filter->clean($source, $type);

        return microtime(true) - $start;
    }
}
