---
title: Risk Assessment and the Risk Register
chapter: Information Security Risk Management
summary: Turn the risk discipline into a repeatable process — identify risk, score likelihood and impact, separate inherent from residual risk, choose a treatment, and record it all in a defensible risk register.
duration: 55
level: beginner
preview: true
objectives:
- Run the four steps of risk assessment — identification, analysis, evaluation and treatment
- Score likelihood and impact on a 5×5 scale and compute a risk rating
- Distinguish inherent from residual risk and explain what reduces each
- Choose between the four risk treatment options and justify the choice
- Build and maintain a defensible risk register with a named owner for every risk
prerequisites:
- Governance, Risk and Compliance Foundations
resources:
- Sample risk register (CSV) | grc-risk-register.csv
---

## Introduction

In the first module you learned that leadership sets *how much* risk is acceptable. This lesson is the engine that works within that appetite: a repeatable process to find risks, size them consistently, decide what to do, and record the decision so it can be defended months later to an auditor, a customer or your own future self.

The output is the single most important document in GRC: the **risk register**. A good register is not a compliance artefact gathering dust — it is a live list of what could hurt the organisation, ranked, owned and tracked to a decision.

## The risk management process

Most frameworks (ISO/IEC 27005, NIST SP 800-30) describe the same core loop. We use four steps:

```text
  ┌──────────────┐   ┌───────────┐   ┌────────────┐   ┌───────────┐
  │ 1 IDENTIFY   │─► │ 2 ANALYSE │─► │ 3 EVALUATE │─► │ 4 TREAT   │
  │ what could   │   │ likelihood│   │ compare to │   │ choose an │
  │ go wrong     │   │ × impact  │   │ appetite   │   │ option    │
  └──────────────┘   └───────────┘   └────────────┘   └───────────┘
          ▲                                                  │
          └──────────────── 5 MONITOR & REVIEW ◄─────────────┘
```

Risk management is continuous: after treatment you monitor and come back round, because assets, threats and the business all change.

### Step 1 — Identify

Describe each risk as a clear cause-and-effect sentence, not a single word. The vocabulary from the SOC path applies: a risk links an **asset**, a **threat** and a **vulnerability** to an **impact**.

> **Weak:** "Phishing."
> **Strong:** "Criminal groups (threat) exploit the absence of MFA on webmail (vulnerability) to compromise employee mailboxes (asset), leading to invoice-fraud losses and data exposure (impact)."

Sources for identification include asset inventories, past incidents, threat intelligence, audit findings, vulnerability scans, and workshops with the people who run each process.

### Step 2 — Analyse

Analysis sizes the risk. The most common method is **qualitative** scoring of two factors on a 1–5 scale:

- **Likelihood** — how probable is it that the threat exploits the vulnerability in a given period?
- **Impact** — how bad is the consequence if it does?

| Score | Likelihood | Impact |
|---|---|---|
| 1 | Rare | Negligible |
| 2 | Unlikely | Minor |
| 3 | Possible | Moderate |
| 4 | Likely | Major |
| 5 | Almost certain | Severe / catastrophic |

The risk rating is simply:

```text
Risk score = Likelihood × Impact      (range 1–25 on a 5×5 scale)
```

Plotting the two factors gives the familiar **risk matrix**. The cell colour turns a number into a priority:

```text
 Impact
   5 │  5  10  15  20  25
   4 │  4   8  12  16  20
   3 │  3   6   9  12  15
   2 │  2   4   6   8  10
   1 │  1   2   3   4   5
     └─────────────────────
        1   2   3   4   5   Likelihood

   1–4  Low      5–9  Medium
  10–14 High    15–25 Critical     (example bands — set yours to appetite)
```

:::note Qualitative vs quantitative
The 5×5 approach is **qualitative** — fast, consistent enough to rank risks, and easy for stakeholders to grasp. **Quantitative** methods (e.g. estimating Annualised Loss Expectancy in currency) are more precise but need good data and effort. Most organisations run qualitative scoring for the whole register and reserve quantitative analysis for a few top risks where a money figure justifies a large investment.
:::

### Step 3 — Evaluate

Evaluation compares each scored risk to the organisation's **risk appetite and tolerance** (from module 1) to decide whether it is acceptable. A risk of 20 ("Critical") on customer data breaches a low-appetite line and *must* be treated; a risk of 4 ("Low") on an internal wiki may sit comfortably within appetite and simply be accepted. Evaluation is where appetite stops being a slogan and starts sorting the list into *act now*, *plan to treat*, and *accept*.

### Step 4 — Treat

For each risk that exceeds appetite, choose one of **four treatment options**:

| Option | Meaning | Example |
|---|---|---|
| **Mitigate (reduce)** | Add or strengthen controls to lower likelihood and/or impact | Enforce MFA; patch faster; add backups |
| **Transfer (share)** | Move some impact to a third party | Cyber-insurance; a clause making a supplier liable |
| **Avoid** | Stop doing the risky activity | Decommission a legacy system; don't launch a risky feature |
| **Accept** | Consciously tolerate the risk within appetite | Record and sign off a low residual risk |

Three principles make treatment defensible:

1. **Treatment changes the score, not the facts.** You re-score *after* planned controls to get the residual risk (below).
2. **Transfer rarely removes all impact.** Insurance pays money but not reputation; the supplier clause doesn't stop the outage. Residual risk remains.
3. **Acceptance is a decision, not a default.** An accepted risk must be *recorded and signed off by the risk owner* — silence is not acceptance.

## Inherent vs residual risk

This distinction appears in every audit, so be precise:

- **Inherent risk** — the risk level *before* (or ignoring) your controls: how bad it would be if you did nothing.
- **Residual risk** — the risk level *remaining after* your controls operate as intended.

```text
  INHERENT RISK  ──(apply controls)──►  RESIDUAL RISK
   L4 × I5 = 20                           L2 × I3 = 6
   "Critical"                             "Medium" — within appetite?
```

Residual risk is the number leadership actually decides on. If residual risk is still above appetite, you need *more* treatment. If residual risk is comfortably below appetite, you may be over-spending on controls — a chance to redirect effort. Auditors look specifically for: a residual score, the controls that justify the reduction, and an owner who has accepted the remainder.

:::warning Don't invent the reduction
A residual score is only credible if real controls justify the drop. Writing "inherent 20 → residual 4" with no implemented control, or with a control that exists only on paper, is exactly the "compliance theatre" from module 1. The gap between inherent and residual must be earned by controls that actually operate.
:::

## The risk register

The register records every risk and its treatment. At minimum each row has:

| Field | Purpose |
|---|---|
| ID | Stable reference (e.g. R-001) |
| Asset | What is at stake |
| Threat / Vulnerability | The cause |
| Likelihood / Impact | The scores (1–5) |
| Inherent score | Likelihood × Impact before treatment |
| Treatment | The option and planned controls |
| Residual score | The score after treatment |
| Owner | The accountable person |
| Status / review date | Open, treated, accepted; when to revisit |

The accompanying **sample risk register** (download it from this lesson's resources) shows a filled-in example. Notice how each row reads as a defensible decision: a named cause, two scores, a treatment, a residual score, and a single accountable owner — exactly what the next lesson's lab asks you to produce.

:::scenario Reading one row
Take `R-001` from the sample: *Customer database · criminal data theft · no encryption at rest · L4 × I5 = 20 (Critical) · treatment: encrypt at rest + DLP · residual 6 · owner: CISO.* In one line it says what could happen, how serious it was, what we are doing, what remains, and who answers for it. That is the entire risk process compressed into a row — and it is auditable.
:::

### Key Risk Indicators

A register is a snapshot; **Key Risk Indicators (KRIs)** are the metrics that tell you whether a risk is getting worse between reviews. Examples: *percentage of critical vulnerabilities open past their SLA*, *number of accounts without MFA*, *mean time to patch internet-facing systems*. A rising KRI is an early warning that a residual score may no longer hold.

## Common mistakes

- **One-word risks.** "Ransomware" is not assessable. Describe the asset, cause and impact so two assessors would score it the same.
- **Scoring inconsistently.** Without agreed definitions for each 1–5 level, every assessor invents their own scale and the register can't be compared.
- **Residual = inherent with wishful thinking.** Claiming a reduction no control supports.
- **No owner, or "the security team" as owner.** A risk with no accountable business owner never gets a decision.
- **A register nobody reviews.** Risks and KRIs must be revisited on a schedule and after major change.

## Security implications

- Score the whole register with *agreed* likelihood/impact definitions so ratings are comparable.
- Make inherent-to-residual reductions traceable to real, operating controls.
- Assign one accountable owner per risk, and require sign-off for any accepted residual risk.
- Track KRIs so you learn a risk has grown *before* it becomes an incident.

## Knowledge check

:::check Questions
1. A risk scores Likelihood 4 and Impact 5. What is its rating on a 5×5 scale, and which band (using the example bands above)?
2. What is the difference between inherent and residual risk?
3. Which treatment option is "buy cyber-insurance", and does it remove all impact?
4. Why must an accepted risk be signed off by its owner rather than simply left alone?
:::

:::solution Answers
1. 4 × 5 = **20**, which falls in the **Critical** band (15–25).
2. **Inherent** risk is the level before/ignoring controls; **residual** risk is what remains after controls operate as intended.
3. **Transfer (share)**. It does **not** remove all impact — it typically covers financial loss but not reputational damage or the operational outage, so residual risk remains.
4. Because acceptance is a deliberate decision with consequences the owner is accountable for; recording and signing it off makes the decision explicit and auditable, whereas silence leaves the risk untracked and unowned.
:::

## Exercise

Open the sample risk register from this lesson's resources and add **two new risks** for a small online retailer:

1. Write each new risk as a full cause-and-effect sentence (asset, threat, vulnerability, impact).
2. Score likelihood and impact (1–5) and compute the inherent score.
3. Choose a treatment option, state the control(s), and give a realistic residual score with a one-line justification.
4. Assign an accountable owner to each.

:::solution Model answer (one example row)
*Risk:* "An attacker uses stolen credentials (threat) against the admin portal, which lacks MFA (vulnerability), to access the order-management system (asset), exposing customer orders and payment references (impact)."
*Scores:* Likelihood 4, Impact 4 → **inherent 16 (Critical)**.
*Treatment:* **Mitigate** — enforce phishing-resistant MFA on all admin accounts and alert on logins from new locations.
*Residual:* 4 ("Low") — MFA makes credential-only access very unlikely; justified because the control is implemented and verified in the sign-in logs.
*Owner:* IT Manager.
A second row of comparable quality (e.g. an unpatched e-commerce plugin, or a supplier with access to customer data) is equally acceptable.
:::

## Summary and next steps

- Risk assessment is a repeatable loop: identify, analyse, evaluate, treat — then monitor and review.
- Score likelihood × impact consistently; compare the result to appetite to decide what to act on.
- Inherent risk is before controls; residual risk is after — and the reduction must be earned by real controls.
- The four treatments are mitigate, transfer, avoid and accept; acceptance requires owner sign-off.
- The risk register, with a named owner per risk and KRIs to watch trends, is GRC's central living document.

Later modules map these treatments to recognised control sets (ISO/IEC 27001 and the NIST Cybersecurity Framework 2.0) and show how to evidence that the controls actually operate.

===assignment===
Build a **starter risk register** of at least six risks for an organisation of your choice, using the sample CSV as a template. Each row must have a full cause-and-effect risk statement, consistent 1–5 likelihood and impact scores, an inherent score, a treatment option with named controls, a justified residual score, and a single accountable owner. Add a short paragraph proposing **two Key Risk Indicators** you would track, and explain what a rising value of each would warn you about.

===criteria===
- Complete the reading, the knowledge check and the exercise.
- Your register must show a credible gap between inherent and residual scores justified by real controls, and every risk must have one accountable owner.
- Mark the lesson complete. The hands-on lab for this module scores a risk register you build; the module quiz draws on this lesson.

===references===
- ISO/IEC 27005:2022, *Guidance on managing information security risks* — https://www.iso.org/standard/80585.html
- NIST SP 800-30 Rev. 1, *Guide for Conducting Risk Assessments* — https://csrc.nist.gov/pubs/sp/800/30/r1/final
- NIST SP 800-39, *Managing Information Security Risk* — https://csrc.nist.gov/pubs/sp/800/39/final
- ISO 31000:2018, *Risk management — Guidelines* — https://www.iso.org/standard/65694.html
