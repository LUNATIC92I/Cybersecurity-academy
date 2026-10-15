<?php

declare(strict_types=1);

namespace Database\Seeders;

final class CommerceSeeder
{
    public function run(): void
    {
        $plans = [
            ['free', 'none', 0, 0, 0, 1,
                ['Free', 'Start with the free SOC Analyst curriculum and public labs.', "Full SOC Analyst Professional course\nBrowser-based SOC labs\nKnowledge checks and final exam\nVerifiable certificate on completion"],
                ['Gratuit', 'Commencez avec le parcours Analyste SOC gratuit et les labs publics.', "Cours complet Analyste SOC Professionnel\nLabs SOC dans le navigateur\nQuiz de connaissances et examen final\nCertificat vérifiable à la réussite"]],
            ['monthly', 'month', 2900, 1, 0, 2,
                ['Professional Monthly', 'Every learning path, billed monthly as a prepaid period.', "All courses: SOC, Pentest and GRC\nAll labs and downloadable datasets\nAll examinations and certificates\nCancel any time — access runs to period end"],
                ['Professionnel mensuel', 'Tous les parcours, facturés au mois en période prépayée.', "Tous les cours : SOC, Pentest et GRC\nTous les labs et jeux de données\nTous les examens et certificats\nSans engagement — accès jusqu'à la fin de la période"]],
            ['annual', 'year', 24900, 1, 0, 3,
                ['Professional Annual', 'Every learning path for twelve months at a reduced rate.', "Everything in Professional Monthly\nTwo months free compared with monthly\nPriority support"],
                ['Professionnel annuel', 'Tous les parcours pendant douze mois à tarif réduit.', "Tout le contenu Professionnel mensuel\nDeux mois offerts par rapport au mensuel\nSupport prioritaire"]],
            ['enterprise', 'none', 0, 1, 1, 4,
                ['Enterprise', 'Team training with administrative access grants and reporting.', "Volume licensing for teams\nAdministrator-managed access grants\nProgress and certificate exports\nDedicated onboarding"],
                ['Entreprise', 'Formation des équipes avec attributions d\'accès et rapports.', "Licences en volume pour les équipes\nAccès attribués par l'administrateur\nExports de progression et de certificats\nAccompagnement dédié"]],
        ];
        foreach ($plans as [$code, $interval, $price, $all, $contact, $order, $en, $fr]) {
            db()->query(
                'INSERT INTO plans (code, billing_interval, price_cents, currency, includes_all_courses, is_contact_sales, sort_order)
                 VALUES (?, ?, ?, "USD", ?, ?, ?)
                 ON DUPLICATE KEY UPDATE billing_interval = VALUES(billing_interval), includes_all_courses = VALUES(includes_all_courses),
                   is_contact_sales = VALUES(is_contact_sales), sort_order = VALUES(sort_order)',
                [$code, $interval, $price, $all, $contact, $order]
            );
            $planId = (int) db()->value('SELECT id FROM plans WHERE code = ?', [$code]);
            foreach (['en' => $en, 'fr' => $fr] as $loc => [$name, $description, $features]) {
                db()->query(
                    'INSERT INTO plan_translations (plan_id, locale, name, description, features) VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), features = VALUES(features)',
                    [$planId, $loc, $name, $description, $features]
                );
            }
        }

        $achievements = [
            ['first-lesson', 'book', 'lessons_completed', 1, ['First Step', 'Completed your first lesson.'], ['Premier pas', 'Première leçon terminée.']],
            ['ten-lessons', 'layers', 'lessons_completed', 10, ['Dedicated Learner', 'Completed ten lessons.'], ['Apprenant assidu', 'Dix leçons terminées.']],
            ['first-lab', 'flask', 'labs_passed', 1, ['Hands-On', 'Passed your first practical lab.'], ['Mise en pratique', 'Premier lab pratique réussi.']],
            ['lab-specialist', 'terminal', 'labs_passed', 5, ['Lab Specialist', 'Passed five practical labs.'], ['Spécialiste des labs', 'Cinq labs pratiques réussis.']],
            ['first-exam', 'clipboard', 'exams_passed', 1, ['Assessed', 'Passed your first assessment.'], ['Évalué', 'Première évaluation réussie.']],
            ['streak-3', 'fire', 'streak_days', 3, ['Three-Day Streak', 'Studied three days in a row.'], ['Série de trois jours', 'Trois jours d\'étude consécutifs.']],
            ['streak-7', 'fire', 'streak_days', 7, ['Seven-Day Streak', 'Studied seven days in a row.'], ['Série de sept jours', 'Sept jours d\'étude consécutifs.']],
            ['first-certificate', 'award', 'certificates', 1, ['Certified', 'Earned your first Cybersecurity-Academy certificate.'], ['Certifié', 'Premier certificat Cybersecurity-Academy obtenu.']],
        ];
        foreach ($achievements as [$code, $icon, $type, $threshold, $en, $fr]) {
            db()->query(
                'INSERT INTO achievements (code, icon, criteria_type, threshold) VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE icon = VALUES(icon), criteria_type = VALUES(criteria_type), threshold = VALUES(threshold)',
                [$code, $icon, $type, $threshold]
            );
            $id = (int) db()->value('SELECT id FROM achievements WHERE code = ?', [$code]);
            foreach (['en' => $en, 'fr' => $fr] as $loc => [$title, $description]) {
                db()->query(
                    'INSERT INTO achievement_translations (achievement_id, locale, title, description) VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description)',
                    [$id, $loc, $title, $description]
                );
            }
        }
    }
}
