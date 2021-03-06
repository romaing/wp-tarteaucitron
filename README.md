wp-tarteaucitron
================

WP tarteaucitron wordpress plugin for cookie management

Comply to the european cookie law is simple with the french *tarte au citron*.
The plugin allows to integrate the javascript tarteaucitron.js in wordpress proposing an admin interface and management in front site. The plugin allows you to choose the cookies that you propose to your users

# What is this script tarteaucitron.js?
The european cookie law regulates the management of cookies and you should ask your visitors their consent before exposing them to third party services.

Clearly this script will:
- Disable all services by default,
- Display a banner on the first page view and a small one on other pages,
- Display a panel to allow or deny each services one by one,
- Activate services on the second page view if not denied,
- Store the consent in a cookie for 365 days.

Bonus:
- Load service when user click on Allow (without reload of the page),
- Incorporate a fallback system (display a link instead of social button and a static banner instead of advertising).

## Supported services
* Advertising network
  * ~~Amazon~~
  * ~~Clicmanager~~
  * ~~Criteo~~
  * ~~FERank (pub)~~
  * ~~Google Adsense~~
  * ~~Google Adsense Search (form)~~
  * ~~Google Adsense Search (result)~~
  * ~~Google Adwords (conversion)~~
  * ~~Google Adwords (remarketing)~~
  * ~~Pubdirecte~~
  * ~~Twenga~~
  * ~~vShop~~

* APIs
  * ~~Google jsapi~~
  * ~~Google Maps~~
  * ~~Google Tag Manager~~
  * ~~Timeline JS~~
  * ~~Typekit (adobe)~~

* Audience measurement
  * ~~Alexa~~
  * ~~Clicky~~
  * ~~Crazyegg~~
  * ~~FERank~~
  * ~~Get+~~
  * Google Analytics (ga.js)
  * Google Analytics (gTag.js)
  * Google Analytics (universal)
  * ~~StatCounter~~
  * ~~VisualRevenue~~
  * ~~Xiti~~

* Comment
  * ~~Disqus~~
  * ~~Facebook (commentaire)~~

* Social network
  * ~~AddThis~~
  * ~~AddToAny (feed)~~
  * ~~AddToAny (share)~~
  * ~~eKomi~~
  * ~~Facebook~~
  * ~~Facebook (like box)~~
  * ~~Google+~~
  * ~~Google+ (badge)~~
  * ~~Linkedin~~
  * ~~Pinterest~~
  * ~~Shareaholic~~
  * ~~ShareThis~~
  * ~~Twitter~~
  * ~~Twitter (cards)~~
  * ~~Twitter (timelines)~~

* Support
  * ~~UserVoice~~
  * ~~Zopim~~

* Video
  * ~~Calameo~~
  * ~~Dailymotion~~
  * ~~Prezi~~
  * ~~SlideShare~~
  * Vimeo
  * Youtube

**Services "barred" are not yet supported by this Wordpress plugin

## Visitors outside the EU
In PHP for example, you can bypass all the script by setting this var `tarteaucitron.user.bypass = true;` if the visitor is not in the EU.

## Tested on
- IE 6+
- FF 3+
- Safari 4+
- Chrome 14+
- Opera 10+

# Installation guide
[Visit JS tarteaucitron.js installation guide](https://opt-out.ferank.eu/fr/install/)

[Github](https://github.com/AmauriC/tarteaucitron.js/)

