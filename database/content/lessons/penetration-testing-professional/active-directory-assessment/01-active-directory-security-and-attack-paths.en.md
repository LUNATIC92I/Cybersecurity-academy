---
title: Active Directory Security and Attack Paths
chapter: Understanding and hardening Active Directory
summary: Understand how Active Directory is structured, why it so often becomes an attacker's path to domain-wide control, and — conceptually and defensively — the misconfiguration classes that create attack paths and how to harden and monitor against them.
duration: 55
level: intermediate
preview: true
objectives:
- Describe Active Directory's core components and why it is a high-value target
- Explain, conceptually, how authentication (Kerberos/NTLM) works in AD
- Recognise the misconfiguration classes that create attack paths
- Explain attack-path thinking and why one weak link can reach domain admin
- Apply tiering, least privilege and monitoring to harden and detect
prerequisites:
- Linux and Technical Foundations for Testers
---

## Introduction

**Active Directory (AD)** is the identity and access backbone of most enterprise Windows environments — it decides who can log in, to what, and with which privileges across thousands of machines. That makes it the single most consequential system an attacker can target: compromise AD and you often compromise the whole organisation. For exactly that reason, understanding AD is essential to both assessing and defending it.

This lesson is conceptual and defensive. It explains how AD works, why it becomes an attack path, and the *classes* of misconfiguration that create those paths — so you can **find and fix them**. It deliberately does not provide attack tooling, commands, or step-by-step exploitation; any hands-on AD testing belongs only in an authorised, isolated lab, within the rules of engagement from the first module. The goal here is the understanding a defender and an assessor both need.

## What Active Directory is

AD is a **directory service**: a central database of an organisation's identities and resources, with the rules governing access. Its core objects and structure:

| Concept | What it is |
|---|---|
| **Domain** | A boundary of administration and a shared identity database |
| **Domain Controller (DC)** | A server running AD; holds the directory and authenticates users — the crown jewel |
| **Users / computers / groups** | The principals (who) and the objects they act on (what) |
| **Organisational Units (OUs)** | Containers for organising and delegating management |
| **Group Policy (GPO)** | Centralised configuration pushed to users and machines |
| **Privileged groups** | e.g. Domain Admins — membership effectively controls the domain |
| **Trusts** | Relationships letting one domain's users access another |

Because the **Domain Controller** authenticates everyone and holds every credential's verifier, control of a DC is control of the domain. Much of AD security is ultimately about protecting the DCs and the small number of accounts and paths that can reach them.

## How authentication works (conceptually)

AD principally uses **Kerberos** (with legacy **NTLM** still present). You do not need the cryptographic detail to understand the security model:

- When a user logs in, they obtain a **ticket-granting ticket (TGT)** from the DC that proves who they are.
- To use a service, they present the TGT and get a **service ticket** for that specific service.
- Services and accounts are identified by **Service Principal Names (SPNs)**.

The security-relevant consequences — the ones a defender cares about — are:

- **Credentials and tickets live in memory** on machines users log in to. A machine an attacker controls can expose the secrets of anyone who logged in there — which is why *where privileged accounts log in* matters enormously.
- **NTLM and weak Kerberos configurations** are older, weaker mechanisms; their presence is a hardening gap.
- **Service accounts with SPNs** can have their service tickets requested by any user, so a weak service-account password is exposed to offline guessing — a well-known class of weakness that strong, long passwords (or group-managed service accounts) defend against.

You are learning these not to perform them but to recognise the weaknesses they imply and close them.

## Misconfiguration classes that create attack paths

AD is rarely broken by a software exploit; it is usually broken by **misconfiguration and excessive privilege** accumulated over years. The recurring classes (described as risks to find and fix, not procedures):

- **Excessive privilege** — far too many accounts in Domain Admins or other high-privilege groups; admins using privileged accounts for everyday work; service accounts granted domain admin "to make it work".
- **Credential exposure** — privileged accounts logging in to ordinary workstations, leaving their credentials in memory on machines a normal-user compromise can reach.
- **Weak service-account passwords** — SPN-bearing accounts with short, old, or guessable passwords, exposed to offline cracking.
- **Dangerous delegation** — accounts or computers configured with Kerberos delegation that lets them impersonate others, often far more broadly than intended.
- **Insecure ACLs / permissions** — one object able to modify another (reset a password, add a member, edit a GPO) in chains that eventually reach a privileged object.
- **Legacy protocols and unpatched DCs** — NTLM, SMBv1, or missing critical patches on domain controllers.
- **GPO weaknesses** — a widely-linked Group Policy that a lower-privileged account can edit becomes a path to mass compromise.

Each of these is something an **assessor inventories and a defender remediates** — and each maps to a concrete hardening action below.

## Attack-path thinking

The defining idea of AD security is the **attack path**: AD is a graph of *who can control whom*, and an attacker only needs a chain of small, individually-unremarkable privileges from their foothold to a privileged object.

```text
 Normal user  ──(can reset password of)──►  Service account
      │                                           │
      │                                  (is local admin on)
      ▼                                           ▼
 Workstation  ◄──(Domain Admin logged in here)──  Server
      │                                           │
      └──────────► credential in memory ──────────┘
                              │
                              ▼
                        Domain Admin  ──►  Domain Controller
```

No single link is dramatic; the *chain* is. This is why "we have no critical AD vulnerabilities" can be false while the environment is trivially ownable — the weakness is the **accumulated graph of privilege**, not a CVE. Defenders and assessors increasingly analyse AD as exactly this graph: find the shortest paths from any ordinary user to Domain Admin and cut them. The defensive goal is to make those paths long, few, and monitored.

:::tip Think in paths, not just permissions
A single over-broad permission looks harmless in isolation. Its danger is whether it *lies on a path* to something privileged. The most valuable AD security work is mapping and shortening those paths — removing the links (excessive memberships, risky ACLs, privileged logins in the wrong places) that connect an ordinary foothold to domain control. This is the AD-specific form of the least-privilege principle from the Linux foundations lesson.
:::

## Hardening and monitoring

Each misconfiguration class has a defensive answer. The core hardening measures:

- **Tiering (privileged access model)** — separate administration into tiers so that DC/domain admin credentials are *only ever used on* secured admin workstations and never on ordinary endpoints. This directly breaks the "credential in memory on a normal workstation" link.
- **Least privilege** — shrink Domain Admins to the few that truly need it; give service accounts only the rights they require; remove standing privilege where just-in-time access can replace it.
- **Protect service accounts** — long, random passwords or group-managed service accounts (so there is no weak password to crack), and minimal privileges.
- **Review ACLs and delegation** — audit who can modify privileged objects and GPOs; remove risky delegation; treat the ability to edit a widely-linked GPO as privileged.
- **Retire legacy protocols** — disable NTLM and SMBv1 where possible, enforce strong Kerberos settings, and keep DCs patched.
- **Monitor** — feed AD security events into the SIEM (the SOC path's territory): privileged group changes, unusual ticket requests, authentication anomalies, DC logon patterns, and changes to critical objects. Many AD attack steps are loud in the logs if you are watching.

:::warning Protect and monitor the domain controllers above all
Because a DC holds every credential verifier and authenticates the domain, DC compromise is effectively game over. Harden DCs hardest (patching, minimal software, tiered admin access only), restrict who can log on to them, and monitor them most closely. The single most important AD defensive question is: *who can reach a DC or a Domain Admin, and by what path?*
:::

## Common mistakes

- **Treating AD security as patching.** Most AD compromise is misconfiguration and excessive privilege, not a missing patch; harden the configuration and the privilege graph.
- **Permission-by-permission thinking.** A permission's risk is whether it sits on a path to privilege; analyse paths, not isolated grants.
- **Privileged logins everywhere.** Domain Admins logging on to ordinary workstations scatter their credentials where attackers can reach them — tiering exists to stop this.
- **"Make it work" service accounts.** Granting a service account domain admin is a standing path to full compromise; give least privilege and strong credentials.
- **Not monitoring AD.** Many attack-path steps are visible in security logs; feeding AD telemetry to the SOC is essential.

## Security implications

- The Domain Controller and privileged accounts are the crown jewels; protecting the paths to them is the heart of AD security.
- Analyse AD as a privilege graph and shorten/cut the paths from ordinary users to Domain Admin.
- Apply tiering and least privilege so privileged credentials never land where an ordinary compromise can reach them.
- Protect service accounts, review ACLs/delegation, retire legacy protocols, and monitor AD events in the SOC.

## Knowledge check

:::check Questions
1. Why is a Domain Controller the most consequential system to protect in an AD environment?
2. What is an "attack path" in Active Directory, and why can an environment with no critical CVEs still be easily compromised?
3. Name three misconfiguration classes that create AD attack paths.
4. How does the tiered privileged-access model break a common attack path?
:::

:::solution Answers
1. Because the DC runs AD, authenticates every principal, and holds every credential's verifier — so controlling a DC means controlling the whole domain (effectively total compromise). Protecting DCs and the paths to them is therefore central.
2. An **attack path** is a chain of individually small privileges (who can control whom) leading from an attacker's foothold to a privileged object like Domain Admin. An environment can have no critical software vulnerabilities yet still be compromised because the weakness is the **accumulated graph of excessive privilege and misconfiguration**, not a CVE.
3. Any three of: excessive privilege (bloated admin groups, admin-for-everyday-use); privileged credential exposure (privileged logins on ordinary machines); weak service-account passwords (SPN accounts); dangerous Kerberos delegation; insecure ACLs/permissions chains; legacy protocols/unpatched DCs; editable widely-linked GPOs.
4. **Tiering** confines privileged (DC/Domain Admin) credentials to secured admin workstations and forbids their use on ordinary endpoints — so an attacker who compromises a normal workstation never finds privileged credentials in its memory, breaking the "credential in memory → domain admin" link.
:::

## Exercise

For a Windows domain you are authorised to review (conceptually — no live testing):

1. List **four misconfiguration classes** you would look for and, for each, why it creates an attack path.
2. Sketch a plausible **attack path** (as a who-can-control-whom chain) from an ordinary user to Domain Admin, labelling each link with the misconfiguration that enables it.
3. For each link in your path, give the **hardening measure** that would cut it.
4. Name **three AD events** you would forward to the SOC and what each would help detect.

:::solution Model answer (abbreviated)
1. Excessive Domain Admin membership (too many standing paths to full control); privileged logins on ordinary workstations (credential exposure in memory); weak SPN service-account passwords (offline cracking exposure); risky GPO edit rights (mass configuration compromise).
2. *Path:* ordinary user → can edit a widely-linked GPO (GPO weakness) → the GPO configures a server → a Domain Admin logs on to that server (privileged credential exposure) → their credential is in memory → Domain Admin → DC. Each arrow is a misconfiguration.
3. Restrict GPO edit rights to privileged, audited admins; enforce tiering so Domain Admins never log on to non-tier-0 servers; shrink Domain Admins and use just-in-time access; harden/monitor the DC.
4. Changes to privileged group membership (privilege escalation/persistence); anomalous Kerberos service-ticket requests (service-account attacks); DC logon and critical-object changes (DC/tier-0 compromise).
:::

## Summary and next steps

- Active Directory is the identity backbone and the highest-value target; the **Domain Controller** and privileged accounts are the crown jewels.
- AD is compromised mostly through **misconfiguration and excessive privilege**, best understood as an **attack-path graph** — a chain of small privileges reaching domain control.
- Defend by **tiering** and **least privilege** (keep privileged credentials off ordinary machines), protecting service accounts, reviewing ACLs/delegation, retiring legacy protocols, and **monitoring** AD in the SOC.
- Hands-on AD testing is performed only in authorised, isolated labs within the rules of engagement; this lesson gives the understanding to assess and, above all, defend.

The remaining Pentest modules (network enumeration, exploitation and privilege-escalation techniques) are operational and are practised only in isolated labs under explicit authorisation; this lesson equips you to understand and harden the environment they concern.

===assignment===
Produce a one-page **Active Directory hardening and monitoring brief** for an organisation of your choice: the misconfiguration classes you would audit for, a sketched attack-path graph (ordinary user → Domain Admin) with each link's enabling misconfiguration and the hardening measure that cuts it, a short section applying tiering and least privilege, and a list of AD security events you would forward to the SOC with what each detects. Keep it entirely defensive — no attack tooling or commands.

===criteria===
- Complete the reading and the knowledge check, and produce the brief.
- The brief must analyse AD as attack paths (not isolated permissions), pair each misconfiguration with a concrete hardening measure, apply tiering/least privilege, and include SOC monitoring; content stays conceptual and defensive (no exploitation steps or tooling).
- Mark the lesson complete. The module quiz draws on this lesson.

===references===
- Microsoft, *Securing privileged access / the enterprise access model (tiering)* — https://learn.microsoft.com/security/privileged-access-workstations/overview
- Microsoft, *Active Directory security best practices* — https://learn.microsoft.com/windows-server/identity/ad-ds/plan/security-best-practices/best-practices-for-securing-active-directory
- MITRE ATT&CK, Credential Access and Lateral Movement tactics — https://attack.mitre.org/tactics/TA0006/
- CISA, guidance on identity and Active Directory hardening — https://www.cisa.gov/
- NIST SP 800-63 / privileged access management concepts — https://pages.nist.gov/800-63-3/
