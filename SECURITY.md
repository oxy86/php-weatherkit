# Security policy

## Reporting a vulnerability
Please report security issues **privately** using GitHub's [private vulnerability reporting](https://github.com/oxy86/php-weatherkit/security/advisories/new) (Security tab → "Report a vulnerability"). Do not open a public issue. You can expect a first reply within a few days.

## Handling credentials
This library signs requests with your Apple WeatherKit private key (`.p8`). Keep it out of version control and out of logs; load it from a protected file or a secret store. If a key is ever exposed, revoke it in the Apple Developer portal and create a new one.
