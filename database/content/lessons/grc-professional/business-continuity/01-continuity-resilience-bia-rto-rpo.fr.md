---
title: Continuité d'activité et résilience — BIA, RTO et RPO
chapter: Continuité d'activité et résilience
summary: Maintenir les services critiques et les rétablir lorsqu'ils s'arrêtent — mener un bilan d'impact sur l'activité, fixer le RTO et le RPO, bâtir des plans de continuité et de reprise sur une stratégie de sauvegarde saine, et prouver qu'ils fonctionnent par des exercices sur table et de reprise.
duration: 55
level: intermediate
preview: true
status: published
objectives:
- Distinguer continuité d'activité, reprise après sinistre et résilience
- Mener un bilan d'impact sur l'activité pour classer ce qui doit être rétabli en premier
- Définir le RTO et le RPO et en déduire une stratégie de sauvegarde et de reprise
- Décrire la règle de sauvegarde 3-2-1 et pourquoi les sauvegardes doivent être testées et isolées
- Expliquer comment les exercices sur table et de reprise prouvent qu'un plan fonctionne réellement
prerequisites:
- Vie privée et protection des données — Gouverner les données personnelles tout au long de leur cycle de vie
---

## Introduction

Chaque mesure vue jusqu'ici réduit la probabilité d'un événement grave. Ce module admet que certains événements graves surviennent malgré tout — une attaque par rançongiciel, un incendie de centre de données, une migration ratée, un fournisseur clé devenu muet — et pose une autre question : **lorsqu'un service critique s'arrête, en combien de temps doit-il revenir, quelle quantité de données pouvons-nous nous permettre de perdre, et avons-nous prouvé que nous savons réellement le faire ?**

C'est la continuité d'activité et la résilience. C'est là que convergent le risque de rançongiciel de votre registre, les sauvegardes de votre déclaration d'applicabilité et la réponse à incident du SOC, en un seul plan testé.

## Trois disciplines liées

| Terme | Portée | Question centrale |
|---|---|---|
| **Continuité d'activité (BC)** | Les fonctions critiques de toute l'organisation | Comment continuer (ou reprendre rapidement) à fournir les services critiques pendant une perturbation ? |
| **Reprise après sinistre (DR)** | Les systèmes informatiques et les données en particulier | Comment rétablir la technologie dont ces services dépendent ? |
| **Résilience** | Philosophie de conception | Comment concevoir pour que les perturbations causent d'emblée moins de dommages ? |

La DR est un sous-ensemble de la BC : la continuité porte sur la *fonction métier* (pouvons-nous encore prendre des commandes ?), la reprise après sinistre sur la *technologie* dont cette fonction a besoin (la base de commandes est-elle rétablie ?). La résilience est l'objectif plus large d'absorber les chocs — redondance, dégradation gracieuse, aucun point de défaillance unique — pour que moins de perturbations deviennent des sinistres.

:::note La continuité porte sur les fonctions, pas seulement sur les serveurs
Une erreur classique est de ne planifier que pour les serveurs. Si le *serveur* de traitement des commandes est rétabli mais que le personnel qui exploite le processus ne peut accéder à un bâtiment et que le *fournisseur* de paiement est lui aussi en panne, la fonction métier est toujours arrêtée. Les plans de BC prévoient pour les personnes, les locaux, les fournisseurs et les processus — pas seulement l'informatique.
:::

## Le bilan d'impact sur l'activité

Vous ne pouvez pas tout protéger de façon égale ; le **bilan d'impact sur l'activité (BIA)** décide donc de ce qui compte le plus. Pour chaque fonction métier, il demande :

1. **Quelle est sa criticité ?** Quel est l'impact de son arrêt — financier, légal, réputationnel, sécurité des personnes — et comment cet impact croît-il *dans le temps* ?
2. **De quoi dépend-elle ?** Systèmes, données, personnes, fournisseurs, installations.
3. **En combien de temps doit-elle être rétablie ?** Cela fixe le RTO (ci-dessous).
4. **Quelle quantité de données récentes peut-elle se permettre de perdre ?** Cela fixe le RPO (ci-dessous).

Le livrable du BIA est une liste classée : les fonctions dont la perte fait mal le plus vite et le plus fort sont rétablies en premier. C'est la même logique de priorisation que votre registre des risques, appliquée à la *perturbation* plutôt qu'à l'*attaque*.

```text
 Impact dans le temps (pourquoi le délai compte)

 Impact │                                   ███  catastrophique
        │                          ████████
        │                 ████████
        │        ████████
        │███████
        └───────┬────────┬────────┬────────┬────────► temps d'arrêt
               1h        4h       1 jour   3 jours

 Une fonction tolérable une heure peut être catastrophique après un jour.
 Le BIA capture cette courbe, non un chiffre unique.
```

## RTO et RPO

Deux objectifs, fixés par le BIA, pilotent chaque décision de continuité. Ils sont faciles à confondre, ancrez-les donc fermement :

```text
          RPO ◄──────────── incident ──────────────► RTO
      (quelle quantité de          (quel TEMPS pour
       DONNÉES perdue,              rétablir le service,
       mesurée vers l'arrière)      mesuré vers l'avant)

   ...dernière bonne sauvegarde    panne         service rétabli...
        │                           │                    │
        └──── fenêtre RPO ──────────┤                    │
         les données créées ici         └──── fenêtre RTO ─┘
         sont perdues si pas encore sauvegardées
```

- **RTO — Recovery Time Objective (objectif de temps de reprise) :** le *temps* maximal acceptable pour rétablir une fonction après son arrêt. « Les commandes doivent revenir en 4 heures. »
- **RPO — Recovery Point Objective (objectif de point de reprise) :** la *quantité* maximale acceptable de perte de données, exprimée en temps. « Nous pouvons perdre au plus 15 minutes de données de commande. » Le RPO détermine la *fréquence* de sauvegarde ou de réplication : un RPO de 15 minutes impose des sauvegardes/réplications au moins toutes les 15 minutes.

Un moyen mnémotechnique : **RTO = temps pour revenir ; RPO = données perdables.** Le RTO se mesure vers l'avant depuis l'incident ; le RPO vers l'arrière, jusqu'au dernier point récupérable.

:::scenario Déduire une stratégie des objectifs
Le BIA indique que le traitement des commandes exige **RTO 4 heures, RPO 15 minutes**. Cela façonne aussitôt la technologie : une sauvegarde nocturne donne un RPO pouvant atteindre 24 heures — bien trop de perte — il faut donc une réplication quasi continue pour tenir 15 minutes ; et une restauration qui prend un jour ne peut tenir un RTO de 4 heures, il faut donc un site de secours « tiède » (warm standby), non « froid ». Le système de paie, en revanche, peut tolérer **RTO 3 jours, RPO 24 heures**, de sorte qu'une sauvegarde nocturne et une reconstruction plus lente suffisent parfaitement — et coûtent bien moins. Les objectifs fixés par le BIA indiquent où dépenser.
:::

Des objectifs plus serrés coûtent plus cher. Ajuster le RTO/RPO de chaque fonction à sa criticité réelle — sans tout surdimensionner — est tout l'art, et c'est exactement le raisonnement d'appétence au risque du module 1 appliqué à l'indisponibilité.

## Stratégie de sauvegarde

Les sauvegardes sont le socle de la reprise, et la règle durable est la **règle 3-2-1** :

```text
 3  copies des données
 2  supports / types de stockage différents
 1  copie conservée hors site (et, aujourd'hui, hors ligne ou immuable)
```

Trois éléments séparent une vraie stratégie de sauvegarde d'un faux sentiment de sécurité :

1. **L'isolement.** Les sauvegardes accessibles depuis le réseau de production sont chiffrées avec tout le reste lors d'une attaque par rançongiciel. Au moins une copie doit être **hors ligne ou immuable** pour ne pouvoir être ni altérée ni supprimée par un attaquant qui détient la production — c'est exactement le traitement du risque de rançongiciel `R-004` de votre exemple de registre.
2. **Les tests de restauration.** Une sauvegarde que vous n'avez jamais restaurée est un espoir, pas une mesure. « La tâche de sauvegarde indique un succès » ne prouve pas que les données peuvent être récupérées intactes et dans le RTO. Testez les restaurations selon un calendrier — c'est le test d'efficacité opérationnelle du module des mesures appliqué aux sauvegardes.
3. **La couverture et la conservation.** Confirmez *ce qui* est sauvegardé (bases de données, configuration, clés de chiffrement, pas seulement des fichiers) et pour combien de temps, en cohérence avec les règles de conservation du module vie privée.

:::warning La sauvegarde que vous n'avez jamais restaurée n'est pas une sauvegarde
Des organisations découvrent pendant un vrai sinistre que les sauvegardes étaient incomplètes, corrompues, chiffrées par le même attaquant, ou mettent trois jours à se restaurer face à un RTO de quatre heures. Chacun de ces cas se découvre à peu de frais par un test de restauration, et de façon catastrophique par un incident réel. Si vous ne testez qu'une seule chose dans ce module, testez une restauration.
:::

## Plans de continuité et de reprise après sinistre

Le BIA et les objectifs alimentent deux documents :

- Un **plan de continuité d'activité (PCA / BCP)** couvre la façon dont les *fonctions* critiques se poursuivent ou reprennent : lieux de travail de repli, contournements manuels, arbres d'appel du personnel, solutions de secours fournisseurs, et communication aux clients et aux régulateurs.
- Un **plan de reprise après sinistre (PRA / DRP)** couvre la façon dont l'*informatique* est rétablie : ordre de reprise (les dépendances d'abord), procédures pas à pas, qui fait quoi, et où se trouvent les sauvegardes et les systèmes de secours.

Les deux doivent nommer des **rôles**, non des personnes de mémoire, et être accessibles **lorsque les systèmes sont en panne** — un PRA stocké uniquement sur le serveur qui vient de tomber est inutile. Conservez-en une copie hors ligne/imprimée.

## Prouver que le plan fonctionne : les exercices

Un plan sur une étagère est une théorie non testée. Deux types d'exercice en font une capacité :

| Exercice | Déroulement | Ce qu'il prouve |
|---|---|---|
| **Sur table (tabletop)** | L'équipe déroule un scénario à l'oral (« un rançongiciel chiffre les serveurs de fichiers dans la nuit ») étape par étape | Les lacunes du *plan*, des rôles et des décisions — peu coûteux, faible risque, à faire souvent |
| **Reprise / test fonctionnel** | Vous restaurez réellement des systèmes depuis la sauvegarde dans un environnement de test, ou basculez vers le secours | Que la *technologie et le RTO/RPO* fonctionnent réellement — plus d'effort, à faire périodiquement |

Les exercices font régulièrement apparaître les défaillances ingrates qui coulent les vraies reprises : un arbre d'appel périmé, un runbook de PRA auquel manque une étape, une sauvegarde qui se restaure mais en deux fois le RTO, une clé introuvable. Chaque constat alimente l'action corrective (la boucle d'amélioration des modules ISO 27001 et mesures). Le but d'un exercice est d'**échouer à peu de frais en répétition pour ne pas échouer à grands frais lors d'un vrai sinistre.**

## Erreurs courantes

- **Planifier pour les serveurs, pas pour les fonctions.** Rétablir l'informatique alors que les personnes, les locaux ou les fournisseurs sont encore à l'arrêt laisse l'activité stoppée.
- **Un seul RTO/RPO pour tout.** Soit vous surpayez la protection du trivial, soit vous sous-protégez le critique. Fixez-les par fonction à partir du BIA.
- **Des sauvegardes accessibles depuis la production.** Le rançongiciel les emporte aussi. Gardez une copie hors ligne ou immuable.
- **Ne jamais tester les restaurations.** Une *tâche* de sauvegarde réussie n'est pas une *reprise* prouvée.
- **Un PRA stocké uniquement là où le sinistre frappe.** Gardez-le accessible hors ligne, avec des rôles et non des noms.
- **Écrire le plan et ne jamais l'exercer.** Les plans non testés échouent précisément quand on en a besoin.

## Implications pour la sécurité

- Laissez le BIA, non l'intuition, classer ce qu'il faut rétablir en premier et à quelle vitesse.
- Déduisez la fréquence de sauvegarde du RPO et l'architecture de reprise du RTO ; ajustez les deux à la criticité réelle de chaque fonction.
- Gardez au moins une sauvegarde hors ligne ou immuable, et testez les restaurations selon un calendrier.
- Exercez les plans régulièrement (sur table souvent, reprise périodiquement) et alimentez chaque constat en action corrective.

## Vérification des connaissances

:::check Questions
1. En une ligne chacun, en quoi le RTO et le RPO diffèrent-ils ?
2. Quel est le but d'un bilan d'impact sur l'activité ?
3. Énoncez la règle de sauvegarde 3-2-1, et pourquoi au moins une copie devrait être hors ligne ou immuable.
4. Que prouve un exercice sur table que le simple fait d'avoir un plan écrit ne prouve pas ?
:::

:::solution Réponses
1. Le **RTO (objectif de temps de reprise)** est le *temps* maximal acceptable pour rétablir une fonction après une panne (mesuré vers l'avant depuis l'incident) ; le **RPO (objectif de point de reprise)** est la *perte de données* maximale acceptable, exprimée en temps (mesurée vers l'arrière jusqu'au dernier point récupérable).
2. Identifier et **classer** les fonctions critiques de l'organisation selon l'impact de leur perturbation dans le temps, et capter leurs dépendances et objectifs de reprise — afin que l'effort et la dépense de reprise aillent d'abord à ce qui compte le plus.
3. **3** copies des données, sur **2** supports/types de stockage différents, avec **1** conservée hors site. Au moins une copie devrait être **hors ligne ou immuable** pour qu'un rançongiciel (ou un attaquant détenant la production) ne puisse pas chiffrer ou supprimer les sauvegardes aussi.
4. Que le *plan, les rôles et les décisions fonctionnent réellement* lorsqu'on les déroule sur un scénario réaliste — faisant apparaître des lacunes (arbres d'appel périmés, étapes manquantes, responsabilité de décision floue) qu'un document jamais utilisé ne révèle pas.
:::

## Exercice

Pour un petit détaillant en ligne doté d'un système de traitement des commandes, d'un site web marketing et d'un système de paie :

1. Menez un mini-**BIA** : classez les trois fonctions par criticité et justifiez l'ordre.
2. Proposez un **RTO et un RPO** pour chacune, avec une raison d'une ligne.
3. Pour le système de commandes, déduisez l'**approche de sauvegarde/reprise** qu'impliquent son RTO/RPO (fréquence et architecture de reprise).
4. Rédigez un **scénario de tabletop** d'un paragraphe pour exercer le cas du rançongiciel, et nommez deux lacunes que vous vous attendriez à y voir apparaître.

:::solution Réponse type (abrégée)
1. **Traitement des commandes** (le plus élevé — revenu direct et impact client, devient grave en quelques heures) → **site web marketing** (modéré — réputationnel, acquisition perdue) → **paie** (sensible au temps seulement près des dates de paie, sinon tolérant plusieurs jours).
2. Commandes : **RTO 4h / RPO 15min** — critique pour le revenu, peu de perte de données tolérable. Site : **RTO 8h / RPO 24h** — informationnel, reconstructible. Paie : **RTO 3 jours / RPO 24h** — urgent seulement près des dates de paie.
3. Les commandes nécessitent une **réplication quasi continue** (pour tenir le RPO 15min — une sauvegarde nocturne perd jusqu'à un jour) et un **secours tiède** (pour tenir le RTO 4h — une restauration à partir de zéro est trop lente), plus une copie hors ligne/immuable contre le rançongiciel.
4. *Scénario :* « Dans la nuit, un rançongiciel chiffre la base de commandes et atteint le partage de sauvegarde principal ; le personnel le découvre à 07:00 quand les commandes échouent. » Lacunes attendues : la sauvegarde principale était accessible depuis la production et est elle aussi chiffrée (besoin d'une copie hors ligne/immuable) ; et l'équipe ne sait pas qui autorise le basculement et la communication client (responsabilité de décision floue).
:::

## Résumé et étapes suivantes

- La continuité d'activité maintient les *fonctions* ; la reprise après sinistre rétablit l'*informatique* ; la résilience élimine par conception les points de défaillance uniques.
- Le **BIA** classe ce qu'il faut rétablir en premier et fixe, pour chaque fonction, le **RTO** (temps de rétablissement) et le **RPO** (perte de données tolérable).
- Ces objectifs pilotent la fréquence de sauvegarde et l'architecture de reprise ; suivez le **3-2-1**, gardez une copie **hors ligne/immuable**, et **testez les restaurations**.
- Les **exercices sur table et de reprise** transforment un plan écrit en capacité prouvée et alimentent l'action corrective.

Le module suivant, **Gestion des risques tiers**, étend la pensée continuité et mesures aux fournisseurs — dont les prestataires cloud et de paiement — dont les propres perturbations peuvent arrêter vos fonctions critiques.

===assignment===
Produisez une **note de continuité** d'une page pour une organisation de votre choix : un BIA classant au moins quatre fonctions avec justification ; un RTO et un RPO pour chacune avec raisons ; une stratégie de sauvegarde indiquant comment elle tient le RPO et le RTO les plus serrés et comment elle survit à un rançongiciel ; et un court plan de tabletop (scénario, participants, et trois questions que vous poseriez pendant l'exercice). Rattachez la stratégie de sauvegarde à un risque précis de votre registre des risques.

===criteria===
- Terminez la lecture, la vérification des connaissances et l'exercice.
- Le RTO/RPO doit être fixé par fonction (pas une valeur unique pour tout) et justifié ; la stratégie de sauvegarde doit traiter l'isolement et le test de restauration, pas seulement « nous faisons des sauvegardes ».
- Marquez la leçon comme terminée. Le quiz du module s'appuie sur cette leçon.

===references===
- ISO 22301:2019, *Sécurité et résilience — Systèmes de management de la continuité d'activité — Exigences* — https://www.iso.org/standard/75106.html
- ISO/IEC 27031:2011, *Lignes directrices pour la préparation des TIC à la continuité d'activité* — https://www.iso.org/standard/44374.html
- NIST SP 800-34 Rev. 1, *Contingency Planning Guide for Federal Information Systems* — https://csrc.nist.gov/pubs/sp/800/34/r1/upd1/final
- CISA, *Ressources sur la continuité d'activité et la reprise après sinistre* — https://www.cisa.gov/topics/critical-infrastructure-security-and-resilience
