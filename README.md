# LogicStrand

LogicStrand is a Laravel 13 site and private knowledge workspace. People can create an account, upload text-based PDF or UTF-8 text documents, ask questions, and inspect the passages behind saved answers. The AI model runs through Groq. Document search uses SQLite FTS5 locally and MySQL full-text search on cPanel; only selected passages are sent to Groq for a question.

## Requirements

- PHP 8.3 or newer with SQLite, mbstring, fileinfo, and OpenSSL extensions
- Composer 2 and Node.js 20 or newer
- A Groq API key for generated answers
- SQLite with FTS5 enabled

## Set up

~~~bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
~~~

Add your key to .env:

~~~dotenv
GROQ_API_KEY=your_key_here
GROQ_MODEL=openai/gpt-oss-120b
~~~

The model ID is a model served through Groq. Change GROQ_MODEL if your Groq account uses another supported structured-output model. Keep the key only in the server environment.

Run the app and asset watcher in separate terminals:

~~~bash
php artisan serve
npm run dev
~~~

Open http://localhost:8000. Uploaded documents stay in private local storage. With the default synchronous queue, the browser starts extraction in a separate request and shows its current stage. If you change `QUEUE_CONNECTION` to `database`, also run `php artisan queue:work --tries=1 --timeout=120`. The cPanel deployment keeps the synchronous queue and needs no worker or cron job.

Email verification is enabled. The default MAIL_MAILER=log writes local verification links to storage/logs/laravel.log; configure a real mail service before inviting people to a hosted instance. To test a logged verification email, prefer the plain-text URL. If copying the URL from the HTML part, replace the HTML entity `&amp;` between query parameters with a literal `&` before pasting it into the browser. Do not otherwise edit the URL: its temporary signature is tied to the exact query string and expires after the configured interval. The account that received the link must be signed in in that browser. The application is delivered as a runnable repository and has not been deployed.

## Contact form

The landing page includes a contact form that sends mail to the existing `support@logicstrand.net` inbox. Set `MAIL_MAILER=smtp` and the normal `MAIL_*` values in production. The sender stays `MAIL_FROM_ADDRESS`; a visitor's email is used only as the reply-to address. Messages are not saved in the database.

Create a **Non-Interactive** Cloudflare Turnstile widget for the hostname in `APP_URL` and add its keys to `.env`:

~~~dotenv
TURNSTILE_SITE_KEY=your_public_site_key
TURNSTILE_SECRET_KEY=your_private_secret_key
~~~

For cPanel deployment, add both lines to the LogicStrand repository's `APP_ENV_EXTRA` GitHub Actions secret alongside any existing application settings. The shared deployer writes them into the private release `.env`. Without both keys, the page displays the support address and an unavailable form state. The server checks Turnstile's response, action, and hostname before sending. A honeypot and an IP-based limit of five submissions per minute add protection. Keep the secret key out of browser code and Git.

## Plans and checkout

- Sandbox starts a one-time seven day trial. It includes 20 documents and 30 questions per day.
- Individual is provisionally priced at $29 per month, with 100 documents and 100 questions per day. Studio is provisionally priced at $79 per month, with 500 documents and 300 questions per day.
- Plan selection continues through registration and email verification. The dashboard shows the current plan, end date, and days remaining. The Sandbox page offers a starter source and a guided question.
- This repository's checkout accepts the 4242 4242 4242 4242 card number, checks a future expiry and security code, and stores only the last four digits. It creates local access periods; it does **not** connect to a payment processor or charge a card. Replace this flow with a real billing integration before public launch.
- Access does not renew automatically. When a period ends, saved documents and answers remain readable while new uploads and questions require another plan period.

## Limits and behavior

- Personal workspaces; each account can access only its own documents and answers.
- The dashboard guides users through upload, evidence review, and first-time setup. Documents show queued, extracting, indexing, ready, or failed stages; after a long wait, the owner can safely resume a stale attempt. Answer history supports search, evidence filters, and favorites. Answers can have owner-only private notes and be exported as PDFs with cited passages; notes are excluded unless the user explicitly includes them.
- Files may be up to 10 MB each. Document and daily saved-answer limits depend on the selected plan. Saved answers, including insufficient-evidence results, count for the day; provider failures do not. Deleting answers or their source documents does not restore that day’s allowance.
- UTF-8 text and PDFs with selectable text are supported. Scanned PDFs are marked **Failed** because OCR is not included.
- Search is lexical: SQLite FTS5 locally and MySQL full-text search on cPanel. A question with no matching passages returns an insufficient-evidence answer without calling Groq.
- Deleting a document also removes answers that cite it. Deleting an account removes its uploaded files and indexed text.
- Source passages are sent to Groq when a matching question is asked. Avoid uploading material you are not allowed to process through that service.

## Verification

~~~bash
php artisan test
npm run build
~~~

The tests fake Groq responses. To verify live generation, supply GROQ_API_KEY, upload a document, wait for it to be ready, and ask a question about its contents.

## Deploy through cPanel without shell access

The [LogicStrand caller workflow](.github/workflows/deploy-cpanel.yml) runs on pushes to `main` or manually. It calls the published reusable Laravel deployment workflow in the public `A1-CM/.github` repository. GitHub Actions tests the app, builds browser assets and installs production Composer packages. The deployer uses HTTPS cPanel APIs to create the app's MySQL database, database user, and `no-reply@<domain>` mailbox, upload a private release, run migrations through a temporary protected PHP endpoint, and publish the site. The host needs no SSH, Composer, Node, or queue worker; the generated production configuration uses Laravel's synchronous queue.

### One-time GitHub setup

Add these **organization variables** (available to the repositories that deploy), or add them as repository variables:

| Variable | Value |
| --- | --- |
| `CPANEL_HOST` | cPanel hostname, without `https://` or a port; HTTPS port 2083 must be reachable |
| `CPANEL_USERNAME` | cPanel account username |
| `CPANEL_HOME` | Absolute account home, such as `/home/username` |
| `APP_URL` | LogicStrand HTTPS origin, such as `https://logicstrand.net` |

Add `CPANEL_API_TOKEN` as an **organization secret** shared with the deploying repositories, or as a repository secret. It must be a cPanel API token with permissions to manage files, MySQL databases and users, and email accounts. Add `GROQ_API_KEY` as a LogicStrand repository secret for document answers. GitHub environment secrets alone are unsuitable unless the called workflow explicitly uses that environment; organization or repository secrets are simplest here.

You do **not** need to create or store `APP_KEY`, `DB_PASSWORD`, or `MAIL_PASSWORD` in GitHub. On first deployment, the script generates a cryptographically random Laravel key and independent database and mailbox passwords. It saves them in `CPANEL_HOME/<project>-deploy-state.json` with owner-only `0600` permissions, then writes them to the private release `.env`. Later deployments read the same state and reuse the credentials. Back up this state file securely; do not delete or rename it while the deployment exists. If a database, user, or mailbox with the chosen names already exists but the state file does not, deployment stops rather than replacing its password. The deployment does not print generated secrets.

Set the optional `PHP_VERSION` variable in the LogicStrand repository to match the PHP version selected for `logicstrand.net` in cPanel; it defaults to `8.3` in CI. The shared `A1-CM/.github` deployment workflow must also read this variable before deployment builds can follow it. The cPanel host must support the locked dependencies and at least PHP 8.3. `DB_HOST` defaults to `localhost` and `DB_PORT` to `3306`. SMTP defaults to `mail.<domain>` on port `465` with `smtps`. Override `DB_HOST`, `DB_PORT`, `MAIL_HOST`, `MAIL_PORT`, or `MAIL_SCHEME` with repository variables if your cPanel provider requires different values. `CPANEL_DB_NAME` and `CPANEL_DB_USER` may override generated names; otherwise the project slug is combined with cPanel's database prefix. `CPANEL_DOMAIN` overrides the hostname from `APP_URL` for cPanel domain lookup and the mailbox domain, useful when the site URL uses a `www` alias. `PROJECT_NAME` is provided by the caller. Other application-specific `.env` values may be supplied as newline-separated `KEY=value` entries in an `APP_ENV_EXTRA` repository secret; deployment-managed keys cannot be overridden this way.

The `public_dir` manual input may be `auto`, `public_html`, or a path relative to `CPANEL_HOME`. `auto` asks cPanel for the document root of the deploying domain and works for primary and addon domains. The target directory must already exist and serve the HTTPS `APP_URL`, which must be a domain origin without a path. The deployment never deletes or empties existing folders. It copies current public assets, updates `index.php`, and inserts a project-marked, domain-scoped routing block into `.htaccess` while preserving existing rules. Files with the same names as deployed assets may be overwritten. An unrelated `index.php` blocks deployment unless `allow_index_replace` is explicitly enabled. Check the target site before enabling that option. **Deploy only when the selected domain has its own document root.** Addon domains in separate subdirectories under `public_html` are normally left intact, provided their paths do not overlap deployed asset paths; a domain sharing the exact target document root can be affected by the replaced entry point or assets. Check cPanel → Domains for each domain’s document root before the first run; `auto` selects a domain’s root but does not reject roots shared with another domain.

Private code and `.env` live under `CPANEL_HOME/<project>-app/releases/`; uploads, sessions, cache, and logs live under `CPANEL_HOME/<project>-app/shared/`. After a verified activation, matching staging ZIPs are removed and the active release plus seven previous releases are retained. Failed deployments keep their ZIP and release until a later healthy deployment. The private app directory must be outside the selected document root. The release archive excludes local `.env` files, Git metadata, tests, and local storage. The host needs PHP 8.3 or newer with PDO MySQL, mbstring, fileinfo, and OpenSSL, and enough PHP request time for migrations. MySQL/MariaDB needs InnoDB full-text support. cPanel File Manager API 2 handles ZIP extraction; API transport verifies TLS. Schema migrations are forward-only, so retain a database backup before schema changes.

### Reuse across an organization

The updated public `A1-CM/.github` toolkit must contain `scripts/deploy_cpanel.py`, `scripts/cpanel_release_manager.php`, `scripts/tests/`, and both reusable deployment and rollback workflows before publishing these LogicStrand callers. The shared repository must remain **public** for this public LogicStrand repository to call its reusable workflow. Keep only deployment code there; put the cPanel token and application keys in GitHub secrets. A private shared repository can serve private callers only. This public toolkit needs no `TOOLKIT_READ_TOKEN`. Pin `toolkit_ref` to a reviewed tag or commit for predictable deployments. In each Laravel repository, add a small caller workflow:

```yaml
name: Deploy to cPanel
on:
  push:
    branches: [main]
  workflow_dispatch:
permissions:
  contents: read
jobs:
  deploy:
    uses: A1-CM/.github/.github/workflows/laravel-cpanel-reusable.yml@87dffd171b476623768149422fb3324470f7fb5c
    with:
      toolkit_repository: A1-CM/.github
      toolkit_ref: 87dffd171b476623768149422fb3324470f7fb5c
      project_slug: yourproject
      project_name: Your Project
      app_url: https://your-domain.example
      public_dir: auto
    secrets: inherit
```

Each repository supplies its own `project_slug` and `app_url`; use a slug unique within the cPanel account. The same account-level cPanel variables and token can be shared across repositories through GitHub organization settings. The caller can set `public_dir: public_html` or another relative directory when needed. A project-specific `APP_ENV_EXTRA` secret supplies extra Laravel configuration. The shared workflow runs `php artisan test`, verifies the deployer, builds assets if a `package.json` exists, and installs production dependencies before deployment. When the toolkit changes, update the workflow and checkout references in both caller workflows to the same tested tag. A manual rollback is available through the [rollback workflow](.github/workflows/rollback-cpanel.yml) with `steps_back` from 1 to 7. It checks `/up` (or `CPANEL_HEALTH_PATH`) and restores the previous live files automatically if health fails. Database migrations remain forward-only; retained code must be compatible with the current schema. See [rollout and rollback details](CPANEL-ROLLBACK.md).

The dashboard's proposed browser-based scanned-PDF OCR flow is documented in [OCR_DRAFT.md](OCR_DRAFT.md). OCR is a future phase and is not enabled in this release.
