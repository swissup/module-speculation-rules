# module-speculation-rules

### [Installation](https://docs.swissuplabs.com/m2/extensions/speculation-rules/installation/)

###### For clients

There are several ways to install extensions for clients:

 1. If you've bought the product at Magento's Marketplace - use
    [Marketplace installation instructions](https://docs.magento.com/marketplace/user_guide/buyers/install-extension.html)

 2. Otherwise, you have two options:
    - Install the sources directly from [our repository](https://docs.swissuplabs.com/m2/extensions/speculation-rules/installation/composer/) - **recommended**
    - Download archive and use [manual installation](https://docs.swissuplabs.com/m2/extensions/speculation-rules/installation/manual/)

###### For maintainers

```bash
cd <magento_root>
composer config repositories.swissup composer https://docs.swissuplabs.com/packages/
composer require swissup/module-speculation-rules
composer require swissup/module-speculation-rules --prefer-source --ignore-platform-reqs
bin/magento module:enable Swissup_SpeculationRules Swissup_Core
bin/magento setup:upgrade
```
