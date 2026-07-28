<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LegalPageController extends Controller
{
    /**
     * @var array<string, array{view: string, title_key: string, description_key: string}>
     */
    private const PAGES = [
        'terms' => [
            'view' => 'landing.legal.terms',
            'title_key' => 'legal.terms.meta_title',
            'description_key' => 'legal.terms.meta_description',
        ],
        'privacy' => [
            'view' => 'landing.legal.privacy',
            'title_key' => 'legal.privacy.meta_title',
            'description_key' => 'legal.privacy.meta_description',
        ],
        'refund-policy' => [
            'view' => 'landing.legal.refund',
            'title_key' => 'legal.refund.meta_title',
            'description_key' => 'legal.refund.meta_description',
        ],
        'faq' => [
            'view' => 'landing.legal.faq',
            'title_key' => 'legal.faq.meta_title',
            'description_key' => 'legal.faq.meta_description',
        ],
    ];

    public function __invoke(string $page): View
    {
        if (! isset(self::PAGES[$page])) {
            throw new NotFoundHttpException;
        }

        $definition = self::PAGES[$page];
        $routeName = match ($page) {
            'terms' => 'legal.terms',
            'privacy' => 'legal.privacy',
            'refund-policy' => 'legal.refund',
            'faq' => 'legal.faq',
        };

        return view($definition['view'], [
            'pageTitle' => __($definition['title_key']),
            'pageDescription' => __($definition['description_key']),
            'canonicalUrl' => route($routeName),
            'legal' => config('legal'),
        ]);
    }
}
