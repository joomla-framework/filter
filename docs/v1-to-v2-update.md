## Updating from v1 to v2

### Minimum supported PHP version raised

All Framework packages now require PHP 7.2 or newer.

### Renamed static class constants
Note the InputFilter static class constants have been renamed:

| Before  | After |
| ------------- | ------------- |
| InputFilter::TAGS_WHITELIST  | InputFilter::ONLY_ALLOW_DEFINED_TAGS  |
| InputFilter::TAGS_BLACKLIST  | InputFilter::ONLY_BLOCK_DEFINED_TAGS  |
| InputFilter::ATTR_WHITELIST  | InputFilter::ONLY_ALLOW_DEFINED_ATTRIBUTES  |
| InputFilter::ATTR_BLACKLIST  | InputFilter::ONLY_BLOCK_DEFINED_ATTRIBUTES  |

The public property `InputFilter::tagBlacklist` has been renamed to `InputFilter::blockedTags`. Similarly
`InputFilter::attrBlacklist` has been renamed to `InputFilter::blockedAttributes`

All code usage of these properties remains unchanged.
