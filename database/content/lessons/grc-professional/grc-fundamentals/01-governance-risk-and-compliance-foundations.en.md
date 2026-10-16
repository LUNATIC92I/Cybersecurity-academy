---
title: Governance, Risk and Compliance Foundations
chapter: GRC Fundamentals
summary: Understand what governance, risk and compliance each mean, how they reinforce one another, and the documents, roles and accountability structures that turn security from a set of tools into a managed business function.
duration: 50
level: beginner
preview: true
objectives:
- Define governance, risk and compliance and explain how the three disciplines connect
- Distinguish policies, standards, procedures and guidelines and know when to use each
- Explain risk appetite and risk tolerance and why leadership must set them
- Describe the Three Lines Model and how it separates ownership, oversight and assurance
- Identify the main security governance roles and what each is accountable for
prerequisites:
- None — this is the first lesson of the GRC path
---

## Introduction

Most organisations can buy firewalls, endpoint protection and a SIEM. Far fewer can answer a simpler question: *who decided those were the right investments, who is accountable when they fail, and how do we prove to a customer or regulator that they work?* Answering that question is the job of **Governance, Risk and Compliance** — GRC.

GRC is not a product and not a department you can bolt on. It is the way an organisation directs its security, decides how much risk it is willing to carry, and demonstrates — with evidence — that it does what it says. This lesson gives you the vocabulary and the mental model that the rest of the path builds on.

## The three disciplines

| Discipline | Question it answers | Example output |
|---|---|---|
| **Governance** | Who decides, who is accountable, and what are the rules? | A board-approved information security policy; defined roles |
| **Risk** | What could go wrong, how bad would it be, and what will we do about it? | A risk register with treatment plans |
| **Compliance** | Are we meeting our obligations, and can we prove it? | Evidence that controls operate; audit reports |

They are deliberately interlocking:

```text
        GOVERNANCE
   sets direction, roles,
     risk appetite
          │
          ▼
         RISK  ──────────► identifies and treats what matters
          │
          ▼
      COMPLIANCE ────────► proves obligations and controls are met
          │
          └───── feedback ─────► informs governance decisions
```

Governance sets the direction and how much risk is acceptable. Risk management works within that direction to find and treat what matters. Compliance demonstrates that the resulting controls and legal obligations are actually met, and feeds the results back so governance can adjust. Remove any one and the others weaken: risk work with no governance has no mandate; compliance with no risk work becomes box-ticking.

:::note GRC is a business function, not just IT
A common mistake is to treat GRC as "the security team's paperwork". In mature organisations, GRC decisions are made by business leaders who own the risk, informed by security experts. Security advises; the business decides and is accountable.
:::

## Governance: direction and accountability

Governance is the system by which an organisation is directed and controlled. For information security it establishes:

- **Direction** — a security strategy aligned with business objectives.
- **Roles and accountability** — who owns what, and who answers for failures.
- **Rules** — the policies and standards everyone must follow.
- **Oversight** — how leadership monitors whether security is working.

### The documentation hierarchy

Governance is written down in a deliberate hierarchy. Confusing these four document types is one of the most common beginner errors.

| Document | What it is | Mandatory? | Example |
|---|---|---|---|
| **Policy** | High-level statement of intent and rules, approved by leadership | Yes | "All access to company systems requires multi-factor authentication." |
| **Standard** | Specific, measurable requirement that supports a policy | Yes | "MFA must use a phishing-resistant method; SMS is not permitted." |
| **Procedure** | Step-by-step instructions to carry out a task | Yes (for the task) | "How to enrol a new employee in MFA" |
| **Guideline** | Recommended, non-mandatory good practice | No | "Consider using a hardware key for administrators." |

A useful way to remember the difference: a **policy** says *what and why*, a **standard** says *how much / to what level*, a **procedure** says *exactly how, step by step*, and a **guideline** says *what we suggest*.

```text
POLICY      (why + what, mandatory, approved by leadership)
   └── STANDARD    (measurable requirement, mandatory)
          └── PROCEDURE  (step-by-step, mandatory for the task)

GUIDELINE   (recommended, optional — supports but does not bind)
```

:::tip Write policies to last
Good policies are technology-neutral so they survive tool changes. "Access requires strong authentication" is a durable policy; "Everyone must use the Acme Authenticator app v3" belongs in a standard or procedure, which you can update without a board sign-off.
:::

## Risk appetite and risk tolerance

Leadership cannot eliminate all risk — it would be infinitely expensive and would stop the business operating. Instead they decide **how much risk is acceptable**. Two linked terms express this:

- **Risk appetite** — the broad amount and type of risk the organisation is *willing to pursue or accept* in pursuit of its objectives. It is strategic and set at board level. Example: *"We have a low appetite for risks to customer data and a moderate appetite for risks that slow product delivery."*
- **Risk tolerance** — the *acceptable variation* around a specific objective or risk; it is more concrete and measurable. Example: *"No single unresolved critical vulnerability on an internet-facing system may remain open for more than 7 days."*

Appetite is the philosophy; tolerance is the measurable line. Both must come from leadership, because only leadership is accountable for the consequences of accepting risk. The risk team's job is to make risks visible and to help leaders decide — not to decide on their behalf.

:::scenario Appetite in action
A payments company states a **very low appetite** for confidentiality risk to cardholder data, and a **higher appetite** for availability risk on its internal wiki. This single sentence tells the security team where to spend first: strong, redundant controls around card data; "good enough" controls on the wiki. Without a stated appetite, teams either gold-plate everything (too expensive) or protect everything equally (the wrong things get the same attention as the crown jewels).
:::

## Accountability: the Three Lines Model

How do you stop governance becoming either a free-for-all or a bureaucratic knot? The **Three Lines Model** (from the Institute of Internal Auditors) separates three distinct kinds of responsibility so they do not conflict.

| Line | Who | Role |
|---|---|---|
| **First line** | Operational management and staff who own and run the risk (e.g. IT, developers) | Own and manage risk day to day; implement controls |
| **Second line** | Risk, compliance and security functions | Set policy, advise, challenge and monitor the first line |
| **Third line** | Internal audit | Provide independent assurance to the board that the first two lines work |

The key principle is **independence of assurance**. The people who run a control (first line) should not be the same people who independently verify it works (third line) — otherwise "we checked our own homework and it's fine" is the only possible answer. The second line sets the rules and watches; the third line, independent of both, tells the governing body the truth.

:::note Accountability vs responsibility
You will hear both words. **Responsibility** can be shared and delegated — many people are responsible for patching. **Accountability** cannot be delegated — one named person answers for whether patching happens. Good governance assigns a single accountable owner to each important outcome.
:::

## Key security governance roles

Titles vary between organisations, but the accountabilities are consistent:

- **Board / executive leadership** — ultimately accountable for security; approve strategy, policy and risk appetite; provide funding.
- **CISO (Chief Information Security Officer)** — leads the security programme; translates business risk into a security strategy; reports risk to leadership.
- **Risk owner** — the business leader accountable for a specific risk and for deciding how it is treated.
- **Control owner** — accountable that a specific control is designed and operates effectively.
- **Data owner** — accountable for a set of data: its classification, who may access it, and how long it is kept.
- **Data custodian / processor** — implements the data owner's decisions in systems.
- **All staff** — responsible for following policy and reporting incidents.

A recurring theme: **the person accountable for a risk is a business leader, not the security team.** Security enables good decisions; it does not absorb accountability for risks the business chooses to run.

## How compliance fits

Compliance means meeting obligations — and being able to **prove** it. Obligations come from three sources:

1. **Legal and regulatory** — laws and regulations that apply to you (data-protection law, sector rules).
2. **Contractual** — commitments to customers and partners (security clauses, service levels).
3. **Internal** — your own policies and standards.

Compliance is evidence-driven. "We require MFA" is a claim; the sign-in logs, configuration export and an access review signed by the control owner are the **evidence** that turns the claim into something an auditor — or a customer — can trust. A large part of later modules is producing and organising that evidence.

:::warning Compliant is not the same as secure
Meeting a standard is a floor, not a ceiling. An organisation can pass an audit and still be breached, because standards lag behind threats and auditors sample rather than test everything. Treat compliance as evidence that baseline controls exist — then use risk management to decide where you must go further.
:::

## Common mistakes

- **Policy sprawl.** Dozens of overlapping documents nobody reads. Fewer, clearer, maintained documents beat a large unread library.
- **Letting security "own" risk.** Risks belong to the business leaders who can accept or fund them; parking them with the security team removes real accountability.
- **Confusing document types.** Putting step-by-step instructions in a board-level policy means every small change needs executive sign-off.
- **Compliance theatre.** Collecting evidence that controls exist on paper while they do not operate in practice.

## Security implications

- Every important security outcome should have one named, accountable owner.
- Risk appetite must be stated by leadership, or teams will guess — and guess inconsistently.
- Keep assurance independent: don't let the team that runs a control be its only reviewer.
- Design controls so they *produce evidence* as a by-product, not as a once-a-year scramble.

## Knowledge check

:::check Questions
1. Which document type is mandatory, measurable, and supports a policy — a standard, a procedure or a guideline?
2. Who should set an organisation's risk appetite, and why?
3. In the Three Lines Model, which line provides *independent* assurance to the board?
4. Give one reason an organisation can be fully compliant yet still insecure.
:::

:::solution Answers
1. A **standard** — it is a mandatory, measurable requirement that supports a policy. (A procedure is step-by-step; a guideline is optional.)
2. **Leadership / the board**, because only they are accountable for the consequences of accepting risk and can allocate the funding to treat it.
3. The **third line — internal audit**, which is independent of both the teams that run controls (first line) and the functions that set policy and monitor (second line).
4. Standards are a baseline and lag behind threats; auditors sample rather than test everything; and a control can exist on paper without operating effectively. (Any one is sufficient.)
:::

## Exercise

A fast-growing startup has no written security governance. The founders ask you to propose the *first four* governance artefacts to create.

1. Name the four documents or decisions you would create first and say which GRC discipline (governance, risk or compliance) each primarily serves.
2. For the risk appetite statement, draft one sentence appropriate for a company whose main asset is customer data.
3. Explain, in two sentences, who should approve the top-level information security policy and why.

:::solution Model answer
1. (a) A short **information security policy** approved by the founders — *governance*; (b) a **risk appetite statement** — *governance/risk*; (c) a lightweight **risk register** to capture the top risks — *risk*; (d) a list of **obligations** (laws, key customer contract clauses) they must meet — *compliance*. A reasonable alternative first set is acceptable if justified.
2. "We have a very low appetite for risks that could expose or lose customer data, and will prioritise investment in protecting its confidentiality and integrity above features that increase that exposure."
3. The **founders / board** should approve it, because they are ultimately accountable for security and the policy sets the mandate and risk appetite that everything else derives from; approval by leadership is what gives the policy authority across the organisation.
:::

## Summary and next steps

- GRC turns security into a managed function: governance directs, risk management prioritises, compliance proves.
- Policies, standards, procedures and guidelines form a hierarchy — use the right one for the right purpose.
- Leadership sets risk appetite and tolerance; the business, not security, owns risk.
- The Three Lines Model keeps assurance independent, and every key outcome needs one accountable owner.

The next module, **Information Security Risk Management**, takes the risk discipline introduced here and turns it into a repeatable process: identifying, analysing, evaluating and treating risk, and recording it in a defensible risk register.

===assignment===
Choose a real or imagined small organisation (for example, a 30-person online retailer). Produce a one-page **governance starter pack** containing: (1) a three-sentence information security policy statement, (2) a risk appetite statement of one or two sentences, (3) a table assigning an **accountable owner** to four important outcomes (e.g. access control, backups, incident response, supplier security), and (4) a short note mapping each of your four items to governance, risk or compliance.

===criteria===
- Complete the reading, the knowledge check and the exercise.
- Your assignment must clearly distinguish an accountable owner from those merely responsible, and must state a risk appetite in business terms.
- Mark the lesson complete. The GRC Fundamentals module quiz draws on this lesson.

===references===
- ISO/IEC 27000:2018, *Information security management systems — Overview and vocabulary* — https://www.iso.org/standard/73906.html
- ISO/IEC 27001:2022, *Information security management systems — Requirements* — https://www.iso.org/standard/27001
- The Institute of Internal Auditors, *The IIA's Three Lines Model (2020)* — https://www.theiia.org/en/content/position-papers/2020/the-iias-three-lines-model-an-update-of-the-three-lines-of-defense/
- NIST, *Cybersecurity Framework 2.0* (the Govern function) — https://www.nist.gov/cyberframework
- ISACA, *Risk appetite and risk tolerance* (glossary and guidance) — https://www.isaca.org/resources/glossary
