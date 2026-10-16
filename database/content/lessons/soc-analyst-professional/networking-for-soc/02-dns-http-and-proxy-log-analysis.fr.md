---
title: Analyse des journaux DNS, HTTP et proxy
chapter: Réseaux pour analystes SOC
summary: Lire les journaux de couche applicative où se révèlent les intrusions modernes — requêtes DNS, requêtes HTTP et enregistrements de proxy web — et y reconnaître le command-and-control, le beaconing, l'exfiltration et les téléchargements malveillants.
duration: 50
level: beginner
preview: true
status: published
objectives:
- Lire les journaux de requêtes DNS et repérer le tunneling, les domaines DGA et les résolutions suspectes
- Interpréter les journaux de requêtes HTTP et reconnaître les user agents, URI et méthodes malveillants
- Utiliser les journaux de proxy web pour trouver le command-and-control, le beaconing et les gros envois
- Reconnaître les schémas d'exfiltration et de beaconing à partir du volume et du rythme
- Pivoter entre les journaux DNS, proxy et pare-feu pour confirmer un constat
prerequisites:
- TCP/IP et analyse du trafic
---

## Introduction

La leçon précédente lisait le trafic au niveau paquet et flux. Mais l'essentiel de ce qu'un SOC surveille réellement, ce sont les journaux de **couche applicative** — DNS, HTTP et proxy web — car c'est là qu'un hôte compromis révèle son intention : *quels noms il interroge, quelles URL il récupère, et combien il envoie vers l'extérieur.* Un attaquant peut chiffrer la charge utile, mais il peut rarement cacher la destination et le rythme.

Cette leçon apprend à lire ces trois sources de journaux et à reconnaître les comportements qu'elles exposent — command-and-control, beaconing, exfiltration et téléchargements malveillants — en s'appuyant directement sur les ports, protocoles et notions de trafic suspect de la première leçon de réseau.

## Journaux DNS : la couche des noms

Presque tout commence par une résolution DNS ; les journaux DNS sont donc l'une des sources SOC les plus riches et les plus sous-exploitées. Un journal DNS enregistre, par requête : le **client**, le **domaine** demandé, le **type d'enregistrement** et la **réponse**.

```text
client=10.0.5.20  query=cdn.example.com         type=A     answer=93.184.216.34
client=10.0.5.20  query=a8f3b1c9.evil-c2.net    type=A     answer=198.51.100.77
client=10.0.5.20  query=k2j9x.data.evil-c2.net  type=TXT   answer=<longue chaîne base64>
```

Ce que recherche un analyste SOC :

- **Domaines DGA.** Les logiciels malveillants utilisent souvent un *algorithme de génération de domaines* produisant des noms d'apparence aléatoire (`a8f3b1c9.evil-c2.net`, `qz7k2p9.net`). Un hôte qui se met soudain à résoudre de nombreux domaines à forte entropie est un signe classique de command-and-control.
- **Tunneling / exfiltration DNS.** Des données passées dans le DNS apparaissent comme des **requêtes `TXT`/sous-domaines inhabituellement longues ou nombreuses** vers un seul domaine — `k2j9x.data.evil-c2.net` avec une étiquette d'allure base64. La navigation normale ne génère pas de longues requêtes TXT en volume.
- **Domaines récemment enregistrés / rares.** Une requête vers un domaine que personne d'autre dans l'organisation n'a interrogé, ou enregistré il y a quelques jours, mérite l'attention.
- **Résolution vers une infrastructure connue malveillante.** L'IP de réponse correspondant au renseignement sur les menaces est un signal direct.

:::tip La forte entropie et le fort volume sont les indices DNS
Deux signaux peu coûteux attrapent beaucoup : le *caractère aléatoire* d'un nom de domaine (entropie) et le *taux* de résolutions vers un domaine ou des enregistrements TXT. Un poste de travail faisant des centaines de résolutions par minute vers des sous-domaines d'un domaine jamais vu ne fait presque jamais quelque chose de légitime — il fait du tunneling ou du beaconing. Pas besoin de décoder la charge utile pour escalader.
:::

## Journaux HTTP : la couche des requêtes

Les journaux de requêtes HTTP (d'un serveur web ou d'un proxy) enregistrent la **méthode**, l'**hôte**, l'**URI/chemin**, le **code d'état**, le **user agent** et les **octets** de chaque requête. Pour un SOC, les champs intéressants sont souvent les métadonnées, pas le contenu.

```text
203.0.113.9  GET  /wp-login.php          401  ua="Mozilla/5.0 ..."
203.0.113.9  POST /wp-login.php          200  ua="python-requests/2.31"
10.0.5.20    GET  /a8f3b1c9/gate.php      200  ua="curl/8.0"           bytes_out=142
10.0.5.20    POST /a8f3b1c9/gate.php      200  ua="curl/8.0"           bytes_out=48213
```

Signaux à reconnaître :

- **User agents suspects.** Des outils automatisés (`python-requests`, `curl`, `sqlmap`, UA vide ou malformé) là où un navigateur est attendu suggèrent un accès scripté — scan, brute force ou check-ins de logiciel malveillant.
- **Méthodes et URI étranges.** Un `POST` vers un script qui sert normalement du `GET`, des requêtes vers des chemins qui n'existent pas dans votre application, ou des URI avec des charges utiles encodées.
- **Schémas de codes d'état.** Une série de `401`/`403` puis un `200` sur un chemin de connexion est de nouveau le schéma brute-force-puis-réussite ; une rafale de `404` est du scan.
- **Téléchargements malveillants.** Un `GET` récupérant un exécutable ou un script depuis un hôte rare, suivi d'un changement de comportement de cet hôte, est un accès initial par téléchargement.

Lisez les journaux HTTP comme du comportement : *qui parle à quoi, comment, et cela a-t-il l'air humain ou automatisé ?*

## Journaux de proxy : le cheval de bataille de la visibilité web du SOC

Un proxy web se place entre les hôtes internes et Internet et journalise **chaque** requête web sortante — client, destination, URL, octets envoyés/reçus, catégorie, et si elle a été autorisée ou bloquée. Parce qu'il voit la sortie de tout le parc, le proxy est l'endroit où un SOC attrape le plus fiablement le command-and-control et l'exfiltration.

### Command-and-control et beaconing

Un logiciel malveillant « appelle à la maison » un serveur C2 selon un calendrier. Dans les journaux de proxy (ou de flux) cela apparaît comme du **beaconing** : des connexions régulières et répétées d'un hôte interne vers une destination externe, souvent petites et régulièrement espacées.

```text
 10.0.5.20 → gate.evil-c2.net   toutes les ~60s, ~150 octets out, 48 octets in   (balise)
 10.0.5.20 → gate.evil-c2.net   14:00:03   14:01:04   14:02:02   14:03:05   ...
```

L'indice est la **régularité**, non le volume : un humain qui navigue est irrégulier et par à-coups ; une balise est métronomique. Du jitter (petits délais aléatoires) est ajouté par les attaquants pour évader, mais une connexion régulière vers une destination rare toutes les minutes pendant des heures est un fort indicateur de C2 — la même balise que dans le lab de flux réseau (10.0.5.20 → 198.51.100.77).

### Exfiltration

L'exfiltration apparaît comme un **volume sortant inhabituel** : un hôte qui envoie normalement des kilo-octets téléverse soudain des centaines de méga-octets, ou un gros `POST`/téléversement vers une destination externe ou jamais vue, souvent hors des heures ouvrées.

```text
 10.0.5.20 → paste.example / upload.unknown-host   bytes_out=512Mo   03:14 heure locale
```

La direction compte : les analystes SOC surveillent surtout les **octets *sortants*** ; un gros trafic entrant est souvent normal (téléchargements, mises à jour) ; un gros trafic *sortant* vers un endroit inhabituel, c'est la donnée qui part.

:::scenario Un hôte, trois journaux, une histoire
L'hôte `10.0.5.20` montre en **DNS** une rafale de requêtes TXT vers des sous-domaines d'`evil-c2.net` ; au **proxy** une connexion métronomique de 60 secondes vers `gate.evil-c2.net` avec de minuscules charges utiles pendant des heures ; puis un unique `POST` de **512 Mo** vers un hôte inconnu à 03:14. Chaque journal seul est suspect ; ensemble ils forment une chaîne claire — résolution C2, beaconing et exfiltration — sur un seul hôte. Ce pivot à trois sources est la façon dont un SOC transforme des signaux épars en un incident confirmé.
:::

## Pivoter entre les sources

Aucun journal applicatif seul ne prouve un incident ; la **corrélation** le fait. Les pivots :

- Une résolution **DNS** vers un domaine malveillant → trouver la connexion **proxy/pare-feu** qui a suivi → identifier l'**hôte et l'utilisateur** → vérifier les **journaux de poste** (Windows/Linux, des leçons journaux OS) pour ce qui s'est exécuté.
- Une balise **proxy** → résoudre la destination via les journaux **DNS** pour voir comment l'hôte l'a trouvée → vérifier les journaux de **flux** pour la taille totale de la session.
- Un **téléchargement HTTP** malveillant → tracer l'activité DNS et proxy ultérieure de l'hôte source pour du C2.

C'est le même raisonnement source-vers-destination que tout le parcours SOC, désormais joint à travers les couches des noms, des requêtes et de la sortie.

## Erreurs courantes

- **Ignorer le DNS.** C'est souvent le signal le plus précoce et le plus clair (DGA, tunneling) et le journal le moins surveillé.
- **Lire le contenu, pas les métadonnées.** Vous avez rarement besoin de la charge utile ; destination, user agent, rythme et volume racontent l'histoire — et la charge est souvent chiffrée de toute façon.
- **Courir après le volume, manquer le rythme.** Le beaconing est une affaire de *régularité*, non de taille ; une connexion minuscule et parfaitement périodique est le C2.
- **Surveiller l'entrant plutôt que le sortant.** L'exfiltration est dans les **octets sortants** vers des destinations inhabituelles.
- **Un journal, un verdict.** Confirmez en pivotant entre DNS, proxy, pare-feu et poste avant d'escalader.

## Implications pour la sécurité

- Surveillez le DNS pour les domaines à forte entropie et les requêtes TXT ou sous-domaines longues/fréquentes (DGA et tunneling).
- Lisez les métadonnées HTTP/proxy — user agent, méthode, URI, état, octets — et non les charges utiles, pour repérer l'automatisation et le C2.
- Traitez les connexions métronomiques vers une destination rare comme du beaconing, et les gros volumes sortants comme une possible exfiltration.
- Pivotez toujours entre les sources pour confirmer, et rattachez la destination à un hôte et un utilisateur.

## Vérification des connaissances

:::check Questions
1. Quels deux signaux DNS peu coûteux révèlent le plus souvent le command-and-control ou le tunneling ?
2. Dans les journaux HTTP/proxy, pourquoi le user agent est-il un champ utile pour un analyste SOC ?
3. Qu'est-ce qui distingue le beaconing de la navigation web humaine normale dans les journaux ?
4. Pour l'exfiltration, quelle direction de volume d'octets compte le plus, et pourquoi ?
:::

:::solution Réponses
1. La **forte entropie** (noms de domaine d'apparence aléatoire, signe d'un algorithme de génération de domaines) et le **fort volume / les longues requêtes TXT ou sous-domaines** vers un seul domaine (signe de tunneling/exfiltration DNS). On peut escalader sur ces indices sans décoder la charge utile.
2. Parce qu'un **user agent automatisé ou inattendu** (p. ex. `python-requests`, `curl`, `sqlmap`, vide/malformé) là où un navigateur est attendu indique un accès scripté — scan, brute force ou check-ins de logiciel malveillant — quel que soit le contenu de la requête.
3. La **régularité.** Le beaconing est métronomique — des connexions répétées et régulièrement espacées vers une destination rare — alors que la navigation humaine est irrégulière et par à-coups. L'indice est le rythme, pas la taille.
4. L'**sortant (octets out).** Un gros entrant est fréquemment légitime (téléchargements, mises à jour) ; un volume *sortant* important ou inhabituel vers une destination inconnue, c'est la donnée qui quitte réellement l'organisation.
:::

## Exercice

On vous donne les journaux d'un hôte pour un après-midi : le DNS montre ~200 requêtes TXT vers des sous-domaines aléatoires de `sync-cdn.net` (enregistré il y a 3 jours) ; le proxy montre une connexion vers `sync-cdn.net` toutes les 45 secondes, ~120 octets out chacune, pendant trois heures ; les flux pare-feu montrent une session sortante de 340 Mo vers un hôte sans rapport à 18:50.

1. Étiquetez chaque observation (DGA/tunneling, beaconing, exfiltration) et dites quel journal l'a révélée.
2. Énoncez les **pivots** que vous lanceriez pour confirmer le périmètre et identifier l'utilisateur.
3. Rédigez un **résumé d'escalade** d'une ligne (comme dans la leçon de tri) avec une gravité et une raison.

:::solution Réponse type
1. **DNS** — ~200 requêtes TXT vers des sous-domaines aléatoires d'un domaine vieux de 3 jours = **DGA + tunneling DNS**. **Proxy** — connexions de 45 s, ~120 octets, pendant des heures = **beaconing (C2)**. **Flux pare-feu** — une seule session sortante de 340 Mo = **exfiltration**.
2. Pivots : rattacher l'hôte à un **utilisateur** (journaux DHCP/identité) ; vérifier les **journaux de poste** (Windows/Linux) pour le processus faisant les résolutions/connexions ; chercher dans **proxy/DNS** tout autre hôte interne contactant `sync-cdn.net` (périmètre) ; et rechercher la destination de 18:50 dans le renseignement sur les menaces.
3. *Escalade :* « L'hôte 10.0.5.20 montre un tunneling DNS DGA vers `sync-cdn.net` (3 jours), un beaconing C2 de ~45 s pendant 3 h, puis un transfert sortant de 340 Mo à 18:50 — probable C2 + exfiltration de données. Gravité **Critique** (exfiltration active, confiance élevée sur trois sources de journaux). Recommande d'isoler l'hôte et d'identifier l'utilisateur/processus maintenant. »
:::

## Résumé et étapes suivantes

- Les journaux de couche applicative — **DNS, HTTP et proxy** — sont là où les intrusions modernes révèlent leur intention ; la charge utile peut être chiffrée, mais la destination, le rythme et le volume ne le sont pas.
- Le DNS expose le DGA et le tunneling (entropie et volume) ; les métadonnées HTTP/proxy exposent l'automatisation et le C2 ; la sortie proxy expose le beaconing (rythme) et l'exfiltration (octets sortants).
- Confirmez en **pivotant** entre les journaux DNS, proxy, pare-feu et poste pour rattacher une destination à un hôte, un utilisateur et un processus.

Le module suivant, **KQL et ingénierie de détection**, transforme ces comportements en requêtes qui les surveillent automatiquement sur tout le parc.

===assignment===
À l'aide de journaux DNS et proxy d'exemple ou de lab (réutilisez le jeu de données du lab de flux réseau si vous voulez), identifiez un hôte probable de **C2/beaconing** et un événement probable d'**exfiltration**. Pour chacun, notez la source du journal et la preuve précise (entropie du domaine, intervalle, octets sortants), les pivots que vous lanceriez pour confirmer, et un résumé d'escalade d'une ligne avec une gravité. Terminez par une **idée de détection** pour le beaconing décrite en langage clair (source, condition, pourquoi elle se déclenche).

===criteria===
- Terminez la lecture, la vérification des connaissances et l'exercice.
- Votre analyse doit distinguer le beaconing (rythme) de l'exfiltration (volume sortant), citer la preuve précise du journal, et nommer au moins deux pivots entre sources.
- Marquez la leçon comme terminée. Le quiz du module s'appuie sur cette leçon et sur la leçon TCP/IP.

===references===
- MITRE ATT&CK, T1071 Application Layer Protocol et T1572 Protocol Tunneling — https://attack.mitre.org/techniques/T1071/
- MITRE ATT&CK, T1048 Exfiltration Over Alternative Protocol — https://attack.mitre.org/techniques/T1048/
- MITRE ATT&CK, T1568 Dynamic Resolution (DGA) — https://attack.mitre.org/techniques/T1568/
- SANS, *Detecting DNS Tunneling* — https://www.sans.org/white-papers/34152/
- CISA, orientations sur l'identification du command-and-control et du beaconing — https://www.cisa.gov/news-events/cybersecurity-advisories
