<?php

return [
    'slug' => 'grc-risk-scoring-and-register',
    'path' => 'GRC',
    'course' => 'grc-professional',
    'module' => 'risk-management',
    'type' => 'simulation',
    'difficulty' => 'beginner',
    'duration' => 40,
    'pass' => 70,
    'required' => true,
    'risk' => 'standard',
    'dataset' => 'grc-risk-register.csv',

    'en' => [
        'title' => 'GRC Lab: Risk Scoring and the Risk Register',
        'summary' => 'Score inherent and residual risk, prioritise treatment and read a risk register correctly.',
        'objectives' => ['Compute risk as likelihood × impact', 'Distinguish inherent from residual risk', 'Prioritise risks for treatment', 'Interpret a risk register'],
        'tools' => ['The in-browser register viewer', 'Optionally a spreadsheet'],
        'prerequisites' => ['Information Security Risk Management'],
        'scenario' => "You have inherited a small risk register. Review the entries, confirm the scoring and advise which risk to treat first.",
        'setup' => "The register is shown below. Columns: risk_id, asset, threat, vulnerability, likelihood (1-5), impact (1-5), inherent_score, treatment, residual_score, owner.",
        'instructions' => "1. Confirm that inherent_score = likelihood × impact for each row.\n2. Identify the risk with the highest inherent score.\n3. Identify which risk reduces the most after treatment (inherent − residual).\n4. Answer the questions.",
        'expected_results' => 'Correct scores and a defensible prioritisation.',
        'scoring' => 'Answers are scored on the server.',
        'solution' => "- R-001 has the highest inherent score: likelihood 4 × impact 5 = **20**.\n- Risk is likelihood × impact; residual risk is what remains after the treatment is applied.\n- R-001 also reduces the most (20 → 6, a reduction of 14), which supports treating it first given both its size and the effectiveness of encryption + DLP.",
        'cleanup' => 'No cleanup required.',
        'lessons_learned' => 'A risk register makes prioritisation explicit and defensible. Treat by residual exposure and treatment effectiveness, not by inherent score alone, and always record an owner so each risk is someone\'s responsibility.',
    ],
    'fr' => [
        'title' => 'Lab GRC : notation des risques et registre des risques',
        'summary' => 'Évaluez le risque inhérent et résiduel, priorisez le traitement et lisez correctement un registre des risques.',
        'objectives' => ['Calculer le risque comme vraisemblance × impact', 'Distinguer risque inhérent et risque résiduel', 'Prioriser les risques à traiter', 'Interpréter un registre des risques'],
        'tools' => ['La visionneuse de registre intégrée', 'Au besoin, un tableur'],
        'prerequisites' => ['Gestion des risques de sécurité de l\'information'],
        'scenario' => "Vous héritez d'un petit registre des risques. Examinez les entrées, vérifiez la notation et indiquez quel risque traiter en premier.",
        'setup' => "Le registre s'affiche ci-dessous. Colonnes : risk_id, asset, threat, vulnerability, likelihood (1-5), impact (1-5), inherent_score, treatment, residual_score, owner.",
        'instructions' => "1. Vérifiez que inherent_score = vraisemblance × impact pour chaque ligne.\n2. Identifiez le risque au score inhérent le plus élevé.\n3. Identifiez le risque qui diminue le plus après traitement (inhérent − résiduel).\n4. Répondez aux questions.",
        'expected_results' => 'Des scores corrects et une priorisation défendable.',
        'scoring' => 'Les réponses sont notées côté serveur.',
        'solution' => "- R-001 a le score inhérent le plus élevé : vraisemblance 4 × impact 5 = **20**.\n- Le risque est vraisemblance × impact ; le risque résiduel est ce qui subsiste après l'application du traitement.\n- R-001 diminue aussi le plus (20 → 6, soit une réduction de 14), ce qui justifie de le traiter en premier compte tenu de son ampleur et de l'efficacité du chiffrement + DLP.",
        'cleanup' => 'Aucun nettoyage requis.',
        'lessons_learned' => 'Un registre des risques rend la priorisation explicite et défendable. Traitez selon l\'exposition résiduelle et l\'efficacité du traitement, pas selon le seul score inhérent, et consignez toujours un propriétaire pour que chaque risque relève de quelqu\'un.',
    ],

    'tasks' => [
        [
            'type' => 'text', 'points' => 25, 'spec' => ['accepted' => ['R-001', 'R001', 'customer database']],
            'en' => ['prompt' => 'Which risk has the highest inherent score?', 'hint' => 'Multiply likelihood by impact for each row.'],
            'fr' => ['prompt' => 'Quel risque a le score inhérent le plus élevé ?', 'hint' => 'Multipliez la vraisemblance par l\'impact pour chaque ligne.'],
        ],
        [
            'type' => 'number', 'points' => 25, 'spec' => ['value' => 20, 'tolerance' => 0],
            'en' => ['prompt' => 'What is that risk\'s inherent score (likelihood × impact)?', 'hint' => 'Likelihood 4 × impact 5.'],
            'fr' => ['prompt' => 'Quel est le score inhérent de ce risque (vraisemblance × impact) ?', 'hint' => 'Vraisemblance 4 × impact 5.'],
        ],
        [
            'type' => 'choice', 'points' => 25, 'spec' => ['correct' => 'b'],
            'en' => ['prompt' => 'What does the residual score represent?', 'options' => ['a' => 'The risk before any controls', 'b' => 'The risk that remains after the treatment is applied', 'c' => 'The cost of the treatment', 'd' => 'The likelihood only'], 'explanation' => 'Residual risk is what remains once the planned treatment (controls) is in place. Inherent risk is before controls.'],
            'fr' => ['prompt' => 'Que représente le score résiduel ?', 'options' => ['a' => 'Le risque avant toute mesure', 'b' => 'Le risque qui subsiste après l\'application du traitement', 'c' => 'Le coût du traitement', 'd' => 'La vraisemblance uniquement'], 'explanation' => 'Le risque résiduel est ce qui subsiste une fois le traitement (les mesures) en place. Le risque inhérent est celui d\'avant les mesures.'],
        ],
        [
            'type' => 'choice', 'points' => 25, 'spec' => ['correct' => 'a'],
            'en' => ['prompt' => 'Based on both inherent score and treatment effectiveness, which risk should be treated first?', 'options' => ['a' => 'R-001 (customer database)', 'b' => 'R-005 (payroll process)', 'c' => 'R-003 (internet firewall)', 'd' => 'All are equal priority'], 'explanation' => 'R-001 has the highest inherent score (20) and the largest reduction after treatment (to 6), making it the clear first priority.'],
            'fr' => ['prompt' => 'En tenant compte du score inhérent et de l\'efficacité du traitement, quel risque traiter en premier ?', 'options' => ['a' => 'R-001 (base de données clients)', 'b' => 'R-005 (processus de paie)', 'c' => 'R-003 (pare-feu Internet)', 'd' => 'Tous ont la même priorité'], 'explanation' => 'R-001 a le score inhérent le plus élevé (20) et la plus forte réduction après traitement (jusqu\'à 6), ce qui en fait clairement la première priorité.'],
        ],
    ],
];
