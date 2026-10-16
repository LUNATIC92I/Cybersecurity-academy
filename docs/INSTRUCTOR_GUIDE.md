# Instructor Guide

For **Instructors** who author lessons, labs and assessments. You can create and
review content for your assigned courses; platform-wide settings, user
management and commerce belong to administrators (see
[`ADMIN_GUIDE.md`](ADMIN_GUIDE.md)).

---

## 1. Your role

As an Instructor you can:

- Write and edit lessons, labs and questions for assigned courses.
- Set translation status and request publication.
- Review learner-facing content as a student would see it.

You cannot manage users, roles, commerce or platform settings. If you need a
course assigned to you, ask a Course Admin or Super Admin.

---

## 2. Writing a good lesson

Lessons are Markdown. HTML is escaped on render, so write in Markdown and use
the custom blocks rather than raw HTML:

```
:::note      background a learner should keep in mind
:::tip       a practical shortcut or best practice
:::warning   a risk, pitfall or safety/authorisation caveat
:::scenario  a realistic situation to reason about
:::hint      a nudge, revealed before the solution
:::solution  the worked answer (hidden until requested)
:::check     a quick knowledge check
:::diagram   a described diagram
```

A strong lesson has:

1. **Clear objectives** at the top — what the learner will be able to do.
2. **Explanation** with concrete, worked examples.
3. **A scenario** that mirrors real work.
4. **An exercise with a solution** so learners can self-check.
5. **An assignment and criteria** describing what good work looks like.
6. **References** for further reading.

Keep claims accurate. Do not imply a technique has been run against real
infrastructure unless the lesson genuinely walks through that, and never
present offensive techniques outside an authorised, isolated context.

### Content structure on disk

Large, reviewed lessons are kept as versioned files under
`database/content/lessons/` with front-matter and `===assignment===`,
`===criteria===` and `===references===` sections, then seeded. Small edits can
be made directly in the admin content editor.

---

## 3. Building labs

A lab teaches a skill through practice on a **synthetic dataset**. To author
one:

1. Describe the scenario and the dataset the learner will examine.
2. Break the work into **tasks**. Each task has an `answer_spec` that defines
   correctness (an exact value, a set, a pattern, a threshold, …).
3. **Keep answers server-side.** The answer spec is never sent to the browser —
   do not reveal it in the lesson or lab description.
4. Attach the dataset file so learners can download it.
5. **Complete the lab yourself** as a student before publishing to confirm the
   tasks are solvable and scored correctly.

Offensive labs must target only intentionally vulnerable applications the
learner deploys in their **own isolated environment** — the platform never
performs attacks or hosts targets.

---

## 4. Writing questions and exams

- Add questions to the module or course **question bank**. Seven question types
  are available; pick the one that tests understanding, not recall of trivia.
- Write a clear stem, plausible distractors, and (where supported) an
  explanation shown on review.
- Each question is **bilingual** — provide the French translation or mark its
  status so it is not shown as complete when it is not.
- Module quizzes check one module; the final exam draws questions at random and
  freezes them per attempt, with a server-side timer. Scoring is server-side.

---

## 5. Translations

Author in English first, then provide French. Set each item's translation
status honestly. Learners are shown when a French translation is incomplete, so
inaccurate status misleads them. The interface itself must be fully translated —
administrators run `php bin/console translations:check` to enforce that.

---

## 6. Reviewing before publish

Before requesting publication:

- [ ] Objectives match the content.
- [ ] Examples run / results are correct.
- [ ] Exercises and labs are solvable and the solutions are right.
- [ ] Answer specs are server-side only, not leaked in text.
- [ ] French translation provided or status set accurately.
- [ ] Any offensive content is framed with authorisation and isolation.
- [ ] You completed the lab/exam yourself as a student.

Unpublished or unwritten modules show as **in development** to learners — that
is correct. Publish only finished, reviewed work.

---

## 7. Getting help

Ask an administrator to assign courses, adjust roles, or publish content beyond
your permissions. Report any security concern via the contact page's Security
category.
