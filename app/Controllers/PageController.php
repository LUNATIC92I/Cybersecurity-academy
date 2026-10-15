<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\CourseRepository;
use App\Repositories\LabRepository;
use App\Repositories\PathRepository;
use App\Repositories\StatsRepository;
use App\Services\Localizer;

final class PageController extends Controller
{
    public function home(Request $request): Response
    {
        $paths = PathRepository::all();
        $courses = CourseRepository::featured(3);
        $labs = array_slice(LabRepository::published(), 0, 3);
        $stats = StatsRepository::public();
        $publicCertificates = db()->fetchAll(
            'SELECT c.holder_name, c.title_en, c.title_fr, c.issued_at, c.share_token, c.certificate_number
             FROM certificates c WHERE c.status = "valid" AND c.is_public = 1 ORDER BY c.issued_at DESC LIMIT 3'
        );
        return view('pages/home', [
            'paths' => $paths,
            'courses' => $courses,
            'labs' => $labs,
            'stats' => $stats,
            'publicCertificates' => $publicCertificates,
            'faqs' => array_slice($this->faqItems(), 0, 5),
            'meta' => $this->meta(__('meta.home.title'), __('meta.home.description'), ['og_type' => 'website']),
        ]);
    }

    public function about(Request $request): Response
    {
        return view('pages/about', [
            'stats' => StatsRepository::public(),
            'meta' => $this->meta(__('nav.about'), __('about.lead')),
        ]);
    }

    public function methodology(Request $request): Response
    {
        return view('pages/methodology', ['meta' => $this->meta(__('methodology.title'), __('methodology.lead'))]);
    }

    public function pricing(Request $request): Response
    {
        $plans = db()->fetchAll('SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order, id');
        $plans = Localizer::attach($plans, 'plan_translations', 'plan_id', 't', locale());
        $courses = CourseRepository::catalog(['access' => 'premium'], 1, 50)['items'];
        return view('pages/pricing', [
            'plans' => $plans,
            'premiumCourses' => $courses,
            'live' => \App\Services\PaymentService::isLive(),
            'meta' => $this->meta(__('nav.pricing'), __('pricing.lead')),
        ]);
    }

    public function faq(Request $request): Response
    {
        $items = $this->faqItems();
        $structured = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
            ], $items),
        ];
        return view('pages/faq', [
            'faqs' => $items,
            'meta' => $this->meta(__('nav.faq'), __('faq.lead'), ['structured' => $structured]),
        ]);
    }

    public function success(Request $request): Response
    {
        $stats = StatsRepository::public();
        $certs = db()->fetchAll(
            'SELECT c.holder_name, c.title_en, c.title_fr, c.issued_at, c.share_token, c.certificate_number, lp.code AS path_code
             FROM certificates c LEFT JOIN learning_paths lp ON lp.id = c.path_id
             WHERE c.status = "valid" AND c.is_public = 1 ORDER BY c.issued_at DESC LIMIT 12'
        );
        $byPath = db()->fetchAll(
            'SELECT lp.code, COUNT(DISTINCT e.user_id) AS learners, COUNT(DISTINCT cert.id) AS certificates
             FROM learning_paths lp
             LEFT JOIN learning_path_courses lpc ON lpc.path_id = lp.id
             LEFT JOIN enrollments e ON e.course_id = lpc.course_id
             LEFT JOIN certificates cert ON cert.path_id = lp.id AND cert.status = "valid"
             GROUP BY lp.id, lp.code ORDER BY lp.sort_order'
        );
        $profiles = db()->fetchAll(
            'SELECT display_name, public_slug, headline FROM users WHERE public_profile = 1 AND status = "active" AND public_slug IS NOT NULL ORDER BY last_login_at DESC LIMIT 8'
        );
        return view('pages/success', [
            'stats' => $stats,
            'certs' => $certs,
            'byPath' => $byPath,
            'profiles' => $profiles,
            'meta' => $this->meta(__('nav.success'), __('success.lead')),
        ]);
    }

    public function certifications(Request $request): Response
    {
        $courses = CourseRepository::catalog([], 1, 50)['items'];
        return view('pages/certifications', [
            'courses' => array_filter($courses, fn ($c) => (int) $c['certificate_enabled'] === 1),
            'meta' => $this->meta(__('nav.certifications'), __('certs.lead')),
        ]);
    }

    public function legal(Request $request): Response
    {
        $page = match ($request->path) {
            '/privacy' => 'privacy',
            '/terms' => 'terms',
            '/cookies' => 'cookies',
            '/responsible-use' => 'responsible',
            default => throw HttpException::notFound(),
        };
        return view('legal/' . locale() . '/' . $page, [
            'page' => $page,
            'meta' => $this->meta(__('legal.' . $page . '.title'), __('legal.' . $page . '.description')),
        ], 'layouts/legal');
    }

    public function sitemap(Request $request): Response
    {
        $urls = [];
        foreach (['home', 'about', 'methodology', 'catalog', 'labs.index', 'assessments.index', 'certifications', 'verify.form', 'success', 'pricing', 'faq', 'contact', 'legal.privacy', 'legal.terms', 'legal.cookies', 'legal.responsible'] as $name) {
            $urls[] = route($name);
        }
        foreach (db()->column('SELECT slug FROM learning_paths WHERE is_published = 1') as $slug) {
            $urls[] = route('paths.show', ['slug' => $slug]);
        }
        foreach (db()->column('SELECT slug FROM courses WHERE status = "published"') as $slug) {
            $urls[] = route('courses.show', ['slug' => $slug]);
        }
        foreach (db()->column('SELECT slug FROM labs WHERE status = "published"') as $slug) {
            $urls[] = route('labs.show', ['slug' => $slug]);
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
        foreach ($urls as $path) {
            $loc = url($path);
            $xml .= '  <url><loc>' . e($loc) . '</loc>'
                . '<xhtml:link rel="alternate" hreflang="en" href="' . e($loc . '?lang=en') . '"/>'
                . '<xhtml:link rel="alternate" hreflang="fr" href="' . e($loc . '?lang=fr') . '"/>'
                . "</url>\n";
        }
        $xml .= '</urlset>';
        return Response::text($xml, 200, 'application/xml; charset=UTF-8')->header('Cache-Control', 'public, max-age=3600');
    }

    public function robots(Request $request): Response
    {
        $body = "User-agent: *\nDisallow: /admin\nDisallow: /dashboard\nDisallow: /learn\nDisallow: /attempts\nDisallow: /checkout\nAllow: /\n\nSitemap: " . url('/sitemap.xml') . "\n";
        return Response::text($body)->header('Cache-Control', 'public, max-age=86400');
    }

    private function faqItems(): array
    {
        $items = [];
        for ($i = 1; $i <= 10; $i++) {
            $items[] = ['q' => __("faq.q$i"), 'a' => __("faq.a$i")];
        }
        return $items;
    }
}
