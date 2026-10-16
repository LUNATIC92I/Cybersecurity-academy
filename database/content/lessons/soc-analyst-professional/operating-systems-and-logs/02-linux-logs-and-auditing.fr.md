---
title: Journaux Linux et audit pour analystes SOC
chapter: Systèmes d'exploitation et journaux
summary: Lire les journaux Linux sur lesquels s'appuie un analyste SOC — authentification, système et audit via syslog et journald — et reconnaître les traces que laissent les échecs d'authentification, l'élévation de privilèges et la persistance.
duration: 50
level: beginner
preview: true
status: published
objectives:
- Localiser les principaux fichiers de journaux Linux et interroger le journal systemd
- Lire les événements d'authentification sshd et PAM et repérer le brute force et le spraying
- Interpréter les événements sudo, su et de modification de compte pour l'abus de privilèges
- Reconnaître les signes de persistance et d'altération (cron, services, journaux effacés)
- Centraliser les journaux pour que les preuves survivent à un hôte compromis
prerequisites:
- Journaux d'événements Windows pour les investigations
---

## Introduction

La leçon précédente lisait Windows par son journal d'événements. Un analyste SOC a besoin de la même aisance sous Linux, qui fait tourner une grande partie de l'infrastructure d'Internet — serveurs web, conteneurs, charges cloud. La journalisation Linux a une autre apparence (surtout des fichiers texte et le journal systemd plutôt qu'un unique journal binaire), mais les questions d'investigation sont identiques : *qui s'est authentifié, depuis où, a-t-il obtenu des privilèges, et qu'a-t-il modifié ?*

Cette leçon donne les fichiers, les commandes et — surtout — les **schémas de preuves** qui projettent sur Linux les mêmes attaques que vous connaissez déjà (brute force, élévation de privilèges, persistance).

## Où vivent les journaux Linux

Deux systèmes coexistent sur la plupart des distributions modernes :

- **Journaux en texte clair** sous `/var/log/`, écrits via **syslog** (rsyslog/syslog-ng).
- **Le journal systemd** (`journald`), un stockage structuré et interrogeable lu avec `journalctl`.

Emplacements et sources clés :

| Source | Debian/Ubuntu | RHEL/CentOS/Fedora | Contient |
|---|---|---|---|
| Authentification | `/var/log/auth.log` | `/var/log/secure` | Connexions, sshd, sudo, su, PAM |
| Système général | `/var/log/syslog` | `/var/log/messages` | Messages des services et du noyau |
| Le journal | `journalctl` | `journalctl` | Tout ce qu'émettent les services systemd |
| Noyau | `dmesg`, `/var/log/kern.log` | `dmesg` | Noyau/matériel/rejets de pare-feu |
| Serveur web | `/var/log/nginx/`, `/var/log/apache2/` | `/var/log/httpd/` | Accès et erreurs HTTP |

:::tip Commencez par le journal
Sur un hôte systemd, `journalctl` interroge tout au même endroit avec des filtres de temps et d'unité — p. ex. `journalctl -u ssh --since "today"` ou `journalctl _SYSTEMD_UNIT=sshd.service -p warning`. Les fichiers texte sous `/var/log/` restent précieux lorsque le journal est volatile (non persistant au redémarrage) ou lorsque vous triez une image disque hors ligne.
:::

## Authentification : le premier arrêt de l'analyste SOC

La plupart des intrusions Linux commencent par une authentification contre SSH. Le journal d'authentification (`auth.log` / `secure`, ou `journalctl -u ssh`) est l'endroit où les confirmer.

### Connexions SSH échouées et réussies

Un seul échec de mot de passe est du bruit ; une **rafale** contre un compte, ou un mot de passe contre de nombreux comptes, est le schéma brute force / password spraying du module de détection — ici sous forme Linux :

```text
Failed password for invalid user admin from 203.0.113.45 port 51214 ssh2
Failed password for root from 203.0.113.45 port 51216 ssh2
Failed password for root from 203.0.113.45 port 51220 ssh2
...        (des dizaines d'autres depuis la même source)
Accepted password for backupsvc from 203.0.113.45 port 51880 ssh2
```

Lisez-le exactement comme le schéma Windows 4625→4624 : de nombreuses lignes **`Failed password`** depuis une source, puis un **`Accepted password`** pour un compte, c'est un brute force vraisemblablement réussi. Notez les distinctions :

- **`invalid user`** signifie que le compte n'existe pas — un signe de devinette de noms d'utilisateur.
- **`Accepted password`** vs **`Accepted publickey`** indique *comment* l'accès a été obtenu ; une réussite par mot de passe là où votre politique impose des clés est en soi suspecte.
- L'**IP source** et le **port** ancrent la session pour la corrélation avec les journaux réseau.

### Événements PAM et de session

PAM (Pluggable Authentication Modules) enregistre l'ouverture/fermeture de session et les problèmes de compte : `session opened for user root`, `authentication failure`, `account locked`. Une session ouverte pour un compte privilégié juste après une rafale d'échecs est le lien recherché.

:::scenario Lire une intrusion SSH
`auth.log` montre ~60 lignes `Failed password` pour `root` et plusieurs noms `invalid user` depuis `198.51.100.23` entre 02:14 et 02:19, puis `Accepted password for deploy from 198.51.100.23` à 02:19, immédiatement suivi de `session opened for user deploy` et, une minute plus tard, d'un `sudo: deploy : COMMAND=/bin/bash`. En quatre lignes vous avez l'accès initial (brute force), la connexion réussie, la session et l'élévation de privilèges — l'ouverture d'un incident, chaque étape avec un horodatage et une IP source sur lesquels pivoter.
:::

## Élévation de privilèges : sudo, su et modifications de compte

Une fois à l'intérieur, les attaquants cherchent root. Linux enregistre les tentatives clairement.

- **`sudo`** journalise chaque invocation dans le journal d'authentification : l'utilisateur, le terminal, le répertoire de travail et la **commande** exécutée. `sudo: deploy : TTY=pts/0 ; PWD=/home/deploy ; USER=root ; COMMAND=/bin/bash` signifie que `deploy` a obtenu un shell root. Un **échec d'authentification** `sudo` ou `user NOT in sudoers` est une tentative refusée — à noter tout de même.
- **`su`** change d'utilisateur ; `su: (to root) deploy on pts/0` montre une voie d'élévation qui contourne la journalisation de commande de sudo.
- **Modifications de compte** — `useradd`, `usermod`, `passwd`, ajouts au groupe `sudo`/`wheel` — sont une persistance classique. Un nouveau compte, ou un compte existant soudain ajouté à un groupe privilégié, juste après une connexion suspecte, est un signal fort.

Projetez-les sur ce que vous savez : les échecs de connexion sont l'accès initial ; sudo/su vers root est l'élévation de privilèges ; un nouveau compte privilégié est la persistance.

## Persistance et altération

Les attaquants veulent rester et se cacher. Signes défensifs à traquer :

- **Tâches planifiées** — **cron** nouveaux ou modifiés (`/etc/crontab`, `/etc/cron.*`, `crontab -l` par utilisateur) et **timers/services systemd**. Une tâche cron qui appelle Internet ou rouvre un shell est un mécanisme de persistance.
- **Services nouveaux ou modifiés** — unités `systemctl enable`d inattendues ; `journalctl` montre un service qui démarre que personne n'a déployé.
- **Ajouts de clés SSH** — une nouvelle entrée dans `~/.ssh/authorized_keys` est une persistance silencieuse ; elle laisse peu de traces dans le journal d'authentification, d'où l'importance de la surveillance d'intégrité des fichiers.
- **Altération des journaux** — des trous dans des journaux par ailleurs continus, un `auth.log` tronqué, ou `journalctl` signalant des plages manquantes/rotées. Un historique shell effacé (`history -c`, un `~/.bash_history` vidé, ou `HISTFILE` désactivé) est un indice anti-forensique.

:::warning L'absence de journaux est elle-même une preuve
Sur un hôte compromis, un attaquant disposant de root peut éditer ou supprimer les journaux locaux. Un *trou* suspect, un journal qui s'arrête puis reprend, ou un historique effacé est un constat, non une impasse — et la raison pour laquelle la section suivante compte. Ne traitez jamais « le journal local ne montre rien » comme « il ne s'est rien passé ».
:::

## Le système d'audit Linux (auditd)

Au-delà de syslog, Linux offre le **démon d'audit (`auditd`)**, qui enregistre les événements pertinents pour la sécurité selon des règles que vous définissez — accès à des fichiers, appels système précis, exécution de commandes — dans `/var/log/audit/audit.log`, interrogé avec `ausearch`/`aureport`. Il est plus bruyant et demande du réglage, mais il capture un détail que syslog n'a pas (p. ex. qui a lu un fichier sensible, ou exactement quels appels système un processus a faits). Beaucoup de SOC activent un jeu de règles d'audit de base sur les hôtes Linux critiques précisément parce qu'il est plus difficile pour un attaquant de l'éluder discrètement que les journaux applicatifs.

## Centraliser, ou perdre la preuve

Tout ce qui précède suppose que les journaux survivent. Sur un hôte contrôlé par l'attaquant, ce n'est pas garanti. La réponse défensive est la même que sous Windows : **expédier les journaux hors de l'hôte en temps réel** vers un SIEM ou un collecteur central (transfert rsyslog, journald distant, ou un agent). La collecte centrale signifie :

- La preuve persiste même si la copie locale est altérée.
- Vous pouvez corréler entre hôtes (la même IP source frappant dix serveurs).
- Les règles de détection — comme la règle de brute force construite au module KQL — s'exécutent sur tout à la fois.

La visibilité d'un SOC sur Linux ne vaut que ce que vaut son pipeline de journaux ; un serveur dont les journaux restent locaux est un angle mort.

## Erreurs courantes

- **Ne connaître que Windows.** Une grande partie du parc est Linux ; les mêmes attaques laissent des preuves d'aspect différent mais équivalent.
- **Faire confiance aux journaux locaux sur un hôte compromis.** Root peut les altérer ; la centralisation est ce qui les rend fiables.
- **Lire une seule connexion échouée et s'arrêter.** Le signal est le *schéma* — des rafales, puis une réussite, puis l'élévation.
- **Ignorer le détail de sudo/su.** Le champ commande indique souvent exactement ce que l'attaquant a fait ensuite.
- **Oublier les emplacements de persistance.** Cron, les unités systemd et `authorized_keys` sont là où se cachent les points d'ancrage.

## Implications pour la sécurité

- Apprenez à la fois les journaux d'authentification (`auth.log`/`secure`) et `journalctl` ; commencez par le journal sur les hôtes systemd.
- Lisez l'authentification comme un schéma — rafales d'échecs puis réussite — et pivotez sur l'IP source vers les journaux réseau.
- Traitez sudo/su, les nouveaux comptes privilégiés, les changements cron/systemd et les éditions d'`authorized_keys` comme des signaux d'élévation/persistance.
- Centralisez les journaux et envisagez auditd sur les hôtes critiques, pour que la preuve survive et se corrèle.

## Vérification des connaissances

:::check Questions
1. Quel fichier contient les événements d'authentification sur Ubuntu, et quel est l'équivalent RHEL ?
2. Que suggère une ligne `Accepted password` suivant des dizaines de lignes `Failed password` depuis une même source ?
3. Quelles entrées de journal montreraient un utilisateur s'élevant vers root, et quel détail utile la journalisation `sudo` ajoute-t-elle ?
4. Pourquoi un trou dans un journal par ailleurs continu est-il un constat plutôt qu'une impasse ?
:::

:::solution Réponses
1. **`/var/log/auth.log`** sur Ubuntu/Debian ; **`/var/log/secure`** sur RHEL/CentOS/Fedora. (Sur les hôtes systemd, `journalctl -u ssh` interroge les mêmes événements depuis le journal.)
2. Un **brute force SSH vraisemblablement réussi** — de nombreux échecs depuis une source puis une réussite pour un compte, c'est le même schéma que la rafale Windows 4625→4624, ici sous forme Linux.
3. Les entrées **`sudo`** et **`su`** du journal d'authentification (p. ex. `sudo: user : ... COMMAND=/bin/bash`, `su: (to root) ...`). La journalisation `sudo` ajoute la **commande exacte** exécutée (plus le TTY et le répertoire de travail), indiquant souvent précisément ce que l'attaquant a fait après l'élévation.
4. Parce qu'un attaquant disposant de root peut éditer ou supprimer les journaux locaux ; un trou, une troncature ou un historique effacé est une activité anti-forensique — une preuve d'altération — et une raison de se fier aux copies centralisées, non de conclure qu'il ne s'est rien passé.
:::

## Exercice

On vous donne l'extrait d'`auth.log` d'un hôte contenant, sur trois minutes : de nombreuses lignes `Failed password for root` depuis `192.0.2.66`, deux tentatives `invalid user`, un `Accepted password for webadmin from 192.0.2.66`, un `session opened for user webadmin`, un `sudo: webadmin : ... COMMAND=/usr/bin/apt install ...`, et plus tard une nouvelle entrée ajoutée à `/home/webadmin/.ssh/authorized_keys`.

1. Reconstituez la **chronologie** et étiquetez chaque étape (accès initial, connexion, élévation, persistance).
2. Identifiez les **indicateurs de pivot** que vous rechercheriez dans d'autres journaux/hôtes.
3. Énoncez **une règle de détection** que vous proposeriez et l'**artefact de persistance** que vous diriez à l'informatique de supprimer.

:::solution Réponse type
1. **Accès initial** — brute force : rafales de `Failed password for root` plus `invalid user` depuis 192.0.2.66. **Connexion** — `Accepted password for webadmin` puis `session opened`. **Élévation** — `sudo ... COMMAND=...` (webadmin a exécuté des commandes en root). **Persistance** — une nouvelle clé ajoutée à l'`authorized_keys` de webadmin.
2. Pivots : l'**IP source 192.0.2.66** (rechercher dans les journaux d'authentification de tous les hôtes et les journaux réseau/pare-feu), le **compte `webadmin`** (autres connexions, autres hôtes), et la **fenêtre temporelle** (corréler les événements autour).
3. *Règle de détection :* alerter lorsqu'une IP source produit de nombreux événements SSH `Failed password` dans une courte fenêtre suivis d'un événement `Accepted` (le schéma brute-force-puis-réussite). *Persistance à supprimer :* la ligne ajoutée par l'attaquant dans `~/.ssh/authorized_keys` (et renouveler les identifiants du compte et revoir ses droits sudo).
:::

## Résumé et étapes suivantes

- La journalisation Linux vit dans `/var/log/` (syslog) et le journal systemd (`journalctl`) ; les événements d'authentification sont dans `auth.log`/`secure`.
- Les schémas d'attaque que vous connaissez apparaissent ici aussi : rafales d'échecs puis réussite pour le brute force, `sudo`/`su` pour l'élévation, cron/systemd/`authorized_keys` pour la persistance.
- Les journaux locaux d'un hôte compromis ne sont pas fiables — un trou est une preuve — alors centralisez les journaux et envisagez `auditd` sur les systèmes critiques.

Windows et Linux en main, le module suivant, **KQL et ingénierie de détection**, transforme ces schémas de preuves en détections automatisées qui surveillent tous les hôtes à la fois.

===assignment===
À l'aide d'un hôte Linux que vous contrôlez ou d'un échantillon `auth.log` fourni, collectez un court extrait et annotez-le : marquez chaque issue d'authentification, tout événement d'élévation de privilèges, et tout ce qui ressemble à de la persistance. Puis rédigez, en langage clair, **deux idées de détection** — une pour le brute force SSH et une pour une élévation de privilèges suspecte — décrivant la source du journal, la condition, et pourquoi elle se déclencherait. Indiquez où vous transféreriez ces journaux pour la collecte centrale.

===criteria===
- Terminez la lecture, la vérification des connaissances et l'exercice.
- Votre annotation doit distinguer l'authentification échouée de la réussie et identifier au moins un artefact d'élévation ou de persistance ; vos idées de détection doivent nommer une source de journal et une condition de déclenchement claire.
- Marquez la leçon comme terminée. Le quiz du module s'appuie sur cette leçon et sur la leçon sur les journaux Windows.

===references===
- Le manuel Linux `journalctl` (journal systemd) — https://www.freedesktop.org/software/systemd/man/journalctl.html
- Documentation de l'audit Linux (`auditd`, `ausearch`, `aureport`) — https://github.com/linux-audit/audit-documentation/wiki
- MITRE ATT&CK, T1110 Brute Force — https://attack.mitre.org/techniques/T1110/
- MITRE ATT&CK, T1098 Account Manipulation et T1053 Scheduled Task/Job — https://attack.mitre.org/techniques/T1098/
- Documentation rsyslog (transfert central des journaux) — https://www.rsyslog.com/doc/
