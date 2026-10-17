---
title: Exploitation du SIEM — Le pipeline de journaux, la normalisation et l'alerte
chapter: Exploitation du SIEM
summary: Comprendre le système sur lequel repose un SOC — comment les journaux sont collectés, analysés et normalisés en un unique magasin interrogeable, comment les règles de détection transforment les événements en alertes, et comment le réglage et la corrélation maintiennent un signal élevé et un bruit faible.
duration: 50
level: beginner
preview: true
status: published
objectives:
- Expliquer ce qu'est un SIEM et le rôle qu'il joue pour un SOC
- Décrire le pipeline de journaux, de la source au magasin interrogeable
- Expliquer l'analyse (parsing), la normalisation et pourquoi un schéma commun importe
- Décrire comment les règles de détection, la corrélation et l'enrichissement créent des alertes
- Expliquer le réglage, les faux positifs et les signaux de santé d'un pipeline de journaux
prerequisites:
- Journaux d'événements Windows pour les investigations
- Journaux Linux et audit pour analystes SOC
---

## Introduction

Les leçons précédentes lisaient des journaux individuels — événements Windows, journaux d'authentification Linux — et écrivaient de la logique de détection en KQL. Mais un SOC ne se connecte pas à chaque serveur pour lire des fichiers ; il travaille depuis un **SIEM** (Security Information and Event Management), le système qui collecte les journaux de partout, les rend interrogeables en un seul endroit, et les transforme en alertes. Cette leçon explique ce système : le pipeline qui l'alimente, comment les journaux bruts deviennent des événements normalisés, et comment les règles et la corrélation produisent les alertes que vous avez triées dans le module de réponse à incident.

Comprendre le SIEM est ce qui relie toutes les autres compétences SOC : les journaux que vous avez appris à lire y affluent, le KQL que vous avez appris à écrire s'y exécute, et les alertes que vous avez appris à trier en sortent.

## Ce qu'est un SIEM et ce qu'il fait

Un **SIEM** centralise les données pertinentes pour la sécurité et offre quatre capacités fondamentales :

| Capacité | Ce qu'elle apporte au SOC |
|---|---|
| **Collecte** | Journaux et télémétrie de tout le parc, en un seul endroit |
| **Stockage & recherche** | Un magasin durable et rapide à interroger sur d'énormes volumes |
| **Détection** | Des règles qui correspondent à des schémas et lèvent des alertes |
| **Investigation** | Pivots, corrélation et tableaux de bord sur toutes les sources |

Sa valeur est la **centralisation et la corrélation** : la preuve survit même si un hôte source est compromis (le point des leçons journaux), et une seule requête peut couvrir tous les hôtes à la fois — ainsi la règle de brute force que vous avez écrite en KQL surveille toute l'organisation, non une machine. Un terme apparenté, **SOAR** (Security Orchestration, Automation and Response), ajoute une réponse automatisée et l'exécution de playbooks par-dessus ; beaucoup de SOC exploitent les deux ensemble.

## Le pipeline de journaux

Faire entrer les données dans le SIEM est un pipeline, et chaque étape est un endroit où les choses réussissent ou échouent discrètement :

:::diagram Le pipeline de journaux du SIEM, de la source à l'alerte
```text
 SOURCES ─► COLLECTE ─► ANALYSER & NORMALISER ─► ENRICHIR ─► STOCKER/INDEXER ─► DÉTECTER ─► ALERTER
 (postes,    (agents,    (champs extraits,        (geoIP,     (interrogeable)   (règles)    (vers le tri)
  serveurs,  forwarders,  mappés vers un           threat
  pare-feu,  syslog,      schéma commun)           intel,
  cloud, IdP) API)                                 infos actifs)
```
:::

- **Sources** — les journaux des leçons précédentes et davantage : postes et EDR, serveurs (Windows/Linux), pare-feu et proxies, plateformes cloud, fournisseurs d'identité, applications.
- **Collecte** — agents sur les hôtes, transfert syslog (le point rsyslog de la leçon journaux Linux), ou récupérations par API depuis le cloud/SaaS. Une collecte fiable est le socle ; une source qui cesse d'émettre est un angle mort.
- **Analyser & normaliser** — la transformation critique, ci-dessous.
- **Enrichir** — ajouter du contexte à l'arrivée : géolocalisation des IP, correspondances de threat intel, l'utilisateur ou l'actif auquel un identifiant correspond (l'enrichissement que vous faisiez à la main au tri, fait automatiquement).
- **Stocker & indexer** — un magasin optimisé pour une recherche rapide sur de grands volumes et plages de temps.
- **Détecter & alerter** — des règles s'exécutent sur les données stockées (et souvent en flux) et lèvent des alertes pour le tri.

## Analyse et normalisation : le schéma commun

Les journaux bruts sont un fouillis de formats : un événement Windows, une ligne `auth.log` Linux et un enregistrement de pare-feu expriment tous « une connexion depuis une IP » de façon totalement différente. L'**analyse (parsing)** extrait les champs de chaque format ; la **normalisation** les mappe vers un **schéma commun** afin que le même concept ait le même nom de champ partout.

:::diagram Normaliser trois formats de journaux vers un schéma commun
```text
 Windows 4624  ── Account_Name, Source_Network_Address ──┐
 auth.log Linux ── user, rhost ────────────────────────► │  normaliser vers :
 Journal pare-feu ── src_user, src_ip ──────────────────┘   user, src_ip, action, host, time
```
:::

Pourquoi c'est important : avec un schéma commun, **une seule requête trouve un comportement sur toutes les sources** — « montre les connexions réussies depuis cette IP sur Windows, Linux et le VPN » est une recherche unique, non trois. Sans normalisation, chaque source exige sa propre requête et la corrélation est quasi impossible. Un bon schéma (beaucoup de SOC en adoptent un publié) est ce qui rend les règles de la leçon de détection KQL portables et les pivots inter-sources de la leçon de tri rapides. Des **horodatages cohérents dans un seul fuseau (UTC)** en font partie — les événements de sources différentes doivent s'aligner sur une seule chronologie.

:::tip La normalisation est ce qui rend la corrélation possible
La chose la plus précieuse qu'un SIEM fait est de vous laisser poser une seule question à tout, d'un coup. Cela n'est possible que parce que l'analyse et la normalisation ont déjà transformé « Source_Network_Address », « rhost » et « src_ip » en un même champ. Quand une nouvelle source est intégrée, écrire correctement son analyseur est ce qui la rend utile ; une source non analysée n'est que du texte que personne ne peut interroger.
:::

## Des événements aux alertes : détection, corrélation, enrichissement

Les événements deviennent des **alertes** par la logique de détection s'exécutant sur le SIEM :

- **Règles / analytiques** — les détections que vous avez écrites en KQL : une condition sur les données (p. ex. de nombreux échecs de connexion depuis une source puis une réussite) qui lève une alerte. Elles peuvent s'exécuter selon un calendrier ou sur des données en flux.
- **Corrélation** — combiner des événements de sources *différentes* en un seul signal de plus grande confiance : une autorisation pare-feu + un démarrage de processus sur le poste + une connexion d'identité qui, ensemble, décrivent une attaque. La corrélation inter-sources est exactement ce que permet la normalisation et ce qui rend une alerte digne d'être escaladée.
- **Enrichissement** — attacher du contexte (géo, threat intel, criticité de l'actif, rôle de l'utilisateur) pour que l'analyste reçoive une alerte triable immédiatement, non un événement nu — l'enrichissement manuel de la leçon de tri, automatisé à l'ingestion.

Le résultat est la file d'alertes que la leçon réponse à incident/tri vous a appris à traiter : meilleures sont les règles, la corrélation et l'enrichissement, plus haute est la qualité de ce qui parvient à un humain.

## Réglage, faux positifs et rapport signal/bruit

L'utilité d'un SIEM se décide à son **rapport signal/bruit**. Trop d'alertes de faible valeur et les vraies menaces se perdent dans le flot (fatigue d'alerte). Garder le signal élevé est un travail continu, et cela se relie directement au tri :

- **Réglage (tuning)** — chaque faux positif issu du tri est réinjecté dans la règle (une exclusion, un seuil resserré) pour que le bruit ne revienne pas — la boucle de rétroaction de la leçon de tri, appliquée au contenu du SIEM.
- **Établissement d'une référence (baselining)** — connaître ce qui est normal pour un environnement afin que les règles alertent sur l'anormal, non sur ce qui a seulement l'air inhabituel.
- **Suppression et agrégation** — regrouper les événements dupliqués ou connus-bénins pour que les analystes voient une alerte significative, non un millier.
- **Détection comme du code** — gérer les règles comme un logiciel (versionnées, revues, testées), pour que les détections s'améliorent délibérément dans le temps.

:::warning Un SIEM ne vaut que ses entrées et son réglage
Deux modes de défaillance coulent les SIEM. D'abord, les **lacunes de couverture** : une source critique qui n'envoie pas de journaux, ou en envoie de non analysés, est un angle mort que le SOC ne peut percer — l'attaque se produit dans le noir. Ensuite, le **bruit** : des milliers d'alertes non réglées enterrent la vraie, et les analystes cessent de faire confiance à la file. Surveiller la santé des sources de journaux (chaque source émet-elle encore ?) et un réglage acharné ne sont pas des options — c'est ce qui fait fonctionner le SIEM.
:::

## La santé du pipeline : la propre surveillance du SOC

Parce que des journaux manquants sont invisibles, un SOC doit surveiller le pipeline lui-même :

- **Santé des sources** — chaque source attendue émet-elle encore, à son volume normal ? Une source qui devient silencieuse est un angle mort (et parfois le signe qu'un attaquant a désactivé la journalisation — le point d'altération de la leçon journaux Linux).
- **Latence d'ingestion** — les événements arrivent-ils en quasi temps réel, ou le pipeline est-il engorgé au point que les alertes se déclenchent en retard ?
- **Santé des analyseurs** — les nouveaux formats de journaux sont-ils analysés, ou arrivent-ils en texte non interrogeable ?

Ce sont des indicateurs clés de risque opérationnels pour le SOC lui-même (l'idée d'ICR du parcours GRC), et les surveiller est aussi important que surveiller les alertes.

## Erreurs courantes

- **Collecter sans analyser.** Des journaux non normalisés ne peuvent être corrélés ni interrogés de façon cohérente ; l'analyseur est ce qui rend une source utile.
- **Pas de surveillance de la santé des sources.** Une source qui s'arrête en silence est un angle mort invisible ; surveillez la couverture, pas seulement les alertes.
- **Ne jamais régler.** Des règles non réglées inondent la file, causent la fatigue d'alerte et cachent la vraie menace.
- **Horodatages/fuseaux incohérents.** Des événements qui ne s'alignent pas sur une seule chronologie UTC rendent la corrélation peu fiable.
- **Traiter le SIEM comme une archive de journaux.** Sa valeur est la détection et la corrélation, non le seul stockage ; les règles et l'enrichissement sont l'essentiel.

## Implications pour la sécurité

- Centralisez les journaux pour que la preuve survive à la compromission d'un hôte et qu'une requête couvre tout le parc.
- Analysez et normalisez chaque source vers un schéma commun avec des horodatages UTC cohérents — la corrélation en dépend.
- Construisez la détection à partir de règles, de corrélation inter-sources et d'enrichissement à l'ingestion ; réinjectez les issues du tri comme réglage.
- Surveillez la santé du pipeline (couverture des sources, latence, analyse) comme une télémétrie SOC de premier plan — les journaux manquants sont invisibles.

## Vérification des connaissances

:::check Questions
1. Quelles quatre capacités fondamentales un SIEM apporte-t-il à un SOC ?
2. Qu'est-ce que la normalisation, et pourquoi rend-elle la corrélation possible ?
3. Comment la boucle de rétroaction du tri se relie-t-elle au réglage du SIEM ?
4. Pourquoi un SOC doit-il surveiller la santé des sources de journaux, et pas seulement les alertes ?
:::

:::solution Réponses
1. **Collecte** (journaux/télémétrie de tout le parc), **stockage & recherche** (interrogation durable et rapide sur de grands volumes), **détection** (règles qui lèvent des alertes), et **investigation** (pivots, corrélation et tableaux de bord sur les sources).
2. La **normalisation** mappe les champs analysés de chaque format de journal vers un **schéma commun**, pour que le même concept (p. ex. une IP source) ait le même nom de champ partout. Elle rend la corrélation possible car une seule requête peut alors couvrir toutes les sources à la fois — au lieu d'une requête différente par format, ce qui rend la corrélation inter-sources quasi impossible.
3. Chaque **faux positif** résolu au tri devrait être réinjecté dans la règle du SIEM (une exclusion ou un seuil resserré) pour que le bruit ne revienne pas — la même boucle de rétroaction, appliquée au contenu de détection, ce qui élève le rapport signal/bruit dans le temps.
4. Parce que les **journaux manquants sont invisibles** : une source qui cesse d'émettre en silence (ou envoie des données non analysées) est un angle mort que le SOC ne peut percer — et le silence peut même signaler un attaquant désactivant la journalisation. Surveiller la couverture, la latence d'ingestion et la santé des analyseurs est ce qui garantit que les alertes reposent sur des données complètes.
:::

## Exercice

Pour une petite organisation mettant en place un SIEM :

1. Listez **cinq sources de journaux** que vous intégreriez en premier et, pour chacune, une menace qu'elle aide à détecter (réutilisez les leçons précédentes).
2. Expliquez, avec un exemple de champ concret, ce que la **normalisation** ferait entre deux de ces sources.
3. Décrivez **une règle de détection** (en langage clair) et l'**enrichissement** qui rendrait ses alertes prêtes au tri.
4. Nommez **deux vérifications de santé du pipeline** que vous mettriez en place et pourquoi.

:::solution Réponse type (abrégée)
1. Journaux de sécurité Windows (brute force / 4625→4624) ; `auth.log` Linux (brute force SSH, abus de sudo) ; pare-feu/proxy (beaconing C2, exfiltration) ; fournisseur d'identité (voyage impossible, échecs MFA) ; EDR (exécution de processus malveillant).
2. Entre les journaux d'authentification Windows et Linux, la normalisation mappe `Account_Name`/`Source_Network_Address` et `user`/`rhost` vers les mêmes champs `user` et `src_ip`, pour qu'une seule requête trouve « les connexions réussies depuis une IP donnée » sur les deux.
3. *Règle :* de nombreux échecs de connexion depuis une IP source suivis d'une réussite dans une courte fenêtre. *Enrichissement :* géolocalisation et réputation threat-intel de l'IP source, plus la criticité de l'actif cible — pour que l'analyste voie « réussite depuis une IP signalée, nouveau pays, sur un contrôleur de domaine » et puisse trier immédiatement.
4. *Santé des sources* — alerter si une source attendue cesse d'émettre ou tombe sous son volume normal (angle mort / altération possible) ; *latence d'ingestion* — alerter si les événements arrivent en retard, car une détection retardée signifie une réponse retardée.
:::

## Résumé et étapes suivantes

- Un **SIEM** collecte, stocke, détecte et investigue — sa puissance est la centralisation et la corrélation inter-sources.
- Le **pipeline** va des sources → collecte → analyse/normalisation → enrichissement → stockage → détection → alerte ; chaque étape peut réussir ou échouer discrètement.
- La **normalisation vers un schéma commun** (avec un temps UTC cohérent) est ce qui permet à une requête et à une règle de couvrir toutes les sources.
- Le **réglage** (alimenté par le tri) garde le rapport signal/bruit élevé, et la **surveillance de la santé du pipeline** garantit des données complètes — les journaux manquants sont invisibles.

Cela complète l'image que le parcours SOC bâtit : lire les journaux, écrire les détections, les exécuter sur le SIEM, et trier les alertes qu'elles produisent. Les modules ultérieurs (renseignement sur les menaces et chasse, investigation de logiciels malveillants, surveillance identité et cloud) étendent les sources et techniques que ce pipeline transporte.

===assignment===
Concevez un **plan d'intégration et de réglage de SIEM** d'une page pour une organisation de votre choix : les sources de journaux prioritaires et les menaces que chacune traite ; une courte description des champs du schéma commun vers lesquels vous normaliseriez (avec un exemple de mappage entre deux sources) ; deux règles de détection avec l'enrichissement qui rend leurs alertes prêtes au tri ; et une section de surveillance de la santé du pipeline (couverture des sources, latence, santé des analyseurs) expliquant pourquoi chacune compte. Rattachez au moins une règle à une détection que vous avez écrite dans le module KQL.

===criteria===
- Terminez la lecture et la vérification des connaissances, et produisez le plan.
- Le plan doit montrer la normalisation entre au moins deux sources, relier le réglage à la boucle de rétroaction du tri, et inclure une surveillance de la santé du pipeline avec une justification.
- Marquez la leçon comme terminée. Le quiz du module s'appuie sur cette leçon.

===references===
- NIST SP 800-92, *Guide to Computer Security Log Management* — https://csrc.nist.gov/pubs/sp/800/92/final
- MITRE ATT&CK (mappage des détections aux techniques) — https://attack.mitre.org/
- The Open Cybersecurity Schema Framework (OCSF) — https://schema.ocsf.io/
- Sigma, format générique de signatures pour détections SIEM — https://github.com/SigmaHQ/sigma
- MITRE, *Cyber Analytics Repository (CAR)* — https://car.mitre.org/
