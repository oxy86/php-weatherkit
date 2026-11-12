# Contributing

Thanks for helping! Bug reports, fixes and documentation improvements are welcome.

## Development
```bash
composer install
vendor/bin/phpunit
```
Tests run offline: HTTP is mocked with a PSR-18 test client and fixtures in `tests/fixtures/`. You never need Apple credentials to run them.

## Pull requests
- Add or update tests for behavior changes; keep `vendor/bin/phpunit` green on PHP 8.1+.
- New source files use `declare(strict_types=1);` and PSR-12 style.
- Add a line to `CHANGELOG.md` under "Unreleased".
- Keep PRs focused; open an issue first for larger changes (see the [ROADMAP](ROADMAP.md)).

## Fixtures
Recorded API responses must contain no credentials or personal data. Prefer well-known public coordinates (major cities) and trim large arrays.

## Never include credentials
Do not paste your `.p8` key, Key ID, Team ID or tokens into issues, PRs or logs.
