---
title: Projet de synthèse GRC — Une évaluation de sécurité organisationnelle de bout en bout
chapter: Projet de synthèse GRC
summary: Mettre tout le parcours en pratique sur une organisation fictive — inventorier ses actifs, apprécier et consigner ses risques, rattacher les mesures à l'ISO/IEC 27001 et mener une analyse d'écart NIST CSF 2.0, puis livrer une feuille de route de remédiation priorisée et un rapport pour la direction.
duration: 90
level: advanced
preview: true
status: published
objectives:
- Planifier et délimiter une évaluation GRC complète d'une organisation
- Produire les artefacts reliés enseignés par le parcours — registre des actifs, registre des risques, correspondance des mesures, analyse d'écart
- Prioriser les constats en une feuille de route de remédiation chiffrée et guidée par le risque
- Rédiger un rapport pour la direction qu'un conseil non technique peut mettre en œuvre
- Présenter toute l'évaluation comme un ensemble unique, traçable et défendable
prerequisites:
- Outils et documentation GRC — Les registres, matrices et formulaires qui rendent la GRC auditable
---

## Introduction

Ce projet de synthèse est le moment où les huit modules précédents cessent d'être des sujets séparés pour devenir un seul travail. Vous mènerez une évaluation complète de gouvernance, risque et conformité d'une organisation fictive unique et produirez les livrables qu'un professionnel GRC remet réellement à la direction : un inventaire des actifs, un registre des risques, une correspondance des mesures et une déclaration d'applicabilité, une analyse d'écart NIST CSF 2.0, une feuille de route de remédiation et un rapport pour la direction.

Rien ici n'est une nouvelle théorie. Le défi — et la compétence — est l'**intégration** : faire que chaque artefact remonte au suivant, délimiter le travail pour qu'il aboutisse, et traduire les constats techniques en décisions qu'un conseil peut prendre. Parcourez-le sur le scénario ci-dessous (ou substituez votre propre organisation de taille similaire).

:::note Ceci est une simulation
L'organisation ci-dessous est fictive et l'exercice est une évaluation sur dossier utilisant les méthodes de ce parcours. Ce n'est pas un test d'intrusion et cela n'exige d'attaquer quoi que ce soit. Toutes les « preuves » que vous citez sont du type papier/entretien enseigné par ce parcours (politiques, configurations décrites, réponses d'entretien) — vous vous exercez au flux de travail GRC, non à du test réel.
:::

## Le scénario : « Meridian Health Booking »

> **Meridian Health Booking** est une entreprise de 40 personnes qui exploite une plateforme de prise de rendez-vous en ligne utilisée par des cliniques privées. Elle stocke les coordonnées des patients et l'historique des rendez-vous (dont la clinique et le type de rendez-vous), encaisse des paiements par carte via un prestataire tiers, et fonctionne sur un prestataire cloud unique. Elle a un responsable informatique, deux développeurs, une équipe de support, et externalise la paie à un fournisseur SaaS. Elle a grandi vite et n'a presque aucune gouvernance de sécurité écrite. La direction veut gagner de plus grandes chaînes de cliniques comme clientes — plusieurs exigent désormais des preuves de bonne sécurité avant de signer.

Cette organisation touche délibérément chaque module : données personnelles sensibles (proches de la santé), un prestataire de paiement et des fournisseurs cloud et paie, une dépendance de continuité à un prestataire cloud unique, aucune gouvernance, et une raison métier de s'améliorer (la demande des clients). Votre travail est de l'évaluer et de dire à la direction quoi faire.

## L'évaluation, phase par phase

Traitez le projet comme six phases. Chacune produit un artefact qui alimente le suivant — la chaîne de traçabilité du module documentation.

### Phase 1 — Délimiter et gouverner

Définissez ce que vous évaluez et établissez la gouvernance minimale. Décidez le **périmètre** (la plateforme de réservation, les données qu'elle détient, les fournisseurs dont elle dépend), identifiez les **parties intéressées** (patients, cliniques, prestataire de paiement, régulateurs), et rédigez les deux artefacts de gouvernance qui manquent à Meridian : une **politique de sécurité de l'information** d'un paragraphe et un **énoncé d'appétence au risque** adapté à une entreprise détenant des données personnelles proches de la santé. Nommez un propriétaire comptable de l'évaluation.

*Artefact :* un bref énoncé de périmètre et de gouvernance.

### Phase 2 — Inventorier les actifs

On ne peut évaluer ce que l'on n'a pas listé. Construisez un **registre des actifs** avec des identifiants stables : l'application de réservation, la base de données patients, l'intégration de paiement, l'infrastructure cloud, les données du SaaS de paie, les postes du personnel. Pour chacun, consignez le propriétaire, la classification (la base patients est clairement la plus sensible) et ce qui en dépend.

*Artefact :* registre des actifs (`A-###`).

### Phase 3 — Apprécier et consigner les risques

Pour chaque actif significatif, identifiez les risques en énoncés de cause à effet, cotez vraisemblance × impact sur l'échelle 5×5, et consignez le score inhérent, le traitement et un résiduel réaliste. Attendez-vous à faire apparaître des risques tels que : pas de MFA sur le portail d'administration ; données patients non chiffrées au repos ; un prestataire cloud unique comme point de défaillance unique de continuité ; aucune évaluation des fournisseurs de paie et de paiement ; pas d'isolement des sauvegardes contre le rançongiciel. Chacun devient une ligne avec un propriétaire.

*Artefact :* registre des risques (`R-###`) — le cœur de l'évaluation.

### Phase 4 — Rattacher les mesures (ISO/IEC 27001) et mener une analyse d'écart (NIST CSF 2.0)

Deux vues complémentaires de la même réalité de maîtrise :

- **Correspondance des mesures / mini-DdA.** Pour les risques que vous avez choisi d'atténuer, sélectionnez des mesures de l'Annexe A, marquez l'applicabilité avec justification (en remontant à l'ID du risque) et consignez le statut — y compris d'honnêtes entrées « pas encore mise en œuvre », puisque Meridian a des lacunes.
- **Analyse d'écart NIST CSF 2.0.** Évaluez un profil actuel sur les six fonctions et fixez un profil cible guidé par le risque. Meridian sera probablement faible en Gouverner (pas de politique), Protéger (pas de MFA, pas de chiffrement) et Rétablir (sauvegardes non testées) ; son profil cible rehausse d'abord les fonctions qui réduisent ses plus grands risques.

*Artefacts :* une matrice de contrôles / mini-DdA et un résumé d'écart CSF actuel/cible.

### Phase 5 — Construire la feuille de route de remédiation

Transformez les écarts en un plan priorisé. Classez les remédiations par le **risque résiduel qu'elles réduisent** rapporté à l'**effort/coût** qu'elles demandent, regroupées par horizons :

```text
 MAINTENANT (0–30 j)   Rapide, fort impact : imposer la MFA, isoler/hors ligne
                       les sauvegardes, vérifier les attestations des fournisseurs
                       de paiement et de paie
 ENSUITE (1–3 mois)    Chiffrer les données patients au repos, rédiger les
                       politiques de base, définir un calendrier de conservation,
                       mener un exercice sur table
 PLUS TARD (3–12 mois) Formaliser le SMSI vers l'ISO 27001, surveillance continue
                       des mesures, une option de continuité/seconde région
```

Chaque élément de la feuille de route nomme le risque qu'il traite, un propriétaire et un coût/effort approximatif — pour que la direction puisse le financer. C'est l'appétence au risque (module 1) transformée en plan de dépenses.

*Artefact :* une feuille de route de remédiation.

### Phase 6 — Rédiger le rapport pour la direction

La direction ne lira pas vos registres. Le **rapport pour la direction** (1 à 2 pages) énonce, en langage métier : le périmètre évalué ; les **3 à 5 risques majeurs** et ce qu'ils signifient pour Meridian (clients perdus, exposition réglementaire, indisponibilité) ; la posture actuelle en un coup d'œil (le profil CSF) ; la feuille de route avec ses demandes « maintenant » et leur coût ; et une recommandation claire. Il répond à la vraie question du conseil — *que devons-nous financer, et qu'arrive-t-il sinon ?* — et ne surestime jamais : il distingue ce qui est mis en œuvre de ce qui est planifié, et ne revendique ni accréditation ni tests non réellement menés.

*Artefact :* le rapport pour la direction — le seul document que la direction lit réellement.

## Comment les artefacts se relient

Le projet est évalué autant sur la **traçabilité** que sur le contenu. Un relecteur doit pouvoir tirer n'importe quel fil et le suivre de bout en bout :

```text
 Actif (A-002 base patients)
    └─ Risque (R-001 données patients non chiffrées, inhérent 20)
          └─ Mesure (A.8.24 cryptographie, rattachée, statut : planifiée)
                └─ Écart CSF (Protéger sous la cible)
                      └─ Élément de feuille de route (ENSUITE : chiffrer au repos, propriétaire, coût)
                            └─ Rapport direction (risque majeur n°1, recommandé maintenant/ensuite)
```

Si chaque risque majeur se trace ainsi, de l'actif au conseil, vous avez produit exactement ce qu'un professionnel GRC livre.

## Erreurs courantes

- **La dérive de périmètre.** Vouloir tout évaluer d'un coup ; l'évaluation n'aboutit jamais. Délimitez délibérément en phase 1.
- **Un registre des risques qui s'arrête à la cotation.** Des risques sans traitement, propriétaire ni lien vers une mesure, c'est la moitié du travail.
- **Une feuille de route non classée par le risque.** Ordonner les remédiations par facilité, ou faire d'abord les plus intéressantes, au lieu du risque résiduel réduit.
- **Un rapport pour la direction plein de jargon.** Si le conseil ne peut l'actionner, il a échoué, aussi rigoureuse que soit l'analyse en dessous.
- **Surestimer la posture.** Prétendre que des mesures sont mises en œuvre alors qu'elles sont planifiées, ou laisser entendre une certification. L'honnêteté est la crédibilité du livrable.

## Implications pour la sécurité

- Délimitez d'abord, pour que l'évaluation soit achevable et défendable.
- Gardez chaque artefact relié par des identifiants stables, pour que tout l'ensemble soit traçable de bout en bout.
- Priorisez la remédiation par le risque résiduel réduit par unité d'effort, non par commodité.
- Rendez compte honnêtement et en termes métier ; le conseil finance ce qu'il comprend et à quoi il se fie.

## Vérification des connaissances

:::check Questions
1. Pourquoi définir le périmètre est-elle la première phase essentielle de l'évaluation ?
2. Quel artefact unique est le « cœur » de l'évaluation, et pourquoi ?
3. Quels deux facteurs doivent classer les éléments de la feuille de route de remédiation ?
4. Nommez deux choses qu'un rapport pour la direction doit faire et qu'un registre des risques ne fait pas.
:::

:::solution Réponses
1. Parce que sans périmètre délibéré, l'évaluation n'aboutit jamais (dérive de périmètre) et ne peut être défendue — le périmètre définit ce qui a été couvert ou non, dont dépendent tous les artefacts ultérieurs et le rapport final.
2. Le **registre des risques.** Tout le reste s'y relie : les actifs sont ce que les risques menacent, les mesures existent pour réduire les risques du registre, l'analyse d'écart et la feuille de route s'organisent autour d'eux, et le rapport pour la direction résume les principaux risques du registre.
3. Le **risque résiduel que chaque élément réduit** et l'**effort/coût** qu'il demande — un classement guidé par le risque et conscient du coût (non la facilité ou l'intérêt).
4. Deux parmi : traduire les constats en **langage et conséquences métier** ; ne faire ressortir que les **quelques risques majeurs** et une **recommandation** claire ; indiquer **quoi financer maintenant et le coût** ; et distinguer honnêtement **mis en œuvre et planifié** — choses qu'un registre détaillé n'est pas conçu pour faire pour un conseil.
:::

## Projet de synthèse

Réalisez l'évaluation complète de **Meridian Health Booking** (ou d'une organisation comparable que vous définissez), en produisant les six artefacts comme un seul ensemble relié :

1. Un **énoncé de périmètre et de gouvernance** (périmètre, parties intéressées, une politique de sécurité d'un paragraphe, un énoncé d'appétence au risque, et le propriétaire comptable).
2. Un **registre des actifs** d'au moins six actifs avec identifiants, propriétaires et classification.
3. Un **registre des risques** d'au moins huit risques avec identifiants référençant les actifs, cotation 5×5, scores inhérent et résiduel, traitement et propriétaires.
4. Une **matrice de contrôles / mini-DdA** (au moins huit mesures de l'Annexe A sur les quatre thèmes, chacune remontant à un risque, avec statut honnête) **et** un **résumé d'écart NIST CSF 2.0 actuel/cible** sur les six fonctions.
5. Une **feuille de route de remédiation** en horizons Maintenant / Ensuite / Plus tard, chaque élément nommant le risque traité, un propriétaire et un coût/effort approximatif.
6. Un **rapport pour la direction** d'une à deux pages en langage métier, avec les 3 à 5 risques majeurs, la posture actuelle, la feuille de route, les coûts et une recommandation.

:::solution Ce que démontre une bonne soumission
Un bon projet de synthèse est jugé sur l'intégration, non sur la longueur. Le relecteur doit pouvoir choisir votre **risque n°1 du rapport direction** et le remonter sans rupture : jusqu'à un élément de feuille de route (avec propriétaire et coût), un écart CSF, une mesure rattachée (avec statut honnête), une ligne cotée du registre des risques, un actif classifié. Le périmètre est explicite et réaliste pour une entreprise de 40 personnes. La feuille de route est ordonnée par risque résiduel réduit, avec de vrais gains rapides en « Maintenant » (MFA, isolement des sauvegardes, vérification des attestations fournisseurs). Le rapport pour la direction est en langage métier clair, nomme les conséquences (clients grands comptes perdus, exposition réglementaire et de confiance des patients, indisponibilité), sépare le mis en œuvre du planifié, et ne revendique ni accréditation ni tests non réalisés. La profondeur d'un fil bien tracé vaut mieux que l'étendue avec des liens rompus.
:::

## Résumé et clôture du cours

- Le projet de synthèse intègre tout le parcours : gouverner, inventorier, apprécier le risque, rattacher mesures et écarts, prioriser la remédiation, et rendre compte à la direction.
- Son véritable test est la **traçabilité** — chaque risque majeur relié de l'actif au conseil — et la **discipline de périmètre** pour que le travail aboutisse.
- Priorisez par le risque résiduel réduit par effort, et rendez compte honnêtement en termes métier.

Réaliser ce projet de synthèse signifie que vous savez prendre une organisation sans — ou presque — gouvernance et la laisser avec un programme de sécurité défendable, priorisé et prêt pour le conseil — ce qui est tout le métier d'un professionnel GRC. L'examen final du cours porte sur tous les modules ; le réussir vous fait obtenir votre certificat **Professionnel GRC**, qui reconnaît votre réussite sur cette plateforme (et, comme toujours, n'est pas une qualification accréditée ni reconnue par l'État).

===assignment===
Soumettez votre évaluation complète en six artefacts de Meridian Health Booking (ou votre équivalent défini) comme un seul ensemble, selon la spécification du Projet de synthèse ci-dessus. Incluez une courte note de présentation (une demi-page) qui guide le lecteur à travers la traçabilité de votre risque le plus prioritaire, de l'actif qu'il menace jusqu'à sa ligne dans le rapport pour la direction.

===criteria===
- Terminez la lecture et la vérification des connaissances, et soumettez les six artefacts du projet comme un ensemble relié.
- L'ensemble doit démontrer une traçabilité de bout en bout pour au moins votre risque majeur, un périmètre explicite et réaliste, une feuille de route classée par risque résiduel, et un rapport pour la direction honnête (mis en œuvre vs planifié, aucune revendication d'accréditation).
- Marquez la leçon comme terminée. L'examen final Professionnel GRC porte sur tous les modules de ce parcours.

===references===
- ISO/IEC 27001:2022 et ISO/IEC 27002:2022 (exigences du SMSI et mesures) — https://www.iso.org/standard/27001
- ISO/IEC 27005:2022 (gestion des risques de sécurité de l'information) — https://www.iso.org/standard/80585.html
- NIST Cybersecurity Framework 2.0 (NIST CSWP 29) — https://doi.org/10.6028/NIST.CSWP.29
- NIST SP 800-30 Rev. 1 (conduite des appréciations de risques) — https://csrc.nist.gov/pubs/sp/800/30/r1/final
- ISO 22301:2019 (continuité d'activité) — https://www.iso.org/standard/75106.html
