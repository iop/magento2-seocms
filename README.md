# Magento Module Iop_SeoCms

## Tested on Version

* Magento 2.3.4

## Main Functionalities
* Fix problem with SEO rankings for multi site setup with some CMS pages (this is causing duplicate content issues) that are shared across the different websites. 
* If the page ID is assigned to multiple website then foreach of the websites it will load the store details 
for that store ID and get the language via the locale setting. 
It will then add a meta tag for this website setting the value to the stores locale, and the href to the store base url and the cms page url. 
*  In the admin area that the “language” variable set for each store e.g. UK can be set to "en-gb", US can be "en-us". 
This is because the meta tag have specific values against each country (this is different from the locale setting in Magento)

### Example
*  There are 2 websites setup within Magento, a UK one and a US one.

The UK language is set to en-gb and the US site is set to en-us.

The UK base URL is https://www.example.co.uk

The US base URL is https://www.example.com

If there is a CMS page for "about-us" and this is assigned to both websites, when the page loads the new block in the head will add the following meta tags:
```
<link rel="alternate" hreflang=“en-gb" href="https://example.co.uk/about-us'" />

<link rel="alternate" hreflang=“en-us" href="https://example.com/about-us'" />
```
## Installation 

#### With Composer
Use the following commands to install this module into Magento 2:

    composer require iop/magento2-seocms
    bin/magento module:enable Iop_SeoCms
    bin/magento setup:upgrade
       
#### Manual (without composer)
These are the steps:
* Upload the files in the folder `app/code/Iop/SeoCms` of your site
* Run `php -f bin/magento module:enable Iop_SeoCms`
* Run `php -f bin/magento setup:upgrade`
* Flush the Magento cache
* Done
