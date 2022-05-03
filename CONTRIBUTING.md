# Contributing

This project uses [DDEV][1] for local development. Make sure to set it up as
described below. For continuous integration, we use GitHub Actions.

## Preparation

```bash
# Clone repository
git clone https://github.com/eliashaeussler/typo3-form-consent.git
cd typo3-form-consent

# Start DDEV project
ddev start
```

You can access the DDEV site at <https://typo3-ext-form-consent.ddev.site/>.

## Fixtures

File fixtures and database fixtures are automatically imported on each `ddev start`.
You can also manually import fixtures by running:

```bash
ddev composer import-fixtures
```

:bulb: You can access the TYPO3 backend using the `admin` / `password` credentials.

## Run linters

```bash
# All linters
ddev composer lint

# Specific linters
ddev composer lint:composer
ddev composer lint:editorconfig
ddev composer lint:php
ddev composer lint:typoscript

# All static code analyzers
ddev composer sca

# Specific static code analyzers
ddev composer sca:php
```

## Run tests

```bash
# All tests
ddev composer test

# Specific tests
ddev composer test:acceptance
ddev composer test:functional
ddev composer test:unit

# Enable Xdebug to collect code coverage
ddev xdebug on

# All tests with code coverage
ddev composer test:ci

# Specific tests with code coverage
ddev composer test:ci:acceptance
ddev composer test:ci:functional
ddev composer test:ci:unit

# Merge code coverage of all test suites
ddev composer test:ci:merge
```

:bulb: Xdebug inside DDEV is configured to run in `coverage` mode only. If you
need additional debug support, you can temporarily change the appropriate web
environment `XDEBUG_MODE` variable in [`.ddev/config.yaml`][2].

### Test reports

Code coverage reports are written to `.Build/log/coverage`. You can open the
last merged HTML report like follows:

```bash
open .Build/log/coverage/html/_merged/index.html
```

:bulb: Make sure to merge coverage reports as written above.

Reports of acceptance tests are written to `.Build/log/acceptance-reports`. You
can open the last HTML report like follows:

```bash
open .Build/log/acceptance-reports/records.html
```

## Submit a pull request

Once you have finished your work, please **submit a pull request** and describe
what you've done. Ideally, your PR references an issue describing the problem
you're trying to solve.

All described code quality tools are automatically executed on each pull request
for all currently supported PHP versions and TYPO3 versions. Take a look at the
appropriate [workflows][2] to get a detailed overview.

[1]: https://ddev.readthedocs.io/en/stable/
[2]: .ddev/config.yaml
[3]: .github/workflows
