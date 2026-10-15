---
title: Autorisation, périmètre et règles d'engagement
chapter: Le cadre juridique et éthique
summary: Comprenez pourquoi l'autorisation écrite sépare le test d'intrusion du délit, comment définir précisément le périmètre, ce que doivent contenir les règles d'engagement et comment les méthodologies reconnues structurent une mission.
duration: 45
level: beginner
objectives:
- Expliquer pourquoi une autorisation écrite du propriétaire de l'actif est obligatoire avant tout test
- Définir les cibles incluses et exclues, les créneaux de test et les techniques autorisées
- Lister le contenu essentiel d'un document de règles d'engagement
- Comparer PTES, OWASP WSTG, NIST SP 800-115 et OSSTMM en tant que méthodologies
- Décrire les obligations de divulgation responsable et de gestion des preuves
prerequisites:
- Aucun prérequis technique ; cette leçon est le socle du parcours
---

## Introduction

Les techniques utilisées par un pentesteur et par un attaquant criminel peuvent être identiques. Ce qui les sépare, c'est l'**autorisation** : une permission explicite et écrite, accordée par une personne ayant l'autorité juridique pour le faire, pour des systèmes précis, à des moments précis, avec des méthodes précises. Sans elle, balayer, se connecter avec des identifiants découverts ou exploiter une vulnérabilité peut constituer une infraction pénale dans la plupart des juridictions — quelle que soit l'intention.

Cette leçon ouvre le parcours pour une bonne raison. Chaque lab et chaque technique qui suivent supposent que vous opérez dans ce cadre.

:::warning Règle de la plateforme
Les labs de Cybersecurity-Academy ne doivent être réalisés que contre les environnements volontairement vulnérables décrits dans chaque lab, sur une infrastructure qui vous appartient ou que vous êtes explicitement autorisé à utiliser. Ne testez jamais de systèmes tiers, les systèmes de production de votre employeur ou des sites web publics sans autorisation écrite. Consultez la **Politique d'utilisation responsable et de sécurité des labs**.
:::

## Pourquoi l'autorisation est essentielle

Les lois réprimant les atteintes aux systèmes informatiques (par exemple le Computer Fraud and Abuse Act aux États-Unis, le Computer Misuse Act 1990 au Royaume-Uni, ou les articles 323-1 et suivants du Code pénal français) incriminent généralement l'accès ou l'entrave à un système **sans autorisation**. Conséquences pratiques :

- **La personne qui autorise doit avoir autorité sur l'actif.** Un responsable informatique ne peut pas autoriser le test de l'infrastructure d'un fournisseur cloud ; un client ne peut pas autoriser le test d'une application SaaS tierce qu'il se contente d'utiliser.
- **Les fournisseurs cloud et d'hébergement ont leurs propres politiques.** Beaucoup publient des politiques de test d'intrusion précisant ce que les clients peuvent tester sur leurs propres ressources sans préavis, et ce qui est toujours interdit (par exemple les tests de déni de service).
- **L'autorisation doit être précise.** « Testez notre sécurité » ne suffit pas ; le document doit préciser quoi, quand et comment.
- **Sortir du périmètre, c'est un accès non autorisé.** Un testeur qui pivote vers un réseau hors périmètre quitte la protection de l'accord.

## Définir le périmètre

Le périmètre répond à : **qu'est-ce qui peut être testé, et qu'est-ce qui ne le peut pas ?**

| Élément | Exemples |
|---|---|
| Cibles incluses | `app.example.org`, `203.0.113.0/28`, l'application mobile `com.example.wallet` v4.2, le domaine AD `corp.example.org` |
| Cibles exclues | Points de terminaison du prestataire de paiement, widget de chat tiers, bases de données de production, appareils personnels des employés |
| Type de test | Externe, interne, application web, API, sans fil, ingénierie sociale, physique |
| Niveau de connaissance | Boîte noire (aucune information), boîte grise (quelques identifiants/docs), boîte blanche (code source et architecture complets) |
| Comptes fournis | Deux comptes utilisateur standard et un compte administrateur dans l'environnement de préproduction |

Le périmètre doit être exprimé avec des **identifiants sans ambiguïté** — noms d'hôtes exacts, plages IP en notation CIDR, versions d'applications — et non par des descriptions comme « le site web ». Si le DNS d'un nom inclus pointe vers une IP appartenant à un tiers (un CDN, par exemple), clarifiez avant de tester.

## Règles d'engagement (RoE)

Les règles d'engagement traduisent le périmètre en règles opérationnelles. Un document solide comprend :

1. **Parties et contacts** — commanditaire client, contacts techniques, équipe de test et un **contact d'urgence 24 h/24** de chaque côté
2. **Périmètre** — les listes d'inclusions/exclusions ci-dessus, en annexe
3. **Créneau de test** — dates, heures et fuseau horaire ; périodes d'interdiction (par ex. traitements de fin de mois)
4. **Techniques autorisées et interdites** — par ex. pas de déni de service, pas de charges destructrices, pas d'ingénierie sociale envers les dirigeants, attaques de mots de passe limitées à 3 tentatives par compte et par heure pour éviter les verrouillages
5. **Gestion des données** — quelles données les testeurs peuvent consulter ou copier, comment les preuves sont chiffrées et stockées, conservation et destruction après la mission
6. **Conditions d'arrêt** — par ex. découverte d'indices d'une compromission réelle, instabilité d'un système de production, accès à des données inattendues et sensibles (dossiers de santé, données de cartes de paiement)
7. **Plan de communication** — fréquence des points d'avancement, signalement immédiat des constats critiques
8. **Adresses IP sources** des testeurs, afin que les défenseurs puissent distinguer le trafic de test
9. **Clauses juridiques** — déclaration d'autorisation, responsabilité, confidentialité, signatures datées

:::scenario Quand s'arrêter et appeler
Lors d'un test interne autorisé, vous découvrez sur un serveur une tâche planifiée qui exécute un binaire inconnu et contacte une IP externe toutes les cinq minutes. Cela ne fait pas partie de vos tests. C'est une **condition d'arrêt** : vous avez peut-être trouvé un véritable intrus. Cessez les activités liées, consignez ce que vous avez observé et contactez immédiatement le contact d'urgence du client. N'investiguez pas davantage de votre propre initiative — le processus de réponse à incident du client prend le relais.
:::

## Méthodologies

Les méthodologies rendent les tests cohérents, reproductibles et défendables.

| Méthodologie | Objet | À utiliser pour |
|---|---|---|
| **PTES** (Penetration Testing Execution Standard) | Cycle complet de la mission : pré-engagement, collecte de renseignements, modélisation des menaces, analyse des vulnérabilités, exploitation, post-exploitation, rapport | La structure globale de la mission |
| **OWASP WSTG** (Web Security Testing Guide) | Cas de test détaillés pour les applications web et les API | Les listes de contrôle web et API |
| **NIST SP 800-115** | Guide technique des tests et évaluations de sécurité de l'information | Les tests orientés secteur public et conformité |
| **OSSTMM** | Approche scientifique de la mesure de la sécurité opérationnelle | Les évaluations orientées indicateurs |

Déroulement typique d'une mission, selon PTES :

```text
Pré-engagement ─► Reconnaissance ─► Énumération ─► Analyse des vulnérabilités
 (périmètre, RoE)                                           │
                                                            ▼
     Rapport ◄── Post-exploitation ◄── Exploitation maîtrisée
     & re-test     (impact, nettoyage)    (preuve de concept)
```

## Collecte des preuves

Dès la première minute, consignez :

- Les **horodatages** (avec fuseau horaire) de chaque action significative
- Les **commandes** exécutées et les versions des outils
- Les **sorties brutes** (résultats de balayage, requêtes et réponses HTTP)
- Des **captures d'écran** démontrant les constats — avec les données sensibles masquées dans le rapport
- Les **empreintes** de tout fichier déposé sur les cibles, afin qu'ils puissent être identifiés et supprimés lors du nettoyage

Stockez les preuves chiffrées, limitez l'accès à l'équipe de la mission et détruisez-les selon la durée de conservation convenue.

## Divulgation responsable

En dehors d'une mission contractuelle — par exemple si vous remarquez une vulnérabilité en utilisant normalement un site — **ne testez pas davantage**. Suivez la politique de divulgation des vulnérabilités de l'organisation (souvent publiée à `/.well-known/security.txt`, selon la RFC 9116) ou les règles de son programme de bug bounty. La divulgation coordonnée consiste à signaler en privé, laisser un délai raisonnable pour corriger et ne pas publier de détails permettant d'exploiter des systèmes non corrigés.

## Erreurs fréquentes

- **Un accord uniquement verbal** — s'il n'est pas écrit et signé, il ne vous protège pas.
- **Tester une IP parce qu'elle est « à côté » d'une IP incluse** — la proximité n'est pas une autorisation.
- **Ignorer la politique du fournisseur cloud** pour les ressources qui y sont hébergées.
- **Lancer des balayages agressifs pendant les heures ouvrées** contre des systèmes fragiles sans accord.
- **Conserver les données du client après la mission** au-delà de la durée convenue.

## Vérification des connaissances

:::check Questions
1. Un client vous demande de tester `shop.example.com`, hébergé sur une plateforme e-commerce SaaS tierce. Que devez-vous vérifier en premier ?
2. Citez quatre éléments qui figurent dans les règles d'engagement.
3. Pendant le test, vous accédez à une base contenant des dossiers de santé de patients, non prévue dans le périmètre. Que devez-vous faire ?
4. Quelle méthodologie fournit des cas de test détaillés pour les applications web ?
:::

:::solution Réponses
1. Que le client a bien l'autorité d'autoriser le test de cette plateforme — l'accord du fournisseur SaaS ou sa politique de test publiée est généralement requis, et l'infrastructure propre du fournisseur est normalement exclue.
2. Quatre parmi : contacts et contacts d'urgence, périmètre, créneau de test, techniques autorisées et interdites, gestion des données, conditions d'arrêt, plan de communication, IP sources des testeurs, autorisation juridique et signatures.
3. Cesser d'accéder aux données, consigner ce qui s'est passé sans copier de dossiers sensibles et prévenir immédiatement le contact client, comme le prévoient les conditions d'arrêt.
4. L'**OWASP Web Security Testing Guide (WSTG)**.
:::

## Exercice

Rédigez la **synthèse du périmètre et des règles d'engagement** pour cette demande : *« Les cliniques Northwind souhaitent un test externe de leur portail patients `portal.northwind.example` et de leur plage IP publique `198.51.100.32/29` avant le lancement. Le portail utilise un fournisseur d'identité tiers. Le test ne doit pas perturber le système de réservation des cliniques, qui partage la même plage à l'adresse `198.51.100.36`. »*

:::solution Corrigé
- **Inclus :** `portal.northwind.example` (connexion en préproduction et en production, fonctions patient avec les comptes de test fournis) ; `198.51.100.32/29` à l'exclusion de `.36`.
- **Exclus :** `198.51.100.36` (système de réservation) ; l'infrastructure du fournisseur d'identité tiers (ne tester que l'intégration et la configuration de Northwind, pas le fournisseur) ; le déni de service ; l'ingénierie sociale.
- **Créneau :** par ex. du 3 au 14 juin, de 20 h à 6 h (heure de Paris) ; contact d'urgence disponible en permanence.
- **Règles :** aucune charge destructrice ; attaques de mots de passe limitées aux comptes fournis ; arrêt immédiat en cas d'accès à de vraies données patients ; constats critiques signalés par téléphone sous 2 heures.
- **Gestion des données :** preuves chiffrées au repos, aucune donnée patient copiée, destruction de toutes les preuves 90 jours après l'acceptation du rapport.
- **Autorisation :** signée par un dirigeant des cliniques Northwind ayant autorité sur le portail et la plage IP, avec confirmation de la politique de test de l'hébergeur.
:::

## Synthèse et prochaines étapes

- Une autorisation écrite et précise du propriétaire de l'actif est le fondement de toute mission.
- Un périmètre et des règles d'engagement précis protègent le client, le testeur et les tiers.
- Les méthodologies comme PTES et l'OWASP WSTG apportent la cohérence ; la rigueur sur les preuves rend les constats défendables.

Poursuivez avec les bases techniques, puis l'**Énumération réseau**, toujours dans ce cadre.

===assignment===
En vous appuyant sur la section pré-engagement de PTES, créez un **modèle réutilisable de règles d'engagement** (deux à trois pages) avec des champs à compléter pour les coordonnées du client, les tableaux de périmètre, les créneaux, les restrictions techniques, les conditions d'arrêt, le plan de communication, la gestion des données et les signatures.

===criteria===
- Terminer la lecture, la vérification des connaissances et l'exercice de cadrage.
- Marquer la leçon comme terminée. Vous devez également accepter la politique d'utilisation responsable avant de commencer les labs à risque élevé.

===references===
- PTES, *Pre-engagement Interactions* — http://www.pentest-standard.org/index.php/Pre-engagement
- OWASP Web Security Testing Guide — https://owasp.org/www-project-web-security-testing-guide/
- NIST SP 800-115, *Technical Guide to Information Security Testing and Assessment* — https://csrc.nist.gov/pubs/sp/800/115/final
- RFC 9116, *A File Format to Aid in Security Vulnerability Disclosure (security.txt)* — https://www.rfc-editor.org/rfc/rfc9116
- Règles d'engagement des tests d'intrusion du cloud Microsoft — https://www.microsoft.com/msrc/pentest-rules-of-engagement
