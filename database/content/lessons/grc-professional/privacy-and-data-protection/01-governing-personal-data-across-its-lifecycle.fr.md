---
title: Vie privée et protection des données — Gouverner les données personnelles tout au long de leur cycle de vie
chapter: Vie privée et protection des données
summary: Apprendre à gouverner les données personnelles de la collecte à la suppression — les classifier, appliquer la protection dès la conception et la minimisation, fixer la conservation, gérer les transferts internationaux et répondre à une violation de données personnelles — à l'aide de principes communs au droit moderne de la protection des données.
duration: 55
level: intermediate
preview: true
status: published
objectives:
- Distinguer la vie privée de la sécurité et expliquer pourquoi les deux sont nécessaires
- Appliquer les principes fondamentaux de protection des données (licéité, minimisation, limitation des finalités, exactitude, limitation de la conservation, intégrité)
- Classifier les données personnelles et les cartographier tout au long de leur cycle de vie
- Appliquer la protection dès la conception et fixer des durées de conservation défendables
- Décrire les obligations liées à une violation de données personnelles et aux transferts internationaux
prerequisites:
- Mesures de sécurité — Conception, tests et preuves
---

## Introduction

La sécurité demande « ces données sont-elles protégées contre les accès non autorisés ? » La vie privée pose une question plus large : « *devrions-nous* détenir ces données personnelles, pour quelle finalité, pendant combien de temps, et quels sont les droits de la personne qu'elles décrivent ? » On peut être parfaitement sécurisé et pourtant violer la vie privée — en collectant plus que nécessaire, en conservant indéfiniment, ou en utilisant les données à des fins que la personne n'a jamais acceptées.

Cette leçon traite les données personnelles comme un objet à **gouverner tout au long de leur cycle de vie**, à l'aide de principes récurrents dans les réglementations modernes. Elle est pédagogique et neutre quant à la juridiction : elle enseigne les concepts, non un conseil juridique pour un pays précis.

:::warning Ceci est pédagogique, non un conseil juridique
Le droit de la vie privée varie selon la juridiction et évolue dans le temps. Les principes présentés sont largement partagés, mais les obligations précises — bases légales, délais de notification de violation, mécanismes de transfert — dépendent des lois qui vous sont applicables. Pour de vraies décisions de conformité, consultez votre délégué à la protection des données ou un conseil qualifié.
:::

## Vie privée et sécurité

| | Sécurité | Vie privée |
|---|---|---|
| Question centrale | Les données sont-elles protégées contre l'accès, la modification ou la perte non autorisés ? | Devrions-nous détenir ces données personnelles, pour quelle finalité, et quels droits a la personne concernée ? |
| Préoccupation | Confidentialité, intégrité, disponibilité | Usage licite, loyal, transparent, minimal et finalisé des données personnelles |
| Exemple d'échec | Une base de données est compromise par un attaquant | Une entreprise conserve des CV pendant 10 ans et les revend à des annonceurs |

La sécurité est **nécessaire** à la vie privée — on ne peut tenir une promesse sur des données personnelles qu'on ne sait pas protéger — mais elle n'est **pas suffisante**. Les bons programmes mènent les deux de front : la vie privée décide *quoi et pourquoi*, la sécurité impose *comment c'est protégé*.

### Termes clés

- **Donnée personnelle** — toute information se rapportant à une personne identifiée ou identifiable (un nom, un e-mail, une adresse IP, un identifiant d'appareil, un numéro de compte).
- **Donnée de catégorie particulière / sensible** — donnée nécessitant une protection renforcée (santé, biométrie, religion, origine, orientation sexuelle, etc.).
- **Personne concernée** — la personne à qui se rapportent les données.
- **Responsable du traitement** — la partie qui décide *pourquoi et comment* les données personnelles sont traitées (elle en est comptable).
- **Sous-traitant** — une partie qui traite les données *pour le compte du responsable* (p. ex. un fournisseur cloud).

La distinction responsable/sous-traitant importe : le responsable porte la responsabilité première et doit lier les sous-traitants par contrat à n'agir que sur instructions — ce qui rejoint directement le module des risques tiers.

## Principes fondamentaux de protection des données

La plupart des régimes de vie privée partagent un ensemble reconnaissable de principes. Apprenez-les et vous pourrez raisonner sur presque n'importe quelle exigence :

| Principe | Signification |
|---|---|
| **Licéité, loyauté, transparence** | Avoir un motif valable de traiter ; être honnête et clair avec les personnes à ce sujet |
| **Limitation des finalités** | Collecter pour des finalités déterminées ; ne pas réutiliser de façon incompatible |
| **Minimisation des données** | Ne collecter que ce dont vous avez réellement besoin pour la finalité |
| **Exactitude** | Tenir les données correctes et à jour ; corriger ou effacer ce qui est erroné |
| **Limitation de la conservation** | Ne conserver qu'aussi longtemps que nécessaire, puis supprimer ou anonymiser |
| **Intégrité et confidentialité** | Protéger les données par une sécurité appropriée (le lien avec vos mesures) |
| **Responsabilité (accountability)** | Pouvoir *démontrer* le respect de tout ce qui précède |

:::tip La minimisation est la mesure de vie privée la moins chère
Les données que vous ne collectez jamais ne peuvent être ni compromises, ni détournées, ni mal revendues, ni réquisitionnées. Avant d'ajouter un champ à un formulaire ou une colonne à une table, demandez « quelle finalité l'exige, et quelle en est la plus petite version ? » Une *année* de naissance peut suffire là où une date complète n'est pas nécessaire. La minimisation réduit à la fois le risque de vie privée *et* le risque de sécurité.
:::

## Classifier les données personnelles

On ne peut protéger ou gouverner des données qu'on n'a pas catégorisées. Un schéma simple et répandu :

```text
 PUBLIC         Destiné à tous (marketing publié)
 INTERNE        Données métier ordinaires, pas pour l'extérieur
 CONFIDENTIEL   Données personnelles ; accès restreint, besoin d'en connaître
 RESTREINT      Catégorie particulière / très sensible ; mesures les plus fortes
```

La classification guide le choix des mesures : les données restreintes justifient chiffrement, contrôle d'accès strict et journalisation supplémentaire ; les données internes en demandent bien moins. La classification des données *personnelles* devrait aussi consigner leur **finalité**, leur **base légale** (le cas échéant) et leur **durée de conservation** — pour que le cycle de vie soit gouverné, non deviné.

## Le cycle de vie des données

Gouvernez les données personnelles à chaque étape, pas seulement lorsqu'elles sont stockées :

```text
 COLLECTER ──► UTILISER ──► STOCKER ──► PARTAGER / TRANSFÉRER ──► CONSERVER ──► SUPPRIMER
     │            │            │                │                     │             │
 minimiser    finalité     sécurisé &      contrats &            calendrier de  supprimer ou
 & informer   limitée      classifié       règles de transfert   conservation   anonymiser
```

- **Collecter** — recueillir le minimum, avec une base légale, et informer les personnes (une notice de confidentialité).
- **Utiliser** — uniquement pour les finalités déclarées et compatibles.
- **Stocker** — sécurisé et classifié ; accès selon le besoin d'en connaître.
- **Partager / transférer** — uniquement avec des contrats en place et, au-delà des frontières, un mécanisme de transfert valide (ci-dessous).
- **Conserver** — selon un **calendrier de conservation**, non « indéfiniment par défaut ».
- **Supprimer** — supprimer de façon sécurisée ou anonymiser irréversiblement à la fin de la finalité.

### Protection dès la conception et par défaut

La **protection dès la conception** signifie intégrer la vie privée dans les systèmes et processus dès le départ, et non l'ajouter après coup. La **protection par défaut** signifie que les réglages les plus protecteurs s'appliquent sauf choix contraire de l'utilisateur. Exemples concrets : mettre le partage de données d'une nouvelle fonctionnalité à *désactivé* par défaut ; pseudonymiser les analyses ; ne collecter les champs facultatifs que lorsqu'une finalité l'exige ; et mener une **analyse d'impact relative à la protection des données** avant de lancer tout traitement de données personnelles à grande échelle ou sensible.

### Conservation

Une durée de conservation défendable répond à « pourquoi si longtemps ? » par une raison — une obligation légale, un besoin contractuel ou une finalité métier réelle — et non « au cas où ». Consignez la durée dans la classification, automatisez la suppression lorsque c'est possible, et traitez « nous ne supprimons jamais rien » comme un risque de vie privée *et* de stockage. La sur-conservation est l'un des constats les plus fréquents d'un audit de vie privée.

:::scenario Minimisation et conservation ensemble
Une équipe de support journalise chaque conversation client avec des transcriptions complètes conservées indéfiniment « pour la qualité ». Une revue de vie privée pose deux questions : *avons-nous besoin de la transcription complète, ou une catégorie et un résultat suffiraient-ils ?* (minimisation) et *combien de temps la revue qualité l'exige-t-elle réellement ?* (conservation). Résultat : les transcriptions sont pseudonymisées après 90 jours et supprimées après 12 mois, avec une raison documentée. Moins de données, conservées moins longtemps, pour une finalité déclarée — un risque de vie privée moindre et un rayon d'impact de violation plus petit, sans rien coûter à l'objectif réel de qualité.
:::

## Transferts internationaux de données

Déplacer des données personnelles au-delà des frontières — y compris les stocker chez un fournisseur cloud d'un autre pays — déclenche souvent des règles supplémentaires. Le schéma commun aux régimes : un transfert vers une autre juridiction n'est autorisé que si la destination offre une **protection adéquate**, établie par un mécanisme reconnu tel qu'une décision d'adéquation, des clauses contractuelles types, des règles d'entreprise contraignantes, ou le consentement explicite de la personne concernée. Les détails dépendent de la juridiction ; l'habitude de gouvernance est universelle : **savoir où vos données personnelles résident et circulent physiquement, et disposer d'un mécanisme licite pour chaque flux transfrontalier.** C'est pourquoi la classification des données devrait capter la localisation, et pourquoi cela renvoie au risque tiers et cloud.

## Répondre à une violation de données personnelles

Une **violation de données personnelles** est un incident de sécurité entraînant, de manière accidentelle ou illicite, la destruction, la perte, l'altération, la divulgation non autorisée de données personnelles ou l'accès non autorisé à celles-ci. C'est là que la réponse à incident du parcours SOC rencontre le droit de la vie privée. Les obligations propres à la vie privée, communes aux régimes, sont :

1. **Détecter et confiner** — le cycle de réponse à incident que vous connaissez déjà.
2. **Évaluer le risque pour les personnes** — pas seulement pour l'entreprise. Les données de qui, en quelle quantité, de quelle sensibilité, quel préjudice pourrait suivre (fraude, usurpation d'identité, détresse) ?
3. **Notifier le régulateur** lorsque requis, souvent dans un délai court et fixe (dans plusieurs régimes, sans retard injustifié et dans un nombre d'heures déterminé) lorsque la violation risque d'atteindre les droits des personnes.
4. **Notifier les personnes concernées** lorsque le risque pour elles est élevé, en langage clair, avec des conseils pour se protéger.
5. **Consigner** chaque violation — même celles que vous décidez de ne pas notifier — avec les faits et votre raisonnement, comme preuve de responsabilité.

:::warning Connaissez votre horloge avant l'incident, pas pendant
Plusieurs lois de vie privée imposent des délais de notification serrés comptés en heures à partir de la *prise de connaissance*. On n'invente pas le processus au milieu d'un incident. Convenez à l'avance de qui évalue le risque pour les personnes, qui décide de la notification et qui contacte le régulateur — et inscrivez-le dès maintenant dans votre plan de réponse à incident et vos runbooks.
:::

## Erreurs courantes

- **Traiter la vie privée comme un simple problème de sécurité.** Chiffrer des données que vous n'auriez jamais dû collecter ne les rend pas conformes.
- **Collecter « au cas où ».** Chaque champ inutile est un risque permanent de vie privée et de sécurité.
- **Pas de calendrier de conservation.** « Nous gardons tout pour toujours » est un constat, pas une stratégie.
- **Partage sensible en opt-out par défaut.** La protection par défaut veut que les réglages protecteurs l'emportent sauf opt-in de l'utilisateur.
- **Découvrir les règles de transfert après la mise en production.** Sachez où résident les données avant de livrer, pas après.
- **Un plan de violation sans horloge de vie privée.** Manquer un délai de notification transforme un incident de sécurité en incident réglementaire.

## Implications pour la sécurité

- Minimisez d'abord : le plus petit jeu de données est le moins coûteux à protéger et le moins dommageable à perdre.
- Classifiez les données personnelles avec leur finalité, base légale, conservation et localisation, et laissez cela guider les mesures.
- Intégrez la vie privée dès la conception et par défaut ; évaluez l'impact avant de lancer un traitement à haut risque.
- Inscrivez à l'avance l'horloge de violation et les rôles de décision dans votre plan de réponse à incident.

## Vérification des connaissances

:::check Questions
1. En une phrase chacune, en quoi la vie privée et la sécurité diffèrent-elles ?
2. Quel principe dit « ne collecter que ce dont vous avez réellement besoin » ?
3. Quelle est la différence entre un responsable du traitement et un sous-traitant ?
4. Nommez deux obligations propres à la réponse à une violation de *données personnelles* (au-delà du confinement d'incident ordinaire).
:::

:::solution Réponses
1. La **sécurité** demande si les données sont protégées contre l'accès, la modification ou la perte non autorisés ; la **vie privée** demande si vous devriez détenir ces données personnelles, pour quelle finalité et combien de temps, et quels droits a la personne concernée.
2. La **minimisation des données.**
3. Le **responsable du traitement** décide *pourquoi et comment* les données personnelles sont traitées et porte la responsabilité première ; le **sous-traitant** traite les données *pour le compte du responsable* et ne doit agir que sur ses instructions.
4. Deux parmi : **évaluer le risque pour les personnes concernées** (pas seulement l'entreprise) ; **notifier le régulateur** dans le délai requis lorsque le risque est probable ; **notifier les personnes concernées** lorsque leur risque est élevé ; **consigner la violation et votre raisonnement** comme preuve de responsabilité.
:::

## Exercice

Pour un petit service en ligne qui stocke des comptes clients (nom, e-mail, adresse, historique de commandes) et des transcriptions de conversations de support :

1. **Classifiez** chaque donnée (public / interne / confidentiel / restreint) et notez une finalité pour chacune.
2. Identifiez **un champ que vous pourriez cesser de collecter** (minimisation) et justifiez-le.
3. Proposez une **durée de conservation** pour l'historique de commandes et pour les transcriptions, chacune avec une raison d'une ligne.
4. Esquissez les **trois premières étapes** que votre équipe suivrait en découvrant une violation de la base de comptes, y compris celles propres à la vie privée.

:::solution Réponse type (abrégée)
1. Nom/e-mail/adresse → données personnelles **confidentielles** (finalité : exécuter les commandes, contacter le client) ; historique de commandes → **confidentiel** (finalité : support, comptabilité) ; transcriptions → **confidentiel**, voire **restreint** si elles contiennent des détails sensibles (finalité : qualité du support).
2. Cesser de collecter la *date de naissance complète* si elle ne sert qu'à une barrière d'âge — une année de naissance ou un simple indicateur majeur/mineur remplit la finalité avec moins de données.
3. Historique de commandes : **7 ans** — une obligation comptable/fiscale plausible. Transcriptions : **12 mois**, pseudonymisées après 90 jours — assez pour la revue qualité, sans besoin réel au-delà.
4. (a) **Détecter et confiner** — isoler le système touché, préserver les preuves. (b) **Évaluer le risque pour les personnes** — données de qui, quelle quantité, quelle sensibilité, quel préjudice. (c) **Notifier** — informer le régulateur dans le délai requis si le risque est probable, et préparer des notices claires pour les clients concernés si leur risque est élevé ; consigner la violation et le raisonnement dans tous les cas.
:::

## Résumé et étapes suivantes

- La vie privée gouverne le *si, le pourquoi et la durée* de détention des données personnelles ; la sécurité les protège. Les deux sont nécessaires.
- Les principes partagés — licéité, minimisation, limitation des finalités, exactitude, limitation de la conservation, intégrité, responsabilité — permettent de raisonner sur la plupart des obligations.
- Classifiez les données personnelles et gouvernez-les sur tout le cycle de vie, en appliquant la protection dès la conception et par défaut.
- Fixez une conservation défendable, sachez où résident les données pour les transferts transfrontaliers, et pré-intégrez l'horloge de violation dans la réponse à incident.

Les modules suivants — **Continuité d'activité et résilience** et **Gestion des risques tiers** — étendent cette gouvernance au maintien des services et aux fournisseurs (dont les prestataires cloud) qui traitent vos données.

===assignment===
Produisez une **fiche de gouvernance des données personnelles** d'une page pour une organisation de votre choix : un inventaire d'au moins six données personnelles avec, pour chacune, classification, finalité, note de base légale, durée de conservation et localisation de stockage ; deux décisions concrètes de **minimisation** justifiées ; et un court **addendum de réponse aux violations** à votre plan de réponse à incident nommant qui évalue le risque pour les personnes, qui décide de la notification et le délai que vous viserez. Rattachez au moins un élément à un risque de votre registre des risques.

===criteria===
- Terminez la lecture, la vérification des connaissances et l'exercice.
- Chaque donnée personnelle doit avoir une finalité déclarée et une durée de conservation défendable ; l'addendum de violation doit attribuer les rôles et une horloge de notification.
- Marquez la leçon comme terminée. Le quiz du module s'appuie sur cette leçon.

===references===
- OCDE, *Lignes directrices sur la protection de la vie privée* (les principes fondateurs d'information loyale) — https://www.oecd.org/digital/privacy/
- ISO/IEC 27701:2019, *Management des informations relatives à la vie privée (extension de 27001/27002)* — https://www.iso.org/standard/71670.html
- ISO/IEC 29100:2011, *Cadre privé (privacy framework)* — https://www.iso.org/standard/45123.html
- NIST Privacy Framework 1.0 — https://www.nist.gov/privacy-framework
- ENISA, *Lignes directrices et outils sur la protection des données et la vie privée dès la conception* — https://www.enisa.europa.eu/topics/data-protection
