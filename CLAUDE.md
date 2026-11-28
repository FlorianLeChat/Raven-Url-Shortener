# CLAUDE.md

Raven Url Shortener is a URL shortener split into two applications: a Next.js 16 front-end in `client/` (TypeScript, React 19, HeroUI, Tailwind CSS, next-intl) and a Symfony 7.4 REST API in `server/` (PHP 8.2+, Doctrine, PostgreSQL, Valkey). In production, Varnish sits in front of the client, and the server image ships the OpenAPI documentation as a static page built with Redocly CLI and served at `/docs`. The code is hosted on a private GitLab instance; GitHub is a mirror.

## Environment

- Local PHP is Windows PHP 8.5 with no coverage driver (no Xdebug, no PCOV). Docker is not installed. GitLab CI (`.gitlab/.gitlab-ci.yml`) runs PHP 8.2 with Xdebug and is the reference for coverage.
- The server `test` environment uses SQLite (`server/var/data.db`) and loads `.env` only, never `.env.local`.
- The client reads its `.env` at build time: `NEXT_PUBLIC_*` variables are baked into the Docker image, so each deployment environment needs its own client image.
- Never run `composer update` or `composer require` without `--no-plugins`: Symfony Flex then applies recipes and creates or rewrites files (`compose.yaml`, `.env.*`, `config/bundles.php`, `<env>` variables in `phpunit.xml`, auto-scripts in `composer.json`...). To refresh only the lock hash, run `composer update --lock --no-plugins`.

## Quality checks

The Husky hooks (`.husky/`) run these; run them before committing and fix every error:

```
# client/
npx eslint --fix <files>
npm run check-spell -- --no-must-find-files
npm run build

# server/
vendor/bin/phpcs -s
vendor/bin/phpstan analyse --memory-limit=-1
vendor/bin/phpmd ./src ansi phpmd.xml
vendor/bin/phpunit
```

Before `phpunit`, rebuild the test database from `server/`: delete `var/data.db`, recreate it empty, then run `php bin/console --env=test doctrine:schema:create --no-interaction` and `php bin/console --env=test doctrine:fixtures:load --no-interaction`.

PhpStorm also runs EA Extended and SonarLint inspections. Code must be free of their warnings: unused imports, redundant `static` in a `final` class, polymorphic calls on an interface, non-complementary `??` operands.

## Tests

- Server tests live in `server/tests/` and mirror `src/` (`Action`, `Domain`, `Infrastructure`). Action tests reload the fixtures in `setUp()` with `loadFixtures()` from `tests/TestHelperTrait.php` and call the API through `KernelBrowser`.
- Tests never touch the network. In the `test` environment, `config/packages/framework.php` replaces the HTTP client with `tests/Mock/HttpClientResponseFactory`: the host of the checked URL picks the scenario (unreachable, 404, blocked by a firewall), any other host answers 200. Elsewhere, `config/services.php` wraps the HTTP client in `NoPrivateNetworkHttpClient`.
- **100% line coverage of `server/src/` is a requirement, for the existing code and every future development.** A change to `src/` ships with the tests that keep it at 100%, in the same commit or the tests commit of the same batch. `src/Infrastructure/Fixture` is excluded in `phpunit.xml`: the fixtures run in a child process, out of reach of the coverage driver.
- `@codeCoverageIgnoreStart`/`End` is only for a block no request can reach, with a comment giving the reason.
- Nothing in `src/` knows the suite exists: no test mode, no branch taken only under test. Test doubles live in `tests/Mock/` and are wired from `config/` for the `test` environment.
- Add tests sparingly: before writing a new test method, extend the one that already reaches the same state. A test that only proves the framework works, or repeats the case above it, is deleted rather than kept for the count.
- Assertions are called as `self::assertX()`. Mock builders stay on `$this` (`$this->once()`, `$this->exactly()`, `$this->createMock()`). In a `final` class, use `self::` instead of `static::`.
- The client has no automated tests: check a front-end change with `npm run build` and, when it affects the UI, in the browser.

## Code style

- Follow `.editorconfig`: LF line endings and lines of 120 characters at most. Keep the indentation style of the file you edit.
- Server: PSR-12, checked by `server/phpcs.xml`. Use type casts (`(int)`) rather than `intval()`.
- Client, checked by ESLint and `@stylistic` (`client/eslint.config.mjs`), so write it by hand rather than discover it through a failed commit:
  - tab indentation, double quotes, semicolons always, no trailing comma, and **no final newline** (`eol-last: never`, unlike `.editorconfig`);
  - Allman braces (opening brace on its own line);
  - spaces inside parentheses, brackets, computed properties and template placeholders: `foo( bar )`, `[ "a", "b" ]`, `record[ key ]`, `` `${ value }` ``;
  - arrow function parameters always in parentheses: `( value ) => ...`.
- Client functions: helpers and utilities are arrow functions (`export const getCookie = ( name: string ) => ...`). React components and Next.js route files (`page.tsx`, `layout.tsx`, `generateMetadata`...) keep `export default function`, and server actions (`"use server"`) keep `export async function`, as in the rest of the code.
- Clarity over shortcuts: extract a long expression or a complex condition into a named constant, a named boolean beats a clever condition. A regular expression lives in a named constant, never inline inside a call.
- `use` and `import` statements are ordered as a staircase by line length, shortest first. A statement spanning several lines counts as a whole and sinks to the bottom. Equal lengths keep their order. A blank line starts a new staircase. The same rule applies to dependency lists in `composer.json` and `package.json`.
- Write the straight apostrophe `'` and three dots `...`, never `’` or `…`.
- Code identifiers are in English.
- A suppression comment (`phpcs:disable`, `@SuppressWarnings`, `eslint-disable`, `@ts-ignore`, `@ts-expect-error`), or a rule switched off in a config to the same end, is only allowed when a third-party API imposes the name. Otherwise, change the code the warning describes.
- `npm run format` (Prettier) rewrites every file it disagrees with: use it only on a file you rewrote, not on a small edit.

## Comments and doc blocks

- Comments, PHPDoc and JSDoc are written in **English**. The client still carries French comments: translate those of a file you edit.
- Every class, interface, trait and enum gets a doc block saying what it is for. Every method gets one too, with `@param`, `@return` and `@throws` where they add something the signature does not say, except the plain getters and setters of an entity the constructors that only receive injected services, and the test methods and PHPUnit hooks (`setUp()`), whose name already says what they do. A comment describes what the code is for and why, not how each line does it.
- Documentation lives in the doc block of the class or method. **No inline comment** (`//`, `/* */` inside a body, `{/* */}` in JSX) is written beside the lines of a change or a fix to explain it: when a decision needs recording, it goes in the doc block of the enclosing method or class. The one exception is a technical trap, a line that looks wrong or useless and would be "fixed" into a bug without its comment (the `NoPrivateNetworkHttpClient` decoration in `config/services.php`).
- A change that alters what a doc block or an existing comment describes updates it in the same commit. A non-obvious method that gains a responsibility gets a doc block. A comment that would stay true whatever the code becomes, or that drifted from it, is worse than none.
- Do not add comments that repeat the code or narrate a change ("now", "used to", "fixed"): the git history owns that. Commented out code goes.

## API documentation

- The OpenAPI 3.0 specification is `server/openapi.yaml`, written by hand. The Docker build turns it into the Redocly page served at `/docs`; the API itself does not serve it.
- Its `${VERSION}`, `${APP_RATE_LIMIT_ENABLED}`, `${APP_RATE_LIMIT_READ_API}` and `${APP_RATE_LIMIT_WRITE_API}` placeholders are replaced from `server/.env` by `envsubst` in `server/docker/Dockerfile`. A new placeholder must be added to both lists of that step.
- **Every change to the API updates the specification in the same commit**: a new or changed endpoint, request body field, validation constraint, status code, error message, response header or response field. Keep its descriptions, examples (`examples`, taken from real responses) and `x-codeSamples` in line with the code.
- A nullable `$ref` is written `allOf` with a nullable schema, such as `NullableDateTime`: OpenAPI 3.0 ignores the siblings of a `$ref`.
- Check it from `server/` with `npx @redocly/cli@2.78.0 lint openapi.yaml`: no error allowed. The Docker build runs the same lint.

## Client

- Every text shown to the user goes through next-intl: add the key to `client/locales/en.json` **and** `client/locales/fr.json`, in the same place in both files, and never hardcode a sentence in a component. New words that trip the spell checker go in `client/.cspell.json`.
- Components live in a `components/` folder next to the route that uses them, with a kebab-case filename (`consent-cookie.tsx`). Split a component when a section gains its own responsibility or the file grows long.
- Use the element that carries the meaning (`<main>`, `<nav>`, `<section>`, `<footer>`, `<time>`, `<ul>`...), `<button>` for actions and `<a>` for navigation. Every interactive control without visible text needs an `aria-label`, and the keyboard reaches everything the pointer does.
- Absolute internal paths that Next.js does not prefix by itself (cookie paths, HeroUI `Link` with `isExternal`, the manifest, `new URL()` in `proxy.ts`) must include `NEXT_PUBLIC_BASE_PATH`, since the test environment is served from `/url`.

## Working method

- For a change spanning several files, agree on a plan with the user before writing code. A typo, a one-line fix or a copy change goes straight to the edit.
- Before calling a change finished, reread the diff and remove what it leaves behind:
  - dead code: a helper nothing calls, an export used only inside its module, a branch that can no longer be reached, a translation key no component reads (delete it from both locales). Unfinished features kept on purpose are the exception: ask before deleting code that looks dead but may be one, and name it under Tests once confirmed;
  - duplication: a literal repeated beside the config or constant that already holds it;
  - comments that fail the rules above;
  - accessibility regressions on anything touched;
  - tests the change made redundant.
- A large change (new feature, new endpoint, new dependency, change to the Docker images, the CI or the proxy) ends with a read-only security and performance audit, reported in the reply with `file:line` and severity, even when nothing is wrong:
  - security: user-supplied URLs reaching a redirect, an `href` or a `src`; validation and size limits on API input; who can read the secrets (`APP_PRIVATE_API_KEY`, `CAPTCHA_HMAC`, database and cache passwords); new third-party scripts or origins; `npm audit --omit=dev` and `composer audit`;
  - performance: Doctrine queries run in loops, missing cache on hot read paths, what Varnish can no longer cache, bundle size from `npm run build`;
  - method: prove a finding through the real code path (a PHPUnit test, the build, a request), not on a copy of the logic. Each finding states its severity and whether it is proven or only suspected.
- Fix what the change introduced. Report the rest instead of widening the change; a fix found by the audit is a commit of its own.

## Commits

- Conventional Commits, checked by commitlint: `type(scope): description`, in English and lowercase, with no trailing period.
- Commit only when asked, and never push without an explicit request.
- Split large work into separate commits by batch (for example CI, Docker, client, server, tests, dependencies), each one coherent and passing the quality checks on its own.
- End the message with the `Co-Authored-By` line of the model in use.
