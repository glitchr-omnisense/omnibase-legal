# Changelog

## [Unreleased]

## [1.0.0] — 2026-11-13

First stable release.

### Changed
- Stable requirements, so that `composer require omnibase/legal` installs at Packagist's default stability:
  `omnisense/omnibase` ^5.1 (in place of `3.x || 4.0 || 5.0` dev branches), `omnibridge/symfony` ^1.1, `doctrine/doctrine-bundle` ^2.10 || ^3.0;
  no `minimum-stability: dev`; the branch alias `dev-main` → `1.x-dev`.
- CI: `.github/workflows/ci.yml` (glitchr-studio/ci's php-package workflow, pinned: the bundle installed alone,
  composer validate, PHPUnit, a consumer install without require-dev with `src/` linted and the bundle class
  autoloaded) and `release.yml` (that CI on a tag, then the release).
- require-dev omnibase/admin and omnibase/office; `config.allow-plugins` for omnibridge/composer; OfficeAudienceTest skips
  without a host's `App\Entity\User`.
- Neutral example data in tests: the domain names are `.example` ones.
- README: installation under Symfony and a first use.
