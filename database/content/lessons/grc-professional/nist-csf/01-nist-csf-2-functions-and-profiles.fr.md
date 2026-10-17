---
title: Le NIST Cybersecurity Framework 2.0 — Fonctions et profils
chapter: NIST Cybersecurity Framework 2.0
summary: Découvrir les six Fonctions du CSF 2.0, comment la fonction Gouverner englobe les autres, et comment utiliser les profils et les niveaux pour transformer un référentiel en un plan d'amélioration priorisé et mesurable.
duration: 50
level: intermediate
preview: true
status: published
objectives:
- Nommer les six Fonctions du CSF 2.0 et expliquer ce que chacune recouvre
- Expliquer pourquoi la fonction Gouverner a été ajoutée en 2.0 et son lien avec les cinq autres Fonctions
- Décrire la structure du Noyau — Fonctions, Catégories et Sous-catégories
- Utiliser des profils actuel et cible et une analyse d'écart pour prioriser l'amélioration
- Expliquer les niveaux de mise en œuvre et ce qu'ils mesurent ou non
prerequisites:
- Fondements de la gouvernance, du risque et de la conformité
- Appréciation des risques et registre des risques
---

## Introduction

Un registre des risques indique *quoi* corriger. Un référentiel aide à organiser *tout le programme* afin que rien d'important ne soit oublié et que les progrès puissent être mesurés et communiqués. Le **NIST Cybersecurity Framework (CSF) 2.0**, publié en 2024, est l'un des plus utilisés. Il est volontairement flexible — non une liste de cases à cocher à réussir, mais un langage commun pour décrire, évaluer et améliorer des résultats de cybersécurité.

Cette leçon couvre les parties que vous utiliserez en permanence : les six **Fonctions**, le **Noyau** qui organise les résultats sous elles, et les **profils** et **niveaux** qui transforment le référentiel en plan.

## Ce qu'est le CSF — et ce qu'il n'est pas

- Il **est** un catalogue de *résultats* de cybersécurité (« à quoi ressemble le bon »), organisé pour évaluer où vous en êtes et où vous voulez aller.
- Il n'est **pas** une certification. On ne « réussit pas le CSF » ; il n'existe pas de certificat CSF. (À l'inverse de l'ISO/IEC 27001, le module suivant, qui *est* certifiable.)
- Il est **axé sur les résultats**, non prescriptif : il indique *quel* résultat atteindre, non *quel produit* acheter.
- Il est **indépendant du secteur et de la taille** : un hôpital, une banque et une startup de deux personnes peuvent tous l'utiliser, à l'échelle de leur contexte.

:::note Un langage commun
La plus grande valeur pratique du CSF est la communication. « Notre fonction Détecter est faible, au niveau 2, et notre cible est le niveau 3 dans un an » dit plus à un conseil en une phrase qu'une liste d'outils ne le pourra jamais. L'associer à votre registre des risques — le référentiel pour la structure, le registre pour la priorité — est la façon dont beaucoup de programmes sont pilotés.
:::

## Les six Fonctions

Le CSF 2.0 organise tout sous six **Fonctions** de premier niveau. La version 1.1 en comptait cinq ; la version 2.0 a ajouté **Gouverner** et l'a placée au centre.

:::diagram NIST CSF 2.0 : Gouverner englobe les cinq fonctions principales
```text
                 ┌─────────────────────────┐
                 │        GOUVERNER        │
                 │  stratégie, rôles,      │
                 │  décisions de risque,   │
                 │  supervision            │
                 └───────────┬─────────────┘
                             │ éclaire & englobe
   ┌──────────┬──────────┬───┴───────┬───────────┬──────────┐
   ▼          ▼          ▼           ▼           ▼
IDENTIFIER  PROTÉGER   DÉTECTER    RÉPONDRE    RÉTABLIR
savoir ce   le         trouver les agir sur    rétablir
qu'on a     sauvegarder problèmes  les incid.  les activités
```
:::

| Fonction | Question centrale | Exemples de résultats |
|---|---|---|
| **Gouverner (GV)** | Comment décidons-nous, supervisons-nous et rendons-nous des comptes ? | Stratégie, politique, rôles, appétence au risque, gouvernance de la chaîne d'approvisionnement |
| **Identifier (ID)** | Qu'avons-nous et quels sont les risques qui pèsent dessus ? | Inventaire des actifs, appréciation des risques, environnement métier |
| **Protéger (PR)** | Comment sauvegardons-nous les actifs ? | Contrôle d'accès, sensibilisation, sécurité des données, maintenance |
| **Détecter (DE)** | Comment découvrons-nous qu'une anomalie survient ? | Surveillance continue, analyse d'événements, détections du SOC |
| **Répondre (RS)** | Que faisons-nous pendant un incident ? | Planification de la réponse, analyse, atténuation, communication |
| **Rétablir (RC)** | Comment rétablissons-nous et apprenons-nous ? | Planification du rétablissement, restauration, retours d'expérience |

### Pourquoi Gouverner a été ajoutée

Dans le CSF 1.1, les idées de gouvernance étaient dispersées dans Identifier. La version 2.0 les a promues en une Fonction à part entière car la mise à jour de 2024 a reconnu ce que le module 1 de ce parcours enseigne : **les cinq autres Fonctions ne fonctionnent que si quelqu'un fixe la stratégie, attribue les rôles et décide de l'appétence au risque.** Gouverner ne remplace pas les autres — elle les *englobe*, insufflant une direction d'Identifier à Rétablir et consommant leurs résultats pour s'ajuster. Elle inclut explicitement la **gestion des risques de la chaîne d'approvisionnement en cybersécurité**, reflétant la part de risque qui transite désormais par les tiers.

:::tip Rattachez les incidents aux Fonctions
Une habitude utile : à mesure que des événements surviennent, rattachez-les aux Fonctions. Un actif oublié dans une brèche est une lacune d'*Identifier* ; un attaquant tapi indétecté pendant des semaines est une lacune de *Détecter* ; une réponse chaotique est une lacune de *Répondre*. Avec le temps, cela révèle quelle Fonction a le plus besoin d'investissement — des preuves sur lesquelles un profil cible peut agir.
:::

## Le Noyau : Fonctions → Catégories → Sous-catégories

Chaque Fonction se décline en **Catégories**, et chaque Catégorie en **Sous-catégories** — les énoncés précis, axés sur les résultats, face auxquels vous vous évaluez réellement.

```text
FONCTION      Protéger (PR)
  CATÉGORIE     Gestion des identités, authentification et contrôle d'accès (PR.AA)
    SOUS-CAT.     PR.AA-01 : les identités et identifiants sont gérés
    SOUS-CAT.     PR.AA-03 : les utilisateurs et appareils sont authentifiés ...
```

- Une **Fonction** est le regroupement le plus large (Protéger).
- Une **Catégorie** est un groupe de résultats liés en son sein (contrôle d'accès).
- Une **Sous-catégorie** est un résultat unique et précis que vous pouvez évaluer : « l'atteignons-nous, et dans quelle mesure ? »

Le CSF renvoie aussi à des **références informatives** — des correspondances montrant quelles mesures d'autres normes (ISO/IEC 27001, NIST SP 800-53) aident à atteindre chaque Sous-catégorie. C'est ainsi que le CSF se connecte aux ensembles de mesures certifiables des modules suivants : le CSF cadre le résultat, 27001/800-53 fournissent les mesures, et votre registre des risques fixe la priorité.

## Profils : du référentiel au plan

Un **profil** est la façon dont vous appliquez le Noyau à *votre* organisation. Vous en créez deux :

- **Profil actuel** — les résultats que vous atteignez *aujourd'hui*. Évaluez honnêtement chaque Sous-catégorie pertinente (ou Catégorie, pour commencer) et consignez votre état actuel.
- **Profil cible** — les résultats que vous *voulez* atteindre, guidés par vos objectifs métier, votre appétence au risque et vos obligations.

L'écart entre les deux est une **analyse d'écart**, et les écarts priorisés deviennent votre feuille de route d'amélioration :

:::diagram Du profil actuel au profil cible via une feuille de route priorisée
```text
  PROFIL ACTUEL        ──analyse d'écart──►      PROFIL CIBLE
  où nous en sommes                              où nous devons être
         │                                              │
         └──────────► feuille de route priorisée ◄──────┘
             (classée par risque + appétence + coût)
```
:::

Point crucial : vous ne visez **pas** le maximum partout. Le profil cible est façonné par le risque : un petit détaillant peut viser un Protéger et un Rétablir solides tout en acceptant une maturité modeste en Détecter, parce que cela correspond à son appétence au risque et à son budget. C'est exactement là que le registre des risques du module précédent intervient — il indique *quels* écarts comptent le plus.

:::scenario Un profil cible à un an
Une entreprise de taille moyenne évalue son profil actuel et constate que Détecter est sa Fonction la plus faible : pas de collecte centralisée de journaux, alertes examinées au cas par cas. Son registre des risques classe le rançongiciel comme risque critique, et la détection précoce est le moyen le moins coûteux d'en réduire l'impact. Son profil cible rehausse donc d'abord Détecter — journalisation centralisée et processus de surveillance défini dans l'année — tout en maintenant Protéger. Le référentiel a structuré l'évaluation ; le registre a fixé la priorité ; le profil a transformé les deux en un plan approuvé par le conseil.
:::

## Niveaux de mise en œuvre

Les **niveaux** décrivent la rigueur et la capacité d'adaptation de vos *pratiques de gestion des risques* de cybersécurité, du niveau 1 au niveau 4 :

| Niveau | Nom | Caractéristique |
|---|---|---|
| 1 | Partiel | Au cas par cas, réactif ; risque géré de façon incohérente |
| 2 | Informé par le risque | Pratiques de risque approuvées mais non généralisées |
| 3 | Reproductible | Politiques formelles, appliquées avec constance et mises à jour |
| 4 | Adaptatif | Amélioration continue ; culture informée par le risque ; s'adapte au changement |

Deux mises en garde :

1. **Les niveaux ne sont pas des grades de maturité à maximiser.** Ils décrivent votre gestion du risque, et le bon niveau dépend de votre risque et de vos ressources. Une petite structure opérant bien au niveau 2 peut faire un choix pertinent ; imposer le niveau 4 partout peut gaspiller de l'argent.
2. **Les niveaux décrivent la *pratique*, les profils décrivent les *résultats*.** Une phrase pour le conseil combine souvent les deux : « Nous sommes au niveau 2 aujourd'hui avec de faibles résultats en Détecter ; notre cible est le niveau 3 avec Détecter rehaussé, dans un an. »

## Erreurs courantes

- **Traiter le CSF comme une liste réussite/échec.** C'est une structure d'évaluation et de communication, pas une certification.
- **Viser le maximum partout.** Sans profil cible guidé par le risque, on surinvestit dans des domaines à faible risque.
- **Sauter le profil actuel.** On ne peut planifier l'amélioration sans une base de référence honnête.
- **Confondre niveaux et profils.** Niveaux = comment vous gérez le risque ; profils = quels résultats vous atteignez.
- **Ignorer Gouverner.** Faire Identifier–Rétablir sans stratégie, rôles ni appétence au risque est précisément la lacune que le CSF 2.0 a été reconçu pour combler.

## Implications pour la sécurité

- Utilisez le CSF pour vous assurer qu'aucune Fonction n'est négligée, puis votre registre des risques pour décider de l'ordre des travaux.
- Construisez un profil actuel et un profil cible ; l'écart entre eux, classé par risque, est votre feuille de route.
- Fixez les profils cibles et les niveaux à votre appétence au risque — plus haut n'est pas automatiquement mieux.
- Appuyez-vous sur les références informatives pour relier les résultats du CSF aux mesures concrètes que vous mettrez en œuvre puis prouverez.

## Vérification des connaissances

:::check Questions
1. Nommez les six Fonctions du CSF 2.0.
2. Quelle Fonction est nouvelle en 2.0, et pourquoi a-t-elle été ajoutée ?
3. Quel est le lien entre un profil actuel, un profil cible et une analyse d'écart ?
4. Les niveaux de mise en œuvre mesurent-ils les résultats atteints ou la façon dont vous gérez le risque ?
:::

:::solution Réponses
1. **Gouverner, Identifier, Protéger, Détecter, Répondre, Rétablir.**
2. **Gouverner** est nouvelle. Elle a été ajoutée parce que les cinq autres Fonctions dépendent d'une stratégie, de rôles, d'une supervision et de décisions d'appétence au risque (et d'une gouvernance de la chaîne d'approvisionnement) auparavant dispersés et sous-estimés ; 2.0 fait de la gouvernance une Fonction explicite et centrale.
3. Le **profil actuel** est votre point de départ ; le **profil cible** est votre destination ; l'**analyse d'écart** est la différence entre les deux, qui, priorisée par le risque, devient la feuille de route d'amélioration.
4. **La façon dont vous gérez le risque** (vos pratiques de gestion des risques), de Partiel (1) à Adaptatif (4). Les résultats sont décrits par les profils, non par les niveaux.
:::

## Exercice

Pour une organisation de votre choix :

1. Pour chacune des six Fonctions, rédigez une phrase décrivant un état **actuel** plausible.
2. Choisissez les **deux** Fonctions que vous prioriseriez d'abord, et justifiez le choix par le risque (reprenez le type de raisonnement de votre registre des risques).
3. Énoncez un **niveau cible** réaliste pour l'organisation et expliquez pourquoi un niveau supérieur ne serait pas nécessairement meilleur.

:::solution Réponse type (abrégée)
1. p. ex. *Gouverner :* « Une politique de sécurité existe mais l'appétence au risque n'est pas écrite. » *Identifier :* « Inventaire partiel des actifs ; appréciation annuelle des risques. » *Protéger :* « MFA sur la messagerie ; correctifs incohérents. » *Détecter :* « Pas de journalisation centralisée ; alertes au cas par cas. » *Répondre :* « Un plan de réponse existe mais n'est pas testé. » *Rétablir :* « Des sauvegardes existent mais la restauration n'est jamais répétée. »
2. Prioriser **Détecter** et **Rétablir** : le registre classe le rançongiciel comme critique, la détection précoce réduit son impact à faible coût, et un rétablissement répété limite l'indisponibilité — ensemble, ils réduisent le risque résiduel le plus élevé de la façon la plus économique.
3. **Niveau cible 3** (Reproductible) : des pratiques formelles et appliquées avec constance conviennent à la taille et au risque. Le niveau 4 (Adaptatif) exigerait une machinerie d'amélioration continue dont le coût n'est pas justifié par l'appétence au risque de l'organisation ; « plus haut » signifierait donc surinvestir plutôt que mieux sécuriser.
:::

## Résumé et étapes suivantes

- Le CSF 2.0 organise la cybersécurité en six Fonctions — **Gouverner, Identifier, Protéger, Détecter, Répondre, Rétablir** — Gouverner englobant les autres.
- Le Noyau descend des Fonctions aux Catégories puis aux Sous-catégories évaluables, reliées par des références informatives à des mesures concrètes.
- Les profils actuel et cible, plus une analyse d'écart classée par risque, transforment le référentiel en feuille de route priorisée.
- Les niveaux de mise en œuvre décrivent votre gestion du risque ; fixez-les, ainsi que vos cibles, à votre appétence au risque.

Le module suivant, **ISO/IEC 27001**, passe d'un référentiel de résultats à un système de management certifiable, et montre comment les mesures qui satisfont les résultats du CSF sont sélectionnées, documentées dans une déclaration d'applicabilité, et auditées.

===assignment===
Produisez un **résumé de profil CSF 2.0** d'une page pour une organisation de votre choix : un bref énoncé de profil actuel pour chacune des six Fonctions, un profil cible qui rehausse deux Fonctions (justifié par le risque), un niveau de mise en œuvre actuel et cible choisi avec une justification d'un paragraphe, et une feuille de route priorisée de trois éléments issue de vos écarts. Référez au moins deux entrées de votre registre des risques pour justifier les priorités.

===criteria===
- Terminez la lecture, la vérification des connaissances et l'exercice.
- Votre profil cible doit être guidé par le risque (et non « le maximum partout ») et votre feuille de route doit remonter à des écarts et risques précis.
- Marquez la leçon comme terminée. Le quiz du module s'appuie sur cette leçon.

===references===
- NIST, *The NIST Cybersecurity Framework (CSF) 2.0* (NIST CSWP 29, 2024) — https://doi.org/10.6028/NIST.CSWP.29
- NIST, *Cybersecurity Framework* — page d'accueil et guides de démarrage rapide — https://www.nist.gov/cyberframework
- NIST, *CSF 2.0 Reference Tool et références informatives* — https://csrc.nist.gov/projects/cybersecurity-framework/filters
- NIST SP 800-53 Rev. 5, *Security and Privacy Controls* (une référence informative courante) — https://csrc.nist.gov/pubs/sp/800/53/r5/upd1/final
