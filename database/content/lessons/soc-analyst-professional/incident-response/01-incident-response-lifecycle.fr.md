---
title: Le cycle de vie de la réponse à incident
chapter: Conduire un incident
summary: Appliquez le cycle de réponse à incident du NIST, de la préparation au retour d'expérience — classer et délimiter les incidents, choisir les stratégies de confinement, préserver les preuves, construire des chronologies et communiquer avec les parties prenantes.
duration: 65
level: intermediate
objectives:
- Décrire chaque phase du cycle de réponse à incident et ses livrables
- Classer les incidents par type et par gravité selon l'impact et l'urgence
- Choisir des actions de confinement qui équilibrent impact métier et préservation des preuves
- Tenir une chronologie d'incident et une chaîne de traçabilité des preuves
- Rédiger une synthèse pour la direction et animer une revue post-incident sans recherche de coupable
prerequisites:
- Les journaux d'événements Windows pour l'investigation
- Détecter la force brute et la pulvérisation de mots de passe
---

## Introduction

La détection n'a de valeur que si elle débouche sur une réponse efficace. La réponse à incident (RI) est le processus méthodique qui consiste à limiter les dégâts, évincer l'attaquant, rétablir les opérations et tirer les leçons de ce qui s'est passé. Sous pression, c'est la structure qui empêche un incident de devenir une crise : chacun connaît son rôle, les décisions sont consignées et les preuves sont préservées pour une analyse ou une action judiciaire ultérieure.

Cette leçon suit le cycle décrit dans la publication NIST SP 800-61 (Computer Security Incident Handling Guide). La révision 3 (2025) aligne la réponse à incident sur les fonctions du NIST Cybersecurity Framework 2.0 ; le modèle classique en quatre phases reste la manière la plus claire d'apprendre le métier.

```text
 ┌──────────────┐   ┌────────────────────────┐   ┌──────────────────────────────┐   ┌───────────────────┐
 │ Préparation  │──►│ Détection et analyse   │──►│ Confinement, éradication et  │──►│ Activités         │
 │              │   │                        │◄──│ rétablissement               │   │ post-incident     │
 └──────────────┘   └────────────────────────┘   └──────────────────────────────┘   └─────────┬─────────┘
        ▲                                                                                      │
        └────────────────────── le retour d'expérience améliore la préparation ◄──────────────┘
```

## Phase 1 — Préparation

L'essentiel de l'issue d'un incident se joue avant qu'il ne commence. La préparation comprend :

- **Politique et autorité** : qui peut déclarer un incident, isoler des systèmes ou faire appel à une aide extérieure.
- **Équipe et contacts** : rôles RI (commandant d'incident, investigateur principal, secrétaire de séance, communication, juridique, relais informatiques), astreintes, liste de contacts hors bande (numéros joignables si la messagerie est compromise).
- **Playbooks** pour les types d'incidents courants : hameçonnage, logiciel malveillant, compromission de compte, rançongiciel, fuite de données, DDoS.
- **Outillage prêt à l'avance** : EDR capable d'isoler, outils de collecte forensique, système de gestion de cas sécurisé, postes d'administration sains.
- **Journalisation et rétention** suffisantes pour investiguer plusieurs semaines en arrière.
- **Exercices** : des exercices sur table qui testent les décisions, et pas seulement les documents.

## Phase 2 — Détection et analyse

### Est-ce un incident ?

Un **événement** est toute occurrence observable. Un **incident** est un événement qui viole, ou menace de façon imminente de violer, les politiques de sécurité ou la charte d'utilisation. Le tri détermine quels événements sont des incidents.

### Classification

Classez par **type** (ce qui se passe) et par **gravité** (à quel point c'est grave) :

| Type | Exemples |
|---|---|
| Logiciel malveillant | Infection, rançongiciel, ver |
| Accès non autorisé | Compromission de compte, abus de privilèges |
| Violation de données | Exfiltration, exposition accidentelle |
| Déni de service | DDoS, épuisement de ressources |
| Abus interne | Violation de politique, vol de données par un employé |
| Attaque web/applicative | Exploitation d'un service exposé |

Une **matrice de gravité** simple combine impact et urgence :

| | Urgence faible | Urgence moyenne | Urgence élevée |
|---|---|---|---|
| **Impact élevé** (systèmes critiques, données réglementées, nombreux utilisateurs) | Élevée | Critique | Critique |
| **Impact moyen** | Moyenne | Élevée | Élevée |
| **Impact faible** (un actif non critique) | Faible | Faible | Moyenne |

La gravité détermine les délais de réponse, l'escalade et les personnes à informer.

### Délimitation

La délimitation répond à : **quels comptes, hôtes, données et quelle période sont touchés ?** Travaillez en élargissant à partir de l'indicateur initial :

1. Pivotez sur les indicateurs dont vous disposez : le compte compromis, l'IP malveillante, l'empreinte du fichier, le domaine C2.
2. Recherchez ces indicateurs dans toutes les sources de journaux pertinentes sur toute la période de rétention.
3. Chaque nouvelle découverte (un autre hôte, un autre compte) devient un nouveau pivot.
4. Arrêtez-vous lorsque les pivots ne rapportent plus rien de nouveau — et documentez ce que vous avez recherché.

### La chronologie de l'incident

Tenez une chronologie unique et partagée dès la première minute. Consignez séparément l'**activité observée** (ce que l'attaquant a fait, en UTC) et les **actions de réponse** (ce que l'équipe a fait).

```text
Heure (UTC)         Source            Observation / action                                   Par
2026-03-14 01:56:41 SecurityEvent     connexion de svc_backup depuis 10.0.9.77 après 38 échecs (attaquant)
2026-03-14 01:58:05 Sysmon 3          upd.exe sur FS-01 se connecte à 198.51.100.77:443      (attaquant)
2026-03-14 02:20:00 SIEM              Incident INC-0142 créé, gravité Élevée                 Niveau 1 (A. Diallo)
2026-03-14 02:31:00 EDR               FS-01 isolé du réseau                                  Niveau 2 (M. Rossi)
```

Distinguer les faits des hypothèses (« C2 probable ») garde l'analyse honnête.

## Phase 3 — Confinement, éradication et rétablissement

### Confinement

Objectif : empêcher les dégâts de s'étendre tout en préservant la capacité d'investigation.

**Confinement à court terme** (minutes à heures) :
- Isoler les hôtes via le confinement réseau de l'EDR (l'agent reste connecté pour l'investigation)
- Désactiver ou réinitialiser les comptes compromis ; révoquer sessions et jetons
- Bloquer les IP/domaines malveillants au pare-feu, au proxy et au DNS
- Supprimer les courriels malveillants de toutes les boîtes aux lettres

**Confinement à long terme** (pendant la préparation de l'éradication) :
- Règles de pare-feu temporaires, segmentation des zones réseau touchées
- Surveillance renforcée des prochaines cibles probables
- Reconstruction des services critiques dans un environnement sain

:::warning Arbitrages du confinement
Éteindre un hôte détruit les preuves volatiles (mémoire, connexions réseau, processus en cours). Alerter l'attaquant trop tôt — par exemple en réinitialisant un compte alors qu'il en contrôle cinq autres — peut déclencher des actions destructrices. Décidez délibérément et, si possible, **collectez les preuves volatiles avant un confinement perturbateur**.
:::

### Gestion des preuves

- Privilégiez les **copies forensiques** ; consignez les **empreintes** (SHA-256) des fichiers et images collectés.
- Tenez une **chaîne de traçabilité** : ce qui a été collecté, par qui, quand, d'où, comment c'est stocké, et chaque transfert.
- Collectez dans l'**ordre de volatilité** : CPU/mémoire → état réseau → processus en cours → disque → journaux et sauvegardes.
- Stockez les preuves dans des emplacements à accès contrôlé ; n'analysez jamais un logiciel malveillant actif sur une machine de production ou personnelle.

### Éradication

Supprimez la présence de l'attaquant et la cause racine :

- Retirer les logiciels malveillants et la persistance (services, tâches planifiées, clés Run, comptes illégitimes, autorisations OAuth)
- Corriger la vulnérabilité exploitée ou la mauvaise configuration
- Réinitialiser les identifiants potentiellement exposés — y compris les comptes de service et, après une compromission du domaine, le compte `krbtgt` (deux fois, avec un intervalle)
- Reconstruire les systèmes dont l'intégrité ne peut être garantie — la réinstallation est souvent plus rapide et plus sûre que le nettoyage

### Rétablissement

- Restaurer à partir de **sauvegardes saines**, vérifiées comme antérieures à la compromission et analysées avant reconnexion
- Remettre les systèmes en service par étapes, avec une **surveillance renforcée**
- Obtenir l'accord des responsables métier sur le service rétabli
- Définir les critères de clôture de l'incident

## Communication et escalade

| Public | Besoins | Canal habituel |
|---|---|---|
| Direction | Impact métier, décisions à prendre, délai de rétablissement | Points écrits brefs à intervalles fixes |
| Exploitation informatique | Actions précises et leur calendrier | Pont d'incident / ticket |
| Juridique et protection des données | Présence de données réglementées ; délais de notification | Communication directe et confidentielle |
| Employés | Ce qu'il faut faire (ou ne pas faire) | Annonce interne approuvée |
| Clients / autorités | Communications exactes et approuvées | Uniquement via les équipes juridique et communication |

Règles : **une seule voix** pour la communication externe ; **canaux hors bande** si la messagerie ou la messagerie instantanée peuvent être compromises ; communiquer des faits, des incertitudes clairement signalées et l'heure du prochain point.

:::note Notification réglementaire
De nombreuses juridictions imposent des délais pour notifier aux autorités ou aux personnes concernées une violation de données personnelles (par exemple 72 heures au titre du RGPD pour la notification à l'autorité de contrôle, lorsque c'est possible). Le rôle du SOC est d'escalader rapidement vers les équipes juridique et protection des données avec des faits de délimitation exacts ; ce sont les juristes qui déterminent les obligations.
:::

## Phase 4 — Activités post-incident

### La synthèse pour la direction

Une bonne synthèse tient sur une page :

1. **Ce qui s'est passé** — en langage clair
2. **L'impact** — systèmes, données, utilisateurs, indisponibilité, implications réglementaires
3. **La cause racine** — comment l'attaquant est entré et pourquoi cela a fonctionné
4. **La réponse** — actions clés et chronologie
5. **L'état actuel** — confiné, éradiqué, rétabli ?
6. **Les recommandations** — améliorations priorisées avec responsables

### La revue de retour d'expérience

Organisez une revue **sans recherche de coupable** dans les deux semaines. Demandez :

- Que s'est-il passé, et quand l'avons-nous su ?
- Qu'est-ce qui a bien fonctionné ? Qu'est-ce qui nous a ralentis ?
- Quelles détections se sont déclenchées, lesquelles auraient dû, et pourquoi ne l'ont-elles pas fait ?
- Qu'est-ce qui aurait empêché ou limité l'incident ?
- Quelles actions allons-nous mener, qui en est responsable et pour quand ?

Suivez des indicateurs comme le délai de détection, de confinement et de rétablissement, et réinjectez les améliorations dans la préparation.

## Erreurs fréquentes

- **Pas de secrétaire de séance** — décisions et horodatages sont perdus.
- **Détruire les preuves** en réinstallant avant la collecte.
- **Confiner au coup par coup** alors que l'attaquant conserve d'autres points d'appui.
- **Crier victoire trop tôt** — les attaquants gardent souvent un accès de secours ; surveillez après le rétablissement.
- **Des revues centrées sur la faute** — les gens cessent de signaler leurs erreurs et l'organisation cesse d'apprendre.

## Vérification des connaissances

:::check Questions
1. Qu'est-ce qui distingue un événement d'un incident ?
2. Pourquoi collecter les preuves volatiles avant d'éteindre un hôte ?
3. Qu'est-ce qu'une chaîne de traçabilité ?
4. Citez trois éléments d'une bonne synthèse pour la direction.
:::

:::solution Réponses
1. Un incident est un événement qui viole ou menace de façon imminente la politique de sécurité ; la plupart des événements sont anodins.
2. La mémoire, les connexions réseau et les processus en cours disparaissent à l'extinction et peuvent contenir les seules preuves des outils et de l'activité de l'attaquant.
3. Un registre documenté de qui a collecté, manipulé, stocké et transféré chaque élément de preuve, quand et comment — prouvant qu'il n'a pas été altéré.
4. Trois parmi : ce qui s'est passé, l'impact, la cause racine, les actions de réponse, l'état actuel, les recommandations.
:::

## Exercice

À 9 h 10, un utilisateur signale que sa boîte aux lettres a envoyé des factures qu'il n'a pas écrites. L'investigation montre : une connexion à son compte depuis un pays inhabituel à 3 h 12 ; une nouvelle règle de boîte de réception déplaçant les réponses contenant « facture » vers un dossier RSS ; et 140 courriels sortants vers des clients entre 3 h 20 et 3 h 40.

1. Classez l'incident par type et par gravité, en justifiant.
2. Listez quatre actions de confinement dans l'ordre où vous les réaliseriez.
3. Quelles preuves collecteriez-vous, et où ?
4. Rédigez deux phrases pour le point à la direction.

:::solution Corrigé
1. **Accès non autorisé / compromission de messagerie professionnelle.** Gravité **Élevée** : fraude visant les clients avec impact financier et de réputation ; urgence élevée car des paiements frauduleux peuvent être en cours.
2. (a) Révoquer toutes les sessions et réinitialiser le mot de passe ; (b) supprimer la règle malveillante et vérifier les transferts de la boîte ; (c) bloquer la source de connexion et exiger un réenregistrement MFA ; (d) rappeler ou avertir les destinataires des fausses factures avec l'équipe communication.
3. Les journaux de connexion et d'audit Entra ID (SigninLogs, AuditLogs), les journaux d'audit de la boîte et le suivi des messages (OfficeActivity), la configuration de la règle de boîte de réception et des copies des courriels frauduleux avec leurs en-têtes — le tout avec empreintes et consigné dans la chaîne de traçabilité.
4. « La boîte aux lettres d'un collaborateur a été utilisée cette nuit par un tiers non autorisé pour envoyer de fausses factures à 140 clients. L'accès a été supprimé, les clients sont prévenus de ne pas payer, et nous vérifions si d'autres comptes ou données sont concernés ; prochain point à 13 h. »
:::

## Synthèse et prochaines étapes

- La RI est un cycle : préparation → détection et analyse → confinement, éradication et rétablissement → retour d'expérience.
- La classification et la délimitation pilotent la réponse ; la chronologie et la chaîne de traçabilité garantissent sa rigueur.
- La communication fait partie de la réponse, ce n'est pas un ajout de dernière minute.

Utilisez désormais ce cycle dans chaque investigation de lab, et tentez l'examen final SOC lorsque vos leçons et labs obligatoires sont terminés.

===assignment===
Rédigez un **rapport d'incident** pour le scénario FS-01 de la leçon sur les journaux Windows : synthèse pour la direction (une page), chronologie détaillée, méthode de délimitation, actions de confinement et d'éradication, liste des preuves avec empreintes (vous pouvez utiliser des empreintes fictives clairement signalées comme telles), cause racine et cinq recommandations priorisées avec responsables.

===criteria===
- Terminer la lecture, la vérification des connaissances et l'exercice de compromission de messagerie.
- Marquer la leçon comme terminée ; l'examen final du cours comprend des scénarios de réponse à incident.

===references===
- NIST SP 800-61 Rév. 3, *Incident Response Recommendations and Considerations for Cybersecurity Risk Management* — https://csrc.nist.gov/pubs/sp/800/61/r3/final
- NIST SP 800-86, *Guide to Integrating Forensic Techniques into Incident Response* — https://csrc.nist.gov/pubs/sp/800/86/final
- RFC 3227, *Guidelines for Evidence Collection and Archiving* — https://www.rfc-editor.org/rfc/rfc3227
- CISA, *Federal Government Cybersecurity Incident and Vulnerability Response Playbooks* — https://www.cisa.gov/resources-tools/resources/federal-government-cybersecurity-incident-and-vulnerability-response-playbooks
