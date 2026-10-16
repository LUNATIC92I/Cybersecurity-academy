---
title: Appréciation des risques et registre des risques
chapter: Gestion des risques de sécurité de l'information
summary: Transformer la discipline du risque en un processus reproductible — identifier le risque, coter la vraisemblance et l'impact, distinguer le risque inhérent du risque résiduel, choisir un traitement et tout consigner dans un registre des risques défendable.
duration: 55
level: beginner
preview: true
status: published
objectives:
- Dérouler les quatre étapes de l'appréciation des risques — identification, analyse, évaluation et traitement
- Coter la vraisemblance et l'impact sur une échelle 5×5 et calculer une cotation du risque
- Distinguer le risque inhérent du risque résiduel et expliquer ce qui réduit chacun
- Choisir parmi les quatre options de traitement du risque et justifier le choix
- Construire et tenir un registre des risques défendable avec un propriétaire nommé pour chaque risque
prerequisites:
- Fondements de la gouvernance, du risque et de la conformité
resources:
- Exemple de registre des risques (CSV) | grc-risk-register.csv
---

## Introduction

Dans le premier module, vous avez appris que la direction fixe *la part* de risque acceptable. Cette leçon est le moteur qui travaille dans cette appétence : un processus reproductible pour repérer les risques, les dimensionner de façon cohérente, décider quoi faire et consigner la décision afin de pouvoir la défendre des mois plus tard devant un auditeur, un client ou vous-même.

Le livrable est le document le plus important de la GRC : le **registre des risques**. Un bon registre n'est pas un artefact de conformité qui prend la poussière — c'est une liste vivante de ce qui pourrait nuire à l'organisation, hiérarchisée, dotée d'un propriétaire et suivie jusqu'à une décision.

## Le processus de gestion des risques

La plupart des référentiels (ISO/IEC 27005, NIST SP 800-30) décrivent la même boucle centrale. Nous utilisons quatre étapes :

```text
  ┌───────────────┐   ┌───────────┐   ┌────────────┐   ┌────────────┐
  │ 1 IDENTIFIER  │─► │ 2 ANALYSER│─► │ 3 ÉVALUER  │─► │ 4 TRAITER  │
  │ ce qui peut   │   │ vraisemb. │   │ comparer à │   │ choisir    │
  │ mal tourner   │   │ × impact  │   │ l'appétence│   │ une option │
  └───────────────┘   └───────────┘   └────────────┘   └────────────┘
          ▲                                                   │
          └──────────── 5 SURVEILLER & RÉEXAMINER ◄───────────┘
```

La gestion des risques est continue : après le traitement, on surveille et on reprend la boucle, car les actifs, les menaces et le métier évoluent tous.

### Étape 1 — Identifier

Décrivez chaque risque par une phrase claire de cause à effet, non par un seul mot. Le vocabulaire du parcours SOC s'applique : un risque relie un **actif**, une **menace** et une **vulnérabilité** à un **impact**.

> **Faible :** « Hameçonnage. »
> **Fort :** « Des groupes criminels (menace) exploitent l'absence de MFA sur la messagerie web (vulnérabilité) pour compromettre les boîtes des employés (actif), entraînant des pertes par fraude à la facture et une exposition de données (impact). »

Les sources d'identification incluent les inventaires d'actifs, les incidents passés, le renseignement sur les menaces, les constats d'audit, les scans de vulnérabilités et des ateliers avec les personnes qui exploitent chaque processus.

### Étape 2 — Analyser

L'analyse dimensionne le risque. La méthode la plus courante est la cotation **qualitative** de deux facteurs sur une échelle de 1 à 5 :

- **Vraisemblance** — quelle est la probabilité que la menace exploite la vulnérabilité sur une période donnée ?
- **Impact** — quelle est la gravité de la conséquence si cela se produit ?

| Note | Vraisemblance | Impact |
|---|---|---|
| 1 | Rare | Négligeable |
| 2 | Peu probable | Mineur |
| 3 | Possible | Modéré |
| 4 | Probable | Majeur |
| 5 | Quasi certain | Grave / catastrophique |

La cotation du risque est simplement :

```text
Score de risque = Vraisemblance × Impact    (plage 1–25 sur une échelle 5×5)
```

Reporter les deux facteurs donne la fameuse **matrice des risques**. La couleur de la case transforme un nombre en priorité :

```text
 Impact
   5 │  5  10  15  20  25
   4 │  4   8  12  16  20
   3 │  3   6   9  12  15
   2 │  2   4   6   8  10
   1 │  1   2   3   4   5
     └─────────────────────
        1   2   3   4   5   Vraisemblance

   1–4  Faible    5–9  Moyen
  10–14 Élevé    15–25 Critique     (bandes d'exemple — fixez les vôtres selon l'appétence)
```

:::note Qualitatif ou quantitatif
L'approche 5×5 est **qualitative** — rapide, assez cohérente pour hiérarchiser les risques, et facile à saisir pour les parties prenantes. Les méthodes **quantitatives** (p. ex. estimer une espérance de perte annualisée en monnaie) sont plus précises mais exigent de bonnes données et des efforts. La plupart des organisations cotent tout le registre en qualitatif et réservent l'analyse quantitative à quelques risques majeurs où un chiffre en euros justifie un investissement important.
:::

### Étape 3 — Évaluer

L'évaluation compare chaque risque coté à l'**appétence et à la tolérance au risque** de l'organisation (du module 1) pour décider s'il est acceptable. Un risque de 20 (« Critique ») sur les données clients franchit une limite de faible appétence et *doit* être traité ; un risque de 4 (« Faible ») sur un wiki interne peut tenir confortablement dans l'appétence et être simplement accepté. L'évaluation est le moment où l'appétence cesse d'être un slogan et commence à trier la liste en *agir maintenant*, *planifier un traitement* et *accepter*.

### Étape 4 — Traiter

Pour chaque risque qui dépasse l'appétence, choisissez l'une des **quatre options de traitement** :

| Option | Signification | Exemple |
|---|---|---|
| **Atténuer (réduire)** | Ajouter ou renforcer des mesures pour abaisser la vraisemblance et/ou l'impact | Imposer la MFA ; corriger plus vite ; ajouter des sauvegardes |
| **Transférer (partager)** | Déplacer une partie de l'impact vers un tiers | Cyber-assurance ; clause rendant un fournisseur responsable |
| **Éviter** | Cesser l'activité risquée | Mettre hors service un système hérité ; ne pas lancer une fonctionnalité risquée |
| **Accepter** | Tolérer sciemment le risque dans l'appétence | Consigner et faire signer un risque résiduel faible |

Trois principes rendent le traitement défendable :

1. **Le traitement change le score, pas les faits.** On recote *après* les mesures prévues pour obtenir le risque résiduel (ci-dessous).
2. **Le transfert supprime rarement tout l'impact.** L'assurance verse de l'argent mais pas la réputation ; la clause fournisseur n'empêche pas la panne. Un risque résiduel subsiste.
3. **L'acceptation est une décision, pas un défaut.** Un risque accepté doit être *consigné et validé par le propriétaire du risque* — le silence n'est pas une acceptation.

## Risque inhérent et risque résiduel

Cette distinction apparaît dans chaque audit, soyez donc précis :

- **Risque inhérent** — le niveau de risque *avant* (ou en ignorant) vos mesures : sa gravité si vous ne faisiez rien.
- **Risque résiduel** — le niveau de risque *qui subsiste après* le fonctionnement de vos mesures comme prévu.

```text
  RISQUE INHÉRENT  ──(appliquer les mesures)──►  RISQUE RÉSIDUEL
   V4 × I5 = 20                                   V2 × I3 = 6
   « Critique »                                   « Moyen » — dans l'appétence ?
```

Le risque résiduel est le nombre sur lequel la direction décide réellement. Si le risque résiduel reste supérieur à l'appétence, il faut *davantage* de traitement. S'il est confortablement en dessous, vous dépensez peut-être trop en mesures — une occasion de réorienter l'effort. Les auditeurs recherchent précisément : un score résiduel, les mesures qui justifient la réduction, et un propriétaire qui a accepté le reste.

:::warning N'inventez pas la réduction
Un score résiduel n'est crédible que si de vraies mesures justifient la baisse. Écrire « inhérent 20 → résiduel 4 » sans mesure mise en œuvre, ou avec une mesure existant seulement sur le papier, c'est exactement le « théâtre de la conformité » du module 1. L'écart entre inhérent et résiduel doit être mérité par des mesures qui fonctionnent réellement.
:::

## Le registre des risques

Le registre consigne chaque risque et son traitement. Au minimum, chaque ligne comporte :

| Champ | Finalité |
|---|---|
| ID | Référence stable (p. ex. R-001) |
| Actif | Ce qui est en jeu |
| Menace / Vulnérabilité | La cause |
| Vraisemblance / Impact | Les scores (1–5) |
| Score inhérent | Vraisemblance × Impact avant traitement |
| Traitement | L'option et les mesures prévues |
| Score résiduel | Le score après traitement |
| Propriétaire | La personne comptable |
| Statut / date de revue | Ouvert, traité, accepté ; quand réexaminer |

L'**exemple de registre des risques** qui accompagne cette leçon (téléchargez-le dans les ressources) montre un exemple rempli. Remarquez comme chaque ligne se lit comme une décision défendable : une cause nommée, deux scores, un traitement, un score résiduel et un unique propriétaire comptable — exactement ce que le lab de la prochaine leçon vous demande de produire.

:::scenario Lire une ligne
Prenez `R-001` de l'exemple : *Base de données clients · vol de données criminel · pas de chiffrement au repos · V4 × I5 = 20 (Critique) · traitement : chiffrer au repos + DLP · résiduel 6 · propriétaire : RSSI.* En une ligne, elle dit ce qui pourrait arriver, la gravité, ce que nous faisons, ce qui subsiste et qui en répond. C'est tout le processus de risque condensé dans une ligne — et c'est auditable.
:::

### Indicateurs clés de risque

Un registre est un instantané ; les **indicateurs clés de risque (ICR / KRI)** sont les mesures qui indiquent si un risque s'aggrave entre deux revues. Exemples : *pourcentage de vulnérabilités critiques ouvertes au-delà de leur délai (SLA)*, *nombre de comptes sans MFA*, *délai moyen de correction des systèmes exposés à Internet*. Un ICR qui monte est une alerte précoce qu'un score résiduel pourrait ne plus tenir.

## Erreurs courantes

- **Les risques en un mot.** « Rançongiciel » n'est pas appréciable. Décrivez l'actif, la cause et l'impact pour que deux évaluateurs le cotent de la même façon.
- **Coter de façon incohérente.** Sans définitions convenues pour chaque niveau 1–5, chaque évaluateur invente sa propre échelle et le registre n'est plus comparable.
- **Résiduel = inhérent avec des vœux pieux.** Revendiquer une réduction qu'aucune mesure n'appuie.
- **Pas de propriétaire, ou « l'équipe sécurité » comme propriétaire.** Un risque sans propriétaire métier comptable n'aboutit jamais à une décision.
- **Un registre que personne ne réexamine.** Risques et ICR doivent être revus selon un calendrier et après tout changement majeur.

## Implications pour la sécurité

- Cotez tout le registre avec des définitions *convenues* de vraisemblance/impact pour que les cotations soient comparables.
- Rendez les réductions de l'inhérent au résiduel traçables jusqu'à des mesures réelles et opérantes.
- Attribuez un unique propriétaire comptable par risque, et exigez une validation pour tout risque résiduel accepté.
- Suivez des ICR pour apprendre qu'un risque a grandi *avant* qu'il ne devienne un incident.

## Vérification des connaissances

:::check Questions
1. Un risque est coté Vraisemblance 4 et Impact 5. Quelle est sa cotation sur une échelle 5×5, et dans quelle bande (selon les bandes d'exemple ci-dessus) ?
2. Quelle est la différence entre risque inhérent et risque résiduel ?
3. Quelle option de traitement correspond à « souscrire une cyber-assurance », et supprime-t-elle tout l'impact ?
4. Pourquoi un risque accepté doit-il être validé par son propriétaire plutôt que simplement laissé tel quel ?
:::

:::solution Réponses
1. 4 × 5 = **20**, ce qui tombe dans la bande **Critique** (15–25).
2. Le risque **inhérent** est le niveau avant/en ignorant les mesures ; le risque **résiduel** est ce qui subsiste après que les mesures fonctionnent comme prévu.
3. **Transférer (partager)**. Elle ne supprime **pas** tout l'impact — elle couvre généralement la perte financière mais pas l'atteinte à la réputation ni la panne opérationnelle, un risque résiduel subsiste donc.
4. Parce que l'acceptation est une décision délibérée dont les conséquences relèvent du propriétaire ; la consigner et la faire signer rend la décision explicite et auditable, tandis que le silence laisse le risque non suivi et sans propriétaire.
:::

## Exercice

Ouvrez l'exemple de registre des risques dans les ressources de cette leçon et ajoutez **deux nouveaux risques** pour un petit détaillant en ligne :

1. Rédigez chaque nouveau risque par une phrase complète de cause à effet (actif, menace, vulnérabilité, impact).
2. Cotez la vraisemblance et l'impact (1–5) et calculez le score inhérent.
3. Choisissez une option de traitement, indiquez la ou les mesures, et donnez un score résiduel réaliste avec une justification d'une ligne.
4. Attribuez un propriétaire comptable à chacun.

:::solution Réponse type (exemple d'une ligne)
*Risque :* « Un attaquant utilise des identifiants volés (menace) contre le portail d'administration, dépourvu de MFA (vulnérabilité), pour accéder au système de gestion des commandes (actif), exposant les commandes clients et les références de paiement (impact). »
*Scores :* Vraisemblance 4, Impact 4 → **inhérent 16 (Critique)**.
*Traitement :* **Atténuer** — imposer une MFA résistante à l'hameçonnage sur tous les comptes d'administration et alerter sur les connexions depuis de nouveaux lieux.
*Résiduel :* 4 (« Faible ») — la MFA rend très peu probable un accès par identifiants seuls ; justifié car la mesure est mise en œuvre et vérifiée dans les journaux de connexion.
*Propriétaire :* Responsable informatique.
Une seconde ligne de qualité comparable (p. ex. un plugin e-commerce non corrigé, ou un fournisseur ayant accès aux données clients) est tout aussi acceptable.
:::

## Résumé et étapes suivantes

- L'appréciation des risques est une boucle reproductible : identifier, analyser, évaluer, traiter — puis surveiller et réexaminer.
- Cotez vraisemblance × impact de façon cohérente ; comparez le résultat à l'appétence pour décider où agir.
- Le risque inhérent est avant les mesures ; le risque résiduel est après — et la réduction doit être méritée par de vraies mesures.
- Les quatre traitements sont atténuer, transférer, éviter et accepter ; l'acceptation exige la validation du propriétaire.
- Le registre des risques, avec un propriétaire nommé par risque et des ICR pour suivre les tendances, est le document vivant central de la GRC.

Les modules suivants rattachent ces traitements à des ensembles de mesures reconnus (ISO/IEC 27001 et le NIST Cybersecurity Framework 2.0) et montrent comment prouver que les mesures fonctionnent réellement.

===assignment===
Construisez un **registre des risques initial** d'au moins six risques pour une organisation de votre choix, en utilisant l'exemple CSV comme modèle. Chaque ligne doit comporter un énoncé de risque complet de cause à effet, des scores de vraisemblance et d'impact cohérents (1–5), un score inhérent, une option de traitement avec des mesures nommées, un score résiduel justifié et un unique propriétaire comptable. Ajoutez un court paragraphe proposant **deux indicateurs clés de risque** que vous suivriez, et expliquez ce qu'une valeur croissante de chacun vous signalerait.

===criteria===
- Terminez la lecture, la vérification des connaissances et l'exercice.
- Votre registre doit montrer un écart crédible entre scores inhérent et résiduel, justifié par de vraies mesures, et chaque risque doit avoir un unique propriétaire comptable.
- Marquez la leçon comme terminée. Le lab pratique de ce module cote un registre des risques que vous construisez ; le quiz du module s'appuie sur cette leçon.

===references===
- ISO/IEC 27005:2022, *Préconisations pour la gestion des risques liés à la sécurité de l'information* — https://www.iso.org/standard/80585.html
- NIST SP 800-30 Rev. 1, *Guide for Conducting Risk Assessments* — https://csrc.nist.gov/pubs/sp/800/30/r1/final
- NIST SP 800-39, *Managing Information Security Risk* — https://csrc.nist.gov/pubs/sp/800/39/final
- ISO 31000:2018, *Management du risque — Lignes directrices* — https://www.iso.org/standard/65694.html
