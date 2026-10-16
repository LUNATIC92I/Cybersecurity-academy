---
title: DNS, HTTP and Proxy Log Analysis
chapter: Networking for SOC analysts
summary: Read the application-layer logs where modern intrusions show themselves — DNS queries, HTTP requests and web-proxy records — and recognise command-and-control, beaconing, exfiltration and malicious downloads in them.
duration: 50
level: beginner
preview: true
objectives:
- Read DNS query logs and spot tunnelling, DGA domains and suspicious resolutions
- Interpret HTTP request logs and recognise malicious user agents, URIs and methods
- Use web-proxy logs to find command-and-control, beaconing and large uploads
- Recognise exfiltration and beaconing patterns from volume and timing
- Pivot across DNS, proxy and firewall logs to confirm a finding
prerequisites:
- TCP/IP and Traffic Analysis
---

## Introduction

The previous lesson read traffic at the packet and flow level. But most of what a SOC actually watches is **application-layer** logs — DNS, HTTP and web-proxy records — because that is where a compromised host reveals its intent: *which names it looks up, which URLs it fetches, and how much it sends out.* An attacker can encrypt the payload, but they can rarely hide the destination and the rhythm.

This lesson teaches you to read those three log sources and to recognise the behaviours they expose — command-and-control, beaconing, exfiltration and malicious downloads — building directly on the ports, protocols and suspicious-traffic concepts from the first networking lesson.

## DNS logs: the name layer

Almost everything starts with a DNS lookup, so DNS logs are one of the richest and most under-used SOC sources. A DNS log records, per query: the **client**, the **domain** requested, the **record type**, and the **response**.

```text
client=10.0.5.20  query=cdn.example.com         type=A     answer=93.184.216.34
client=10.0.5.20  query=a8f3b1c9.evil-c2.net    type=A     answer=198.51.100.77
client=10.0.5.20  query=k2j9x.data.evil-c2.net  type=TXT   answer=<long base64 string>
```

What a SOC analyst looks for:

- **DGA domains.** Malware often uses a *domain generation algorithm* producing random-looking names (`a8f3b1c9.evil-c2.net`, `qz7k2p9.net`). A host suddenly resolving many high-entropy domains is a classic command-and-control sign.
- **DNS tunnelling / exfiltration.** Data smuggled inside DNS shows as **unusually long or numerous `TXT`/subdomain queries** to one domain — `k2j9x.data.evil-c2.net` with a base64-looking label. Normal browsing does not generate long TXT lookups in volume.
- **Newly registered / rare domains.** A lookup for a domain nobody else in the org has queried, or one registered days ago, deserves attention.
- **Resolution to known-bad infrastructure.** The answer IP matching threat intelligence is a direct hit.

:::tip High entropy and high volume are the DNS tells
Two cheap signals catch a lot: the *randomness* of a domain name (entropy) and the *rate* of lookups to one domain or TXT records. A workstation making hundreds of lookups a minute to subdomains of one newly seen domain is almost never doing something legitimate — it is tunnelling or beaconing. You do not need to decode the payload to escalate.
:::

## HTTP logs: the request layer

HTTP request logs (from a web server or a proxy) record each request's **method**, **host**, **URI/path**, **status code**, **user agent**, and **bytes**. For a SOC, the interesting fields are often the metadata, not the content.

```text
203.0.113.9  GET  /wp-login.php          401  ua="Mozilla/5.0 ..."
203.0.113.9  POST /wp-login.php          200  ua="python-requests/2.31"
10.0.5.20    GET  /a8f3b1c9/gate.php      200  ua="curl/8.0"           bytes_out=142
10.0.5.20    POST /a8f3b1c9/gate.php      200  ua="curl/8.0"           bytes_out=48213
```

Signals to recognise:

- **Suspicious user agents.** Automated tools (`python-requests`, `curl`, `sqlmap`, empty or malformed UA) where a browser is expected suggest scripted access — scanning, brute force or malware check-ins.
- **Odd methods and URIs.** `POST` to a script that normally serves `GET`, requests to paths that do not exist in your app, or URIs with encoded payloads.
- **Status-code patterns.** A run of `401`/`403` then a `200` on a login path is the brute-force-then-success pattern again; a burst of `404`s is scanning.
- **Malicious downloads.** A `GET` fetching an executable or script from a rare host, followed by that host behaving differently, is initial access via download.

Read HTTP logs as behaviour: *who is talking to what, how, and does it look human or automated?*

## Proxy logs: the SOC's web-visibility workhorse

A web proxy sits between internal hosts and the internet and logs **every** outbound web request — client, destination, URL, bytes sent/received, category, and whether it was allowed or blocked. Because it sees egress from the whole estate, the proxy is where a SOC most reliably catches command-and-control and exfiltration.

### Command-and-control and beaconing

Malware "phones home" to a C2 server on a schedule. In proxy (or flow) logs this shows as **beaconing**: regular, repeated connections from one internal host to one external destination, often small and evenly spaced.

```text
 10.0.5.20 → gate.evil-c2.net   every ~60s, ~150 bytes out, 48 bytes in   (beacon)
 10.0.5.20 → gate.evil-c2.net   14:00:03   14:01:04   14:02:02   14:03:05   ...
```

The tell is **regularity**, not volume: a human browsing is bursty and irregular; a beacon is metronomic. Jitter (small random delays) is added by attackers to evade, but a steady connection to one rare destination every minute for hours is a strong C2 indicator — the same beacon you saw in the network-flows lab (10.0.5.20 → 198.51.100.77).

### Exfiltration

Exfiltration shows as an **unusual outbound volume**: a host that normally sends kilobytes suddenly uploads hundreds of megabytes, or a large `POST`/upload to an external or newly seen destination, often outside business hours.

```text
 10.0.5.20 → paste.example / upload.unknown-host   bytes_out=512MB   03:14 local
```

The direction matters: SOC analysts watch **bytes *out*** especially — large inbound is often normal (downloads, updates); large *outbound* to an unusual place is the data leaving.

:::scenario One host, three logs, one story
Host `10.0.5.20` shows in **DNS** a burst of TXT lookups to subdomains of `evil-c2.net`; in the **proxy** a metronomic 60-second connection to `gate.evil-c2.net` with tiny payloads for hours; then a single **512 MB** `POST` to an unknown host at 03:14. Each log alone is suspicious; together they are a clear chain — C2 resolution, beaconing, and exfiltration — on one host. That three-source pivot is how a SOC turns scattered signals into a confirmed incident.
:::

## Pivoting across sources

No single application log proves an incident; **correlation** does. The pivots:

- A **DNS** lookup to a bad domain → find the **proxy/firewall** connection that followed it → identify the **host and user** → check **endpoint logs** (Windows/Linux, from the OS-logs lessons) for what ran.
- A **proxy** beacon → resolve the destination back through **DNS** logs to see how the host found it → check **flow** logs for the full session size.
- An **HTTP** malicious download → trace the source host's subsequent DNS and proxy activity for C2.

This is the same source-to-destination reasoning as the whole SOC path, now joined across the name, request and egress layers.

## Common mistakes

- **Ignoring DNS.** It is often the earliest and clearest signal (DGA, tunnelling) and the most under-monitored log.
- **Reading content, not metadata.** You rarely need the payload; destination, user agent, timing and volume usually tell the story — and the payload is often encrypted anyway.
- **Chasing volume, missing rhythm.** Beaconing is about *regularity*, not size; a tiny, perfectly periodic connection is the C2.
- **Watching inbound instead of outbound.** Exfiltration is in the **bytes out** to unusual destinations.
- **One log, one verdict.** Confirm by pivoting across DNS, proxy, firewall and endpoint before escalating.

## Security implications

- Monitor DNS for high-entropy domains and long/frequent TXT or subdomain queries (DGA and tunnelling).
- Read HTTP/proxy metadata — user agent, method, URI, status, bytes — not payloads, to spot automation and C2.
- Treat metronomic connections to one rare destination as beaconing, and large outbound volumes as possible exfiltration.
- Always pivot across sources to confirm, and tie the destination back to a host and user.

## Knowledge check

:::check Questions
1. What two cheap DNS signals most often reveal command-and-control or tunnelling?
2. In HTTP/proxy logs, why is the user agent a useful field for a SOC analyst?
3. What distinguishes beaconing from normal human web browsing in the logs?
4. For exfiltration, which direction of byte volume matters most, and why?
:::

:::solution Answers
1. **High entropy** (random-looking domain names, a sign of a domain generation algorithm) and **high volume / long TXT or subdomain queries** to one domain (a sign of DNS tunnelling/exfiltration). You can escalate on these without decoding the payload.
2. Because an **automated or unexpected user agent** (e.g. `python-requests`, `curl`, `sqlmap`, empty/malformed) where a browser is expected indicates scripted access — scanning, brute force or malware check-ins — regardless of the request content.
3. **Regularity.** Beaconing is metronomic — repeated, evenly spaced connections to one rare destination — whereas human browsing is bursty and irregular. The tell is the rhythm, not the size.
4. **Outbound (bytes out).** Large inbound is frequently legitimate (downloads, updates); a large or unusual *outbound* volume to an unfamiliar destination is the data actually leaving the organisation.
:::

## Exercise

You are given one host's logs for an afternoon: DNS shows ~200 TXT queries to random subdomains of `sync-cdn.net` (registered 3 days ago); the proxy shows a connection to `sync-cdn.net` every 45 seconds, ~120 bytes out each, for three hours; firewall flows show one 340 MB outbound session to an unrelated host at 18:50.

1. Label each observation (DGA/tunnelling, beaconing, exfiltration) and say which log revealed it.
2. State the **pivots** you would run to confirm scope and identify the user.
3. Write a one-line **escalation summary** (as from the triage lesson) with a severity and reason.

:::solution Model answer
1. **DNS** — ~200 TXT queries to random subdomains of a 3-day-old domain = **DGA + DNS tunnelling**. **Proxy** — 45-second, ~120-byte connections for hours = **beaconing (C2)**. **Firewall flow** — a single 340 MB outbound session = **exfiltration**.
2. Pivots: resolve the host to a **user** (DHCP/identity logs); check **endpoint logs** (Windows/Linux) for the process making the lookups/connections; search **proxy/DNS** for any other internal host contacting `sync-cdn.net` (scope); and look up the 18:50 destination in threat intel.
3. *Escalation:* "Host 10.0.5.20 shows DGA DNS tunnelling to `sync-cdn.net` (3-day-old), ~45s C2 beaconing for 3h, then a 340 MB outbound transfer at 18:50 — likely C2 + data exfiltration. Severity **Critical** (active exfiltration, high confidence across three log sources). Recommend isolating the host and identifying the user/process now."
:::

## Summary and next steps

- Application-layer logs — **DNS, HTTP and proxy** — are where modern intrusions reveal intent; the payload may be encrypted but the destination, rhythm and volume are not.
- DNS exposes DGA and tunnelling (entropy and volume); HTTP/proxy metadata exposes automation and C2; proxy egress exposes beaconing (rhythm) and exfiltration (bytes out).
- Confirm by **pivoting** across DNS, proxy, firewall and endpoint logs to tie a destination to a host, a user and a process.

The next module, **KQL and Detection Engineering**, turns these behaviours into queries that watch for them automatically across the whole estate.

===assignment===
Using sample or lab DNS and proxy logs (reuse the network-flows lab dataset if you like), identify one likely **C2/beaconing** host and one likely **exfiltration** event. For each, write down the log source and the specific evidence (domain entropy, interval, bytes out), the pivots you would run to confirm, and a one-line escalation summary with a severity. Finish with one **detection idea** for beaconing described in plain language (source, condition, why it fires).

===criteria===
- Complete the reading, the knowledge check and the exercise.
- Your analysis must distinguish beaconing (rhythm) from exfiltration (outbound volume), cite the specific log evidence, and name at least two cross-source pivots.
- Mark the lesson complete. The module quiz draws on this lesson and the TCP/IP lesson.

===references===
- MITRE ATT&CK, T1071 Application Layer Protocol and T1572 Protocol Tunneling — https://attack.mitre.org/techniques/T1071/
- MITRE ATT&CK, T1048 Exfiltration Over Alternative Protocol — https://attack.mitre.org/techniques/T1048/
- MITRE ATT&CK, T1568 Dynamic Resolution (DGA) — https://attack.mitre.org/techniques/T1568/
- SANS, *Detecting DNS Tunneling* — https://www.sans.org/white-papers/34152/
- CISA, guidance on identifying command-and-control and beaconing — https://www.cisa.gov/news-events/cybersecurity-advisories
