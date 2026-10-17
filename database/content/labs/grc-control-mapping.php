<?php

return [
    'slug' => 'grc-control-mapping-and-evidence',
    'path' => 'GRC',
    'course' => 'grc-professional',
    'module' => 'controls-and-auditing',
    'type' => 'simulation',
    'difficulty' => 'intermediate',
    'duration' => 40,
    'pass' => 70,
    'required' => false,
    'risk' => 'standard',
    'dataset' => 'grc-control-matrix.csv',

    'en' => [
        'title' => 'GRC Lab: Control Mapping and Audit Evidence',
        'summary' => 'Read a control matrix, distinguish a missing control from one that is designed but not proven to operate, and judge what counts as strong audit evidence of operating effectiveness.',
        'objectives' => [
            'Read a control matrix mapped to a framework',
            'Distinguish design deficiencies from operating-effectiveness deficiencies and from missing controls',
            'Understand why a policy without a test provides no assurance',
            'Judge the strength of audit evidence',
        ],
        'tools' => ['The in-browser dataset viewer', 'Optionally a spreadsheet'],
        'prerequisites' => ['Security Controls and Auditing module lessons'],
        'scenario' => "You are preparing for an internal audit. Review the control matrix, which maps each control to an ISO/IEC 27001 Annex A reference and records its status and the evidence on file.",
        'setup' => "The matrix is shown below. Columns: control_id, control, framework_ref, control_type, status, evidence, last_tested. Statuses are operating, design_only (designed but not shown to operate), and not_implemented. All data is synthetic.",
        'instructions' => "1. Review each control's status and the evidence on file.\n2. Identify the control that is designed but not proven to operate.\n3. Classify the control that is not implemented at all.\n4. Reason about what counts as strong evidence of operating effectiveness.\n5. Answer the questions.",
        'expected_results' => 'Correct classification of the deficiencies and a sound judgement of evidence quality.',
        'scoring' => 'Answers are scored on the server.',
        'solution' => "- **C-04** (backups) is *designed but not proven to operate*: a backup policy exists, but there is **no restore test on record** and no last_tested date. A control that is never tested provides no assurance it actually works — the restore test is the evidence of operating effectiveness.\n- **C-07** (change-management approvals) is **not implemented** and has no evidence: this is a control *gap* (a missing control), not merely an operating-effectiveness deficiency. It should be raised as a finding and added to the remediation plan with an owner and a date.\n- The strongest audit evidence of operating effectiveness is not the policy document or a verbal assurance, but artefacts showing the control actually operated over the period — for C-02 (MFA), the IdP policy export **plus a sample of actually-enrolled users**. Evidence should be collected as a by-product of the control operating, not reconstructed the week before an audit.",
        'cleanup' => 'No cleanup required.',
        'lessons_learned' => 'Auditing separates three things: whether a control is designed, whether it operates, and whether there is evidence of both. A policy with no test is a design on paper; a missing control is a gap; and the credibility of a control rests on evidence produced as it operates, with an owner and a review date for every exception.',
    ],
    'fr' => [
        'title' => 'Lab GRC : cartographie des mesures et preuves d\'audit',
        'summary' => 'Lisez une matrice de mesures, distinguez une mesure absente d\'une mesure conçue mais non prouvée comme opérante, et jugez ce qui constitue une preuve d\'audit solide d\'efficacité opérationnelle.',
        'objectives' => [
            'Lire une matrice de mesures associée à un référentiel',
            'Distinguer les déficiences de conception, les déficiences d\'efficacité opérationnelle et les mesures absentes',
            'Comprendre pourquoi une politique sans test n\'apporte aucune assurance',
            'Juger la solidité des preuves d\'audit',
        ],
        'tools' => ['La visionneuse de jeu de données intégrée', 'Au besoin, un tableur'],
        'prerequisites' => ['Leçons du module Mesures de sécurité et audit'],
        'scenario' => "Vous préparez un audit interne. Examinez la matrice de mesures, qui associe chaque mesure à une référence de l'Annexe A d'ISO/IEC 27001 et consigne son statut et les preuves disponibles.",
        'setup' => "La matrice s'affiche ci-dessous. Colonnes : control_id, control, framework_ref, control_type, status, evidence, last_tested. Les statuts sont operating (opérante), design_only (conçue mais non démontrée comme opérante) et not_implemented (non implémentée). Toutes les données sont synthétiques.",
        'instructions' => "1. Examinez le statut et les preuves de chaque mesure.\n2. Identifiez la mesure conçue mais non prouvée comme opérante.\n3. Classez la mesure qui n'est pas du tout implémentée.\n4. Raisonnez sur ce qui constitue une preuve solide d'efficacité opérationnelle.\n5. Répondez aux questions.",
        'expected_results' => 'Une classification correcte des déficiences et un jugement sain sur la qualité des preuves.',
        'scoring' => 'Les réponses sont notées côté serveur.',
        'solution' => "- **C-04** (sauvegardes) est *conçue mais non prouvée comme opérante* : une politique de sauvegarde existe, mais il n'y a **aucun test de restauration consigné** ni date de dernier test. Une mesure jamais testée n'apporte aucune assurance qu'elle fonctionne — le test de restauration est la preuve de l'efficacité opérationnelle.\n- **C-07** (approbations de gestion des changements) est **non implémentée** et sans preuve : c'est un *manque* de mesure (une mesure absente), pas seulement une déficience d'efficacité opérationnelle. Elle doit être remontée comme constatation et ajoutée au plan de remédiation avec un responsable et une date.\n- La preuve d'audit la plus solide d'efficacité opérationnelle n'est pas le document de politique ni une assurance verbale, mais des artefacts montrant que la mesure a réellement fonctionné sur la période — pour C-02 (MFA), l'export de la politique de l'IdP **plus un échantillon d'utilisateurs réellement enrôlés**. Les preuves doivent être collectées comme sous-produit du fonctionnement de la mesure, pas reconstituées la semaine précédant l'audit.",
        'cleanup' => 'Aucun nettoyage requis.',
        'lessons_learned' => 'L\'audit distingue trois choses : si une mesure est conçue, si elle fonctionne, et s\'il existe des preuves des deux. Une politique sans test est une conception sur le papier ; une mesure absente est un manque ; et la crédibilité d\'une mesure repose sur des preuves produites pendant son fonctionnement, avec un responsable et une date de revue pour chaque exception.',
    ],

    'tasks' => [
        [
            'type' => 'text', 'points' => 25, 'spec' => ['accepted' => ['C-04', 'C04', '4']],
            'en' => ['prompt' => 'Which control_id is designed but not proven to operate (a policy exists, but there is no test on record)?', 'hint' => 'Look for status design_only with an empty last_tested date.'],
            'fr' => ['prompt' => 'Quel control_id est conçu mais non prouvé comme opérant (une politique existe, mais aucun test n\'est consigné) ?', 'hint' => 'Cherchez le statut design_only avec une date last_tested vide.'],
        ],
        [
            'type' => 'choice', 'points' => 25, 'spec' => ['correct' => 'b'],
            'en' => ['prompt' => 'C-07 (change-management approvals) has status not_implemented and no evidence. In audit terms, this is:', 'options' => ['a' => 'An operating-effectiveness deficiency', 'b' => 'A control gap — a missing control to raise as a finding and remediate', 'c' => 'A false positive', 'd' => 'Fully compliant'], 'explanation' => 'A control that is not implemented at all is a gap (a missing control), distinct from a control that exists but did not operate. It should be raised as a finding with an owner and a remediation date.'],
            'fr' => ['prompt' => 'C-07 (approbations de gestion des changements) a le statut not_implemented et aucune preuve. En termes d\'audit, il s\'agit :', 'options' => ['a' => 'D\'une déficience d\'efficacité opérationnelle', 'b' => 'D\'un manque de mesure — une mesure absente à remonter comme constatation et à remédier', 'c' => 'D\'un faux positif', 'd' => 'D\'une conformité totale'], 'explanation' => 'Une mesure pas du tout implémentée est un manque (mesure absente), distinct d\'une mesure qui existe mais n\'a pas fonctionné. Elle doit être remontée comme constatation avec un responsable et une date de remédiation.'],
        ],
        [
            'type' => 'choice', 'points' => 25, 'spec' => ['correct' => 'b'],
            'en' => ['prompt' => 'Why is C-04 a concern even though a backup policy exists?', 'options' => ['a' => 'Policies are never needed', 'b' => 'A control that is designed but never tested provides no assurance it works — the restore test is the evidence', 'c' => 'It is fully effective as written', 'd' => 'Backups never need testing'], 'explanation' => 'Having a policy is design, not operation. Without a restore test there is no evidence the backups can actually be recovered, so the control provides no real assurance.'],
            'fr' => ['prompt' => 'Pourquoi C-04 est-il préoccupant même si une politique de sauvegarde existe ?', 'options' => ['a' => 'Les politiques ne sont jamais nécessaires', 'b' => 'Une mesure conçue mais jamais testée n\'apporte aucune assurance qu\'elle fonctionne — le test de restauration est la preuve', 'c' => 'Elle est pleinement efficace telle qu\'écrite', 'd' => 'Les sauvegardes n\'ont jamais besoin d\'être testées'], 'explanation' => 'Avoir une politique relève de la conception, pas du fonctionnement. Sans test de restauration, rien ne prouve que les sauvegardes peuvent réellement être restaurées, donc la mesure n\'apporte aucune assurance réelle.'],
        ],
        [
            'type' => 'choice', 'points' => 25, 'spec' => ['correct' => 'c'],
            'en' => ['prompt' => 'For C-02 (MFA on remote access), which is the strongest evidence of operating effectiveness?', 'options' => ['a' => 'The policy document alone', 'b' => 'A verbal confirmation from IT', 'c' => 'The IdP policy export plus a sample of actually-enrolled users showing the control operating', 'd' => 'A screenshot of the login page'], 'explanation' => 'Strong evidence shows the control actually operated over the period — configuration plus a sample of real enrolled users — not just that a policy or intention exists.'],
            'fr' => ['prompt' => 'Pour C-02 (MFA sur l\'accès distant), quelle est la preuve la plus solide d\'efficacité opérationnelle ?', 'options' => ['a' => 'Le document de politique seul', 'b' => 'Une confirmation verbale de l\'IT', 'c' => 'L\'export de la politique de l\'IdP plus un échantillon d\'utilisateurs réellement enrôlés montrant la mesure opérante', 'd' => 'Une capture d\'écran de la page de connexion'], 'explanation' => 'Une preuve solide montre que la mesure a réellement fonctionné sur la période — la configuration plus un échantillon d\'utilisateurs réellement enrôlés — et pas seulement qu\'une politique ou une intention existe.'],
        ],
    ],
];
