---
title: Linux and Technical Foundations for Testers
chapter: The systems you will be testing
summary: Build the systems literacy a penetration tester relies on — the Linux filesystem and shell, users, permissions and privilege, processes and services, and networking basics — understood as the model that explains both how systems are secured and where weaknesses come from.
duration: 55
level: beginner
preview: true
objectives:
- Navigate the Linux filesystem hierarchy and work in the shell
- Explain the Linux user, group and permission model, including root and sudo
- Describe how processes, services and scheduled jobs run and are controlled
- Connect these foundations to common misconfiguration weaknesses, defensively
- Know where configuration and logs live so you can assess and harden a system
prerequisites:
- Authorization, Scope and Rules of Engagement
---

## Introduction

Before you can assess a system you must understand it. The large majority of servers, containers and cloud workloads a tester meets run Linux, and nearly every weakness class from later modules — weak permissions, exposed services, misconfiguration, credential exposure — is a *consequence of how these fundamentals are (mis)configured*. This lesson builds that systems literacy conceptually. It is not an attack tutorial; it is the model that explains both how a system is meant to be secured and where that security breaks down, so you can assess it and recommend fixes.

If you have taken the SOC path's Linux logs lesson, this is the complementary builder's-eye view: there you read the evidence; here you understand the system that produces it.

## The filesystem and the shell

Linux organises everything under a single root (`/`) in a standard hierarchy. The locations that matter to a tester or defender:

| Path | Contains |
|---|---|
| `/etc` | System and service **configuration** (and `/etc/passwd`, `/etc/shadow` for accounts) |
| `/home` | User home directories (and their dotfiles, keys, histories) |
| `/var/log` | **Logs** (the SOC path's territory) |
| `/var/www`, `/srv` | Web content and served data |
| `/usr/bin`, `/usr/sbin` | Installed programs |
| `/tmp`, `/var/tmp` | World-writable temporary space (a classic source of misconfiguration) |
| `/proc`, `/sys` | Virtual views of running kernel and process state |

Work is done through the **shell** (commonly `bash`): a text interface that runs commands, chains them with pipes, and is scriptable. You do not need to memorise commands today; you need the mental map — *configuration lives in `/etc`, evidence in `/var/log`, and user secrets in home directories* — because that map tells an assessor where to look and a defender what to protect.

:::note "Everything is a file"
Linux represents devices, processes and even kernel settings as file-like objects (notably under `/proc` and `/sys`). This uniformity is powerful and is why permissions (below) are so central: controlling access to files is, to a large degree, controlling access to the whole system.
:::

## Users, groups and permissions

This is the heart of Linux security. Every file and process has an **owner** (a user) and a **group**, and every file carries permissions for three classes — **owner, group, other** — across three actions — **read (r), write (w), execute (x)**.

```text
   -rwxr-x---   1  deploy  developers   ...  app.sh
    │└┬┘└┬┘└┬┘      │       │
    │ │  │  └ other: no access       owner = deploy
    │ │  └── group: read, execute    group = developers
    │ └───── owner: read, write, execute
    └─ file type (- = regular file, d = directory)
```

- **The root account** (user id 0) is all-powerful: it bypasses permission checks. Protecting root is therefore the central concern.
- **`sudo`** lets specified users run specific commands as root, with each use logged (the audit trail the SOC path reads). It exists so people do *not* log in as root directly — least privilege applied to administration.
- **Special permission bits** such as **SUID/SGID** let a program run with its owner's privileges rather than the caller's. Legitimately used by a few system tools, but a misconfigured SUID-root program is a classic privilege-escalation weakness — which is exactly why an assessor inventories them and a defender keeps them to a minimum.

The security principle running through all of this is **least privilege**: accounts, files and programs should have the minimum access needed. Most privilege-related weaknesses are simply violations of it — an over-permissive file, an account with needless sudo rights, a service running as root that need not be.

:::tip Permissions are where "who can do what" is really decided
When later modules talk about privilege escalation, they almost always mean exploiting a *permission or privilege misconfiguration* — a writable file a root process trusts, an over-privileged account, an unnecessary SUID binary. Understand the permission model and you understand both the weakness and its fix: tighten ownership and rights to least privilege.
:::

## Processes, services and scheduled jobs

A running program is a **process**, each with its own id (PID) and the privileges of the user that started it. Testers and defenders care about *what is running and as whom*:

- **Services (daemons)** are long-running background processes — a web server, a database, SSH. On modern Linux they are managed by **systemd** (`systemctl`), which also controls whether they start at boot. A service running as root, or exposed to the network when it need not be, is an expanded attack surface.
- **Scheduled jobs** run work automatically: **cron** and **systemd timers**. These are both a legitimate admin tool and a classic persistence location (as you saw defensively in the SOC Linux-logs lesson) — so an assessor reviews them and a defender monitors them.
- **The principle:** every running service and scheduled job is attack surface and should exist for a reason, run with least privilege, and be known. "What is running, as whom, and why?" is a question that finds real weaknesses.

## Networking basics on the host

A tester must connect the host to the network view from the SOC path:

- **Listening services** — each network service binds to a **port** on an interface. A host exposing more ports than it needs is a larger attack surface; knowing what *should* listen is the baseline for spotting what should not.
- **Interfaces and addresses** — a host may sit on several networks (public, internal, management); where it is reachable *from* determines its real exposure (the risk-vs-severity idea from the vulnerability-assessment lesson).
- **Host firewall** — Linux can filter its own traffic (e.g. nftables/iptables); a correctly configured host firewall reduces exposure even if a service is misconfigured — defence in depth.

The defensive takeaway is consistent: **minimise listening services, know where the host is reachable from, and filter what you cannot remove.**

## Where configuration and logs live (for assessment and hardening)

Tying it together, the assessor's and defender's map of a Linux host:

```text
 Accounts & auth   /etc/passwd, /etc/shadow, /etc/sudoers, ~/.ssh/
 Service config    /etc/<service>/...            (how daemons are set up)
 What's running    systemctl / ps                (services and processes)
 Scheduled work    /etc/cron*, systemctl timers  (automation & persistence)
 Logs & evidence   /var/log, journalctl          (the SOC path)
 Permissions       ownership + rwx + SUID/SGID   (who can do what)
```

An assessment reads this map to find weaknesses (over-permissive files, needless services, risky sudo rights); hardening uses the same map to fix them (tighten permissions, disable unused services, restrict sudo, patch, and ship logs to the SOC). The two are the same knowledge pointed in opposite directions.

## Common mistakes

- **Skipping fundamentals.** You cannot assess or harden what you do not understand; later "privilege escalation" is just permission misconfiguration, which this model explains.
- **Logging in as root.** Direct root use removes the audit trail and the least-privilege guardrail; use `sudo` for specific commands.
- **Leaving unnecessary services running.** Every listening daemon is attack surface; disable what is not needed.
- **Ignoring SUID/SGID and sudo rights.** Over-privileged programs and accounts are the classic escalation path; inventory and minimise them.
- **Forgetting the host firewall.** Filtering is cheap defence in depth even when a service is misconfigured.

## Security implications

- Learn the map: configuration in `/etc`, logs in `/var/log`, secrets in home directories, privilege in the permission model.
- Apply least privilege everywhere — accounts, files, SUID binaries, sudo rights, and the user a service runs as.
- Treat every running service and scheduled job as attack surface that must have a reason and run minimally.
- Minimise listening services, know the host's exposure, filter with a host firewall, and forward logs to the SOC.

## Knowledge check

:::check Questions
1. Where does Linux keep service and account configuration, and where does it keep logs?
2. What do the `r`, `w`, `x` permissions apply to, and for which three classes of user?
3. Why should administrators use `sudo` rather than logging in as root?
4. Why are a host's running services and scheduled jobs of interest to both an assessor and a defender?
:::

:::solution Answers
1. **Configuration** lives under **`/etc`** (including `/etc/passwd` and `/etc/shadow` for accounts and per-service config); **logs** live under **`/var/log`** (and the systemd journal via `journalctl`).
2. **Read, write and execute** apply to each of three user classes — **owner, group, and other** — for every file (and directory). They are the core of "who can do what" on the system.
3. Because `sudo` grants root privileges only for specific commands and **logs each use**, preserving least privilege and an audit trail; logging in directly as root removes both the guardrail and the record of who did what.
4. Because every running service and scheduled job is **attack surface**: an assessor looks for needless or over-privileged ones (and cron/timers as persistence), and a defender ensures each exists for a reason, runs with least privilege, and is monitored.
:::

## Exercise

For a Linux web server you are authorised to review (conceptually — no live probing required):

1. List **four locations** you would inspect and what each tells you about the host's security.
2. Describe **two permission or privilege conditions** that would concern you and the least-privilege fix for each.
3. Name **two services or scheduled-job checks** you would make and why.
4. State one **host-level hardening** recommendation connecting to networking.

:::solution Model answer (abbreviated)
1. `/etc/passwd` and `/etc/sudoers` (accounts and who has sudo); the web service's config under `/etc` (how it is set up, what it exposes); `/var/log` (evidence and whether logging is on); home directories' `~/.ssh/authorized_keys` (who can log in). 
2. A world-writable file used by a root process → restrict ownership/permissions to least privilege; an application account with broad `sudo` rights it does not need → remove or narrow those rights to the specific commands required.
3. Review running services (`systemctl`) to disable any not needed (reduce attack surface); review cron jobs and systemd timers for anything unexpected (automation and a persistence location).
4. Enable and configure the host firewall to allow only the ports the server must expose (e.g. HTTPS), reducing exposure as defence in depth even if a service is misconfigured.
:::

## Summary and next steps

- Most Linux weaknesses are consequences of these fundamentals: filesystem layout, the user/group/permission model, processes/services/scheduled jobs, and host networking.
- **Least privilege** is the thread: over-permissive files, over-privileged accounts, needless services and unnecessary SUID binaries are where "privilege escalation" actually comes from.
- The assessor's map (config in `/etc`, logs in `/var/log`, privilege in permissions, attack surface in running services) is the same map a defender uses to harden.

The later, operational modules (network enumeration, exploitation, privilege escalation, Active Directory) build on these foundations and are performed only within explicit authorisation and isolated lab environments; this lesson gives you the model to understand and defend the systems they concern.

===assignment===
Write a **Linux host hardening checklist** (one page) grounded in this lesson: group items under accounts/permissions, services, scheduled jobs, logging, and networking. For each item, state the weakness it addresses and the least-privilege or defence-in-depth principle behind it. Add a short paragraph explaining how the same checklist serves an *assessor* (what to look for) and a *defender* (what to fix).

===criteria===
- Complete the reading and the knowledge check, and produce the checklist.
- Each checklist item must name the weakness it addresses and the principle (least privilege / defence in depth / reduce attack surface); the content must stay conceptual/defensive (no exploitation steps).
- Mark the lesson complete. The module quiz draws on this lesson.

===references===
- The Linux Filesystem Hierarchy Standard (FHS) — https://refspecs.linuxfoundation.org/fhs.shtml
- The `sudo` and permission model (man pages: `sudoers`, `chmod`) — https://www.sudo.ws/docs/man/
- systemd / systemctl documentation (services and timers) — https://www.freedesktop.org/software/systemd/man/
- CIS Benchmarks for Linux (hardening baselines) — https://www.cisecurity.org/cis-benchmarks
- NIST SP 800-123, *Guide to General Server Security* — https://csrc.nist.gov/pubs/sp/800/123/final
