---
title: ISO/IEC 27001 — The ISMS and the Statement of Applicability
chapter: ISO/IEC 27001
summary: Understand what an Information Security Management System is, how the Plan-Do-Check-Act cycle drives it, and how risk assessment leads to selecting Annex A controls and documenting them — defensibly — in a Statement of Applicability.
duration: 55
level: intermediate
preview: true
objectives:
- Explain what an ISMS is and how ISO/IEC 27001 differs from a framework like CSF
- Describe the Plan-Do-Check-Act cycle and the clause 4–10 structure of the standard
- Connect risk assessment and treatment to the selection of Annex A controls
- Explain the purpose of the Statement of Applicability and what makes it defensible
- Describe internal audit, management review and continual improvement
prerequisites:
- Risk Assessment and the Risk Register
- The NIST Cybersecurity Framework 2.0 — Functions and Profiles
---

## Introduction

The previous lesson used the NIST Cybersecurity Framework to *structure* a programme around outcomes. **ISO/IEC 27001** goes one step further: it defines a complete, auditable **management system** for information security — and unlike CSF, you can be **certified** against it by an accredited body. That certificate is what many customers and regulators ask for as proof that security is managed, not improvised.

This lesson explains the management system, the cycle that keeps it alive, and the single document auditors scrutinise most: the **Statement of Applicability**.

## From framework to management system

An **Information Security Management System (ISMS)** is the set of policies, processes, roles and records through which an organisation manages information security *as an ongoing system*, not a one-off project. ISO/IEC 27001:2022 specifies the requirements for building, running and improving one.

| | NIST CSF 2.0 | ISO/IEC 27001 |
|---|---|---|
| Nature | Outcome framework | Management-system standard |
| Certifiable? | No | **Yes**, by an accredited body |
| Focus | *What* good looks like | *How* to manage security as a system |
| Controls | Informative references | Risk-selected, with Annex A as a reference set |
| Output | Profiles, roadmap | A running ISMS + certificate |

They are complementary: CSF helps you *see and prioritise*; 27001 gives you the *system and evidence*. Both start from the same engine you already learned — risk assessment.

:::note "Certification" means the system, not the product
Being ISO/IEC 27001 certified means an accredited auditor examined your *management system* and found it conforms to the standard and operates. It does **not** certify a product as secure, and it is not a government accreditation of individuals. Describe it precisely: "our ISMS is certified to ISO/IEC 27001", never "we are government-certified secure".
:::

## The Plan-Do-Check-Act cycle

An ISMS runs on continual improvement, expressed as **Plan-Do-Check-Act (PDCA)**:

```text
        ┌────────► PLAN ─────────┐
        │   context, scope,      │
        │   risks, objectives,   ▼
      ACT                       DO
   improve,                  implement
   corrective               controls &
   actions                  processes
        ▲                       │
        │   CHECK ◄──────────────┘
        └── monitor, audit,
            management review
```

- **Plan** — understand context, set the scope, assess risk, choose treatments and objectives.
- **Do** — implement the controls and run the processes.
- **Check** — monitor, measure, audit internally, and review at management level.
- **Act** — correct what is not working and improve.

The cycle never stops; that is what makes security a *managed* function rather than an annual scramble.

## How the standard is organised

ISO/IEC 27001 has numbered **clauses 4–10** (the certifiable requirements) plus **Annex A** (a reference list of controls).

| Clause | Theme | PDCA |
|---|---|---|
| 4 Context of the organisation | Internal/external issues, interested parties, **ISMS scope** | Plan |
| 5 Leadership | Top-management commitment, policy, roles | Plan |
| 6 Planning | **Risk assessment and treatment**, objectives, the SoA | Plan |
| 7 Support | Resources, competence, awareness, documented information | Do |
| 8 Operation | Operate the processes; run risk assessment/treatment | Do |
| 9 Performance evaluation | Monitoring, **internal audit**, **management review** | Check |
| 10 Improvement | Nonconformity, corrective action, continual improvement | Act |

Two clauses deserve emphasis now.

**Clause 4 — scope.** You must define exactly what the ISMS covers: which parts of the organisation, which locations, which systems and services. Scope is a deliberate decision — too narrow and the certificate is meaningless to customers; too broad and it is unmanageable. Everything else depends on it.

**Clause 5 — leadership.** Top management must demonstrably own the ISMS: approve the policy, assign roles, provide resources and set direction. This is module 1's governance made mandatory — the standard will not accept security that leadership merely tolerates.

## Risk assessment → control selection → the SoA

Clause 6 is where the risk engine from the risk-management module drives everything:

```text
 RISK ASSESSMENT ──► RISK TREATMENT ──► CONTROL SELECTION ──► STATEMENT OF
 (identify, score)   (choose option)    (what reduces risk)   APPLICABILITY
```

1. **Assess risk** using your defined method (likelihood × impact, as you learned).
2. **Decide treatment** for each risk (mitigate, transfer, avoid, accept).
3. For risks you mitigate, **select controls** that actually reduce them.
4. Compare your selected controls against **Annex A** — a catalogue of 93 controls in the 2022 edition, grouped into four themes: **Organizational, People, Physical, Technological**.
5. Record the result in the **Statement of Applicability**.

Annex A is a *checklist against which to compare*, not a menu to adopt wholesale. The standard is clear that controls are chosen because your **risk assessment** calls for them — Annex A simply ensures you have not overlooked a category.

### The Statement of Applicability (SoA)

The **SoA** is the keystone document of a 27001 ISMS. For **every** Annex A control it records:

- whether the control is **applicable** (included) or not;
- the **justification** for including or excluding it;
- its **implementation status** (and where it is defined).

```text
 Control        Applicable?   Justification                         Status
 A.5.15 Access   Yes          Risk R-002 (account compromise)       Implemented — access policy + MFA
   control
 A.8.24          Yes          Protects customer data at rest (R-001) Implemented — DB + backup encryption
   Cryptography
 A.7.4 Physical  No           No owned premises; fully cloud-hosted  Excluded with rationale
   monitoring
```

What makes an SoA **defensible**:

1. **Every inclusion traces to a risk.** An auditor should be able to follow a control back to the risk in your register that justifies it.
2. **Every exclusion has a genuine, documented reason.** "Not applicable because we have no physical premises" is defensible; excluding a control simply because it is inconvenient is a finding.
3. **It matches reality.** A control marked "implemented" must actually operate, with evidence — the inherent-vs-residual honesty from the risk module applies directly here.

:::warning The SoA is where audits are won or lost
Auditors use the SoA as their map: they pick controls from it and ask for evidence. An SoA that claims controls you cannot evidence, or that excludes controls without real justification, turns straight into nonconformities. Keep it honest and keep it current — update it whenever risks or controls change, not once a year.
:::

## Check and Act: audit, review, improvement

**Internal audit (clause 9.2).** The organisation audits its own ISMS on a planned schedule to confirm it conforms to the standard *and* to your own requirements, and that it is effective. As module 1's Three Lines Model requires, internal audit must be **independent** of the activities it audits — an auditor does not review their own work.

**Management review (clause 9.3).** Top management formally reviews the ISMS at planned intervals: audit results, risk and objective status, incidents, and opportunities to improve. This closes the governance loop — leadership sees whether the system works and directs change.

**Improvement (clause 10).** When something does not conform — a failed control, a missed objective, an incident — you raise a **nonconformity**, take **corrective action** (fix the issue *and* its root cause), and record it. Continual improvement is not optional; the standard requires the ISMS to keep getting better.

:::scenario One thread, end to end
Risk `R-001` (customer database, no encryption at rest, inherent 20) is treated by *mitigate*. You select Annex A **A.8.24 (use of cryptography)**, mark it applicable in the SoA with R-001 as justification, implement database and backup encryption, and record the residual risk as 6. At the next internal audit, the auditor opens the SoA, sees A.8.24, and asks for evidence; you show the encryption configuration and a key-management procedure. Management review notes the risk is now within appetite. That single thread — risk → treatment → control → SoA → evidence → audit → review — *is* the ISMS working.
:::

## Common mistakes

- **Adopting all of Annex A regardless of risk.** Controls must be risk-driven; blanket adoption wastes effort and still fails if they do not operate.
- **An SoA that does not match reality.** Claiming controls you cannot evidence is the fastest route to a major nonconformity.
- **Vague or missing scope.** An undefined scope makes the whole ISMS — and any certificate — untrustworthy.
- **Leadership in name only.** Without real top-management ownership, clause 5 is unmet and the system decays.
- **One-and-done.** Treating certification as a finish line instead of a cycle; the standard requires continual improvement.

## Security implications

- Let risk drive control selection, then use Annex A to check you missed no category.
- Keep the SoA honest and current; it is the audit's map and your proof of a managed system.
- Preserve audit independence and hold real management reviews — governance must see the evidence.
- Treat nonconformities as improvement fuel: fix the root cause, not just the symptom.

## Knowledge check

:::check Questions
1. What is the key difference between NIST CSF and ISO/IEC 27001 regarding certification?
2. In the PDCA cycle, which activities belong to "Check"?
3. What two things must the Statement of Applicability record for every Annex A control?
4. Why must internal audit be independent of the activity being audited?
:::

:::solution Answers
1. CSF is an outcome **framework** and is **not certifiable**; ISO/IEC 27001 specifies a **management system** against which an organisation **can be certified** by an accredited body.
2. **Monitoring and measurement, internal audit, and management review** (clause 9, performance evaluation).
3. Whether the control is **applicable (included or excluded)** and the **justification** for that decision — and, in practice, its implementation status. (Applicability + justification are the core two.)
4. So the audit is credible: if auditors reviewed their own work, "we checked ourselves and it's fine" would be the only possible answer. Independence (the Three Lines Model) is what lets internal audit give leadership an honest assessment.
:::

## Exercise

Using the sample risk register from the risk-management module:

1. Pick **three** risks you chose to *mitigate*.
2. For each, name a plausible **Annex A theme** (Organizational, People, Physical or Technological) and a control that would reduce it.
3. Draft the matching **SoA rows**: control, applicable (yes/no), justification (reference the risk ID), and implementation status.
4. Add **one** control you would mark *not applicable*, with a genuine justification.

:::solution Model answer (abbreviated)
1–3.
- R-001 (customer DB, no encryption) → **Technological**, *use of cryptography (A.8.24)* → SoA: *Applicable; justified by R-001; Implemented — database and backup encryption with documented key management.*
- R-002 (no MFA on webmail) → **Technological/Organizational**, *access control & identity management* → SoA: *Applicable; justified by R-002; Implemented — access control policy and enforced MFA.*
- A phishing/people risk → **People**, *information security awareness, education and training* → SoA: *Applicable; justified by the phishing risk; Implemented — onboarding and annual awareness training with completion records.*
4. *Not applicable:* a physical control such as *physical security monitoring* → *Excluded; the organisation owns no premises and is fully cloud-hosted, so the risk is addressed through the cloud provider's shared-responsibility controls.*
:::

## Summary and next steps

- ISO/IEC 27001 defines a **certifiable ISMS** — a system for managing security, driven by PDCA.
- Clauses 4–10 are the requirements; clause 4 sets **scope** and clause 5 mandates **leadership**.
- Clause 6 turns **risk assessment** into **control selection**, compared against **Annex A** and recorded in the **Statement of Applicability**.
- A defensible SoA traces every included control to a risk, justifies every exclusion, and matches reality.
- **Internal audit, management review and corrective action** keep the ISMS honest and improving.

The next modules turn to designing and testing the controls themselves, evidencing that they operate, and managing privacy, continuity and third-party risk — all of which populate the SoA you have just learned to build.

===assignment===
For an organisation of your choice, draft a **mini-SoA extract** of at least eight Annex A controls spanning all four themes (Organizational, People, Physical, Technological). For each: applicability, a justification that references a risk (use or extend your risk register), and an implementation status. Include at least two justified exclusions. Add a short paragraph defining the **ISMS scope** you assumed and explaining how scope affected which controls are applicable.

===criteria===
- Complete the reading, the knowledge check and the exercise.
- Every included control must trace to a risk, every exclusion must have a genuine documented reason, and statuses must be realistic (no claimed controls you could not evidence).
- Mark the lesson complete. The module quiz draws on this lesson.

===references===
- ISO/IEC 27001:2022, *Information security, cybersecurity and privacy protection — Information security management systems — Requirements* — https://www.iso.org/standard/27001
- ISO/IEC 27002:2022, *Information security controls* (guidance on the Annex A controls) — https://www.iso.org/standard/75652.html
- ISO/IEC 27005:2022, *Guidance on managing information security risks* — https://www.iso.org/standard/80585.html
- ISO, *ISO/IEC 27001 and related standards — information security management* — https://www.iso.org/isoiec-27001-information-security.html
