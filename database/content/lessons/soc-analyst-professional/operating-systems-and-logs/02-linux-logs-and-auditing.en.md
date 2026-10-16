---
title: Linux Logs and Auditing for SOC Analysts
chapter: Operating systems and logs
summary: Read the Linux logs a SOC analyst relies on — authentication, system and audit logs via syslog and journald — and recognise the evidence that failed logons, privilege escalation and persistence leave behind.
duration: 50
level: beginner
preview: true
objectives:
- Locate the key Linux log files and query the systemd journal
- Read sshd and PAM authentication events and spot brute force and spraying
- Interpret sudo, su and account-change events for privilege abuse
- Recognise persistence and tampering signs (cron, services, cleared logs)
- Centralise logs so evidence survives a compromised host
prerequisites:
- Windows Event Logs for Investigations
---

## Introduction

The previous lesson read Windows through its Event Log. A SOC analyst needs the same fluency on Linux, which runs much of the internet's infrastructure — web servers, containers, cloud workloads. Linux logging looks different (mostly plain-text files and the systemd journal rather than a single binary log), but the investigative questions are identical: *who authenticated, from where, did they gain privilege, and what did they change?*

This lesson gives you the files, the commands and — most importantly — the **evidence patterns** that map the same attacks you already know (brute force, privilege escalation, persistence) onto Linux.

## Where Linux logs live

Two systems coexist on most modern distributions:

- **Plain-text logs** under `/var/log/`, written via **syslog** (rsyslog/syslog-ng).
- **The systemd journal** (`journald`), a structured, queryable store read with `journalctl`.

Key locations and sources:

| Source | Debian/Ubuntu | RHEL/CentOS/Fedora | Contains |
|---|---|---|---|
| Authentication | `/var/log/auth.log` | `/var/log/secure` | Logins, sshd, sudo, su, PAM |
| General system | `/var/log/syslog` | `/var/log/messages` | Service and kernel messages |
| The journal | `journalctl` | `journalctl` | Everything systemd services emit |
| Kernel | `dmesg`, `/var/log/kern.log` | `dmesg` | Kernel/hardware/firewall drops |
| Web server | `/var/log/nginx/`, `/var/log/apache2/` | `/var/log/httpd/` | HTTP access and errors |

:::tip Reach for the journal first
On a systemd host, `journalctl` queries everything in one place with time and unit filters — e.g. `journalctl -u ssh --since "today"` or `journalctl _SYSTEMD_UNIT=sshd.service -p warning`. The plain-text files under `/var/log/` remain invaluable when the journal is volatile (not persisted across reboot) or when you are triaging a disk image offline.
:::

## Authentication: the SOC analyst's first stop

Most Linux intrusions begin with authentication against SSH. The auth log (`auth.log` / `secure`, or `journalctl -u ssh`) is where you confirm them.

### Failed and successful SSH logons

A single failed password is noise; a **burst** of them against one account, or one password across many accounts, is the brute-force / password-spraying pattern from the detection module — now in Linux form:

```text
Failed password for invalid user admin from 203.0.113.45 port 51214 ssh2
Failed password for root from 203.0.113.45 port 51216 ssh2
Failed password for root from 203.0.113.45 port 51220 ssh2
...        (dozens more from the same source)
Accepted password for backupsvc from 203.0.113.45 port 51880 ssh2
```

Read it exactly as you read the Windows 4625→4624 pattern: many **`Failed password`** lines from one source, then an **`Accepted password`** for an account is a likely successful brute force. Note the distinctions:

- **`invalid user`** means the account does not exist — a sign of guessing usernames.
- **`Accepted password`** vs **`Accepted publickey`** tells you *how* they got in; a password success where your policy mandates keys is itself suspicious.
- The **source IP** and **port** anchor the session for correlation with network logs.

### PAM and session events

PAM (Pluggable Authentication Modules) records session open/close and account issues: `session opened for user root`, `authentication failure`, `account locked`. A session opened for a privileged account right after a burst of failures is the join you are looking for.

:::scenario Reading one SSH intrusion
`auth.log` shows ~60 `Failed password` lines for `root` and several `invalid user` names from `198.51.100.23` between 02:14 and 02:19, then `Accepted password for deploy from 198.51.100.23` at 02:19, immediately followed by `session opened for user deploy` and, a minute later, a `sudo: deploy : COMMAND=/bin/bash`. In four lines you have initial access (brute force), the successful logon, the session, and privilege escalation — the opening of an incident, each step with a timestamp and a source IP to pivot on.
:::

## Privilege escalation: sudo, su and account changes

Once in, attackers seek root. Linux records the attempts clearly.

- **`sudo`** logs every invocation in the auth log: the user, the terminal, the working directory, and the **command** run. `sudo: deploy : TTY=pts/0 ; PWD=/home/deploy ; USER=root ; COMMAND=/bin/bash` means `deploy` obtained a root shell. A `sudo` **authentication failure** or `user NOT in sudoers` is an attempt that was refused — still worth noting.
- **`su`** switches user; `su: (to root) deploy on pts/0` shows an escalation path that bypasses sudo logging of the command.
- **Account changes** — `useradd`, `usermod`, `passwd`, additions to the `sudo`/`wheel` group — are classic persistence. A new account, or an existing one suddenly added to a privileged group, right after a suspicious login is a strong signal.

Map these to what you know: failed logons are initial access; sudo/su to root is privilege escalation; a new privileged account is persistence.

## Persistence and tampering

Attackers want to stay and to hide. Defensive signs to hunt:

- **Scheduled tasks** — new or modified **cron** jobs (`/etc/crontab`, `/etc/cron.*`, `crontab -l` per user) and **systemd timers/services**. A cron job that calls out to the internet or re-opens a shell is a persistence mechanism.
- **New or modified services** — unexpected `systemctl enable`d units; `journalctl` shows a service starting that nobody deployed.
- **SSH key additions** — a new entry in `~/.ssh/authorized_keys` is silent persistence; it leaves little in the auth log, so file-integrity monitoring matters.
- **Log tampering** — gaps in otherwise continuous logs, a truncated `auth.log`, or `journalctl` reporting rotated/missing ranges. Shell history cleared (`history -c`, an emptied `~/.bash_history`, or `HISTFILE` unset) is an anti-forensic tell.

:::warning Absence of logs is itself evidence
On a compromised host an attacker with root can edit or delete local logs. A suspicious *gap*, a log that stops and restarts, or history that was wiped is a finding, not a dead end — and the reason the next section matters. Never treat "the local log shows nothing" as "nothing happened".
:::

## The Linux audit system (auditd)

Beyond syslog, Linux offers the **audit daemon (`auditd`)**, which records security-relevant events against rules you define — file access, specific syscalls, command execution — in `/var/log/audit/audit.log`, queried with `ausearch`/`aureport`. It is noisier and needs tuning, but it captures detail syslog does not (e.g. who read a sensitive file, or exactly which syscalls a process made). Many SOCs enable a baseline audit ruleset on critical Linux hosts precisely because it is harder for an attacker to evade quietly than the application-level logs.

## Centralise, or lose the evidence

Everything above assumes the logs survive. On a host the attacker controls, they may not. The defensive answer is the same as on Windows: **ship logs off the host in real time** to a central SIEM or log collector (rsyslog forwarding, journald remote, or an agent). Central collection means:

- Evidence persists even if the local copy is tampered with.
- You can correlate across hosts (the same source IP hitting ten servers).
- Detection rules — like the brute-force rule you built in the KQL module — run over everything at once.

A SOC's visibility into Linux is only as good as its log pipeline; a server whose logs stay local is a blind spot.

## Common mistakes

- **Only knowing Windows.** Much of the estate is Linux; the same attacks leave different-looking but equivalent evidence.
- **Trusting local logs on a compromised host.** Root can alter them; centralisation is what makes them reliable.
- **Reading one failed login and stopping.** The signal is the *pattern* — bursts, then a success, then escalation.
- **Ignoring sudo/su detail.** The command field often tells you exactly what the attacker did next.
- **Forgetting persistence locations.** Cron, systemd units and `authorized_keys` are where footholds hide.

## Security implications

- Learn both auth logs (`auth.log`/`secure`) and `journalctl`; reach for the journal first on systemd hosts.
- Read authentication as a pattern — bursts of failure then success — and pivot on source IP into network logs.
- Treat sudo/su, new privileged accounts, cron/systemd changes and `authorized_keys` edits as escalation/persistence signals.
- Centralise logs and consider auditd on critical hosts, so evidence survives and correlates.

## Knowledge check

:::check Questions
1. Which file holds authentication events on Ubuntu, and what is the RHEL equivalent?
2. What does an `Accepted password` line following dozens of `Failed password` lines from one source suggest?
3. Which log entries would show a user escalating to root, and what useful detail does `sudo` logging add?
4. Why is a gap in an otherwise continuous log a finding rather than a dead end?
:::

:::solution Answers
1. **`/var/log/auth.log`** on Ubuntu/Debian; **`/var/log/secure`** on RHEL/CentOS/Fedora. (On systemd hosts, `journalctl -u ssh` queries the same events from the journal.)
2. A likely **successful SSH brute force** — many failures from one source then a success for an account is the same pattern as the Windows 4625→4624 burst, now in Linux form.
3. **`sudo`** and **`su`** entries in the auth log (e.g. `sudo: user : ... COMMAND=/bin/bash`, `su: (to root) ...`). `sudo` logging adds the **exact command** run (plus TTY and working directory), often telling you precisely what the attacker did after escalating.
4. Because an attacker with root can edit or delete local logs; a gap, truncation, or wiped shell history is anti-forensic activity — evidence of tampering — and a reason to rely on centralised copies, not to conclude nothing happened.
:::

## Exercise

You are given a host's `auth.log` extract that contains, over three minutes: many `Failed password for root` lines from `192.0.2.66`, two `invalid user` attempts, one `Accepted password for webadmin from 192.0.2.66`, a `session opened for user webadmin`, a `sudo: webadmin : ... COMMAND=/usr/bin/apt install ...`, and later a new entry added to `/home/webadmin/.ssh/authorized_keys`.

1. Reconstruct the **timeline** and label each stage (initial access, logon, escalation, persistence).
2. Identify the **pivot indicators** you would search other logs/hosts for.
3. State **one detection rule** you would propose and the **persistence artefact** you would tell IT to remove.

:::solution Model answer
1. **Initial access** — brute force: bursts of `Failed password for root` plus `invalid user` from 192.0.2.66. **Logon** — `Accepted password for webadmin` then `session opened`. **Escalation** — `sudo ... COMMAND=...` (webadmin ran commands as root). **Persistence** — a new key appended to `webadmin`'s `authorized_keys`.
2. Pivots: the **source IP 192.0.2.66** (search all hosts' auth logs and network/firewall logs for it), the **account `webadmin`** (other logins, other hosts), and the **time window** (correlate surrounding events).
3. *Detection rule:* alert when one source IP produces many SSH `Failed password` events within a short window followed by an `Accepted` event (the brute-force-then-success pattern). *Persistence to remove:* the attacker-added line in `~/.ssh/authorized_keys` (and rotate the account's credentials and review sudo rights).
:::

## Summary and next steps

- Linux logging lives in `/var/log/` (syslog) and the systemd journal (`journalctl`); auth events are in `auth.log`/`secure`.
- The attack patterns you know appear here too: failure-bursts-then-success for brute force, `sudo`/`su` for escalation, cron/systemd/`authorized_keys` for persistence.
- Local logs on a compromised host are untrustworthy — a gap is evidence — so centralise logs and consider `auditd` on critical systems.

With Windows and Linux logs both in hand, the next module, **KQL and Detection Engineering**, turns these evidence patterns into automated detections that watch every host at once.

===assignment===
Using a Linux host you control or a provided sample `auth.log`, collect a short extract and annotate it: mark every authentication outcome, any privilege-escalation event, and anything resembling persistence. Then write, in plain language, **two detection ideas** — one for SSH brute force and one for suspicious privilege escalation — describing the log source, the condition, and why it would fire. Note where you would forward these logs for central collection.

===criteria===
- Complete the reading, the knowledge check and the exercise.
- Your annotation must distinguish failed from successful authentication and identify at least one escalation or persistence artefact; your detection ideas must name a log source and a clear firing condition.
- Mark the lesson complete. The module quiz draws on this lesson and the Windows logs lesson.

===references===
- The Linux `journalctl` manual (systemd journal) — https://www.freedesktop.org/software/systemd/man/journalctl.html
- Linux audit documentation (`auditd`, `ausearch`, `aureport`) — https://github.com/linux-audit/audit-documentation/wiki
- MITRE ATT&CK, T1110 Brute Force — https://attack.mitre.org/techniques/T1110/
- MITRE ATT&CK, T1098 Account Manipulation and T1053 Scheduled Task/Job — https://attack.mitre.org/techniques/T1098/
- rsyslog documentation (central log forwarding) — https://www.rsyslog.com/doc/
