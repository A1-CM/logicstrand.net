# LogicStrand

LogicStrand is a Laravel 13 site and private knowledge workspace. People can create an account, upload text-based PDF or UTF-8 text documents, ask questions, and inspect the passages behind saved answers. The AI model runs through Groq. Document search uses SQLite FTS5 locally; only selected passages are sent to Groq for a question.

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

Run these in separate terminals:

~~~bash
php artisan serve
npm run dev
php artisan queue:work --tries=1 --timeout=120
~~~

Open http://localhost:8000. Uploaded documents stay in private local storage. The queue worker must be running for uploads to change from **Processing** to **Ready** or **Failed**.

Email verification is enabled. The default MAIL_MAILER=log writes local verification links to storage/logs/laravel.log; configure a real mail service before inviting people to a hosted instance. The application is delivered as a runnable repository and has not been deployed.

## Plans and checkout

- Sandbox starts a one-time seven day trial. It includes 20 documents and 30 questions per day.
- Individual is provisionally priced at $29 per month, with 100 documents and 100 questions per day. Studio is provisionally priced at $79 per month, with 500 documents and 300 questions per day.
- Plan selection continues through registration and email verification. The dashboard shows the current plan, end date, and days remaining. The Sandbox page offers a starter source and a guided question.
- This repository's checkout accepts the 4242 4242 4242 4242 card number, checks a future expiry and security code, and stores only the last four digits. It creates local access periods; it does **not** connect to a payment processor or charge a card. Replace this flow with a real billing integration before public launch.
- Access does not renew automatically. When a period ends, saved documents and answers remain readable while new uploads and questions require another plan period.

## Limits and behavior

- Personal workspaces; each account can access only its own documents and answers.
- Files may be up to 10 MB each. Document and daily question limits depend on the selected plan.
- UTF-8 text and PDFs with selectable text are supported. Scanned PDFs are marked **Failed** because OCR is not included.
- Search is lexical SQLite FTS5 search. A question with no matching passages returns an insufficient-evidence answer without calling Groq.
- Deleting a document also removes answers that cite it. Deleting an account removes its uploaded files and indexed text.
- Source passages are sent to Groq when a matching question is asked. Avoid uploading material you are not allowed to process through that service.

## Verification

~~~bash
php artisan test
npm run build
~~~

The tests fake Groq responses. To verify live generation, supply GROQ_API_KEY, upload a document, wait for it to be ready, and ask a question about its contents.
