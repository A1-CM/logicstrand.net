@extends('legal.layout', ['pageTitle' => 'Privacy Policy', 'description' => 'Learn what information LogicStrand handles and how it is used to provide the service.'])

@section('legal-content')
<p>This policy explains how LogicStrand handles information when you visit the site, create an account, use a personal workspace, or contact us. It describes the current product and may change as the service changes.</p>

<h2>Who operates LogicStrand</h2>
<p>LogicStrand is operated by LogicStrand Technologies (Pvt) Ltd in Sri Lanka and LogicStrand Technologies Inc. in the United States. For privacy questions or requests, contact <a href="mailto:support@logicstrand.net">support@logicstrand.net</a>.</p>

<h2>Information we handle</h2>
<ul>
    <li><strong>Account information:</strong> name, email address, password credential (stored as a password hash), email verification state, and account settings.</li>
    <li><strong>Workspace content:</strong> documents you upload, extracted text and passages, questions, generated answers, citations, favorites, private notes, onboarding state, and usage records. Your uploaded originals are kept in private application storage and used to provide your workspace features.</li>
    <li><strong>Plan information:</strong> selected plan, access period dates, and the last four digits associated with the current checkout flow. The current checkout does not connect to a payment processor or charge a card; it records plan access and the displayed last four digits only.</li>
    <li><strong>Contact messages:</strong> name, email, optional organization, subject, and message. The contact form sends these details to our support inbox by email; the app does not save the submission in its database.</li>
    <li><strong>Technical information:</strong> information processed by the hosting environment and security controls to deliver the site, protect forms, prevent abuse, and troubleshoot service problems. This may include network and request information such as IP address in service logs or rate limiting.</li>
</ul>

<h2>How workspace content is used</h2>
<p>We process your documents to extract and index text, search your ready documents when you ask a question, and show the passages cited by an answer. To generate an answer, the question and selected relevant passages are sent to Groq through its AI service. Do not upload information unless you are authorized to provide it for this processing. Groq’s handling of submitted data is also subject to its own terms and privacy practices.</p>
<p>Documents and answers are associated with your account and shown in your personal workspace. We do not provide team sharing in the current product. You can delete documents and saved answers using the available controls. Deleting a document also removes answer history that cites it. You can request account deletion from account settings; deletion removes account resources handled by the application, subject to operational backups and records that may take time to expire.</p>

<h2>Service providers and external services</h2>
<p>We use providers needed to operate the app, including cPanel hosting for application infrastructure and storage, Groq for AI answer generation, Cloudflare Turnstile to help protect the public contact form, and the SMTP service configured for email delivery. Information is shared with these providers only as needed for the requested feature. Their own privacy terms apply to information they process.</p>
<p>Links to social networks and map services leave LogicStrand. Those services process information under their own policies. The site currently does not include advertising trackers or analytics tools.</p>

<h2 id="cookies">Cookies and similar storage</h2>
<p>LogicStrand uses essential first-party cookies for session continuity, sign-in, request security, and remembering your cookie choice. The current site does not run optional analytics or marketing cookies. The banner lets you record whether you allow optional cookies or prefer essential cookies only; choosing either option does not activate an optional tracker that is not currently in use.</p>

<h2>Retention and security</h2>
<p>We keep account and workspace information while it is needed to provide the service and allow you to manage it. Deletion from the application may not immediately remove copies in operational backups, hosting logs, or provider systems; those are handled according to the relevant operational retention cycles. We use account access controls and private storage for workspace data, but no online service can promise absolute security.</p>

<h2>International processing</h2>
<p>LogicStrand has operating entities in Sri Lanka and the United States, and service providers may process information in other locations. As a result, information may be handled in countries with privacy rules different from those where you live.</p>

<h2>Your choices and requests</h2>
<p>You can update account details, delete documents and answers, or request account deletion through the product controls. You may also contact <a href="mailto:support@logicstrand.net">support@logicstrand.net</a> with questions or privacy requests. The rights available to you depend on where you live and the laws that apply.</p>

<h2>Changes to this policy</h2>
<p>We may update this policy as the service changes. We will post the current version here and update the date above. Contact us at <a href="mailto:support@logicstrand.net">support@logicstrand.net</a> if you have questions.</p>
@endsection
