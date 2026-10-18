---
title: The NIST Cybersecurity Framework 2.0 — Functions and Profiles
chapter: NIST Cybersecurity Framework 2.0
summary: Learn the six Functions of CSF 2.0, how Govern wraps the others, and how to use Profiles and Tiers to turn a framework into a prioritised, measurable improvement plan.
duration: 50
level: intermediate
preview: true
objectives:
- Name the six CSF 2.0 Functions and explain what each one covers
- Explain why Govern was added in 2.0 and how it relates to the other five Functions
- Describe the Core's structure — Functions, Categories and Subcategories
- Use Current and Target Profiles plus a gap analysis to prioritise improvement
- Explain Implementation Tiers and what they do and do not measure
prerequisites:
- Governance, Risk and Compliance Foundations
- Risk Assessment and the Risk Register
---

## Introduction

A risk register tells you *what* to fix. A framework helps you organise *the whole programme* so nothing important is forgotten and progress can be measured and communicated. The **NIST Cybersecurity Framework (CSF) 2.0**, released in 2024, is one of the most widely used. It is deliberately flexible — not a checklist to pass, but a common language for describing, assessing and improving cybersecurity outcomes.

This lesson covers the parts you will use constantly: the six **Functions**, the **Core** that organises outcomes beneath them, and the **Profiles** and **Tiers** that turn the framework into a plan.

## What CSF is — and is not

- It **is** a catalogue of cybersecurity *outcomes* ("what good looks like"), organised so you can assess where you stand and where you want to be.
- It is **not** a certification. You do not "pass CSF"; there is no CSF certificate. (Contrast ISO/IEC 27001, the next module, which *is* certifiable.)
- It is **outcome-based**, not prescriptive: it tells you *what* outcome to achieve, not *which product* to buy.
- It is **sector- and size-agnostic**: a hospital, a bank and a two-person startup can all use it, scaled to their context.

:::note A common language
CSF's biggest practical value is communication. "Our Detect function is weak, Tier 2, and our target is Tier 3 within a year" conveys more to a board in one sentence than a list of tools ever could. Pairing it with your risk register — framework for structure, register for priority — is how many programmes are run.
:::

## The six Functions

CSF 2.0 organises everything under six top-level **Functions**. Version 1.1 had five; version 2.0 added **Govern** and placed it at the centre.

:::diagram NIST CSF 2.0: Govern wraps the five core functions
```text
                 ┌─────────────────────────┐
                 │         GOVERN          │
                 │  strategy, roles, risk  │
                 │  decisions, oversight   │
                 └───────────┬─────────────┘
                             │ informs & wraps
   ┌──────────┬──────────┬───┴───────┬───────────┬──────────┐
   ▼          ▼          ▼           ▼           ▼
IDENTIFY   PROTECT     DETECT     RESPOND     RECOVER
know what  safeguard   find       act on      restore
you have   it          problems   incidents   operations
```
:::

| Function | Core question | Examples of outcomes |
|---|---|---|
| **Govern (GV)** | How do we decide, oversee and hold ourselves accountable? | Strategy, policy, roles, risk appetite, supply-chain governance |
| **Identify (ID)** | What do we have and what are the risks to it? | Asset inventory, risk assessment, business environment |
| **Protect (PR)** | How do we safeguard assets? | Access control, awareness training, data security, maintenance |
| **Detect (DE)** | How do we find that something is wrong? | Continuous monitoring, event analysis, SOC detections |
| **Respond (RS)** | What do we do during an incident? | Response planning, analysis, mitigation, communication |
| **Recover (RC)** | How do we restore and learn? | Recovery planning, restoration, lessons learned |

### Why Govern was added

In CSF 1.1, governance ideas were scattered inside Identify. Version 2.0 promoted them to a full Function because the 2024 update recognised what module 1 of this path teaches: **the other five Functions only work if someone sets strategy, assigns roles and decides risk appetite.** Govern does not replace the others — it *wraps* them, feeding direction into Identify through Recover and consuming their results to adjust. It explicitly includes **cybersecurity supply-chain risk management**, reflecting how much risk now comes through third parties.

:::tip Map incidents to Functions
A useful habit: as events happen, map them to Functions. A missed asset in a breach is an *Identify* gap; an attacker dwelling undetected for weeks is a *Detect* gap; a chaotic response is a *Respond* gap. Over time this shows which Function most needs investment — evidence a Target Profile can act on.
:::

## The Core: Functions → Categories → Subcategories

Each Function breaks down into **Categories**, and each Category into **Subcategories** — the specific, outcome-based statements you actually assess against.

```text
FUNCTION      Protect (PR)
  CATEGORY      Identity Management, Authentication and Access Control (PR.AA)
    SUBCATEGORY   PR.AA-01: Identities and credentials are managed
    SUBCATEGORY   PR.AA-03: Users and devices are authenticated ...
```

- A **Function** is the broadest grouping (Protect).
- A **Category** is a group of related outcomes within it (access control).
- A **Subcategory** is a single, specific outcome you can assess as "are we achieving this, and how well?"

CSF also points to **Informative References** — mappings that show which controls in other standards (ISO/IEC 27001, NIST SP 800-53) help achieve each Subcategory. This is how CSF connects to the certifiable control sets in later modules: CSF frames the outcome, 27001/800-53 supply the controls, and your risk register sets the priority.

## Profiles: from framework to plan

A **Profile** is how you apply the Core to *your* organisation. You create two:

- **Current Profile** — the outcomes you achieve *today*. Honestly assess each relevant Subcategory (or Category, to start) and record your current state.
- **Target Profile** — the outcomes you *want* to achieve, driven by your business objectives, risk appetite and obligations.

The difference between them is a **gap analysis**, and the prioritised gaps become your improvement roadmap:

:::diagram From current to target profile via a prioritised roadmap
```text
  CURRENT PROFILE      ──gap analysis──►      TARGET PROFILE
  where we are now                            where we need to be
         │                                           │
         └──────────► prioritised roadmap ◄──────────┘
             (ranked by risk + appetite + cost)
```
:::

Crucially, you do **not** aim for the maximum on everything. The Target Profile is shaped by risk: a small retailer may target strong Protect and Recover but accept modest Detect maturity, because that matches its risk appetite and budget. This is exactly where the risk register from the previous module feeds in — it tells you *which* gaps matter most.

:::scenario A one-year Target Profile
A mid-size company assesses its Current Profile and finds Detect is its weakest Function: no central log collection, alerts reviewed ad hoc. Its risk register shows ransomware as a critical risk, and early detection is the cheapest way to cut that risk's impact. So its Target Profile raises Detect first — central logging and a defined monitoring process within a year — while holding Protect steady. The framework structured the assessment; the register set the priority; the Profile turned both into a plan the board approved.
:::

## Implementation Tiers

**Tiers** describe how rigorous and adaptive your cybersecurity *risk management practices* are, from Tier 1 to Tier 4:

| Tier | Name | Characteristic |
|---|---|---|
| 1 | Partial | Ad hoc, reactive; risk managed inconsistently |
| 2 | Risk Informed | Risk practices approved but not organisation-wide |
| 3 | Repeatable | Formal policies, consistently applied and updated |
| 4 | Adaptive | Continuous improvement; risk-informed culture; adapts to change |

Two cautions:

1. **Tiers are not maturity grades to maximise.** They describe how you manage risk, and the right Tier depends on your risk and resources. A small firm operating well at Tier 2 may be making a sound choice; forcing Tier 4 everywhere can waste money.
2. **Tiers describe the *practice*, Profiles describe the *outcomes*.** A board sentence often combines both: "We are Tier 2 today with weak Detect outcomes; our target is Tier 3 with Detect raised, within a year."

## Common mistakes

- **Treating CSF as a pass/fail checklist.** It is a structure for assessment and communication, not a certification.
- **Targeting maximum everywhere.** Without a risk-driven Target Profile, you over-invest in low-risk areas.
- **Skipping the Current Profile.** You cannot plan improvement without an honest baseline.
- **Confusing Tiers with Profiles.** Tiers = how you manage risk; Profiles = which outcomes you achieve.
- **Ignoring Govern.** Doing Identify–Recover with no strategy, roles or risk appetite is the gap CSF 2.0 was redesigned to close.

## Security implications

- Use CSF to make sure no Function is neglected, then use your risk register to decide the order of work.
- Build both Current and Target Profiles; the gap between them, ranked by risk, is your roadmap.
- Set Target Profiles and Tiers to your risk appetite — higher is not automatically better.
- Lean on Informative References to connect CSF outcomes to the specific controls you will implement and later evidence.

## Knowledge check

:::check Questions
1. Name the six CSF 2.0 Functions.
2. Which Function is new in 2.0, and why was it added?
3. What is the relationship between a Current Profile, a Target Profile and a gap analysis?
4. Do Implementation Tiers measure the outcomes you achieve, or how you manage risk?
:::

:::solution Answers
1. **Govern, Identify, Protect, Detect, Respond, Recover.**
2. **Govern** is new. It was added because the other five Functions depend on strategy, roles, oversight and risk-appetite decisions (and supply-chain governance) that were previously scattered and under-emphasised; 2.0 makes governance an explicit, central Function.
3. The **Current Profile** is where you are now; the **Target Profile** is where you want to be; the **gap analysis** is the difference between them, which, prioritised by risk, becomes the improvement roadmap.
4. **How you manage risk** (your risk-management practices), from Partial (1) to Adaptive (4). Outcomes are described by Profiles, not Tiers.
:::

## Exercise

For an organisation of your choice:

1. For each of the six Functions, write one sentence describing a plausible **Current** state.
2. Choose the **two** Functions you would prioritise raising first, and justify the choice using risk (reference the kind of reasoning from your risk register).
3. State a realistic **Target Tier** for the organisation and explain why a higher Tier would not necessarily be better.

:::solution Model answer (abbreviated)
1. e.g. *Govern:* "A security policy exists but risk appetite is unwritten." *Identify:* "Partial asset inventory; annual risk assessment." *Protect:* "MFA on email; patching inconsistent." *Detect:* "No central logging; alerts ad hoc." *Respond:* "An IR plan exists but is untested." *Recover:* "Backups exist but restoration is never rehearsed."
2. Prioritise **Detect** and **Recover**: the register rates ransomware as critical, early detection cuts its impact cheaply, and rehearsed recovery limits downtime — together they reduce the highest residual risk most cost-effectively.
3. **Target Tier 3** (Repeatable): formal, consistently applied practices are appropriate for the size and risk. Tier 4 (Adaptive) would demand continuous-improvement machinery whose cost is not justified by the organisation's risk appetite, so "higher" would mean over-spending rather than better security.
:::

## Summary and next steps

- CSF 2.0 organises cybersecurity into six Functions — **Govern, Identify, Protect, Detect, Respond, Recover** — with Govern wrapping the rest.
- The Core descends from Functions to Categories to assessable Subcategories, linked by Informative References to concrete controls.
- Current and Target Profiles plus a risk-ranked gap analysis turn the framework into a prioritised roadmap.
- Implementation Tiers describe how you manage risk; set them, and your targets, to your risk appetite.

The next module, **ISO/IEC 27001**, moves from an outcome framework to a certifiable management system, and shows how the controls that satisfy CSF outcomes are selected, documented in a Statement of Applicability, and audited.

===assignment===
Produce a one-page **CSF 2.0 Profile summary** for an organisation of your choice: a short Current Profile statement for each of the six Functions, a Target Profile that raises two Functions (justified by risk), a chosen Current and Target Implementation Tier with a one-paragraph rationale, and a three-item prioritised roadmap derived from your gaps. Reference at least two entries from your risk register to justify the priorities.

===criteria===
- Complete the reading, the knowledge check and the exercise.
- Your Target Profile must be risk-driven (not "maximum everywhere") and your roadmap must trace back to specific gaps and risks.
- Mark the lesson complete. The module quiz draws on this lesson.

===references===
- NIST, *The NIST Cybersecurity Framework (CSF) 2.0* (NIST CSWP 29, 2024) — https://doi.org/10.6028/NIST.CSWP.29
- NIST, *Cybersecurity Framework* home and Quick-Start Guides — https://www.nist.gov/cyberframework
- NIST, *CSF 2.0 Reference Tool and Informative References* — https://csrc.nist.gov/projects/cybersecurity-framework/filters
- NIST SP 800-53 Rev. 5, *Security and Privacy Controls* (a common Informative Reference) — https://csrc.nist.gov/pubs/sp/800/53/r5/upd1/final
