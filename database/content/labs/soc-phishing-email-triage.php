<?php

return [
    'slug' => 'soc-phishing-email-triage',
    'path' => 'SOC',
    'course' => 'soc-analyst-professional',
    'module' => 'incident-response',
    'type' => 'simulation',
    'difficulty' => 'intermediate',
    'duration' => 35,
    'pass' => 70,
    'required' => false,
    'risk' => 'standard',
    'dataset' => 'soc-email-events.csv',

    'en' => [
        'title' => 'SOC Lab: Phishing Email Triage',
        'summary' => 'Read email authentication results (SPF/DKIM/DMARC) and header signals to spot a phishing message, recognise an impersonation attempt that authentication does not catch, and choose the correct containment action.',
        'objectives' => [
            'Interpret SPF, DKIM and DMARC results',
            'Spot look-alike (typosquatted) sender domains and reply-to mismatches',
            'Understand why passing authentication does not prove a message is safe',
            'Choose a sound first containment action for a confirmed phish',
        ],
        'tools' => ['The in-browser dataset viewer', 'Optionally a spreadsheet'],
        'prerequisites' => ['Incident Response: Lifecycle and Alert Triage'],
        'scenario' => "A batch of inbound messages has been flagged for review. Each row shows the envelope sender, the display name, the SPF/DKIM/DMARC verdicts, the reply-to address, the primary link domain and any attachment. Triage them.",
        'setup' => "The dataset is shown below. Columns: msg_id, sender, display_name, spf, dkim, dmarc, reply_to, url_domain, attachment. All data is synthetic.",
        'instructions' => "1. Review each message's authentication results and header fields.\n2. Identify the clearest credential-phishing message.\n3. Reason about the message that passes authentication but still looks like impersonation.\n4. Choose the correct first response, then answer the questions.",
        'expected_results' => 'The phishing message is identified from converging signals, and the containment action is proportionate.',
        'scoring' => 'Answers are scored on the server.',
        'solution' => "- **M-03** is the clearest credential phish: the sender domain `examp1e-helpdesk.com` is a look-alike (the letter *l* replaced by the digit *1*), SPF and DMARC both **fail**, the reply-to points to an unrelated external mailbox (`credentials@mail.ru`), and the link domain is a fake login page.\n- Failed SPF **and** failed DMARC together indicate the message was not sent by an authorised server for the domain it claims — a strong spoofing signal.\n- **M-07** passes SPF, DKIM and DMARC, yet it impersonates the CEO, sets reply-to to a personal Gmail address and is urgent. Authentication only proves the message really came from `example.com`'s mail; it does **not** prove the content is trustworthy, so this is a likely business email compromise / impersonation attempt that authentication alone will not catch.\n- The correct first action for M-03 is to contain it: quarantine or remove the message from any mailboxes that received it, block the sender and look-alike domain and the URL, and check who interacted with it — not to reply, and never to open the link outside a controlled analysis environment.",
        'cleanup' => 'No cleanup required.',
        'lessons_learned' => 'SPF/DKIM/DMARC are necessary but not sufficient: they tell you whether a message was sent by an authorised server, not whether it is benign. Phishing is confirmed by converging signals — look-alike domains, reply-to mismatches, failed authentication, suspicious links — and the first response is containment, not conversation.',
    ],
    'fr' => [
        'title' => 'Lab SOC : tri des courriels d\'hameçonnage',
        'summary' => 'Lisez les résultats d\'authentification des courriels (SPF/DKIM/DMARC) et les signaux d\'en-tête pour repérer un message d\'hameçonnage, reconnaître une tentative d\'usurpation que l\'authentification ne détecte pas, et choisir la bonne action de confinement.',
        'objectives' => [
            'Interpréter les résultats SPF, DKIM et DMARC',
            'Repérer les domaines expéditeurs sosies (typosquattés) et les incohérences de reply-to',
            'Comprendre pourquoi une authentification réussie ne prouve pas qu\'un message est sûr',
            'Choisir une bonne première action de confinement pour un hameçonnage confirmé',
        ],
        'tools' => ['La visionneuse de jeu de données intégrée', 'Au besoin, un tableur'],
        'prerequisites' => ['Réponse à incident : cycle de vie et tri des alertes'],
        'scenario' => "Un lot de messages entrants a été signalé pour examen. Chaque ligne indique l'expéditeur d'enveloppe, le nom affiché, les verdicts SPF/DKIM/DMARC, l'adresse reply-to, le domaine du lien principal et toute pièce jointe. Triez-les.",
        'setup' => "Le jeu de données s'affiche ci-dessous. Colonnes : msg_id, sender, display_name, spf, dkim, dmarc, reply_to, url_domain, attachment. Toutes les données sont synthétiques.",
        'instructions' => "1. Examinez les résultats d'authentification et les champs d'en-tête de chaque message.\n2. Identifiez le message d'hameçonnage de type vol d'identifiants le plus clair.\n3. Raisonnez sur le message qui réussit l'authentification mais ressemble malgré tout à une usurpation.\n4. Choisissez la bonne première réponse, puis répondez aux questions.",
        'expected_results' => 'Le message d\'hameçonnage est identifié par des signaux convergents, et l\'action de confinement est proportionnée.',
        'scoring' => 'Les réponses sont notées côté serveur.',
        'solution' => "- **M-03** est l'hameçonnage de vol d'identifiants le plus clair : le domaine expéditeur `examp1e-helpdesk.com` est un sosie (la lettre *l* remplacée par le chiffre *1*), SPF et DMARC **échouent** tous deux, le reply-to pointe vers une boîte externe sans rapport (`credentials@mail.ru`) et le domaine du lien est une fausse page de connexion.\n- Un SPF **et** un DMARC en échec indiquent ensemble que le message n'a pas été envoyé par un serveur autorisé pour le domaine revendiqué — un fort signal d'usurpation.\n- **M-07** réussit SPF, DKIM et DMARC, mais il usurpe le PDG, fixe le reply-to vers une adresse Gmail personnelle et se veut urgent. L'authentification prouve seulement que le message vient bien de la messagerie d'`example.com` ; elle ne prouve **pas** que le contenu est digne de confiance, donc c'est une probable compromission de messagerie d'entreprise / usurpation que l'authentification seule ne détecte pas.\n- La bonne première action pour M-03 est de le confiner : mettre en quarantaine ou retirer le message des boîtes qui l'ont reçu, bloquer l'expéditeur, le domaine sosie et l'URL, et vérifier qui a interagi avec lui — ne pas répondre, et ne jamais ouvrir le lien hors d'un environnement d'analyse contrôlé.",
        'cleanup' => 'Aucun nettoyage requis.',
        'lessons_learned' => 'SPF/DKIM/DMARC sont nécessaires mais non suffisants : ils indiquent si un message a été envoyé par un serveur autorisé, pas s\'il est inoffensif. L\'hameçonnage se confirme par des signaux convergents — domaines sosies, incohérences de reply-to, authentification en échec, liens suspects — et la première réponse est le confinement, pas la conversation.',
    ],

    'tasks' => [
        [
            'type' => 'text', 'points' => 25, 'spec' => ['accepted' => ['M-03', 'M03', '3']],
            'en' => ['prompt' => 'Which message ID is the clearest credential-phishing attempt (look-alike sender domain, failed SPF and DMARC, reply-to to an unrelated external mailbox)?', 'hint' => 'Look for the row where both SPF and DMARC fail and the sender domain is slightly misspelled.'],
            'fr' => ['prompt' => 'Quel identifiant de message est la tentative d\'hameçonnage de vol d\'identifiants la plus claire (domaine expéditeur sosie, SPF et DMARC en échec, reply-to vers une boîte externe sans rapport) ?', 'hint' => 'Cherchez la ligne où SPF et DMARC échouent tous deux et où le domaine expéditeur est légèrement mal orthographié.'],
        ],
        [
            'type' => 'choice', 'points' => 25, 'spec' => ['correct' => 'b'],
            'en' => ['prompt' => 'For M-03, which authentication results most strongly indicate spoofing?', 'options' => ['a' => 'SPF and DMARC both pass', 'b' => 'SPF fails and DMARC fails', 'c' => 'DKIM passes only', 'd' => 'No checks failed'], 'explanation' => 'A failed SPF together with a failed DMARC means the message was not sent by a server authorised for the domain it claims — a strong spoofing signal.'],
            'fr' => ['prompt' => 'Pour M-03, quels résultats d\'authentification indiquent le plus fortement une usurpation ?', 'options' => ['a' => 'SPF et DMARC réussissent tous deux', 'b' => 'SPF échoue et DMARC échoue', 'c' => 'DKIM réussit seulement', 'd' => 'Aucune vérification n\'a échoué'], 'explanation' => 'Un SPF en échec avec un DMARC en échec signifie que le message n\'a pas été envoyé par un serveur autorisé pour le domaine revendiqué — un fort signal d\'usurpation.'],
        ],
        [
            'type' => 'choice', 'points' => 25, 'spec' => ['correct' => 'b'],
            'en' => ['prompt' => 'M-07 passes SPF, DKIM and DMARC, but it impersonates the CEO, sets reply-to to a personal Gmail address and is urgent. What does this most likely indicate?', 'options' => ['a' => 'It is safe, because authentication passed', 'b' => 'A business email compromise / impersonation attempt that authentication alone does not catch', 'c' => 'A routine false positive to close', 'd' => 'A newsletter'], 'explanation' => 'Authentication proves the message came from the claimed domain\'s mail, not that the content is trustworthy. CEO impersonation with a mismatched personal reply-to and urgency is a classic business email compromise pattern.'],
            'fr' => ['prompt' => 'M-07 réussit SPF, DKIM et DMARC, mais il usurpe le PDG, fixe le reply-to vers une adresse Gmail personnelle et se veut urgent. Qu\'est-ce que cela indique le plus probablement ?', 'options' => ['a' => 'Il est sûr, car l\'authentification a réussi', 'b' => 'Une compromission de messagerie d\'entreprise / usurpation que l\'authentification seule ne détecte pas', 'c' => 'Un faux positif de routine à clôturer', 'd' => 'Une infolettre'], 'explanation' => 'L\'authentification prouve que le message vient de la messagerie du domaine revendiqué, pas que le contenu est fiable. L\'usurpation du PDG avec un reply-to personnel incohérent et l\'urgence est un schéma classique de compromission de messagerie d\'entreprise.'],
        ],
        [
            'type' => 'choice', 'points' => 25, 'spec' => ['correct' => 'c'],
            'en' => ['prompt' => 'What is the best first response to the confirmed phishing message M-03?', 'options' => ['a' => 'Reply to the sender to ask whether it is legitimate', 'b' => 'Open the link to verify it before deciding', 'c' => 'Contain it: quarantine/remove the message from mailboxes, block the sender, look-alike domain and URL, and check who interacted', 'd' => 'Ignore it, since authentication already failed'], 'explanation' => 'Containment comes first: remove the message, block the indicators and identify anyone who interacted. Replying tips off the attacker, and the link should only ever be examined in a controlled analysis environment.'],
            'fr' => ['prompt' => 'Quelle est la meilleure première réponse au message d\'hameçonnage confirmé M-03 ?', 'options' => ['a' => 'Répondre à l\'expéditeur pour demander s\'il est légitime', 'b' => 'Ouvrir le lien pour le vérifier avant de décider', 'c' => 'Le confiner : mettre en quarantaine/retirer le message des boîtes, bloquer l\'expéditeur, le domaine sosie et l\'URL, et vérifier qui a interagi', 'd' => 'L\'ignorer, puisque l\'authentification a déjà échoué'], 'explanation' => 'Le confinement d\'abord : retirer le message, bloquer les indicateurs et identifier qui a interagi. Répondre alerte l\'attaquant, et le lien ne doit être examiné que dans un environnement d\'analyse contrôlé.'],
        ],
    ],
];
