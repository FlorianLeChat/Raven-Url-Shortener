# CLAUDE.md

Raven Url Shortener is a URL shortener split into two applications: a Next.js 16 front-end in `client/` (TypeScript, React 19, HeroUI, Tailwind CSS, next-intl) and a Symfony 7.4 REST API in `server/` (PHP 8.2+, Doctrine, PostgreSQL, Valkey). In production, Varnish sits in front of the client and Swagger UI serves the OpenAPI documentation. The code is hosted on a private GitLab instance; GitHub is a mirror.

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

- Server tests live in `server/tests/` and mirror `src/` (`Action`, `Domain`, `Infrastructure`). Action tests reload the fixtures in `setUp()` and call the API through `KernelBrowser`.
- Tests never touch the network.
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
- Comments and doc blocks are written in French, following the existing style. Do not add comments that repeat the code or narrate a change ("now", "used to"): the git history owns that. Commented out code goes.
- `use` and `import` statements are ordered as a staircase by line length, shortest first. A statement spanning several lines counts as a whole and sinks to the bottom. Equal lengths keep their order. A blank line starts a new staircase. The same rule applies to dependency lists in `composer.json` and `package.json`.
- Write the straight apostrophe `'` and three dots `...`, never `’` or `…`.
- Code identifiers are in English.
- A suppression comment (`phpcs:disable`, `@SuppressWarnings`, `eslint-disable`, `@ts-ignore`, `@ts-expect-error`), or a rule switched off in a config to the same end, is only allowed when a third-party API imposes the name. Otherwise, change the code the warning describes.
- `npm run format` (Prettier) rewrites every file it disagrees with: use it only on a file you rewrote, not on a small edit.

## Client

- Every text shown to the user goes through next-intl: add the key to `client/locales/en.json` **and** `client/locales/fr.json`, in the same place in both files, and never hardcode a sentence in a component. New words that trip the spell checker go in `client/.cspell.json`.
- Components live in a `components/` folder next to the route that uses them, with a kebab-case filename (`consent-cookie.tsx`). Split a component when a section gains its own responsibility or the file grows long.
- Use the element that carries the meaning (`<main>`, `<nav>`, `<section>`, `<footer>`, `<time>`, `<ul>`...), `<button>` for actions and `<a>` for navigation. Every interactive control without visible text needs an `aria-label`, and the keyboard reaches everything the pointer does.
- Absolute internal paths that Next.js does not prefix by itself (cookie paths, HeroUI `Link` with `isExternal`, the manifest, `new URL()` in `proxy.ts`) must include `NEXT_PUBLIC_BASE_PATH`, since the test environment is served from `/url`.

## Working method

- For a change spanning several files, agree on a plan with the user before writing code. A typo, a one-line fix or a copy change goes straight to the edit.
- Before calling a change finished, reread the diff and remove what it leaves behind:
  - dead code: a helper nothing calls, an export used only inside its module, a translation key no component reads (delete it from both locales);
  - duplication: a literal repeated beside the config or constant that already holds it;
  - comments that fail the rules above;
  - accessibility regressions on anything touched;
  - tests the change made redundant.
- A large change (new feature, new endpoint, new dependency, change to the Docker images, the CI or the proxy) ends with a read-only security and performance audit, reported in the reply with `file:line` and severity, even when nothing is wrong:
  - security: user-supplied URLs reaching a redirect, an `href` or a `src`; validation and size limits on API input; who can read the secrets (`APP_PRIVATE_API_KEY`, `CAPTCHA_HMAC`, database and cache passwords); new third-party scripts or origins; `npm audit --omit=dev` and `composer audit`;
  - performance: Doctrine queries run in loops, missing cache on hot read paths, what Varnish can no longer cache, bundle size from `npm run build`.
- Fix what the change introduced. Report the rest instead of widening the change; a fix found by the audit is a commit of its own.

## Commits

- Conventional Commits, checked by commitlint: `type(scope): description`, in English and lowercase, with no trailing period.
- Commit only when asked, and never push without an explicit request.
- Split large work into separate commits by batch (for example CI, Docker, client, server, tests, dependencies), each one coherent and passing the quality checks on its own.
- End the message with the `Co-Authored-By` line of the model in use.
