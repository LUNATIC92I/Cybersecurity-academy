---
title: La triade CIA et les principes fondamentaux de la sécurité
chapter: Principes de sécurité
summary: Découvrez les trois propriétés que protège toute mesure de sécurité, ainsi que les principes de conception — moindre privilège, défense en profondeur, zero trust — que les analystes SOC utilisent pour raisonner sur les alertes.
duration: 40
level: beginner
objectives:
- Définir la confidentialité, l'intégrité et la disponibilité et donner un exemple opérationnel de chacune
- Classer un incident de sécurité selon la propriété principalement atteinte
- Expliquer le moindre privilège, la séparation des tâches, la défense en profondeur et le zero trust
- Utiliser la triade CIA pour prioriser les alertes dans une file SOC
prerequisites:
- Connaissances de base des ordinateurs, des réseaux et des comptes utilisateurs
---

## Introduction

Chaque alerte qui parvient à un Security Operations Center (SOC) pose au fond une seule question : *quelque chose qui compte pour nous est-il en train d'être endommagé ?* Pour y répondre vite, les analystes ont besoin d'un vocabulaire commun pour décrire ce « dommage ». La **triade CIA** — Confidentialité, Intégrité, Disponibilité (*Confidentiality, Integrity, Availability*) — est ce vocabulaire. Elle est volontairement simple, et c'est sa force : un analyste débutant et un RSSI peuvent décrire le même incident dans les mêmes termes.

Dans cette leçon, vous découvrirez les trois propriétés, les principes classiques qui servent à les protéger et la manière dont un SOC s'en sert pour trier son travail.

## Les trois propriétés

### Confidentialité

La confidentialité signifie que l'information n'est divulguée **qu'aux personnes, processus ou systèmes autorisés**. Elle est violée lorsque des données sont lues, copiées ou exposées par quelqu'un qui ne devrait pas y accéder — attaquant externe ou employé disposant de droits excessifs.

Mesures typiques de confidentialité :

- Listes de contrôle d'accès et contrôle d'accès fondé sur les rôles (RBAC)
- Chiffrement au repos (disque, base de données) et en transit (TLS)
- Prévention des fuites de données (DLP) et étiquettes de classification
- Authentification multifacteur protégeant les comptes qui peuvent lire les données

Exemple opérationnel : le SOC reçoit une alerte indiquant que 4 Go ont été téléversés depuis un serveur de fichiers de la finance vers un domaine de stockage cloud personnel à 2 h du matin. Si c'est confirmé, il s'agit principalement d'un incident de **confidentialité**.

### Intégrité

L'intégrité signifie que l'information et les systèmes sont **exacts, complets et modifiés uniquement de manière autorisée**. Les attaques contre l'intégrité sont souvent plus discrètes que le vol de données : un attaquant qui modifie les coordonnées bancaires de la paie, altère des journaux d'audit ou falsifie une mise à jour logicielle peut causer bien plus de dégâts qu'un attaquant qui se contente de lire des données.

Mesures typiques d'intégrité :

- Empreintes cryptographiques et signatures numériques (signature de code, mises à jour signées)
- Gestion des changements et circuits d'approbation
- Surveillance de l'intégrité des fichiers (FIM)
- Stockage des journaux en écriture unique ou en ajout seul
- Contraintes de base de données et validation des entrées

Exemple opérationnel : Sysmon montre que `C:\Windows\System32\drivers\etc\hosts` a été modifié par un processus non administrateur, redirigeant un domaine bancaire vers une adresse IP inconnue. Il s'agit principalement d'un incident d'**intégrité**.

### Disponibilité

La disponibilité signifie que les systèmes et les données sont **accessibles aux utilisateurs autorisés lorsqu'ils en ont besoin**. Les rançongiciels, les attaques par déni de service distribué (DDoS), les logiciels destructeurs et même les erreurs de configuration menacent la disponibilité.

Mesures typiques de disponibilité :

- Redondance, mise en grappe et répartition de charge
- Sauvegardes testées avec copies hors ligne ou immuables
- Protection DDoS et limitation de débit
- Surveillance de la capacité et fenêtres de correctifs

Exemple opérationnel : des centaines d'événements de renommage de fichiers avec une nouvelle extension `.lockd` apparaissent sur un serveur de fichiers en deux minutes. Il s'agit d'un incident de **disponibilité** (et probablement aussi de confidentialité, car les groupes de rançongiciels modernes exfiltrent généralement les données avant de chiffrer).

:::note Un incident touche rarement une seule propriété
Classez selon l'impact **principal** pour l'acheminement et la gravité, mais consignez les impacts secondaires. Un rançongiciel accompagné d'un vol de données est un incident de disponibilité *et* de confidentialité, et le plan de réponse doit couvrir les deux.
:::

## Au-delà de la triade : authenticité, imputabilité et non-répudiation

De nombreux référentiels étendent la triade :

| Propriété | Signification | Intérêt pour le SOC |
|---|---|---|
| Authenticité | Une identité ou un message est bien ce qu'il prétend être | Hameçonnage, expéditeurs usurpés, jetons falsifiés |
| Imputabilité | Les actions peuvent être rattachées à une entité responsable | Exige une journalisation fiable et centralisée |
| Non-répudiation | Un acteur ne peut pas nier de façon crédible une action | Transactions signées, journaux inviolables |

L'imputabilité est particulièrement importante pour le travail du SOC : **si une action n'est pas journalisée, elle ne peut pas être investiguée.** La couverture de journalisation est donc une mesure de sécurité à part entière.

## Principes de conception fondamentaux

### Moindre privilège

Chaque utilisateur, service et processus ne doit disposer **que des permissions nécessaires à sa tâche, et seulement pendant la durée nécessaire.** Le moindre privilège limite le « rayon d'impact » d'un compte compromis.

En pratique :

- Des comptes standard pour le travail quotidien ; des comptes d'administration distincts, utilisés uniquement pour l'administration
- Des comptes de service limités à des ressources précises, et non membres de Domain Admins
- Une élévation juste-à-temps (JIT) qui expire automatiquement

Signal SOC : un compte du support ajouté soudainement au groupe `Domain Admins` (événement Windows **4728** / **4732** / **4756** selon la portée du groupe) viole le moindre privilège et mérite une attention immédiate.

### Séparation des tâches

Aucune personne ne doit pouvoir réaliser seule une action sensible. Celui qui demande un paiement ne doit pas l'approuver ; l'ingénieur qui écrit une règle de détection ne doit pas être le seul relecteur qui la déploie. La séparation des tâches protège à la fois contre la fraude et contre les erreurs de bonne foi.

### Défense en profondeur

Partez du principe que toute mesure isolée peut échouer, et superposez des mesures indépendantes afin qu'une défaillance ne soit pas catastrophique :

:::diagram Défense en profondeur : des couches indépendantes entre Internet et les données
```text
 Internet
    │
 [ Passerelle de messagerie ]  ← bloque les pièces jointes connues comme malveillantes
    │
 [ Protection des postes / EDR ]  ← détecte l'exécution malveillante
    │
 [ Compte à moindre privilège ]  ← limite ce que le logiciel malveillant peut atteindre
    │
 [ Segmentation réseau ]  ← stoppe le mouvement latéral
    │
 [ Détection SIEM + SOC ]  ← repère ce qui est passé au travers
    │
 [ Sauvegardes ]  ← rétablit la disponibilité si tout le reste échoue
```
:::

Pour un analyste SOC, la défense en profondeur explique pourquoi **une alerte provenant d'une couche est une raison de vérifier les autres.** Si l'EDR a bloqué une charge malveillante, la passerelle de messagerie l'a-t-elle laissée passer ? L'utilisateur a-t-il aussi saisi ses identifiants sur une page d'hameçonnage ?

### Zero trust

Le zero trust remplace « l'intérieur est fiable, l'extérieur ne l'est pas » par **« ne jamais faire confiance, toujours vérifier ».** Chaque demande d'accès est évaluée selon l'identité, l'état du poste, la localisation et le contexte — même depuis le réseau interne. La publication NIST SP 800-207 décrit formellement cette architecture.

Signal SOC : dans un environnement zero trust, le *risque de connexion* et la *conformité du poste* deviennent des télémétries de premier plan. Une connexion réussie depuis un poste non conforme dans un pays inhabituel est significative, même si le mot de passe était correct.

### Paramètres sûrs par défaut et défaillance sécurisée

Les systèmes doivent démarrer dans un état sûr et, lorsqu'ils tombent en panne, échouer en mode fermé (refuser l'accès) plutôt qu'ouvert. Un pare-feu qui laisse passer tout le trafic lorsque son moteur de règles plante n'échoue pas de manière sécurisée.

## Utiliser la triade pour trier les alertes

Une file SOC peut contenir des dizaines d'alertes en début de poste. Un analyste méthodique se pose, pour chacune :

1. **Quelle propriété est menacée ?** (C, I, D — éventuellement plusieurs)
2. **Quel actif est concerné, et quelle est sa criticité ?** Un contrôleur de domaine passe avant une borne d'accueil.
3. **Quelle est la qualité de la preuve ?** Un signal isolé de faible fiabilité ou plusieurs signaux corrélés.
4. **L'activité est-elle en cours ?** Un chiffrement ou une exfiltration active est critique dans le temps.

:::scenario Exemple guidé : trois alertes, un analyste
**Alerte A** — Dix échecs de connexion pour un utilisateur du marketing depuis une IP interne, puis plus rien.
**Alerte B** — Un serveur de sauvegarde signale l'échec de sa tâche planifiée car le volume de sauvegarde est « en cours d'utilisation ».
**Alerte C** — Une alerte EDR : `vssadmin.exe delete shadows /all /quiet` exécuté sur un serveur de fichiers par un compte de service.

L'alerte C est clairement prioritaire. La suppression des clichés instantanés de volume est une étape classique de préparation d'un rançongiciel (MITRE ATT&CK **T1490 — Inhibit System Recovery**) et vise la **disponibilité** d'un actif critique. L'alerte B peut être liée — vérifiez si les sauvegardes sont altérées. L'alerte A correspond très probablement à un mot de passe oublié ; elle peut attendre, mais doit tout de même être clôturée avec une note.
:::

## Erreurs fréquentes

- **Considérer la confidentialité comme la seule propriété importante.** Les attaques contre l'intégrité (altération de journaux, fraude) sont souvent découvertes tard justement parce que les équipes ne les recherchent pas.
- **Ignorer les incidents de disponibilité causés par vos propres mesures.** Un blocage automatique trop agressif qui fait tomber un service de production reste un incident de disponibilité.
- **Des comptes de service sur-privilégiés « parce que ça marche ».** C'est l'une des premières causes de mouvement latéral dans les compromissions réelles.
- **Supposer que le réseau interne est sûr.** La plupart des incidents graves impliquent un attaquant déjà à l'intérieur.

## Implications de sécurité pour les défenseurs

- Associez chaque actif critique à la propriété qui compte le plus pour le métier ; c'est ce qui détermine la gravité des alertes.
- Traitez les chaînes de journalisation comme critiques pour l'intégrité : si un attaquant peut effacer les journaux, l'imputabilité disparaît. Transférez rapidement les journaux hors de l'hôte.
- Revoyez l'appartenance aux groupes privilégiés en continu, pas une fois par an.

## Vérification des connaissances

:::check Questions
1. Un attaquant modifie le numéro de compte destinataire d'un virement en attente. Quelle propriété est principalement atteinte ?
2. Pourquoi la journalisation est-elle considérée comme une mesure de sécurité ?
3. Donnez un exemple de défense en profondeur appliquée à l'hameçonnage.
4. Que signifie « échouer de manière sécurisée » pour un service d'authentification ?
:::

:::solution Réponses
1. **L'intégrité** — des données ont été modifiées sans autorisation. La confidentialité peut ne pas être touchée du tout.
2. Les journaux assurent l'**imputabilité** : sans eux, les actions ne peuvent être ni attribuées ni investiguées, et aucune détection ne peut être construite.
3. Filtrage de la messagerie, sensibilisation des utilisateurs, MFA (un mot de passe volé ne suffit plus), EDR sur les postes et surveillance par le SOC des connexions à risque — chaque couche rattrape ce que la précédente a manqué.
4. Si le service ne peut pas vérifier un utilisateur (par exemple, sa base de données est injoignable), il doit **refuser** l'accès plutôt que l'accorder.
:::

## Exercice

Classez chaque événement selon la propriété CIA principalement atteinte, et citez une mesure qui l'aurait empêché ou détecté.

1. Un ordinateur portable contenant des données clients non chiffrées est volé dans une voiture.
2. Un administrateur supprime par erreur une base de données de production sans sauvegarde récente.
3. Un logiciel malveillant modifie la clé de registre `Run` pour se lancer à chaque ouverture de session.
4. Une attaque DDoS rend le portail client inaccessible pendant trois heures.

:::solution Corrigé
1. **Confidentialité** — le chiffrement intégral du disque (BitLocker/FileVault) aurait empêché la divulgation.
2. **Disponibilité** (et intégrité du jeu de données) — sauvegardes testées et versionnées ; contrôle des changements et séparation des tâches pour les commandes destructrices.
3. **Intégrité** de la configuration du système — surveillance des clés d'exécution automatique par l'EDR ou par l'événement Sysmon 13 (valeur de registre définie) ; liste blanche d'applications.
4. **Disponibilité** — service de protection DDoS, limitation de débit et procédure d'incident pour la redirection du trafic.
:::

## Synthèse et prochaines étapes

- La triade CIA donne au SOC un langage commun pour décrire l'impact.
- Le moindre privilège, la séparation des tâches, la défense en profondeur et le zero trust sont les principes de conception que vous retrouverez dans chaque mesure.
- Classer les alertes selon la propriété atteinte et la criticité de l'actif est la première étape d'un tri méthodique.

Vous allez ensuite étendre ce vocabulaire aux notions de **menaces, vulnérabilités, risques et mesures** — les termes nécessaires pour expliquer *pourquoi* une alerte compte pour l'entreprise.

===assignment===
Choisissez une organisation que vous connaissez (votre employeur, votre école ou une entreprise fictive). Identifiez **trois actifs critiques**, indiquez pour chacun la propriété CIA la plus importante et pourquoi, et proposez **une mesure préventive et une mesure détective** par actif. Ne dépassez pas une page ; vous réutiliserez ce travail dans la leçon sur la gestion des risques.

===criteria===
- Lire l'intégralité de la leçon et tenter la vérification des connaissances avant d'afficher les réponses.
- Réaliser l'exercice de classification.
- Marquer la leçon comme terminée ; le quiz du module et l'examen final du cours évaluent ces notions.

===references===
- NIST SP 800-12 Rev. 1, *An Introduction to Information Security* — https://csrc.nist.gov/pubs/sp/800/12/r1/final
- NIST SP 800-207, *Zero Trust Architecture* — https://csrc.nist.gov/pubs/sp/800/207/final
- MITRE ATT&CK T1490, Inhibit System Recovery — https://attack.mitre.org/techniques/T1490/
- Microsoft, *Événements d'audit de sécurité relatifs aux modifications de groupes* — https://learn.microsoft.com/windows/security/threat-protection/auditing/event-4728
