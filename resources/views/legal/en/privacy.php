<div class="notice notice--info"><p>This policy describes how the platform software processes data. The organisation operating this instance must review it with qualified legal counsel and complete the operator details before going live.</p></div>
<h2>1. Who is responsible</h2>
<p>The operator of this Cybersecurity-Academy instance is the data controller. Contact: <a href="mailto:<?= e(setting('contact_email')) ?>"><?= e(setting('contact_email')) ?></a>.</p>
<h2>2. Data we process</h2>
<ul>
<li><strong>Account data:</strong> e-mail address, display name, password (stored only as a one-way hash), language preferences.</li>
<li><strong>Learning records:</strong> enrolments, lesson progress, study time reported while a lesson tab is visible, lab answers and scores, exam attempts and responses, certificates and achievements.</li>
<li><strong>Security data:</strong> sign-in attempts with IP address and browser user agent, audit logs of administrative actions, rate-limiting counters.</li>
<li><strong>Billing data:</strong> order references, amounts and status. Card numbers and security codes are never received or stored by the platform; payments are handled by the payment provider.</li>
<li><strong>Support requests:</strong> the name, e-mail and message you send.</li>
</ul>
<h2>3. Why we process it</h2>
<ul>
<li>To provide the training service and issue certificates (performance of the contract).</li>
<li>To protect accounts and the platform against abuse (legitimate interest).</li>
<li>To meet accounting obligations for paid orders (legal obligation).</li>
</ul>
<h2>4. What is public</h2>
<p>Nothing is public by default. Certificate verification pages show only the certificate number, holder name, certification title, issue date, status and achievement criteria. Achievement pages and public profiles appear only if you switch them on, and you can switch them off at any time. The platform never posts to social networks on your behalf.</p>
<h2>5. Retention</h2>
<ul>
<li>Account and learning data: for the life of your account.</li>
<li>Sign-in attempt records: 90 days. Expired password-reset tokens: removed hourly.</li>
<li>Certificates: retained after account deletion so that employers can continue to verify them; the record keeps the holder name, title and dates but is detached from your account and removed from public achievement pages.</li>
</ul>
<h2>6. Your rights</h2>
<p>You can download a copy of your data (Settings → Download my data), correct your profile, and request deletion of your account. Deletion takes effect after a 14-day grace period. Depending on where you live, you may have additional rights such as objection or complaint to a supervisory authority.</p>
<h2>7. Security</h2>
<p>Passwords are hashed, sessions are protected against fixation and cross-site request forgery, data in transit should be protected by HTTPS, and administrative actions are logged. No system is perfectly secure; report suspected vulnerabilities via the contact page (category "Security").</p>
<h2>8. Cookies</h2>
<p>See the <a href="<?= e(route('legal.cookies')) ?>">Cookie Policy</a>.</p>
