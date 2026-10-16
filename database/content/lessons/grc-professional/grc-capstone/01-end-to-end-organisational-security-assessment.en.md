---
title: GRC Capstone — An End-to-End Organisational Security Assessment
chapter: GRC Capstone Project
summary: Put the whole path into practice on one fictional organisation — inventory its assets, assess and register its risks, map controls to ISO/IEC 27001 and run a NIST CSF 2.0 gap analysis, then deliver a prioritised remediation roadmap and an executive report.
duration: 90
level: advanced
preview: true
objectives:
- Plan and scope a complete GRC assessment of an organisation
- Produce the linked artefacts the path taught — asset register, risk register, control mapping, gap analysis
- Prioritise findings into a costed, risk-driven remediation roadmap
- Write an executive report that a non-technical board can act on
- Present the whole assessment as one traceable, defensible package
prerequisites:
- GRC Tools and Documentation — The Registers, Matrices and Forms That Make GRC Auditable
---

## Introduction

This capstone is where the eight preceding modules stop being separate topics and become one piece of work. You will run a complete governance, risk and compliance assessment of a single fictional organisation and produce the deliverables a real GRC professional hands to leadership: an asset inventory, a risk register, a control mapping and Statement of Applicability, a NIST CSF 2.0 gap analysis, a remediation roadmap, and an executive report.

Nothing here is new theory. The challenge — and the skill — is **integration**: making every artefact trace to the next, scoping the work so it finishes, and translating technical findings into decisions a board can make. Work through it on the scenario below (or substitute your own organisation of similar size).

:::note This is a simulation
The organisation below is fictional and the exercise is a desk assessment using the methods of this path. It is not a penetration test and does not require you to attack anything. All "evidence" you cite is of the paper/interview kind this path teaches (policies, configurations described, interview answers) — you are practising the GRC workflow, not live testing.
:::

## The scenario: "Meridian Health Booking"

> **Meridian Health Booking** is a 40-person company that runs an online appointment-booking platform used by private clinics. It stores patient contact details and appointment history (including the clinic and appointment type), takes card payments through a third-party processor, and runs on a single cloud provider. It has an IT manager, two developers, a support team, and outsources payroll to a SaaS vendor. It has grown fast and has almost no written security governance. Leadership wants to win larger clinic chains as customers — several now demand evidence of good security before signing.

This organisation deliberately touches every module: sensitive personal (health-adjacent) data, a payment processor and cloud and payroll suppliers, a continuity dependency on one cloud provider, no governance, and a business reason to improve (customer demand). Your job is to assess it and tell leadership what to do.

## The assessment, phase by phase

Treat the capstone as six phases. Each produces one artefact that feeds the next — the traceability chain from the documentation module.

### Phase 1 — Scope and govern

Define what you are assessing and establish the minimum governance. Decide the **scope** (the booking platform, the data it holds, the suppliers it depends on), identify the **interested parties** (patients, clinics, the payment processor, regulators), and draft the two governance artefacts Meridian lacks: a one-paragraph **information security policy** and a **risk appetite statement** appropriate to a company holding health-adjacent personal data. Name an accountable owner for the assessment.

*Artefact:* a short scope-and-governance statement.

### Phase 2 — Inventory the assets

You cannot assess what you have not listed. Build an **asset register** with stable IDs: the booking application, the patient database, the payment integration, the cloud infrastructure, the payroll SaaS data, staff endpoints. For each, record owner, classification (the patient database is clearly the most sensitive), and what depends on it.

*Artefact:* asset register (`A-###`).

### Phase 3 — Assess and register the risks

For each significant asset, identify risks as cause-and-effect statements, score likelihood × impact on the 5×5 scale, and record inherent score, treatment and a realistic residual. Expect to surface risks such as: no MFA on the admin portal; patient data not encrypted at rest; a single cloud provider as a continuity single point of failure; no vetting of the payroll and payment suppliers; no backup isolation against ransomware. Each becomes a row with an owner.

*Artefact:* risk register (`R-###`) — the heart of the assessment.

### Phase 4 — Map controls (ISO/IEC 27001) and run a gap analysis (NIST CSF 2.0)

Two complementary views of the same control reality:

- **Control mapping / mini-SoA.** For the risks you chose to mitigate, select Annex A controls, mark applicability with justification (tracing to the risk ID), and record status — including honest "not yet implemented" entries, since Meridian has gaps.
- **NIST CSF 2.0 gap analysis.** Assess a Current Profile across the six Functions and set a risk-driven Target Profile. Meridian will likely be weak in Govern (no policy), Protect (no MFA, no encryption) and Recover (untested backups); its Target Profile raises the functions that reduce its biggest risks first.

*Artefacts:* a control matrix / mini-SoA and a CSF Current-vs-Target gap summary.

### Phase 5 — Build the remediation roadmap

Turn the gaps into a prioritised plan. Rank remediations by the **residual risk they reduce** against the **effort/cost** they take, grouped into horizons:

```text
 NOW (0–30 days)      Quick, high-impact: enforce MFA, isolate/offline backups,
                      vet the payment & payroll suppliers' attestations
 NEXT (1–3 months)    Encrypt patient data at rest, write core policies,
                      define a retention schedule, run a tabletop exercise
 LATER (3–12 months)  Formalise the ISMS toward ISO 27001, continuous control
                      monitoring, a second-region/continuity option
```

Each roadmap item names the risk it treats, an owner, and a rough cost/effort — so leadership can fund it. This is risk appetite (module 1) turned into a spending plan.

*Artefact:* a remediation roadmap.

### Phase 6 — Write the executive report

Leadership will not read your registers. The **executive report** (1–2 pages) states, in business language: the scope assessed; the **top 3–5 risks** and what they mean for Meridian (lost customers, regulatory exposure, downtime); the current posture at a glance (the CSF profile); the roadmap with its "now" asks and their cost; and a clear recommendation. It answers the board's real question — *what should we fund, and what happens if we don't?* — and never overstates: it distinguishes what is implemented from what is planned, and makes no claim of accreditation or of tests not actually run.

*Artefact:* the executive report — the one document leadership actually reads.

## How the artefacts connect

The capstone is graded as much on **traceability** as on content. A reviewer should be able to pull any thread and follow it the whole way:

```text
 Asset (A-002 patient DB)
    └─ Risk (R-001 unencrypted patient data, inherent 20)
          └─ Control (A.8.24 cryptography, mapped, status: planned)
                └─ CSF gap (Protect below target)
                      └─ Roadmap item (NEXT: encrypt at rest, owner, cost)
                            └─ Executive report (top risk #1, recommended now/next)
```

If every top risk traces like this from asset to boardroom, you have produced exactly what a GRC professional delivers.

## Common mistakes

- **Scope creep.** Trying to assess everything at once; the assessment never finishes. Scope deliberately in phase 1.
- **A risk register that stops at scoring.** Risks with no treatment, owner or link to a control are half the job.
- **A roadmap that is not risk-ranked.** Ordering remediations by ease, or doing the interesting ones first, instead of by residual risk reduced.
- **An executive report full of jargon.** If the board cannot act on it, it failed, however rigorous the analysis beneath it.
- **Overstating posture.** Claiming controls are implemented when they are planned, or implying certification. Honesty is the deliverable's credibility.

## Security implications

- Scope first, so the assessment is finishable and defensible.
- Keep every artefact linked by stable IDs, so the whole package is traceable end to end.
- Prioritise remediation by residual risk reduced per unit of effort, not by convenience.
- Report honestly and in business terms; the board funds what it understands and trusts.

## Knowledge check

:::check Questions
1. Why is defining scope the essential first phase of the assessment?
2. Which single artefact is the "heart" of the assessment, and why?
3. What two factors should rank items in the remediation roadmap?
4. Name two things an executive report must do that a risk register does not.
:::

:::solution Answers
1. Because without a deliberate scope the assessment never finishes (scope creep) and cannot be defended — scope defines what was and was not covered, which every later artefact and the final report depend on.
2. The **risk register.** Everything else connects to it: assets are what risks threaten, controls exist to reduce register risks, the gap analysis and roadmap are organised around them, and the executive report summarises the top register risks.
3. The **residual risk each item reduces** and the **effort/cost** it takes — a risk-driven, cost-aware ranking (not ease or interest).
4. Any two of: translate findings into **business language and consequences**; surface only the **top few risks** and a clear **recommendation**; state **what to fund now and the cost**; and honestly distinguish **implemented vs planned** — things a detailed register is not designed to do for a board.
:::

## Capstone project

Complete the full assessment of **Meridian Health Booking** (or a comparable organisation you define), producing all six artefacts as one linked package:

1. A **scope-and-governance statement** (scope, interested parties, a one-paragraph security policy, a risk appetite statement, and the accountable owner).
2. An **asset register** of at least six assets with IDs, owners and classification.
3. A **risk register** of at least eight risks with IDs referencing assets, 5×5 scoring, inherent and residual scores, treatment and owners.
4. A **control matrix / mini-SoA** (at least eight Annex A controls across the four themes, each tracing to a risk, with honest status) **and** a **NIST CSF 2.0 Current-vs-Target gap summary** across the six Functions.
5. A **remediation roadmap** in Now / Next / Later horizons, each item naming the risk treated, an owner and a rough cost/effort.
6. A one-to-two-page **executive report** in business language with the top 3–5 risks, current posture, the roadmap, costs, and a recommendation.

:::solution What a strong submission demonstrates
A strong capstone is judged on integration, not length. The reviewer should be able to pick your **#1 executive-report risk** and trace it backwards without gaps: to a roadmap item (with owner and cost), to a CSF gap, to a mapped control (with honest status), to a scored risk-register row, to a classified asset. Scope is explicit and realistic for a 40-person company. The roadmap is ordered by residual-risk-reduced, with genuine quick wins in "Now" (MFA, backup isolation, supplier attestation checks). The executive report is in plain business language, names consequences (lost enterprise customers, regulatory and patient-trust exposure, downtime), separates implemented from planned, and makes no claim of accreditation or of testing not performed. Depth in one well-traced thread beats breadth with broken links.
:::

## Summary and course close

- The capstone integrates the whole path: govern, inventory, assess risk, map controls and gaps, prioritise remediation, and report to leadership.
- Its real test is **traceability** — every top risk linked from asset to boardroom — and **scope discipline** so the work finishes.
- Prioritise by residual risk reduced per effort, and report honestly in business terms.

Completing this capstone means you can take an organisation with little or no governance and leave it with a defensible, prioritised, board-ready security programme — which is the whole job of a GRC professional. The course final exam draws across all the modules; passing it earns your **GRC Professional** certificate, which recognises your achievement on this platform (and, as always, is not an accredited or government-recognised qualification).

===assignment===
Submit your complete six-artefact assessment of Meridian Health Booking (or your defined equivalent) as one package, as specified in the Capstone project above. Include a short cover note (half a page) that walks the reader through the trace of your single highest-priority risk from the asset it threatens all the way to its line in the executive report.

===criteria===
- Complete the reading and the knowledge check, and submit all six capstone artefacts as one linked package.
- The package must demonstrate end-to-end traceability for at least your top risk, an explicit and realistic scope, a residual-risk-ranked roadmap, and an honest executive report (implemented vs planned, no accreditation claims).
- Mark the lesson complete. The GRC Professional final exam draws on every module in this path.

===references===
- ISO/IEC 27001:2022 and ISO/IEC 27002:2022 (ISMS requirements and controls) — https://www.iso.org/standard/27001
- ISO/IEC 27005:2022 (information security risk management) — https://www.iso.org/standard/80585.html
- NIST Cybersecurity Framework 2.0 (NIST CSWP 29) — https://doi.org/10.6028/NIST.CSWP.29
- NIST SP 800-30 Rev. 1 (conducting risk assessments) — https://csrc.nist.gov/pubs/sp/800/30/r1/final
- ISO 22301:2019 (business continuity) — https://www.iso.org/standard/75106.html
