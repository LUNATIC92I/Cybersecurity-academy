---
title: Security Controls — Design, Testing and Evidence
chapter: Security Controls and Auditing
summary: Turn the controls named in your Statement of Applicability into controls that demonstrably work — design them well, assign owners, test design and operating effectiveness, manage findings to closure, and monitor them continuously.
duration: 55
level: intermediate
preview: true
objectives:
- Describe what makes a control well-designed and name its owner's responsibilities
- Distinguish design effectiveness from operating effectiveness and test each
- Collect audit evidence and judge its quality
- Write a clear finding and track remediation to closure
- Explain continuous control monitoring and the role of audit workpapers
prerequisites:
- ISO/IEC 27001 — The ISMS and the Statement of Applicability
---

## Introduction

Your Statement of Applicability lists the controls you rely on. But a control written in a document is a *claim*; an auditor, a customer and an attacker all care about the same thing — does it actually **work**, and can you **prove** it? This lesson takes the controls you selected and makes them demonstrable: designed to reduce the risk, owned by someone accountable, tested for both design and operation, and backed by evidence.

This is where the inherent-to-residual honesty from the risk module and the "SoA must match reality" rule from the 27001 module become day-to-day practice.

## What a control is — and what makes it good

A **control** is a measure that modifies risk. From the SOC path you know controls by **function** (preventive, detective, corrective) and **type** (technical, administrative, physical). Auditing adds a sharper question: *is this control well-designed, and does it operate?*

A well-designed control is:

- **Risk-linked** — it traces to a specific risk it is meant to reduce (as the SoA requires).
- **Specific and measurable** — "access is reviewed quarterly by the system owner" beats "access is managed".
- **Owned** — one accountable **control owner** (module 1: accountability cannot be delegated).
- **Evidence-producing** — it generates a record as a by-product, so proving it costs little.
- **Proportionate** — its cost fits the risk it reduces.

:::note Control objective vs control
Keep two ideas distinct. A **control objective** is the outcome you want ("only authorised users can access customer data"). A **control** is the specific measure that achieves it ("access requires MFA and is reviewed quarterly"). Several controls often serve one objective; auditors test the controls but judge whether the objective is met.
:::

### The control owner

Every control needs one accountable owner — usually the manager of the function it sits in, not the security or audit team. The control owner is responsible that the control is **designed** correctly, **operates** as intended, **produces evidence**, and is **fixed** when it fails. Audit tests the control; the owner runs it. Confusing these two roles breaks the independence the Three Lines Model requires.

## Two kinds of effectiveness

Auditing a control asks two separate questions, in order:

```text
  1. DESIGN EFFECTIVENESS          2. OPERATING EFFECTIVENESS
     If it worked as intended,        Did it actually operate
     would it reduce the risk?        that way over the period?
          │                                │
     (inspect the design)            (test real instances/samples)
```

- **Design effectiveness** — *Would* the control reduce the risk if it operated as described? A quarterly access review that excludes administrators is poorly designed — it would not catch the biggest risk even if performed perfectly.
- **Operating effectiveness** — *Did* the control operate as designed throughout the period? A well-designed quarterly review that was skipped for two quarters is not operating effectively.

A control must pass **both**. A beautiful design that nobody performs fails; a diligently performed control that is designed wrong fails. Test design first — there is no point sampling the operation of a control that could never work.

## Testing: the four techniques

Auditors (and control owners self-assessing) gather evidence with four classic techniques, in roughly increasing strength:

| Technique | What it is | Example | Strength |
|---|---|---|---|
| **Inquiry** | Ask someone how it works | "Walk me through the access-review process" | Weakest — alone, never sufficient |
| **Observation** | Watch it happen | Observe an analyst triaging an alert | Point-in-time only |
| **Inspection** | Examine records/evidence | Review signed access-review records for 4 quarters | Strong, repeatable |
| **Re-performance** | Independently redo the control | Re-run the access query and compare to what the owner approved | Strongest |

:::tip Inquiry is a starting point, never a conclusion
"They told me they do it" is not evidence a control operates. Corroborate inquiry with inspection or re-performance. A frequent audit failure is relying on a confident interview and never asking to *see* the records.
:::

### Sampling

You rarely test every instance. If an access review happens quarterly, you inspect all four; if a control runs thousands of times (e.g. every code deployment requires approval), you take a **representative sample** and test those. Sampling must be defensible: large enough and chosen without bias, so a clean sample genuinely supports a conclusion about the whole period.

## Evidence and its quality

**Evidence** is the record that lets someone *other than the person who ran the control* conclude it operated. Good evidence is:

- **Sufficient** — enough of it to support the conclusion.
- **Relevant** — it actually relates to the control objective.
- **Reliable** — from a trustworthy source; system-generated logs beat a manually typed summary.
- **Timely** — covers the period under review, with dates.

```text
 Weak evidence:  "Access is reviewed regularly."  (a claim, no proof)
 Strong evidence: an exported access list per quarter, each annotated with
                  approver, date, and the removals actioned — four quarters,
                  system-generated, signed off by the control owner.
```

The "evidence-producing control" from earlier pays off here: if the control emits a dated, system-generated record every time it runs, evidence collection is almost free. If proving a control requires a scramble of screenshots once a year, the control is poorly designed for audit.

## Findings and remediation

When a control is missing, poorly designed, or not operating, you raise a **finding**. A useful finding has a consistent structure:

| Element | Purpose |
|---|---|
| **Condition** | What you found (the fact) |
| **Criteria** | What should be true (policy, standard, the control objective) |
| **Cause** | Why the gap exists (root cause, not just the symptom) |
| **Consequence / risk** | What could happen as a result |
| **Recommendation** | What to do about it |

> **Finding (example):** *Condition* — administrator accounts were excluded from the Q2 and Q3 access reviews. *Criteria* — the access-control standard requires all privileged accounts to be reviewed quarterly. *Cause* — the review script filters out the admin group. *Consequence* — unauthorised or stale admin access could persist undetected, a high risk to customer data (R-001). *Recommendation* — include all privileged accounts and re-run the Q2–Q3 review within 30 days.

Findings are then tracked to **closure**: each gets a severity, an owner, a due date, and a status, and remediation means fixing the **root cause**, not just the one instance. This is the corrective-action loop (27001 clause 10) in operational form. A finding is not closed until the fix is in place *and evidenced* — re-tested, not merely promised.

:::warning Don't close findings on a promise
"We will fix it" is a plan, not a closure. A finding closes only when you can evidence the corrected control operating — otherwise next year's audit re-opens the same gap. Track open findings on a remediation register with due dates and owners, exactly like a risk register.
:::

## Continuous control monitoring

Annual testing leaves long blind spots. **Continuous control monitoring (CCM)** uses automation to check controls far more often — ideally continuously:

- A daily job that flags any privileged account not covered by the last review.
- An alert when MFA is disabled on any account.
- A dashboard of the percentage of systems patched within the SLA.

CCM turns point-in-time testing into an ongoing signal and connects back to the **Key Risk Indicators** from the risk module: a KRI trending the wrong way is often the first sign a control has quietly stopped operating. Mature programmes test high-risk controls continuously and reserve manual audit effort for judgement-heavy areas.

### Workpapers

Whatever you test, **audit workpapers** record it: what was tested, the technique used, the sample, the evidence examined, and the conclusion. Good workpapers let an independent reviewer reach the same conclusion from the same evidence — the audit equivalent of a reproducible result. They are also what a certification auditor or a customer's assessor will ask to see.

## Common mistakes

- **Testing operation before design.** Sampling a control that could never achieve its objective wastes effort — check the design first.
- **Inquiry-only "testing".** Believing an interview without inspecting records.
- **Evidence that is a claim, not a record.** "We do this regularly" proves nothing.
- **Closing findings without re-testing.** A promised fix is not a fixed control.
- **The control owner auditing their own control.** Breaks independence; self-assessment is fine, but assurance must be independent.

## Security implications

- Design controls to be specific, owned, and evidence-producing before you worry about testing them.
- Always establish design effectiveness first, then operating effectiveness over the period.
- Prefer reliable, system-generated, dated evidence; corroborate every inquiry.
- Drive findings to evidenced closure at the root cause, and monitor high-risk controls continuously.

## Knowledge check

:::check Questions
1. What is the difference between design effectiveness and operating effectiveness, and which do you test first?
2. Rank these from weakest to strongest evidence: re-performance, inquiry, inspection.
3. In a finding, what is the difference between the "cause" and the "consequence"?
4. Why can't a control owner provide independent assurance over their own control?
:::

:::solution Answers
1. **Design effectiveness** asks whether the control *would* reduce the risk if it operated as described; **operating effectiveness** asks whether it *did* operate that way over the period. Test **design first** — there is no value in sampling the operation of a control that could never achieve its objective.
2. Weakest → strongest: **inquiry → inspection → re-performance.**
3. The **cause** is the root reason the gap exists (e.g. the review script filters out admins); the **consequence** is what could happen as a result (e.g. stale admin access persists, risking customer data). Cause explains *why*; consequence explains *so what*.
4. Because assurance must be independent (the Three Lines Model): if the person who runs a control also attests it works, "I checked my own work and it's fine" is the only possible answer. The owner may self-assess, but independent assurance must come from someone else (internal audit).
:::

## Exercise

Take one control from your Statement of Applicability (e.g. "quarterly privileged-access review"):

1. State its **control objective** and the **risk** it reduces.
2. Describe how you would test its **design** and then its **operating effectiveness**, naming the **technique** and the **evidence** you would collect for each.
3. Invent a plausible gap and write a full **finding** (condition, criteria, cause, consequence, recommendation).
4. Describe one **continuous monitoring** check that would detect this control failing before the next annual audit.

:::solution Model answer (abbreviated)
1. *Objective:* only authorised staff hold privileged access. *Risk:* stale/unauthorised admin access leading to data compromise (e.g. R-001).
2. *Design:* **inspect** the review procedure — does it cover *all* privileged accounts and require removal of unneeded access? *Operating:* **inspect/re-perform** — obtain the four quarterly reviews, confirm each was performed, signed and dated, and re-run the access query for one quarter to confirm the list matches what was approved.
3. *Finding:* Condition — service accounts were omitted from all four reviews. Criteria — the standard requires every privileged account to be reviewed quarterly. Cause — the review scope was defined as "named users" only. Consequence — over-privileged service accounts could be abused undetected (high risk to customer data). Recommendation — redefine scope to include service accounts and complete a catch-up review within 30 days.
4. *Continuous check:* a daily automated job that lists every account in privileged groups and flags any not present in the most recent completed review — alerting the control owner immediately rather than at year-end.
:::

## Summary and next steps

- A control in the SoA is a claim; auditing proves it is designed right, operates, and is evidenced.
- Test **design effectiveness first**, then **operating effectiveness** over the period.
- Use inquiry, observation, inspection and re-performance — never inquiry alone — and collect sufficient, relevant, reliable, timely evidence.
- Write structured findings (condition, criteria, cause, consequence, recommendation) and drive them to **evidenced** closure at the root cause.
- **Continuous control monitoring** and good workpapers keep assurance live and reproducible.

The next modules apply this discipline to specific domains — privacy and data protection, business continuity, and third-party risk — each adding controls that your SoA records and this lesson's methods verify.

===assignment===
Choose four controls from your Statement of Applicability spanning at least two control functions (preventive, detective, corrective). For each, produce a short **test plan**: the control objective and risk, how you would test design and operating effectiveness, the technique(s) and evidence, and whether you would include it in continuous monitoring. Then write **two complete findings** (using the five-element structure) for plausible gaps, each with a severity, an owner and a 30/60/90-day remediation target.

===criteria===
- Complete the reading, the knowledge check and the exercise.
- Test plans must separate design from operating effectiveness and specify reliable evidence; findings must identify a genuine root cause and drive to evidenced closure.
- Mark the lesson complete. The module quiz draws on this lesson.

===references===
- ISO/IEC 27002:2022, *Information security controls* — https://www.iso.org/standard/75652.html
- ISO/IEC 27001:2022, clauses 9 (performance evaluation) and 10 (improvement) — https://www.iso.org/standard/27001
- ISACA, *Information Systems Auditing: Tools and Techniques* (audit evidence and testing) — https://www.isaca.org/resources
- AICPA, *SOC 2 Trust Services Criteria* (design vs operating effectiveness of controls) — https://www.aicpa-cima.com/topic/audit-assurance/audit-and-assurance-greater-than-soc-2
- NIST SP 800-53A Rev. 5, *Assessing Security and Privacy Controls* — https://csrc.nist.gov/pubs/sp/800/53/a/r5/final
