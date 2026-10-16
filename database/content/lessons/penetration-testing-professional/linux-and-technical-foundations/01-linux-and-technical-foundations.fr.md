---
title: Linux et fondamentaux techniques pour testeurs
chapter: Les systèmes que vous allez tester
summary: Bâtir la culture systèmes sur laquelle s'appuie un testeur d'intrusion — le système de fichiers et le shell Linux, les utilisateurs, permissions et privilèges, les processus et services, et les bases du réseau — compris comme le modèle qui explique à la fois comment les systèmes sont sécurisés et d'où viennent les faiblesses.
duration: 55
level: beginner
preview: true
status: published
objectives:
- Naviguer dans la hiérarchie du système de fichiers Linux et travailler dans le shell
- Expliquer le modèle utilisateur, groupe et permissions de Linux, y compris root et sudo
- Décrire comment les processus, services et tâches planifiées s'exécutent et sont contrôlés
- Relier ces fondamentaux aux faiblesses de mauvaise configuration courantes, de façon défensive
- Savoir où vivent la configuration et les journaux pour évaluer et durcir un système
prerequisites:
- Autorisation, périmètre et règles d'engagement
---

## Introduction

Avant de pouvoir évaluer un système, il faut le comprendre. La grande majorité des serveurs, conteneurs et charges cloud qu'un testeur rencontre fonctionnent sous Linux, et presque chaque classe de faiblesse des modules ultérieurs — permissions faibles, services exposés, mauvaise configuration, exposition d'identifiants — est une *conséquence de la façon dont ces fondamentaux sont (mal) configurés*. Cette leçon bâtit cette culture systèmes de façon conceptuelle. Ce n'est pas un tutoriel d'attaque ; c'est le modèle qui explique à la fois comment un système est censé être sécurisé et où cette sécurité se brise, afin de pouvoir l'évaluer et recommander des corrections.

Si vous avez suivi la leçon journaux Linux du parcours SOC, voici la vue complémentaire du bâtisseur : là, vous lisiez les preuves ; ici, vous comprenez le système qui les produit.

## Le système de fichiers et le shell

Linux organise tout sous une racine unique (`/`) dans une hiérarchie standard. Les emplacements qui comptent pour un testeur ou un défenseur :

| Chemin | Contient |
|---|---|
| `/etc` | **Configuration** système et des services (et `/etc/passwd`, `/etc/shadow` pour les comptes) |
| `/home` | Répertoires personnels des utilisateurs (et leurs dotfiles, clés, historiques) |
| `/var/log` | **Journaux** (le territoire du parcours SOC) |
| `/var/www`, `/srv` | Contenu web et données servies |
| `/usr/bin`, `/usr/sbin` | Programmes installés |
| `/tmp`, `/var/tmp` | Espace temporaire inscriptible par tous (une source classique de mauvaise configuration) |
| `/proc`, `/sys` | Vues virtuelles de l'état du noyau et des processus en cours |

Le travail se fait via le **shell** (souvent `bash`) : une interface texte qui exécute des commandes, les enchaîne par des tubes (pipes), et est scriptable. Vous n'avez pas besoin de mémoriser des commandes aujourd'hui ; vous avez besoin de la carte mentale — *la configuration vit dans `/etc`, les preuves dans `/var/log`, et les secrets des utilisateurs dans les répertoires personnels* — car cette carte indique à un évaluateur où regarder et à un défenseur quoi protéger.

:::note « Tout est fichier »
Linux représente les périphériques, les processus et même les réglages du noyau comme des objets de type fichier (notamment sous `/proc` et `/sys`). Cette uniformité est puissante et explique pourquoi les permissions (ci-dessous) sont si centrales : contrôler l'accès aux fichiers, c'est, dans une large mesure, contrôler l'accès à tout le système.
:::

## Utilisateurs, groupes et permissions

C'est le cœur de la sécurité Linux. Chaque fichier et processus a un **propriétaire** (un utilisateur) et un **groupe**, et chaque fichier porte des permissions pour trois classes — **propriétaire, groupe, autres** — sur trois actions — **lecture (r), écriture (w), exécution (x)**.

```text
   -rwxr-x---   1  deploy  developers   ...  app.sh
    │└┬┘└┬┘└┬┘      │       │
    │ │  │  └ autres : aucun accès       propriétaire = deploy
    │ │  └── groupe : lecture, exécution groupe = developers
    │ └───── propriétaire : lecture, écriture, exécution
    └─ type de fichier (- = fichier régulier, d = répertoire)
```

- **Le compte root** (identifiant 0) est tout-puissant : il contourne les vérifications de permissions. Protéger root est donc la préoccupation centrale.
- **`sudo`** permet à des utilisateurs désignés d'exécuter des commandes précises en tant que root, chaque usage étant journalisé (la piste d'audit que lit le parcours SOC). Il existe pour que les gens ne se connectent *pas* directement en root — le moindre privilège appliqué à l'administration.
- **Les bits de permission spéciaux** tels que **SUID/SGID** laissent un programme s'exécuter avec les privilèges de son propriétaire plutôt que de l'appelant. Légitimement utilisés par quelques outils système, mais un programme SUID-root mal configuré est une faiblesse classique d'élévation de privilèges — raison pour laquelle un évaluateur les inventorie et un défenseur les garde au minimum.

Le principe de sécurité qui traverse tout cela est le **moindre privilège** : comptes, fichiers et programmes ne devraient avoir que l'accès minimal nécessaire. La plupart des faiblesses liées aux privilèges ne sont que des violations de ce principe — un fichier trop permissif, un compte avec des droits sudo superflus, un service tournant en root sans nécessité.

:::tip Les permissions sont là où « qui peut faire quoi » se décide réellement
Quand les modules ultérieurs parlent d'élévation de privilèges, ils désignent presque toujours l'exploitation d'une *mauvaise configuration de permission ou de privilège* — un fichier inscriptible auquel un processus root fait confiance, un compte surprivilégié, un binaire SUID inutile. Comprenez le modèle de permissions et vous comprenez à la fois la faiblesse et sa correction : resserrer la propriété et les droits au moindre privilège.
:::

## Processus, services et tâches planifiées

Un programme en cours d'exécution est un **processus**, chacun avec son identifiant (PID) et les privilèges de l'utilisateur qui l'a démarré. Testeurs et défenseurs se soucient de *ce qui tourne et sous quel compte* :

- **Les services (démons)** sont des processus d'arrière-plan de longue durée — un serveur web, une base de données, SSH. Sur Linux moderne, ils sont gérés par **systemd** (`systemctl`), qui contrôle aussi leur démarrage au boot. Un service tournant en root, ou exposé au réseau sans nécessité, est une surface d'attaque élargie.
- **Les tâches planifiées** exécutent automatiquement du travail : **cron** et **timers systemd**. Elles sont à la fois un outil d'administration légitime et un emplacement classique de persistance (comme vu défensivement dans la leçon journaux Linux du SOC) — un évaluateur les examine donc et un défenseur les surveille.
- **Le principe :** chaque service en cours et tâche planifiée est une surface d'attaque et devrait exister pour une raison, s'exécuter au moindre privilège, et être connu. « Qu'est-ce qui tourne, sous quel compte, et pourquoi ? » est une question qui trouve de vraies faiblesses.

## Bases du réseau sur l'hôte

Un testeur doit relier l'hôte à la vue réseau du parcours SOC :

- **Services en écoute** — chaque service réseau se lie à un **port** sur une interface. Un hôte exposant plus de ports qu'il n'en a besoin est une surface d'attaque plus grande ; savoir ce qui *devrait* écouter est la base pour repérer ce qui ne le devrait pas.
- **Interfaces et adresses** — un hôte peut se trouver sur plusieurs réseaux (public, interne, administration) ; l'endroit *d'où* il est atteignable détermine son exposition réelle (l'idée risque-vs-sévérité de la leçon d'évaluation des vulnérabilités).
- **Pare-feu de l'hôte** — Linux peut filtrer son propre trafic (p. ex. nftables/iptables) ; un pare-feu hôte correctement configuré réduit l'exposition même si un service est mal configuré — défense en profondeur.

Le point défensif est constant : **minimiser les services en écoute, savoir d'où l'hôte est atteignable, et filtrer ce que vous ne pouvez pas supprimer.**

## Où vivent la configuration et les journaux (pour l'évaluation et le durcissement)

En synthèse, la carte de l'hôte Linux pour l'évaluateur et le défenseur :

```text
 Comptes & auth    /etc/passwd, /etc/shadow, /etc/sudoers, ~/.ssh/
 Config services   /etc/<service>/...            (comment les démons sont réglés)
 Ce qui tourne     systemctl / ps                (services et processus)
 Travail planifié  /etc/cron*, timers systemctl  (automatisation & persistance)
 Journaux & preuves /var/log, journalctl         (le parcours SOC)
 Permissions       propriété + rwx + SUID/SGID   (qui peut faire quoi)
```

Une évaluation lit cette carte pour trouver des faiblesses (fichiers trop permissifs, services superflus, droits sudo risqués) ; le durcissement utilise la même carte pour les corriger (resserrer les permissions, désactiver les services inutilisés, restreindre sudo, corriger, et expédier les journaux au SOC). Les deux sont la même connaissance pointée dans des directions opposées.

## Erreurs courantes

- **Sauter les fondamentaux.** On ne peut évaluer ou durcir ce qu'on ne comprend pas ; l'« élévation de privilèges » ultérieure n'est qu'une mauvaise configuration de permissions, que ce modèle explique.
- **Se connecter en root.** L'usage direct de root supprime la piste d'audit et le garde-fou du moindre privilège ; utilisez `sudo` pour des commandes précises.
- **Laisser tourner des services inutiles.** Chaque démon en écoute est une surface d'attaque ; désactivez ce qui n'est pas nécessaire.
- **Ignorer SUID/SGID et les droits sudo.** Les programmes et comptes surprivilégiés sont la voie d'élévation classique ; inventoriez-les et minimisez-les.
- **Oublier le pare-feu de l'hôte.** Le filtrage est une défense en profondeur peu coûteuse même quand un service est mal configuré.

## Implications pour la sécurité

- Apprenez la carte : configuration dans `/etc`, journaux dans `/var/log`, secrets dans les répertoires personnels, privilège dans le modèle de permissions.
- Appliquez le moindre privilège partout — comptes, fichiers, binaires SUID, droits sudo, et le compte sous lequel tourne un service.
- Traitez chaque service en cours et tâche planifiée comme une surface d'attaque qui doit avoir une raison et s'exécuter au minimum.
- Minimisez les services en écoute, connaissez l'exposition de l'hôte, filtrez avec un pare-feu hôte, et transférez les journaux au SOC.

## Vérification des connaissances

:::check Questions
1. Où Linux conserve-t-il la configuration des services et des comptes, et où conserve-t-il les journaux ?
2. À quoi s'appliquent les permissions `r`, `w`, `x`, et pour quelles trois classes d'utilisateur ?
3. Pourquoi les administrateurs devraient-ils utiliser `sudo` plutôt que de se connecter en root ?
4. Pourquoi les services en cours et les tâches planifiées d'un hôte intéressent-ils à la fois un évaluateur et un défenseur ?
:::

:::solution Réponses
1. La **configuration** vit sous **`/etc`** (y compris `/etc/passwd` et `/etc/shadow` pour les comptes et la config par service) ; les **journaux** vivent sous **`/var/log`** (et le journal systemd via `journalctl`).
2. **Lecture, écriture et exécution** s'appliquent à chacune des trois classes d'utilisateur — **propriétaire, groupe et autres** — pour chaque fichier (et répertoire). Elles sont le cœur du « qui peut faire quoi » sur le système.
3. Parce que `sudo` n'accorde les privilèges root que pour des commandes précises et **journalise chaque usage**, préservant le moindre privilège et une piste d'audit ; se connecter directement en root supprime à la fois le garde-fou et la trace de qui a fait quoi.
4. Parce que chaque service en cours et tâche planifiée est une **surface d'attaque** : un évaluateur cherche ceux qui sont superflus ou surprivilégiés (et cron/timers comme persistance), et un défenseur s'assure que chacun existe pour une raison, s'exécute au moindre privilège, et est surveillé.
:::

## Exercice

Pour un serveur web Linux que vous êtes autorisé à revoir (conceptuellement — aucun sondage en direct requis) :

1. Listez **quatre emplacements** que vous inspecteriez et ce que chacun vous apprend sur la sécurité de l'hôte.
2. Décrivez **deux conditions de permission ou de privilège** qui vous préoccuperaient et la correction au moindre privilège pour chacune.
3. Nommez **deux vérifications de services ou de tâches planifiées** que vous feriez et pourquoi.
4. Énoncez une recommandation de **durcissement au niveau de l'hôte** reliée au réseau.

:::solution Réponse type (abrégée)
1. `/etc/passwd` et `/etc/sudoers` (comptes et qui a sudo) ; la config du service web sous `/etc` (comment il est réglé, ce qu'il expose) ; `/var/log` (preuves et si la journalisation est active) ; l'`~/.ssh/authorized_keys` des répertoires personnels (qui peut se connecter).
2. Un fichier inscriptible par tous utilisé par un processus root → restreindre la propriété/les permissions au moindre privilège ; un compte applicatif avec de larges droits `sudo` dont il n'a pas besoin → supprimer ou réduire ces droits aux seules commandes requises.
3. Revoir les services en cours (`systemctl`) pour désactiver ceux qui ne sont pas nécessaires (réduire la surface d'attaque) ; revoir les tâches cron et les timers systemd pour tout élément inattendu (automatisation et emplacement de persistance).
4. Activer et configurer le pare-feu de l'hôte pour n'autoriser que les ports que le serveur doit exposer (p. ex. HTTPS), réduisant l'exposition en défense en profondeur même si un service est mal configuré.
:::

## Résumé et étapes suivantes

- La plupart des faiblesses Linux sont des conséquences de ces fondamentaux : disposition du système de fichiers, modèle utilisateur/groupe/permissions, processus/services/tâches planifiées, et réseau de l'hôte.
- Le **moindre privilège** est le fil conducteur : fichiers trop permissifs, comptes surprivilégiés, services superflus et binaires SUID inutiles sont l'origine réelle de l'« élévation de privilèges ».
- La carte de l'évaluateur (config dans `/etc`, journaux dans `/var/log`, privilège dans les permissions, surface d'attaque dans les services en cours) est la même carte qu'un défenseur utilise pour durcir.

Les modules opérationnels ultérieurs (énumération réseau, exploitation, élévation de privilèges, Active Directory) s'appuient sur ces fondamentaux et ne sont menés que dans une autorisation explicite et des environnements de lab isolés ; cette leçon vous donne le modèle pour comprendre et défendre les systèmes qu'ils concernent.

===assignment===
Rédigez une **liste de contrôle de durcissement d'hôte Linux** (une page) fondée sur cette leçon : regroupez les éléments sous comptes/permissions, services, tâches planifiées, journalisation et réseau. Pour chaque élément, énoncez la faiblesse qu'il traite et le principe de moindre privilège ou de défense en profondeur qui le sous-tend. Ajoutez un court paragraphe expliquant comment la même liste sert un *évaluateur* (quoi chercher) et un *défenseur* (quoi corriger).

===criteria===
- Terminez la lecture et la vérification des connaissances, et produisez la liste de contrôle.
- Chaque élément de la liste doit nommer la faiblesse qu'il traite et le principe (moindre privilège / défense en profondeur / réduction de la surface d'attaque) ; le contenu doit rester conceptuel/défensif (aucune étape d'exploitation).
- Marquez la leçon comme terminée. Le quiz du module s'appuie sur cette leçon.

===references===
- La norme de hiérarchie du système de fichiers Linux (FHS) — https://refspecs.linuxfoundation.org/fhs.shtml
- Le modèle `sudo` et de permissions (pages de manuel : `sudoers`, `chmod`) — https://www.sudo.ws/docs/man/
- Documentation systemd / systemctl (services et timers) — https://www.freedesktop.org/software/systemd/man/
- CIS Benchmarks pour Linux (socles de durcissement) — https://www.cisecurity.org/cis-benchmarks
- NIST SP 800-123, *Guide to General Server Security* — https://csrc.nist.gov/pubs/sp/800/123/final
