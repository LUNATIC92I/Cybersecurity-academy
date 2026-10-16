---
title: Fondamentaux de la sécurité des applications web
chapter: Comment les applications web échouent — et comment l'éviter
summary: Comprendre les classes de faiblesses d'applications web les plus courantes à l'aide de l'OWASP Top 10 comme carte — ce qu'est chacune, comment elle apparaît dans le code, et comment la prévenir — dans une optique d'évaluation et de défense plutôt que d'exploitation.
duration: 55
level: intermediate
preview: true
status: published
objectives:
- Expliquer comment le modèle web client-serveur crée une surface d'attaque
- Décrire conceptuellement les principales catégories de risque de l'OWASP Top 10
- Expliquer comment l'injection et le contrôle d'accès défaillant apparaissent et comment les prévenir
- Reconnaître les risques d'authentification, de mauvaise configuration et de composants
- Associer chaque faiblesse à une mesure défensive concrète
prerequisites:
- Évaluation des vulnérabilités et notation CVSS
---

## Introduction

Les applications web sont la plus grande surface d'attaque de la plupart des organisations : accessibles à tous, exécutant du code complexe, et connectées aux données qui comptent. Cette leçon cartographie les classes de faiblesses d'applications web les plus courantes à l'aide de l'**OWASP Top 10**, la liste de référence du secteur. L'accent est conceptuel et défensif : pour chaque classe, vous apprendrez *ce qu'est la faiblesse, comment elle apparaît dans le code, et comment la prévenir* — afin de pouvoir évaluer une application et recommander des corrections. Ce n'est pas un tutoriel d'exploitation ; toute pratique offensive concrète n'a lieu que dans des labs isolés, dans l'autorisation et le périmètre du premier module.

Comprendre *pourquoi* les applications échouent est ce qui permet à la fois de trouver les faiblesses (comme évaluateur) et de les stopper (comme défenseur ou développeur).

## Le modèle web et sa surface d'attaque

Une application web est une conversation entre un **client** non fiable (le navigateur, entièrement sous le contrôle de l'utilisateur) et un **serveur** de confiance. Chaque entrée que le serveur reçoit — paramètres d'URL, champs de formulaire, en-têtes, cookies, fichiers téléversés, corps d'API — provient d'un endroit que le serveur ne contrôle pas et doit donc être traitée comme potentiellement hostile.

```text
   NAVIGATEUR (non fiable)                       SERVEUR (de confiance)
   l'utilisateur contrôle tout  ── requête ──►   doit TOUT valider
   ce qu'il envoie              ◄── réponse ──   ce qu'il reçoit
```

La cause racine unique derrière la plupart des vulnérabilités web est de **faire confiance à une entrée ou un contexte qui ne devrait pas l'être** — que cette entrée soit une donnée (injection), une identité (contrôle d'accès) ou une configuration. Gardez ce prisme et le Top 10 cesse d'être une liste à mémoriser pour devenir un ensemble de conséquences d'une seule erreur.

## L'OWASP Top 10, conceptuellement

L'**OWASP Top 10** est une liste de sensibilisation largement utilisée des risques de sécurité d'applications web les plus critiques, mise à jour périodiquement par l'Open Worldwide Application Security Project. Les catégories actuelles, en clair :

| Catégorie | Ce que c'est (concept) |
|---|---|
| **Contrôle d'accès défaillant** | Les utilisateurs peuvent agir hors de leurs permissions prévues (voir les données d'autrui, atteindre des fonctions admin) |
| **Défaillances cryptographiques** | Données sensibles mal protégées (chiffrement faible/absent, secrets exposés) |
| **Injection** | Une entrée non fiable est interprétée comme du code/des commandes (SQL, OS, etc.) |
| **Conception non sécurisée** | La conception elle-même manque de mesures nécessaires, avant tout bug de code |
| **Mauvaise configuration de sécurité** | Réglages par défaut non sûrs, erreurs verbeuses, fonctions inutiles laissées actives |
| **Composants vulnérables et obsolètes** | Utilisation de bibliothèques/frameworks à vulnérabilités connues |
| **Défaillances d'identification et d'authentification** | Connexion, session ou gestion d'identifiants faibles |
| **Défaillances d'intégrité des logiciels et des données** | Faire confiance à du code/des mises à jour/des données sans en vérifier l'intégrité |
| **Défaillances de journalisation et de surveillance** | Ne pas détecter ou enregistrer les attaques (lien avec le parcours SOC) |
| **Falsification de requête côté serveur (SSRF)** | Le serveur est amené à faire des requêtes vers des destinations non prévues |

Vous n'avez pas besoin de chaque détail aujourd'hui ; vous avez besoin de la *forme* de chacune et de sa défense. Les sections suivantes approfondissent celles qu'un testeur rencontre le plus.

## Injection

L'**injection** survient lorsqu'une entrée non fiable est incorporée dans une commande ou une requête d'une manière qui laisse l'entrée en changer la structure, de sorte que la donnée est interprétée comme du **code**. L'exemple classique est l'injection SQL : une valeur censée être un terme de recherche devient partie de l'instruction SQL et modifie ce que fait la base de données.

- **Comment elle apparaît :** l'application construit une requête ou une commande en concaténant l'entrée de l'utilisateur dans une chaîne, de sorte qu'une entrée forgée change le sens de l'instruction.
- **La prévention (le point important) :** ne jamais construire de commandes par concaténation de chaînes avec une entrée non fiable. Utilisez des **requêtes paramétrées / instructions préparées**, qui envoient le code et la donnée séparément afin que l'entrée ne puisse jamais être exécutée comme du code. Ajoutez une validation d'entrée (listes d'autorisation) et des comptes de base de données à moindre privilège en défense en profondeur.

:::tip Vous avez déjà vu la défense dans le propre code de cette plateforme
L'application sur laquelle vous apprenez utilise des instructions préparées pour chaque requête de base de données précisément pour rendre l'injection SQL structurellement impossible — la couche de données n'a aucune API qui interpole des valeurs dans le SQL. C'est la défense canonique contre l'injection : la correction est architecturale (séparer le code de la donnée), non un filtre ajouté après coup.
:::

Le même principe couvre l'injection de commandes OS, l'injection LDAP et d'autres : gardez la donnée non fiable hors de la grammaire de l'interpréteur.

## Contrôle d'accès défaillant

Le **contrôle d'accès défaillant** signifie qu'un utilisateur peut faire ou voir quelque chose que son rôle ne devrait pas permettre — lire l'enregistrement d'un autre utilisateur en changeant un identifiant dans l'URL, ou atteindre une page admin en y naviguant directement.

- **Comment il apparaît :** l'application vérifie l'*authentification* (qui vous êtes) mais pas l'*autorisation* (ce que vous pouvez faire) à chaque action sensible, ou se repose sur l'interface qui masque un contrôle plutôt que sur le serveur qui applique la règle.
- **La prévention :** appliquer l'autorisation **côté serveur à chaque requête**, refuser par défaut, et vérifier la propriété (cet enregistrement appartient-il à l'utilisateur demandeur ?). Masquer un bouton n'est jamais un contrôle d'accès — le serveur doit revérifier, exactement le principe RBAC/« autorisation côté serveur » des parcours GRC et SOC.

C'est régulièrement parmi les risques web les plus courants et les plus graves car c'est une faille de logique, invisible à de nombreux scanners automatisés et propre à chaque application.

## Authentification, mauvaise configuration et composants

Trois autres classes qu'un testeur rencontre constamment :

- **Défaillances d'identification et d'authentification** — politiques de mot de passe faibles, aucune protection contre le credential stuffing ou le brute force, jetons de session non sûrs, MFA absente. *Prévention :* authentification forte, limitation de débit et verrouillage de compte (le throttle vu dans le parcours SOC), gestion de session sécurisée, et MFA — surtout pour les comptes privilégiés.
- **Mauvaise configuration de sécurité** — identifiants par défaut laissés en place, messages d'erreur verbeux divulguant des internes, listage de répertoires activé, services ou modes debug inutiles actifs en production. *Prévention :* durcir par défaut, désactiver ce dont vous n'avez pas besoin, supprimer les erreurs détaillées aux utilisateurs (les journaliser côté serveur), et revoir la configuration lors du déploiement (la liste de vérification de déploiement de la doc).
- **Composants vulnérables et obsolètes** — l'application dépend de bibliothèques et frameworks à CVE connues. *Prévention :* tenir un inventaire logiciel, suivre les dépendances, corriger selon un calendrier, et surveiller les avis (la discipline d'évaluation des vulnérabilités appliquée à votre propre stack).

:::scenario Une classe de faille, tracée de bout en bout
Un évaluateur constate que changer le `account_id` numérique dans une URL renvoie les relevés d'un autre client — contrôle d'accès défaillant. La cause racine : le serveur authentifie la session mais ne vérifie jamais que le compte demandé *appartient à* l'utilisateur connecté. La correction est une unique vérification de propriété côté serveur sur ce point d'accès (refuser par défaut, vérifier la propriété). L'évaluateur le rapporte avec le CVSS et le risque contextuel (données financières clients, exposé à Internet → élevé), une preuve caviardée, et la remédiation d'une ligne — exactement la structure de constat de la leçon de rapport. Faiblesse comprise → risque communiqué → correction spécifiée.
:::

## Cross-site scripting et CSRF (confiance côté navigateur)

Deux problèmes classiques côté navigateur illustrent le « contexte non fiable » :

- **Cross-site scripting (XSS)** — l'application reflète une entrée non fiable dans une page sans encodage correct, de sorte que le navigateur exécute un script fourni par l'attaquant dans la session de la victime. *Prévention :* **encoder en sortie** tout ce qui est rendu dans une page selon son contexte, valider l'entrée, et appliquer une **Content-Security-Policy** stricte interdisant le script en ligne (l'approche exacte de cette plateforme : tous les scripts externes, pas d'`unsafe-inline`).
- **Cross-site request forgery (CSRF)** — un site malveillant fait envoyer par le navigateur de la victime une requête authentifiée à votre application. *Prévention :* **jetons anti-CSRF** par requête et cookies `SameSite` (de nouveau, comme implémenté dans cette plateforme).

Les deux sont des conséquences de faire confiance à quelque chose venant du navigateur — une entrée, ou une requête inter-origine — qui devrait être vérifié.

## Associer les faiblesses aux défenses

La valeur de l'évaluateur est de transformer chaque faiblesse en une défense que le client peut mettre en œuvre :

```text
 Injection                  → requêtes paramétrées, listes d'autorisation, moindre privilège
 Contrôle d'accès défaillant → autorisation côté serveur à chaque requête, refus par défaut
 Défaillances d'auth         → auth forte, limitation de débit, MFA, sessions sécurisées
 Mauvaise configuration      → durcir les défauts, supprimer les erreurs verbeuses, revoir la config
 Composants vulnérables      → inventaire + correctifs + surveillance des avis
 XSS / CSRF                  → encodage en sortie + CSP stricte ; jetons anti-CSRF + SameSite
 Lacunes journalisation      → journaliser les événements de sécurité, alimenter le SOC (lien bleu)
```

Chaque ligne est un constat que vous pourriez rédiger et une correction que vous pourriez recommander — tout l'intérêt d'évaluer une application web.

## Erreurs courantes

- **Traiter le Top 10 comme une liste à mémoriser.** C'est une carte des conséquences du fait de faire confiance à une entrée, une identité ou une configuration non fiables ; comprenez la cause racine.
- **Filtrer l'injection au lieu de séparer le code de la donnée.** Les listes de blocage se contournent ; les requêtes paramétrées rendent l'injection structurellement impossible.
- **Appliquer le contrôle d'accès dans l'interface.** Masquer un contrôle n'est pas un contrôle ; le serveur doit revérifier l'autorisation et la propriété à chaque requête.
- **Laisser les défauts et erreurs verbeuses en production.** La mauvaise configuration est peu coûteuse pour les attaquants et fréquente dans les rapports.
- **Ignorer les dépendances.** Les composants à vulnérabilités connues sont parmi les voies d'entrée les plus faciles ; inventoriez-les et corrigez-les.
- **Pas de journalisation.** Si les attaques ne sont pas enregistrées, le SOC ne peut pas les voir — une défaillance en soi.

## Implications pour la sécurité

- Traitez chaque entrée du client et chaque contexte inter-origine comme non fiables ; validez et appliquez côté serveur.
- Prévenez l'injection structurellement par des requêtes paramétrées, et appliquez l'autorisation côté serveur à chaque requête.
- Durcissez la configuration, corrigez les composants, et utilisez une authentification forte avec limitation de débit et MFA.
- Encodez la sortie et appliquez une CSP stricte et des jetons anti-CSRF, et journalisez les événements de sécurité pour le SOC.

## Vérification des connaissances

:::check Questions
1. Quelle cause racine unique sous-tend la plupart des vulnérabilités d'applications web ?
2. Quelle est la défense structurelle contre l'injection SQL, et pourquoi fonctionne-t-elle ?
3. Pourquoi masquer un bouton dans l'interface n'est-il pas un contrôle d'accès valide ?
4. Nommez les mesures défensives contre le cross-site scripting (XSS).
:::

:::solution Réponses
1. **Faire confiance à une entrée ou un contexte qui ne devrait pas l'être** — donnée (injection), identité/autorisation (contrôle d'accès défaillant), ou configuration. La plupart des catégories du Top 10 sont des conséquences de cette unique erreur.
2. Les **requêtes paramétrées / instructions préparées** : elles envoient le code SQL et la donnée de l'utilisateur à la base séparément, de sorte que l'entrée est toujours traitée comme une donnée et ne peut jamais changer la structure de la requête ni être exécutée comme du code. C'est architectural, non un filtre.
3. Parce que le **serveur**, et non le navigateur, doit appliquer l'autorisation ; un attaquant peut envoyer la requête directement, quel que soit ce que montre l'interface. Le contrôle d'accès doit être revérifié côté serveur à chaque requête sensible (refus par défaut, vérification de la propriété).
4. L'**encodage en sortie** de la donnée non fiable selon le contexte où elle est rendue, la **validation d'entrée**, et une **Content-Security-Policy** stricte qui interdit les scripts en ligne (plus des cookies sécurisés et `HttpOnly`).
:::

## Exercice

Pour une application web simple (p. ex. une boutique en ligne avec comptes clients et espace admin) :

1. Choisissez **quatre** catégories de l'OWASP Top 10 les plus pertinentes pour elle et, pour chacune, décrivez en une ou deux phrases comment la faiblesse pourrait apparaître dans *cette* application.
2. Pour chacune, donnez la **mesure défensive** qui la prévient.
3. Prenez-en une comme **constat** (réutilisez la structure de la leçon de rapport) : titre, risque contextuel, une preuve de concept décrite/caviardée, et remédiation.
4. Énoncez une manière dont l'application devrait **journaliser** pour que les tentatives contre elle soient visibles d'un SOC.

:::solution Réponse type (abrégée)
1. *Injection* — une recherche de produits construit une requête SQL en concaténant le terme de recherche. *Contrôle d'accès défaillant* — l'historique de commandes est récupéré par un `order_id` dans l'URL sans vérification de propriété. *Défaillances d'auth* — la connexion admin n'a ni limitation de débit ni MFA. *Mauvaise configuration* — l'application renvoie des traces de pile complètes aux utilisateurs en cas d'erreur.
2. Requêtes paramétrées + utilisateur BD à moindre privilège ; vérification de propriété côté serveur (refus par défaut) ; limitation de débit/verrouillage + MFA sur l'admin ; supprimer le détail d'erreur aux utilisateurs et journaliser côté serveur.
3. *Constat :* « Contrôle d'accès défaillant sur l'historique de commandes — tout utilisateur authentifié peut lire n'importe quelle commande en changeant `order_id`. » Risque : Élevé (données clients, exposé à Internet). Preuve (décrite/caviardée) : « changer l'identifiant a renvoyé le récapitulatif de commande d'un autre client. » Remédiation : appliquer une vérification côté serveur que la commande appartient à l'utilisateur de la session ; refuser par défaut.
4. Journaliser les échecs et réussites d'authentification (pour la détection de brute force, comme dans le parcours SOC) et les refus d'autorisation, avec l'IP source et le compte, et les transférer au SIEM.
:::

## Résumé et étapes suivantes

- Les vulnérabilités web découlent presque toutes du fait de **faire confiance à une entrée, une identité ou une configuration non fiables** ; l'**OWASP Top 10** en cartographie les conséquences.
- L'**injection** se prévient structurellement (requêtes paramétrées) ; le **contrôle d'accès défaillant** par l'autorisation côté serveur et les vérifications de propriété à chaque requête.
- Durcissez la configuration, corrigez les composants, utilisez une authentification forte avec limitation de débit et MFA, encodez la sortie avec une CSP stricte et des jetons anti-CSRF, et journalisez pour le SOC.
- Chaque faiblesse correspond à une défense concrète — ce qui est exactement ce que vous rapportez et recommandez.

Les sujets de sécurité web ultérieurs et tout test pratique ne sont menés que contre des applications intentionnellement vulnérables dans des labs isolés, dans l'autorisation et le périmètre qui gouvernent chaque mission.

===assignment===
Produisez un **plan de revue de sécurité d'application web** pour une application de votre choix : pour au moins six catégories de l'OWASP Top 10, énoncez en langage clair comment la faiblesse pourrait se manifester dans l'application et la mesure défensive précise qui la prévient. Transformez-en deux en constats complets (structure de la leçon de rapport, avec risque contextuel et remédiation). Ajoutez une courte note sur ce que l'application devrait journaliser pour que les attaques soient visibles d'un SOC, reliant les vues web et bleue.

===criteria===
- Terminez la lecture et la vérification des connaissances, et produisez le plan de revue.
- Chaque faiblesse doit être associée à une mesure défensive concrète et correcte (p. ex. requêtes paramétrées pour l'injection, autorisation côté serveur pour le contrôle d'accès) ; les deux constats doivent utiliser le risque contextuel et une remédiation précise ; et le contenu doit rester conceptuel/défensif (aucune étape d'exploitation).
- Marquez la leçon comme terminée. Le quiz du module s'appuie sur cette leçon.

===references===
- OWASP Top 10 — https://owasp.org/Top10/
- OWASP Web Security Testing Guide (WSTG) — https://owasp.org/www-project-web-security-testing-guide/
- OWASP Cheat Sheet Series (mesures défensives) — https://cheatsheetseries.owasp.org/
- OWASP Application Security Verification Standard (ASVS) — https://owasp.org/www-project-application-security-verification-standard/
- MITRE CWE (Common Weakness Enumeration) — https://cwe.mitre.org/
