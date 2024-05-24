# module-speculation-rules

The [Speculation Rules API](https://github.com/WICG/nav-speculation/blob/main/triggers.md#speculation-rules) is a JSON-defined API developed by Google to enhance the performance of web page loading through speculative loading strategies.
One more specification [Speculation Rules API](https://developer.mozilla.org/en-US/docs/Web/API/Speculation_Rules_API) by Mozilla Developer

### [Installation](https://docs.swissuplabs.com/m2/extensions/speculation-rules/installation/)

###### For clients

```bash
cd <magento_root>
composer require swissup/module-speculation-rules
bin/magento module:enable Swissup_SpeculationRules Swissup_Core
bin/magento setup:upgrade
```

###### For maintainers

```bash
cd <magento_root>
composer config repositories.swissup composer https://docs.swissuplabs.com/packages/
composer require swissup/module-speculation-rules --prefer-source --ignore-platform-reqs
bin/magento module:enable Swissup_SpeculationRules Swissup_Core
bin/magento setup:upgrade
```
