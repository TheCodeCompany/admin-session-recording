# Admin Session Recording

Maintained by: Tim Sheehan @ [The Code Company](https://thecode.co)

[![License: ISC](https://img.shields.io/badge/License-ISC-blue.svg)](https://opensource.org/licenses/ISC)

> [!IMPORTANT]  
> Admin Session Recording is currently in alpha, as such there may be breaking changes introduced over time. While we hope you will find the package useful in its current state you're using it at your own risk.

## Overview
This plugin allows either Hotjar or Microsoft Clarity to be enabled on administrative pages to help debug issues related to editor workflow as efficiently as possible. The official Hotjar WordPress plugin hasn't been updated in 2 years (as of the date of this readme) and the Microsoft Clarity plugin runs on all front-end pages by default, while this plugin allows for the optional inclusion of front-end pages it's primary focus is to help resolve issues that logged in users run into within the administration area.

<img src="https://thecodeco.b-cdn.net/admin-sessions/admin-session-recording.png" width="500">

### Regarding Hotjar
Unfortunately, the free version of Hotjar does not support custom user attributes. While this plugin will still work, it won't pass along and WordPress user info for you to cross reference against activity logs.

https://help.hotjar.com/hc/en-us/articles/115011819928-Use-Cases-for-Filtering-Recordings

### Regarding Microsoft Clarity
The users email address is passed through as a custom userId, however Clarity will hash this in the backend and not display it. To get around this issue, Clarity provides a "friendly" version for which we're passing through the WordPress username.

https://learn.microsoft.com/en-us/clarity/setup-and-installation/identify-api

## Requirements

- PHP 7.4
- WordPress 6.4
- Hotjar or Microsoft Clarity account

## TODO

- Documentation
- More providers

## Contributing

[Admin Session Recording](https://github.com/TheCodeCompany/admin-session-recording) is maintained by [The Code Company](https://thecode.co/), while we appreciate feedback and will endeavour to action requests for features/bug fixes this repository is not open to outside contribution at this time. You are, however, free to fork and use it in any way you see fit as per the ISC license.