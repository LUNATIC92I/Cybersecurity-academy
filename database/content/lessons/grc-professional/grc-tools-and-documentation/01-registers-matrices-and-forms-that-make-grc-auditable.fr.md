---
title: Outils et documentation GRC — Les registres, matrices et formulaires qui rendent la GRC auditable
chapter: Outils et documentation GRC
summary: Rassembler tout le parcours à travers les documents que les professionnels tiennent réellement — les registres d'actifs et de risques, la matrice de contrôles et la déclaration d'applicabilité, le tableau de bord de conformité, et les formulaires d'acceptation des risques, de dérogation et d'action corrective qui transforment les décisions en preuves auditables.
duration: 55
level: intermediate
preview: true
status: published
objectives:
- Expliquer pourquoi la GRC dépend d'une bonne documentation et d'une source unique de vérité
- Tenir les registres fondamentaux — actifs, risques — et savoir comment ils se relient
- Construire une matrice de contrôles et la relier à la déclaration d'applicabilité
- Utiliser correctement les formulaires d'acceptation des risques, de dérogation et d'action corrective
- Lire un tableau de bord de conformité et tenir un référentiel de preuves d'audit
prerequisites:
- Gestion des risques tiers — Évaluer et surveiller les fournisseurs
---

## Introduction

Chaque module précédent a produit une décision : un risque coté, une mesure sélectionnée, une exclusion justifiée, un risque résiduel accepté, un fournisseur hiérarchisé. La GRC ne fonctionne que si ces décisions sont *écrites, reliées et tenues à jour* — car une décision non documentée ne peut être ni revue, ni défendue devant un auditeur, ni reprise par la personne suivante. Ce module est le tissu conjonctif : les registres, matrices, tableaux de bord et formulaires qui rendent tout le programme **auditable**.

Le fil conducteur est la **traçabilité**. Un auditeur doit pouvoir tirer n'importe quel fil — une mesure, un constat, un risque accepté — et le remonter jusqu'à une raison documentée et un propriétaire nommé. Une bonne documentation GRC n'est pas de la bureaucratie pour elle-même ; c'est la preuve que les décisions que vous avez appris à prendre ont réellement eu lieu.

## Une source unique de vérité

Le plus grand échec documentaire est la *duplication* : le même actif listé différemment dans trois tableurs, un risque clos à un endroit et ouvert à un autre. Visez **un enregistrement faisant autorité par objet**, référencé par un identifiant stable, auquel tout le reste renvoie au lieu de le recopier.

```text
            ┌──────────────────┐
            │ REGISTRE ACTIFS  │  (ce que nous avons)
            └────────┬─────────┘
                     │ les actifs sont menacés par les risques
            ┌────────▼─────────┐
            │ REGISTRE RISQUES │  (ce qui peut mal tourner) ◄── apports
            └────────┬─────────┘                               fournisseurs & vie privée
                     │ les risques sont réduits par les mesures
         ┌───────────▼────────────┐
         │ MATRICE CONTRÔLES / DdA │  (ce que nous faisons)
         └───────────┬────────────┘
                     │ les écarts/défaillances ouvrent...
     ┌───────────────▼────────────────┐
     │ CONSTATS / ACTION CORRECTIVE    │  (ce que nous corrigeons) + décisions
     │ + formulaires de DÉROGATION     │     d'ACCEPTATION DES RISQUES
     └───────────────┬────────────────┘
                     │ consolidés dans...
            ┌────────▼──────────┐
            │ TABLEAU DE BORD   │  (où nous en sommes, pour la direction)
            │ DE CONFORMITÉ     │
            └───────────────────┘
```

Chaque couche référence celle du dessus par identifiant. Cette chaîne unique est ce qui permet à un auditeur — ou à un nouveau membre de l'équipe — de comprendre tout le programme sans connaissance tacite.

## Les registres fondamentaux

### Registre des actifs

On ne peut apprécier le risque pesant sur des actifs que l'on n'a pas listés (la leçon du tout premier module SOC). Un **registre des actifs** consigne chaque actif de valeur, son **propriétaire**, sa **classification** (du module vie privée), et ce qui en dépend. C'est le socle sur lequel repose le registre des risques : les risques menacent des actifs, donc des actifs inconnus signifient des risques invisibles.

### Registre des risques

Le **registre des risques** (construit au module de gestion des risques) est le document vivant central de la GRC : chaque risque avec un énoncé de cause à effet, vraisemblance × impact, scores inhérent et résiduel, traitement, propriétaire et date de revue. Tout le reste y renvoie — une mesure existe pour réduire un risque du registre ; un risque accepté est une ligne du registre validée ; un constat fournisseur devient une entrée du registre.

:::tip Les identifiants stables rendent la traçabilité possible
Donnez à chaque enregistrement un identifiant stable — `A-012` pour un actif, `R-001` pour un risque, `A.8.24` pour une mesure, `F-2026-003` pour un constat — et référencez par identifiant, jamais en recopiant la description. Quand un auditeur demande « montrez-moi la mesure qui traite R-001 », un ensemble lié par identifiant répond en quelques secondes ; une pile de tableurs non reliés répond en quelques jours.
:::

## La matrice de contrôles et la déclaration d'applicabilité

Une **matrice de contrôles** relie les mesures à ce qu'elles satisfont : les risques qu'elles réduisent (identifiants du registre des risques), les résultats de référentiel qu'elles atteignent (p. ex. sous-catégories du NIST CSF), les obligations qu'elles acquittent, ainsi que le propriétaire et le statut de la mesure. C'est l'outil pratique pour répondre à « qu'est-ce qui couvre cette exigence ? » et « si cette mesure défaillait, qu'est-ce qui serait exposé ? »

La **déclaration d'applicabilité** (du module ISO 27001) est une matrice de contrôles formelle et spécialisée pour un SMSI : chaque mesure de l'Annexe A, applicable ou non, avec justification et statut. En pratique, beaucoup d'organisations tiennent une seule matrice de contrôles et considèrent la DdA comme une vue définie de celle-ci. Dans les deux cas, la règle est la même qu'avant : **chaque mesure remonte à un risque, chaque exclusion est justifiée, et le statut correspond à la réalité.**

## Les formulaires de décision

Trois enregistrements courts et formels transforment des décisions ponctuelles en gouvernance auditable. Chacun existe pour qu'une décision soit explicite, détenue, limitée dans le temps et réexaminable — jamais silencieuse.

| Formulaire | Ce qu'il capte | Champs clés |
|---|---|---|
| **Formulaire d'acceptation des risques** | Une décision d'*accepter* un risque résiduel dans l'appétence | ID du risque, score résiduel, justification, **propriétaire acceptant**, date d'expiration/revue |
| **Formulaire de dérogation (waiver)** | Un écart temporaire et approuvé à une politique ou une norme | Quelle règle, pourquoi, mesures compensatoires, **approbateur**, **date d'expiration** |
| **Enregistrement d'action corrective** | La correction d'un constat ou d'une non-conformité | ID du constat, cause racine, action, propriétaire, échéance, preuve de clôture |

Deux principes les rendent dignes de confiance :

1. **Tout expire.** Un risque accepté ou une dérogation à une politique sans expiration devient un trou permanent que personne ne réexamine. Une date force une nouvelle revue.
2. **L'approbateur est à la hauteur de l'enjeu.** Accepter un risque critique ou déroger à une mesure fondamentale est une décision de direction (appétence au risque, module 1), non une validation discrète par celui que cela dérangeait.

:::warning Une dérogation sans expiration est une politique discrètement abandonnée
« Nous accordons une dérogation temporaire à la norme MFA pour cette application héritée » est raisonnable — *avec* des mesures compensatoires et une expiration. Sans expiration, ce n'est pas une dérogation ; c'est une décision tacite d'abandonner la norme, découverte des années plus tard lors d'un audit ou d'une violation. Chaque dérogation et chaque acceptation de risque porte une date de revue, et quelque chose doit porter cette date à l'attention de quelqu'un.
:::

## Le tableau de bord de conformité

La direction ne peut lire les registres ligne par ligne ; un **tableau de bord de conformité** consolide donc le détail en signal : couverture et santé des mesures, constats ouverts de forte gravité et leur ancienneté, actions correctives en retard, dérogations et acceptations de risques qui expirent, couverture des référentiels/obligations, et indicateurs clés de risque (du module risque) dans le temps.

Un bon tableau de bord répond aux vraies questions d'un dirigeant — *Nos risques critiques sont-ils dans l'appétence ? Qu'est-ce qui est en retard ? Quelles décisions m'incombent ?* — et renvoie à la revue de direction (article 9.3 de l'ISO 27001). Il rend compte du statut honnêtement : un tableau de bord toujours vert parce que les éléments rouges sont discrètement retirés est pire que rien, car il fabrique une fausse assurance — l'échec du théâtre de la conformité du module 1.

## Le référentiel de preuves d'audit

Quand un auditeur ou l'évaluateur d'un client arrive, vous ne devriez pas courir partout. Un **référentiel de preuves d'audit** organise les preuves du fonctionnement des mesures (du module des mesures) — enregistrements de revues d'accès, exports de configuration, journaux de réalisation des formations, rapports de tests d'intrusion, comptes rendus de réunion — indexés sur les mesures et constats qu'ils appuient. Les mêmes preuves servent l'audit interne, la certification externe, les questionnaires de diligence des clients (votre côté du module risque tiers) et les régulateurs. Collectez-les comme sous-produit du fonctionnement des mesures, non dans une panique annuelle.

## Erreurs courantes

- **De nombreuses copies, aucune source de vérité.** Le même actif ou risque consigné différemment à plusieurs endroits ; rien ne se réconcilie.
- **Des documents sans identifiants ni liens.** Sans identifiants stables ni références, la traçabilité — le but même — est impossible.
- **Une déclaration d'applicabilité ou une matrice qui dérive de la réalité.** Un statut qui ne correspond plus à ce qui fonctionne échoue au prochain audit.
- **Des acceptations de risques et dérogations sans expiration ni approbateur senior.** Des trous permanents que personne ne réexamine et que personne n'a détenus.
- **Un tableau de bord vert par omission.** Masquer les éléments rouges fabrique une fausse assurance.
- **Des preuves rassemblées seulement au moment de l'audit.** Si les mesures ne produisent pas de preuves en fonctionnant, vous courez toujours.

## Implications pour la sécurité

- Tenez un enregistrement faisant autorité par actif, risque, mesure et constat, reliés par des identifiants stables.
- Faites que chaque mesure de la matrice/DdA remonte à un risque, et gardez le statut aligné sur la réalité.
- Exigez une expiration et un approbateur à la hauteur de l'enjeu sur chaque acceptation de risque et dérogation.
- Rendez compte de la conformité honnêtement et tenez un référentiel de preuves indexé, alimenté par les mesures au fil de leur fonctionnement.

## Vérification des connaissances

:::check Questions
1. Pourquoi « une source unique de vérité » avec des identifiants stables est-elle si importante dans la documentation GRC ?
2. À quoi une matrice de contrôles relie-t-elle une mesure ?
3. Quels deux champs chaque formulaire d'acceptation de risque et de dérogation doit-il comporter pour rester digne de confiance ?
4. Pourquoi un tableau de bord « toujours vert » peut-il être pire que pas de tableau de bord du tout ?
:::

:::solution Réponses
1. Parce que la valeur de la GRC est la **traçabilité** — pouvoir remonter n'importe quel élément (mesure, constat, risque accepté) jusqu'à une raison documentée et un propriétaire. Des enregistrements dupliqués qui se contredisent détruisent cela, tandis qu'un enregistrement faisant autorité par objet, référencé par identifiant stable, permet à quiconque (auditeur compris) de reconstituer tout le programme sans connaissance tacite.
2. Les éléments que la mesure **satisfait** : le(s) risque(s) qu'elle réduit (identifiants du registre des risques), les résultats/obligations de référentiel qu'elle atteint, ainsi que son propriétaire et son statut — afin de répondre à « qu'est-ce qui couvre cette exigence ? » et « qu'est-ce qui est exposé si cette mesure défaille ? »
3. Une **date d'expiration/revue** et un **approbateur à la hauteur de l'enjeu (propriétaire acceptant)**. Sans expiration, la décision devient un trou permanent non réexaminé ; sans le bon approbateur, un risque important est accepté par quelqu'un qui n'en est pas comptable.
4. Parce que s'il est vert seulement parce que les éléments rouges sont discrètement retirés ou jamais consignés, il **fabrique une fausse assurance** — la direction croit le programme sain alors qu'il ne l'est pas. C'est pire que pas de tableau de bord, qui au moins n'induit pas activement en erreur.
:::

## Exercice

Pour une organisation de votre choix, assemblez une **carte de documentation GRC** d'une page :

1. Listez les **enregistrements fondamentaux** que vous tiendriez (registre des actifs, registre des risques, matrice de contrôles/DdA, constats/actions correctives, tableau de bord, référentiel de preuves) et le **schéma d'identifiants stables** de chacun.
2. Prenez un risque de votre registre et montrez la **traçabilité** : ID du risque → mesure(s) qui le traite(nt) → où vivent ses preuves → la métrique du tableau de bord dans laquelle il se consolide.
3. Rédigez un **formulaire d'acceptation de risque** pour un risque résiduel, avec un propriétaire acceptant et une expiration.
4. Rédigez un **formulaire de dérogation** pour un écart temporaire à une norme, avec mesures compensatoires, approbateur et expiration.

:::solution Réponse type (abrégée)
1. Registre des actifs (`A-###`), registre des risques (`R-###`), matrice de contrôles / DdA (réfs Annexe A comme `A.8.24` plus `C-###` locaux), constats/actions correctives (`F-AAAA-###`), un tableau de bord de conformité, et un référentiel de preuves indexé par ID de mesure.
2. *Traçabilité pour R-001 (BD clients, pas de chiffrement) :* R-001 → mesure **A.8.24 utilisation de la cryptographie** (la traite) → preuve : export de configuration du chiffrement et procédure de gestion des clés dans le référentiel de preuves → métrique du tableau de bord : « % des stockages de données restreintes chiffrés » et « risques critiques dans l'appétence ».
3. *Acceptation de risque :* « R-017, résiduel 6 (faible) ; accepté car le coût d'un traitement supplémentaire dépasse le bénéfice à ce niveau résiduel ; accepté par le responsable de l'ingénierie ; revue le 2026-12-31. »
4. *Dérogation :* « L'application de reporting héritée ne peut pas encore imposer la MFA (écart à la norme d'authentification) ; mesures compensatoires : liste d'autorisation d'IP et revue quotidienne des journaux d'accès ; approuvée par le RSSI ; expire le 2026-06-30, à remédier ou réexaminer d'ici là. »
:::

## Résumé et étapes suivantes

- La GRC vit de la documentation : les décisions doivent être écrites, reliées par des identifiants stables et tenues à jour pour être auditables.
- La chaîne fondamentale est **registre des actifs → registre des risques → matrice de contrôles/DdA → constats & action corrective (avec formulaires d'acceptation des risques et de dérogation) → tableau de bord de conformité**, le tout alimenté par un **référentiel de preuves**.
- Chaque mesure remonte à un risque ; chaque acceptation et dérogation a une expiration et un approbateur à la hauteur de l'enjeu.
- Rendez compte honnêtement et laissez les mesures produire leurs propres preuves, pour que les audits confirment plutôt qu'ils ne surprennent.

Le module final, le **Projet de synthèse GRC**, met tout cela en pratique : une évaluation organisationnelle simulée de bout en bout qui produit exactement ces artefacts — un inventaire des actifs, un registre des risques, une correspondance des mesures, une analyse d'écart, une feuille de route de remédiation et un rapport pour la direction.

===assignment===
Construisez un ensemble documentaire restreint mais entièrement **relié** pour une organisation de votre choix : un registre des actifs (≥4 actifs avec identifiants et propriétaires), un registre des risques (≥4 risques avec identifiants référençant ces actifs), une matrice de contrôles reliant des mesures à ces identifiants de risques avec statut, et au moins un formulaire d'acceptation de risque et un formulaire de dérogation (chacun avec approbateur et expiration). Terminez par une brève description des **cinq métriques de tableau de bord** que vous présenteriez à la direction et à partir de quels enregistrements chacune est calculée.

===criteria===
- Terminez la lecture, la vérification des connaissances et l'exercice.
- Les enregistrements doivent utiliser des identifiants stables et se référencer mutuellement (pas de duplication) ; chaque mesure doit remonter à un risque ; et chaque acceptation/dérogation doit avoir un approbateur et une expiration.
- Marquez la leçon comme terminée. Le quiz du module s'appuie sur cette leçon.

===references===
- ISO/IEC 27001:2022, article 7.5 (informations documentées) et la déclaration d'applicabilité — https://www.iso.org/standard/27001
- ISO/IEC 27004:2016, *Surveillance, mesurage, analyse et évaluation* (indicateurs pour un SMSI) — https://www.iso.org/standard/64120.html
- NIST SP 800-53 Rev. 5, documentation des mesures et artefacts d'évaluation — https://csrc.nist.gov/pubs/sp/800/53/r5/upd1/final
- The Institute of Internal Auditors, recommandations sur les dossiers de travail et les preuves — https://www.theiia.org/
