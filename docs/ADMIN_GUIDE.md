# Administrator Guide

For **Super Admins** and **Course Admins** who operate the platform. It assumes
the platform is installed and you can sign in to the admin area. For setup see
[`INSTALLATION.md`](INSTALLATION.md); for hardening see
[`SECURITY.md`](SECURITY.md).

---

## 1. Roles

| Role | Can do |
|---|---|
| **Super Admin** | Everything: users, roles, content, commerce, certificates, settings, audit log. |
| **Course Admin** | Manage courses, modules, lessons, labs, questions and translations. |
| **Instructor** | Author and review content and assessments for assigned courses. |
| **Student** | Learn, take labs and exams, earn certificates. |

Authorisation is enforced on the server for every action. Granting a role
grants only the permissions attached to it.

### Creating the first admin

```bash
php bin/console make:admin --email=admin@example.org --name="Platform Admin"
```

Then create further staff from **Admin → Users**, assigning the least role that
lets them do their job.

---

## 2. The admin area

After signing in as staff, open the admin area from the account menu. It is
organised into:

- **Dashboard** — activity, enrolments, recent quizzes and certificates.
- **Users** — search accounts, assign roles, verify e-mail, suspend.
- **Content** — paths, courses, modules, lessons.
- **Labs** — lab definitions, tasks and datasets.
- **Questions** — question banks, module quizzes, final exams.
- **Translations** — per-item translation status across the catalogue.
- **Certificates** — issued certificates, revocation, events.
- **Commerce** — products/pricing, coupons, orders and payments.
- **Support** — contact messages and categories.
- **Settings** — platform settings.
- **Audit log** — who did what, when, from where.

---

## 3. Managing content

Courses sit under paths; modules sit under courses; lessons sit under modules.

1. **Create or edit a course** from **Content**, filling English fields first.
2. **Add modules** in teaching order.
3. **Add lessons.** Lesson bodies are Markdown; HTML is escaped on render, and
   custom blocks are available:

   ```
   :::note ... :::      :::tip ... :::       :::warning ... :::
   :::hint ... :::       :::solution ... :::  :::scenario ... :::
   :::check ... :::      :::diagram ... :::
   ```

4. **Publish** only when the lesson is complete and reviewed. Unpublished or
   unwritten modules appear in the syllabus as **in development** — this is
   intentional honesty, so never mark a module complete before its lessons
   exist.

Content can also be seeded from versioned files under `database/content/`
(`php bin/console seed`), which is the recommended path for large, reviewed
additions.

---

## 4. Labs

A lab has tasks, and each task has an `answer_spec` that defines correctness.
The answer spec is stored server-side and is **never** sent to the browser —
do not paste answers into lesson text. Attach any synthetic dataset the lab
uses so learners can download it. Test every lab by completing it yourself as a
student before publishing.

---

## 5. Questions and exams

- Build **question banks** per module and course. Seven question types are
  supported; each question is bilingual and carries a translation status.
- **Module quizzes** check a single module; a **final exam** draws questions at
  random per attempt and freezes them for that attempt.
- Scoring is server-side only; the exam has a server-enforced timer. Set the
  retake policy per assessment.

---

## 6. Translations

Open **Translations** to see, per item, whether French is complete. The
interface itself is guarded by `php bin/console translations:check`, which
fails if any UI key is missing in French. For content, keep the
`translation_status` accurate: learners are shown when a French translation is
incomplete rather than being given English silently.

---

## 7. Certificates

Certificates are issued automatically when a learner meets a course's
completion rule (lessons, labs and the exam). From **Certificates** you can:

- View issued certificates and their verification status.
- **Revoke** a certificate (with a reason) — this is recorded in the
  certificate event log and reflected on the public verification page.

Each certificate has a unique number and an HMAC signature tied to `APP_KEY`.
Do not change `APP_KEY` after certificates exist, or every signature becomes
invalid. Certificates are **not** accredited or government-recognised; never
describe them as equivalent to external professional certifications.

---

## 8. Commerce

Products, pricing and coupons live under **Commerce**. Out of the box payments
run in **demo mode** (clearly labelled, no real charges). To accept real
payments, configure a provider and verify an end-to-end checkout yourself — see
[`DEPLOYMENT.md`](DEPLOYMENT.md §6). Enrolment is only ever granted on a
server-verified payment webhook, so an order marked paid in the UI reflects a
real settlement, not a browser redirect.

---

## 9. Users and support

- From **Users**, assist with verification, role changes and suspensions.
- From **Support**, triage contact messages. Security reports arrive under the
  Security category — handle them per [`SECURITY.md`](SECURITY.md §11).

---

## 10. Audit and maintenance

- The **Audit log** records sensitive actions (actor, action, target, IP,
  time). Review it regularly.
- Ensure the hourly maintenance job is running (see
  [`INSTALLATION.md`](INSTALLATION.md §8)); it expires stale sessions and prunes
  throttle data.
- Keep backups of the database and `storage/`, and store `APP_KEY` safely.

---

## 11. Good operating practice

- Give staff the least role they need.
- Review content and labs as a student before publishing.
- Keep translation status honest.
- Never relax the Content-Security-Policy to allow inline scripts.
- Remove demo accounts before go-live.
