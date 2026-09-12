<?php

/**
 * Part of the Joomla Framework Filter Package
 *
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Filter;

use Joomla\String\StringHelper;

/**
 * InputFilter is a class for filtering input from any data source
 *
 * Forked from the php input filter library by: Daniel Morris <dan@rootcube.com>
 * Original Contributors: Gianpaolo Racca, Ghislain Picard, Marco Wandschneider, Chris Tobin and Andrew Eddie.
 *
 * @since  1.0
 */
class InputFilter
{
    /**
     * Defines the InputFilter instance should only allow the supplied list of HTML tags.
     *
     * @var    integer
     * @since  1.4.0
     */
    public const ONLY_ALLOW_DEFINED_TAGS = 0;

    /**
     * Defines the InputFilter instance should block the defined list of HTML tags and allow all others.
     *
     * @var    integer
     * @since  1.4.0
     */
    public const ONLY_BLOCK_DEFINED_TAGS = 1;

    /**
     * Defines the InputFilter instance should only allow the supplied list of attributes.
     *
     * @var    integer
     * @since  1.4.0
     */
    public const ONLY_ALLOW_DEFINED_ATTRIBUTES = 0;

    /**
     * Defines the InputFilter instance should block the defined list of attributes and allow all others.
     *
     * @var    integer
     * @since  1.4.0
     */
    public const ONLY_BLOCK_DEFINED_ATTRIBUTES = 1;

    /**
     * The array of permitted tags.
     *
     * @var    array
     * @since  1.0
     */
    public $tagsArray;

    /**
     * The array of permitted tag attributes.
     *
     * @var    array
     * @since  1.0
     */
    public $attrArray;

    /**
     * The method for sanitising tags
     *
     * @var    integer
     * @since  1.0
     */
    public $tagsMethod;

    /**
     * The method for sanitising attributes
     *
     * @var    integer
     * @since  1.0
     */
    public $attrMethod;

    /**
     * A flag for XSS checks. Only auto clean essentials = 0, Allow clean blocked tags/attr = 1
     *
     * @var    integer
     * @since  1.0
     */
    public $xssAuto;

    /**
     * The list the blocked tags for the instance.
     *
     * @var    string[]
     * @since  1.0
     */
    public $blockedTags = [
        'applet',
        'body',
        'bgsound',
        'base',
        'basefont',
        'canvas',
        'embed',
        'frame',
        'frameset',
        'head',
        'html',
        'id',
        'iframe',
        'ilayer',
        'layer',
        'link',
        'meta',
        'name',
        'object',
        'script',
        'style',
        'title',
        'xml',
    ];

    /**
     * The list of blocked tag attributes for the instance.
     *
     * @var    string[]
     * @since  1.0
     */
    public $blockedAttributes = [
        'action',
        'background',
        'codebase',
        'dynsrc',
        'formaction',
        'lowsrc',
    ];

    /**
     * A special list of blocked characters.
     *
     * @var    string[]
     * @since  1.3.3
     */
    private $blockedChars = [
        '&tab;',
        '&newline;',
        '&space;',
        '&colon;',
        '&column;',
    ];

    /**
     * Constructor for InputFilter class.
     *
     * @param   array    $tagsArray   List of permitted HTML tags
     * @param   array    $attrArray   List of permitted HTML tag attributes
     * @param   integer  $tagsMethod  Method for filtering tags, should be one of the `ONLY_*_DEFINED_TAGS` constants
     * @param   integer  $attrMethod  Method for filtering attributes, should be one of the `ONLY_*_DEFINED_ATTRIBUTES` constants
     * @param   integer  $xssAuto     Only auto clean essentials = 0, Allow clean blocked tags/attributes = 1
     *
     * @since   1.0
     */
    public function __construct(
        array $tagsArray = [],
        array $attrArray = [],
        $tagsMethod = self::ONLY_ALLOW_DEFINED_TAGS,
        $attrMethod = self::ONLY_ALLOW_DEFINED_ATTRIBUTES,
        $xssAuto = 1
    ) {
        // Make sure user defined arrays are in lowercase
        $tagsArray = array_map('strtolower', (array) $tagsArray);
        $attrArray = array_map('strtolower', (array) $attrArray);

        // Assign member variables
        $this->tagsArray  = $tagsArray;
        $this->attrArray  = $attrArray;
        $this->tagsMethod = $tagsMethod;
        $this->attrMethod = $attrMethod;
        $this->xssAuto    = $xssAuto;
    }

    /**
     * Cleans the given input source based on the instance configuration and specified data type
     *
     * @param   string|string[]|object  $source  Input string/array-of-string/object to be 'cleaned'
     * @param   string                  $type    The return type for the variable:
     *                                           INT:       An integer
     *                                           UINT:      An unsigned integer
     *                                           FLOAT:     A floating point number
     *                                           BOOLEAN:   A boolean value
     *                                           WORD:      A string containing A-Z or underscores only (not case sensitive)
     *                                           ALNUM:     A string containing A-Z or 0-9 only (not case sensitive)
     *                                           CMD:       A string containing A-Z, 0-9, underscores, periods or hyphens (not case
     *                                                      sensitive)
     *                                           BASE64:    A string containing A-Z, 0-9, forward slashes, plus or equals (not case
     *                                                      sensitive)
     *                                           STRING:    A fully decoded and sanitised string (default)
     *                                           HTML:      A sanitised string
     *                                           ARRAY:     An array
     *                                           PATH:      A sanitised file path
     *                                           TRIM:      A string trimmed from normal, non-breaking and multibyte spaces
     *                                           USERNAME:  Do not use (use an application specific filter)
     *                                           RAW:       The raw string is returned with no filtering
     *                                           unknown:   An unknown filter will act like STRING. If the input is an array it will
     *                                                      return an array of fully decoded and sanitised strings.
     *
     * @return  mixed  'Cleaned' version of the `$source` parameter
     *
     * @since   1.0
     */
    public function clean($source, $type = 'string')
    {
        $type = ucfirst(strtolower($type));

        if ($type === 'Array') {
            return (array) $source;
        }

        if ($type === 'Raw') {
            return $source;
        }

        if (\is_array($source)) {
            $result = [];

            foreach ($source as $key => $value) {
                $result[$key] = $this->clean($value, $type);
            }

            return $result;
        }

        if (\is_object($source)) {
            foreach (get_object_vars($source) as $key => $value) {
                $source->$key = $this->clean($value, $type);
            }

            return $source;
        }

        $method = 'clean' . $type;

        if (method_exists($this, $method)) {
            return $this->$method((string) $source);
        }

        // Unknown filter method
        if (\is_string($source) && !empty($source)) {
            // Filter source for XSS and other 'bad' code etc.
            return $this->cleanString($source);
        }

        // Not an array or string... return the passed parameter
        return $source;
    }

    /**
     * Function to determine if contents of an attribute are safe
     *
     * @param   array  $attrSubSet  A 2 element array for attribute's name, value
     *
     * @return  boolean  True if bad code is detected
     *
     * @since   1.0
     */
    public static function checkAttribute($attrSubSet)
    {
        $attrSubSet[0] = strtolower($attrSubSet[0]);

        /*
         * Decode the value the same way a browser would before looking for a dangerous scheme.
         *
         * This has to happen BEFORE lowercasing: PHP's entity tables are case sensitive, so
         * lowercasing first would turn the HTML5 entity `&NewLine;` into the unknown `&newline;`
         * and it would survive decoding untouched.
         */
        $decoded = html_entity_decode($attrSubSet[1], \ENT_QUOTES | \ENT_HTML5, 'UTF-8');

        /*
         * html_entity_decode() only resolves numeric character references that are properly
         * terminated with a semicolon. Browsers are more forgiving and also consume unterminated
         * references such as `java&#10script:`, so resolve those here as well.
         */
        $decoded = preg_replace_callback(
            '/&#(x[0-9a-f]+|[0-9]+);?/i',
            static function ($matches) {
                $reference = $matches[1];

                if ($reference[0] === 'x' || $reference[0] === 'X') {
                    $codepoint = (int) hexdec(substr($reference, 1));
                } else {
                    $codepoint = (int) $reference;
                }

                // Ignore anything that is not a scalar Unicode value and keep the raw match instead.
                // Surrogates (U+D800 - U+DFFF) are excluded as they are not encodable on their own.
                if ($codepoint > 0x10FFFF || ($codepoint >= 0xD800 && $codepoint <= 0xDFFF)) {
                    return $matches[0];
                }

                // mb_chr() is used over chr() so codepoints above U+00FF are not truncated
                return mb_chr($codepoint, 'UTF-8');
            },
            $decoded
        );

        // Now that everything is decoded, normalise the value for the checks below
        $attrSubSet[1] = strtolower($decoded);

        // Remove common XSS-evasion characters
        $attrSubSet[1] = str_replace(["\t", "\n", "\r", " ", "\0", "\v", "\f"], '', $attrSubSet[1]);

        return (strpos($attrSubSet[1], 'expression') !== false && $attrSubSet[0] === 'style')
            || preg_match('/(?:(?:java|vb|live)script|behaviour|mocha)(?::|&colon;|&column;)/', $attrSubSet[1]) !== 0;
    }

    /**
     * Internal method to iteratively remove all unwanted tags and attributes
     *
     * @param   string  $source  Input string to be 'cleaned'
     *
     * @return  string  'Cleaned' version of input parameter
     *
     * @since   1.0
     */
    protected function remove($source)
    {
        // Iteration provides nested tag protection
        do {
            $temp   = $source;
            $source = $this->cleanTags($source);
        } while ($temp !== $source);

        return $source;
    }

    /**
     * Internal method to strip a string of disallowed tags
     *
     * @param   string  $source  Input string to be 'cleaned'
     *
     * @return  string  'Cleaned' version of input parameter
     *
     * @since   1.0
     */
    protected function cleanTags($source)
    {
        // First, pre-process this for illegal characters inside attribute values
        $source = $this->escapeAttributeValues($source);

        /*
         * The source is walked with an offset instead of repeatedly creating substrings of the
         * remainder. Doing the latter makes the whole method quadratic in the length of the
         * input, which allows a denial of service with a moderately sized payload of malformed
         * tags (e.g. "<a x<a x<a x...").
         */
        $sourceLength = \strlen($source);
        $offset       = 0;

        // In the beginning we don't really have a tag, so everything is postTag
        $preTag = null;

        /*
         * Caches for the forward scans below. Since the offsets only ever move forward, the
         * position of the next occurrence can be remembered instead of re-scanning the whole
         * remainder of the source on every iteration.
         */
        $ltCache    = [];
        $gtCache    = [];
        $closeCache = [];

        // Is there a tag? If so it will certainly start with a '<'.
        $tagOpenStart = $this->findCached($source, '<', 0, $ltCache);

        while ($tagOpenStart !== false) {
            // Get some information about the tag we are processing
            $preTag .= substr($source, $offset, $tagOpenStart);
            $offset += $tagOpenStart;

            // Everything from here on is relative to the tag opening at $offset
            $tagOpenEnd = $this->findCached($source, '>', $offset + 1, $gtCache);

            // Check for mal-formed tag where we have a second '<' before the first '>'
            $nextOpenTag = (($sourceLength - $offset) > $tagOpenStart)
                ? $this->findFrom($source, '<', $offset + $tagOpenStart + 1)
                : false;

            if ($nextOpenTag !== false) {
                $nextOpenTag += $tagOpenStart + 1;
            }

            if (($nextOpenTag !== false) && ($nextOpenTag < $tagOpenEnd)) {
                /*
                 * At this point we have a mal-formed tag -- remove the offending open.
                 * The character at $tagOpenStart is dropped by shifting the (short) prefix in
                 * front of it one byte to the right, which keeps this branch cheap.
                 */
                if ($tagOpenStart > 0) {
                    for ($i = $offset + $tagOpenStart; $i > $offset; $i--) {
                        $source[$i] = $source[$i - 1];
                    }

                    /*
                     * The '>' cache stays valid as the moved bytes cannot contain a '>' (the first
                     * one is located behind the nested open), the closing tag caches may be
                     * invalidated by the moved bytes and are repaired.
                     */
                    $this->repairCaches($closeCache, $source, $offset, $offset + $tagOpenStart);

                    $offset++;

                    // The opening '<' of this tag now sits at the new offset
                    $ltCache      = ['from' => $offset, 'pos' => $offset];
                    $tagOpenStart = 0;

                    continue;
                }

                $offset++;
                $tagOpenStart = $this->findCached($source, '<', $offset, $ltCache);

                continue;
            }

            // Let's catch any non-terminated tags and skip over them
            if ($tagOpenEnd === false) {
                $offset       = min($sourceLength, $offset + $tagOpenStart + 1);
                $tagOpenStart = $this->findCached($source, '<', $offset, $ltCache);

                continue;
            }

            // Do we have a nested tag?
            $tagOpenNested = $this->findCached($source, '<', $offset + 1, $ltCache);

            if (($tagOpenNested !== false) && ($tagOpenNested < $tagOpenEnd)) {
                $preTag .= substr($source, $offset + 1, $tagOpenNested);
                $offset += $tagOpenNested + 1;
                $tagOpenStart = $this->findCached($source, '<', $offset, $ltCache);

                continue;
            }

            // Let's get some information about our tag and setup attribute pairs
            $currentTag    = substr($source, $offset + 1, $tagOpenEnd);
            $tagLength     = strlen($currentTag);
            $tagLeftOffset = 0;
            $attrSet       = [];
            $currentSpace  = $this->findFrom($currentTag, ' ', 0);

            // Scan caches for the attribute loop below, see the caches for the source above
            $eqCache    = [];
            $spaceCache = [];
            $quoteCache = [];
            $slashCache = [];
            $pregCache  = null;

            // Are we an open tag or a close tag?
            if (substr($currentTag, 0, 1) === '/') {
                // Close Tag
                $isCloseTag    = true;
                list($tagName) = explode(' ', $currentTag);
                $tagName       = substr($tagName, 1);
            } else {
                // Open Tag
                $isCloseTag    = false;
                list($tagName) = explode(' ', $currentTag);
            }

            /*
             * No tagname
             * OR exclude all "non-regular" tagnames
             * OR remove if xssauto is on and tag is blocked
             */
            if (
                (!$tagName)
                || (!preg_match('/^[a-z][a-z0-9]*$/i', $tagName))
                || ((\in_array(strtolower($tagName), $this->blockedTags)) && $this->xssAuto)
            ) {
                $offset       = min($sourceLength, $offset + $tagLength + 2);
                $tagOpenStart = $this->findCached($source, '<', $offset, $ltCache);

                // Strip tag
                continue;
            }

            /*
             * Time to grab any attributes from the tag... need this section in
             * case attributes have spaces in the values.
             */
            while ($currentSpace !== false) {
                $attr = '';

                // Offset of the former "$fromSpace" within the current tag
                $fromSpace = $tagLeftOffset + $currentSpace + 1;

                $nextEqual  = $this->findCached($currentTag, '=', $fromSpace, $eqCache);
                $nextSpace  = $this->findCached($currentTag, ' ', $fromSpace, $spaceCache);
                $openQuotes = $this->findCached($currentTag, '"', $fromSpace, $quoteCache);
                $closeQuotes = (int) $this->findCached($currentTag, '"', $fromSpace + (int) $openQuotes + 1, $quoteCache)
                    + (int) $openQuotes + 1;

                /*
                 * Find position of equal and open quotes ignoring. The position of the first match
                 * is cached as well; as $fromSpace only moves forward a cached match which starts
                 * at or after $fromSpace is by definition still the first one.
                 */
                if (
                    $pregCache === null
                    || $pregCache['from'] > $fromSpace
                    || ($pregCache['pos'] !== false && $pregCache['pos'] < $fromSpace)
                ) {
                    if (
                        $fromSpace <= $tagLength
                        && preg_match('#\s*=\s*\"#', $currentTag, $matches, \PREG_OFFSET_CAPTURE, $fromSpace)
                    ) {
                        $pregCache = ['from' => $fromSpace, 'pos' => $matches[0][1], 'text' => $matches[0][0]];
                    } else {
                        $pregCache = ['from' => $fromSpace, 'pos' => false, 'text' => ''];
                    }
                }

                if ($pregCache['pos'] !== false) {
                    $startAttPosition = $pregCache['pos'] - $fromSpace;
                    $startAtt         = $pregCache['text'];
                    $closeQuotePos    = $this->findCached(
                        $currentTag,
                        '"',
                        $fromSpace + $startAttPosition + strlen($startAtt),
                        $quoteCache
                    );
                    $closeQuotes = (int) $closeQuotePos + $startAttPosition + strlen($startAtt);
                    $nextEqual   = $startAttPosition + strpos($startAtt, '=');
                    $openQuotes  = $startAttPosition + strpos($startAtt, '"');
                    $nextSpace   = (int) $this->findCached($currentTag, ' ', $fromSpace + $closeQuotes, $spaceCache)
                        + $closeQuotes;
                }

                $isSlashOnly = ($tagLength - $fromSpace === 1) && $currentTag[$fromSpace] === '/';

                // Do we have an attribute to process? [check for equal sign]
                if (!$isSlashOnly && (($nextEqual && $nextSpace && $nextSpace < $nextEqual) || !$nextEqual)) {
                    if (!$nextEqual) {
                        $attribEnd = (int) $this->findCached($currentTag, '/', $fromSpace, $slashCache) - 1;
                    } else {
                        $attribEnd = $nextSpace - 1;
                    }

                    // If there is an ending, use this, if not, do not worry.
                    if ($attribEnd > 0) {
                        $fromSpace   = min($tagLength, $fromSpace + $attribEnd + 1);
                        $isSlashOnly = ($tagLength - $fromSpace === 1) && $currentTag[$fromSpace] === '/';
                    }
                }

                if ($this->findCached($currentTag, '=', $fromSpace, $eqCache) !== false) {
                    /*
                     * If the attribute value is wrapped in quotes we need to grab the substring from the closing quote,
                     * otherwise grab until the next space.
                     */
                    if (
                        ($openQuotes !== false)
                        && (
                            $this->findCached($currentTag, '"', min($tagLength, $fromSpace + $openQuotes + 1), $quoteCache)
                            !== false
                        )
                    ) {
                        $attr = substr($currentTag, $fromSpace, ($closeQuotes + 1));
                    } else {
                        $attr = substr($currentTag, $fromSpace, $nextSpace);
                    }
                } else {
                    // No more equal signs so add any extra text in the tag into the attribute array [eg. checked]
                    if (!$isSlashOnly) {
                        $attr = substr($currentTag, $fromSpace, $nextSpace);
                    }
                }

                // Last Attribute Pair
                if (!$attr && !$isSlashOnly) {
                    $attr = substr($currentTag, $fromSpace);
                }

                // Add attribute pair to the attribute array
                $attrSet[] = $attr;

                // Move search point and continue iteration
                $tagLeftOffset = min($tagLength, $fromSpace + strlen($attr));
                $currentSpace  = $this->findCached($currentTag, ' ', $tagLeftOffset, $spaceCache);
            }

            // Is our tag in the user input array?
            $tagFound = \in_array(strtolower($tagName), $this->tagsArray);

            // If the tag is allowed let's append it to the output string.
            if ((!$tagFound && $this->tagsMethod) || ($tagFound && !$this->tagsMethod)) {
                // Reconstruct tag with allowed attributes
                if (!$isCloseTag) {
                    // Open or single tag
                    $attrSet = $this->cleanAttributes($attrSet);
                    $preTag .= '<' . $tagName;

                    for ($i = 0, $count = \count($attrSet); $i < $count; $i++) {
                        $preTag .= ' ' . $attrSet[$i];
                    }

                    // Reformat single tags to XHTML
                    if (!isset($closeCache[$tagName])) {
                        $closeCache[$tagName] = [];
                    }

                    $closeTagPosition = $this->findCached($source, '</' . $tagName, $offset + 1, $closeCache[$tagName]);

                    if ($closeTagPosition !== false && $closeTagPosition !== 0) {
                        $preTag .= '>';
                    } else {
                        $preTag .= ' />';
                    }
                } else {
                    // Closing tag
                    $preTag .= '</' . $tagName . '>';
                }
            }

            // Find next tag's start and continue iteration
            $offset       = min($sourceLength, $offset + $tagLength + 2);
            $tagOpenStart = $this->findCached($source, '<', $offset, $ltCache);
        }

        // Append any code after the end of tags and return
        $postTag = substr($source, $offset);

        if ($postTag !== '<') {
            $preTag .= $postTag;
        }

        return $preTag;
    }

    /**
     * Repair the closing tag search caches after bytes of the source have been moved.
     *
     * The malformed tag handling in cleanTags() drops a single character by moving the bytes in
     * front of it one position to the right. Cached positions behind the moved bytes stay valid,
     * but the moved bytes themselves may create a new, earlier occurrence, so the (small) changed
     * window is re-checked.
     *
     * @param   array   $caches  The caches, keyed by tag name, passed by reference
     * @param   string  $source  The source after the bytes have been moved
     * @param   integer $start   The index of the first changed byte
     * @param   integer $end     The index of the last changed byte
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function repairCaches(array &$caches, $source, $start, $end)
    {
        foreach ($caches as $tagName => $cache) {
            if (!isset($cache['from'])) {
                continue;
            }

            $needle       = '</' . $tagName;
            $needleLength = \strlen($needle);

            // An occurrence in front of the changed bytes is recomputed by findCached() anyway
            if ($cache['pos'] !== false && $cache['pos'] <= $end) {
                unset($caches[$tagName]);

                continue;
            }

            $windowStart = max($cache['from'], $start - $needleLength + 1, 0);
            $window      = substr($source, $windowStart, ($end - $windowStart) + $needleLength);
            $hit         = strpos($window, $needle);

            if ($hit !== false) {
                $hit += $windowStart;

                if ($cache['pos'] === false || $hit < $cache['pos']) {
                    $caches[$tagName]['pos'] = $hit;
                }
            }
        }
    }

    /**
     * Find the first occurrence of a needle at or after the given offset, remembering the result.
     *
     * The scans in cleanTags() only ever move forward, so a remembered position which lies at or
     * behind the requested offset is by definition still the first occurrence. Without this the
     * repeated scans for a character which is not present (e.g. the '>' of a never terminated tag)
     * make cleanTags() quadratic in the length of its input.
     *
     * @param   string   $haystack  The string to search in
     * @param   string   $needle    The string to search for
     * @param   integer  $start     The offset to start searching at
     * @param   array    $cache     The cache holding the last search, passed by reference
     *
     * @return  integer|boolean  The position relative to `$start` or false if not found
     *
     * @since   __DEPLOY_VERSION__
     */
    private function findCached($haystack, $needle, $start, array &$cache)
    {
        if (
            !isset($cache['from'])
            || $cache['from'] > $start
            || ($cache['pos'] !== false && $cache['pos'] < $start)
        ) {
            $position = $this->findFrom($haystack, $needle, $start);

            $cache = [
                'from' => $start,
                'pos'  => $position === false ? false : $position + $start,
            ];
        }

        return $cache['pos'] === false ? false : $cache['pos'] - $start;
    }

    /**
     * Find the first occurrence of a needle at or after the given offset.
     *
     * This is the offset based equivalent of `strpos(substr($haystack, $start), $needle)`, i.e. the
     * returned position is relative to `$start`, without creating a copy of the haystack.
     *
     * @param   string   $haystack  The string to search in
     * @param   string   $needle    The string to search for
     * @param   integer  $start     The offset to start searching at
     *
     * @return  integer|boolean  The position relative to `$start` or false if not found
     *
     * @since   __DEPLOY_VERSION__
     */
    private function findFrom($haystack, $needle, $start)
    {
        if ($start >= \strlen($haystack)) {
            return false;
        }

        $position = strpos($haystack, $needle, $start);

        return $position === false ? false : $position - $start;
    }

    /**
     * Internal method to strip a tag of disallowed attributes
     *
     * @param   array  $attrSet  Array of attribute pairs to filter
     *
     * @return  array  Filtered array of attribute pairs
     *
     * @since   1.0
     */
    protected function cleanAttributes(array $attrSet)
    {
        $newSet = [];

        $count = \count($attrSet);

        // Iterate through attribute pairs
        for ($i = 0; $i < $count; $i++) {
            // Skip blank spaces
            if (!$attrSet[$i]) {
                continue;
            }

            // Split into name/value pairs
            $attrSubSet = explode('=', trim($attrSet[$i]), 2);

            // Take the last attribute in case there is an attribute with no value
            $attrSubSet0   = explode(' ', trim($attrSubSet[0]));
            $attrSubSet[0] = array_pop($attrSubSet0);

            $attrSubSet[0] = strtolower($attrSubSet[0]);
            $quoteStyle    = \ENT_QUOTES | \ENT_HTML401;

            // Remove all spaces as valid attributes does not have spaces.
            $attrSubSet[0] = html_entity_decode($attrSubSet[0], $quoteStyle, 'UTF-8');
            $attrSubSet[0] = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $attrSubSet[0]);
            $attrSubSet[0] = preg_replace('/\s+/u', '', $attrSubSet[0]);

            // Remove blocked chars from the attribute name
            foreach ($this->blockedChars as $blockedChar) {
                $attrSubSet[0] = str_ireplace($blockedChar, '', $attrSubSet[0]);
            }

            // Remove all symbols
            $attrSubSet[0] = preg_replace('/[^\p{L}\p{N}\-\s]/u', '', $attrSubSet[0]);

            // Remove all "non-regular" attribute names
            // AND blocked attributes
            if (
                (!preg_match('/[a-z]*$/i', $attrSubSet[0]))
                || ($this->xssAuto && ((\in_array(strtolower($attrSubSet[0]), $this->blockedAttributes))
                || substr($attrSubSet[0], 0, 2) == 'on'))
            ) {
                continue;
            }

            // XSS attribute value filtering
            if (!isset($attrSubSet[1])) {
                continue;
            }

            // Remove blocked chars from the attribute value
            foreach ($this->blockedChars as $blockedChar) {
                $attrSubSet[1] = str_ireplace($blockedChar, '', $attrSubSet[1]);
            }

            // Trim leading and trailing spaces
            $attrSubSet[1] = trim($attrSubSet[1]);

            // Strips unicode, hex, etc
            $attrSubSet[1] = str_replace('&#', '', $attrSubSet[1]);

            // Strip tab and newline within attr value (browsers drop these when parsing a URL)
            $attrSubSet[1] = preg_replace('/[\t\n\r]/', '', $attrSubSet[1]);

            // Strip double quotes
            $attrSubSet[1] = str_replace('"', '', $attrSubSet[1]);

            // Convert single quotes from either side to doubles (Single quotes shouldn't be used to pad attr values)
            if ((substr($attrSubSet[1], 0, 1) == "'") && (substr($attrSubSet[1], (\strlen($attrSubSet[1]) - 1), 1) == "'")) {
                $attrSubSet[1] = substr($attrSubSet[1], 1, (\strlen($attrSubSet[1]) - 2));
            }

            // Strip slashes
            $attrSubSet[1] = stripslashes($attrSubSet[1]);

            // Autostrip script tags
            if (static::checkAttribute($attrSubSet)) {
                continue;
            }

            // Strip html data URIs
            if (stripos($attrSubSet[1], 'data:text/html') === 0) {
                continue;
            }

            // Is our attribute in the user input array?
            $attrFound = \in_array(strtolower($attrSubSet[0]), $this->attrArray);

            // If the tag is allowed lets keep it
            if ((!$attrFound && $this->attrMethod) || ($attrFound && !$this->attrMethod)) {
                // Does the attribute have a value?
                if (empty($attrSubSet[1]) === false) {
                    $newSet[] = $attrSubSet[0] . '="' . $attrSubSet[1] . '"';
                } elseif ($attrSubSet[1] === '0') {
                    // Special Case
                    // Is the value 0?
                    $newSet[] = $attrSubSet[0] . '="0"';
                } else {
                    // Leave empty attributes alone
                    $newSet[] = $attrSubSet[0] . '=""';
                }
            }
        }

        return $newSet;
    }

    /**
     * Escape < > and " inside attribute values
     *
     * @param   string  $source  The source string.
     *
     * @return  string  Filtered string
     *
     * @since   1.0
     */
    protected function escapeAttributeValues($source)
    {
        $alreadyFiltered = '';
        $badChars        = ['<', '"', '>'];
        $escapedChars    = ['&lt;', '&quot;', '&gt;'];

        /*
         * As in cleanTags() the source is walked with an offset. Cutting the already processed
         * part off the remainder copies the rest of the string for every single attribute, which
         * makes this method quadratic in the length of its input.
         */
        $sourceLength = \strlen($source);
        $offset       = 0;

        // Process each portion based on presence of =" and "<space>, "/>, or ">
        // See if there are any more attributes to process
        while (
            $offset < $sourceLength
            && preg_match('#<[^>]*?=\s*?(\"|\')#s', $source, $matches, \PREG_OFFSET_CAPTURE, $offset)
        ) {
            // Get the character length before the attribute value
            $nextBefore = ($matches[0][1] - $offset) + strlen($matches[0][0]);

            // Figure out if we have a single or double quote and look for the matching closing quote
            // Closing quote should be "/>, ">, "<space>, or " at the end of the string
            $quote     = substr($matches[0][0], -1);
            $pregMatch = ($quote == '"') ? '#(\"\s*/\s*>|\"\s*>|\"\s+|\"$)#' : "#(\'\s*/\s*>|\'\s*>|\'\s+|\'$)#";

            // Get the portion after attribute value
            if (preg_match($pregMatch, $source, $matches, \PREG_OFFSET_CAPTURE, $offset + $nextBefore)) {
                $nextAfter = $matches[0][1] - $offset;
            } else {
                // No closing quote
                $nextAfter = $sourceLength - $offset;
            }

            // Get the actual attribute value
            $attributeValue = substr($source, $offset + $nextBefore, $nextAfter - $nextBefore);

            // Escape bad chars
            $attributeValue = str_replace($badChars, $escapedChars, $attributeValue);
            $attributeValue = $this->stripCssExpressions($attributeValue);
            $alreadyFiltered .= substr($source, $offset, $nextBefore) . $attributeValue . $quote;
            $offset = min($sourceLength, $offset + $nextAfter + 1);
        }

        // At this point, we just have to return the $alreadyFiltered and the remainder
        return $alreadyFiltered . substr($source, $offset);
    }

    /**
     * Remove CSS Expressions in the form of <property>:expression(...)
     *
     * @param   string  $source  The source string.
     *
     * @return  string  Filtered string
     *
     * @since   1.0
     */
    protected function stripCssExpressions($source)
    {
        // Strip any comments out (in the form of /*...*/)
        $test = preg_replace('#\/\*.*\*\/#U', '', $source);

        // Test for :expression
        if (!stripos($test, ':expression')) {
            // Not found, so we are done
            return $source;
        }

        // At this point, we have stripped out the comments and have found :expression
        // Test stripped string for :expression followed by a '('
        if (preg_match_all('#:expression\s*\(#', $test, $matches)) {
            // If found, remove :expression
            return str_ireplace(':expression', '', $test);
        }

        return $source;
    }

    /**
     * Integer filter
     *
     * @param   string  $source  The string to be filtered
     *
     * @return  integer  The filtered value
     */
    private function cleanInt($source)
    {
        $pattern = '/[-+]?[0-9]+/';

        preg_match($pattern, $source, $matches);

        return isset($matches[0]) ? (int) $matches[0] : 0;
    }

    /**
     * Alias for cleanInt()
     *
     * @param   string  $source  The string to be filtered
     *
     * @return  integer  The filtered value
     */
    private function cleanInteger($source)
    {
        return $this->cleanInt($source);
    }

    /**
     * Unsigned integer filter
     *
     * @param   string  $source  The string to be filtered
     *
     * @return  integer  The filtered value
     */
    private function cleanUint($source)
    {
        $pattern = '/[-+]?[0-9]+/';

        preg_match($pattern, $source, $matches);

        return isset($matches[0]) ? abs((int) $matches[0]) : 0;
    }

    /**
     * Float filter
     *
     * @param   string  $source  The string to be filtered
     *
     * @return  float  The filtered value
     */
    private function cleanFloat($source)
    {
        $pattern = '/[-+]?[0-9]+(\.[0-9]+)?([eE][-+]?[0-9]+)?/';

        preg_match($pattern, $source, $matches);

        return isset($matches[0]) ? (float) $matches[0] : 0.0;
    }

    /**
     * Alias for cleanFloat()
     *
     * @param   string  $source  The string to be filtered
     *
     * @return  float  The filtered value
     */
    private function cleanDouble($source)
    {
        return $this->cleanFloat($source);
    }

    /**
     * Boolean filter
     *
     * @param   string  $source  The string to be filtered
     *
     * @return  boolean  The filtered value
     */
    private function cleanBool($source)
    {
        return (bool) $source;
    }

    /**
     * Alias for cleanBool()
     *
     * @param   string  $source  The string to be filtered
     *
     * @return  boolean  The filtered value
     */
    private function cleanBoolean($source)
    {
        return $this->cleanBool($source);
    }

    /**
     * Word filter
     *
     * @param   string  $source  The string to be filtered
     *
     * @return  string  The filtered string
     */
    private function cleanWord($source)
    {
        $pattern = '/[^A-Z_]/i';

        return preg_replace($pattern, '', $source);
    }

    /**
     * Alphanumerical filter
     *
     * @param   string  $source  The string to be filtered
     *
     * @return  string  The filtered string
     */
    private function cleanAlnum($source)
    {
        $pattern = '/[^A-Z0-9]/i';

        return preg_replace($pattern, '', $source);
    }

    /**
     * Command filter
     *
     * @param   string  $source  The string to be filtered
     *
     * @return  string  The filtered string
     */
    private function cleanCmd($source)
    {
        $pattern = '/[^A-Z0-9_\.-]/i';

        $result = preg_replace($pattern, '', $source);
        $result = ltrim($result, '.');

        return $result;
    }

    /**
     * Base64 filter
     *
     * @param   string  $source  The string to be filtered
     *
     * @return  string  The filtered string
     */
    private function cleanBase64($source)
    {
        $pattern = '/[^A-Z0-9\/+=]/i';

        return preg_replace($pattern, '', $source);
    }

    /**
     * String filter
     *
     * @param   string  $source  The string to be filtered
     *
     * @return  string  The filtered string
     */
    private function cleanString($source)
    {
        return $this->remove(html_entity_decode($source, \ENT_QUOTES, 'UTF-8'));
    }

    /**
     * HTML filter
     *
     * @param   string  $source  The string to be filtered
     *
     * @return  string  The filtered string
     */
    private function cleanHtml($source)
    {
        return $this->remove($source);
    }

    /**
     * Path filter
     *
     * @param   string  $source  The string to be filtered
     *
     * @return  string  The filtered string
     */
    private function cleanPath($source)
    {
        $linuxPattern = '/^[A-Za-z0-9_\/-]+[A-Za-z0-9_\.-]*([\\\\\/]+[A-Za-z0-9_-]+[A-Za-z0-9_\.-]*)*$/';

        if (preg_match($linuxPattern, $source)) {
            return preg_replace('~/+~', '/', $source);
        }

        $windowsPattern = '/^([A-Za-z]:(\\\\|\/))?[A-Za-z0-9_-]+[A-Za-z0-9_\.-]*((\\\\|\/)+[A-Za-z0-9_-]+[A-Za-z0-9_\.-]*)*$/';

        if (preg_match($windowsPattern, $source)) {
            return preg_replace('~(\\\\|\/)+~', '\\', $source);
        }

        return '';
    }

    /**
     * Trim filter
     *
     * @param   string  $source  The string to be filtered
     *
     * @return  string  The filtered string
     */
    private function cleanTrim($source)
    {
        $result = trim($source);
        $result = StringHelper::trim($result, \chr(0xE3) . \chr(0x80) . \chr(0x80));
        $result = StringHelper::trim($result, \chr(0xC2) . \chr(0xA0));

        return $result;
    }

    /**
     * Username filter
     *
     * @param   string  $source  The string to be filtered
     *
     * @return  string  The filtered string
     */
    private function cleanUsername($source)
    {
        $pattern = '/[\x00-\x1F\x7F<>"\'%&]/';

        return preg_replace($pattern, '', $source);
    }
}
