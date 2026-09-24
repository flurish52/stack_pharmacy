<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Regenerate the public sitemap.xml';

    public function handle(): void
    {
        $sitemap = Sitemap::create()
            ->add(Url::create(route('pharm.home'))->setPriority(1.0))
            ->add(Url::create(route('shop.index'))->setPriority(0.9))
            ->add(Url::create(route('about-us'))->setPriority(0.7))
            ->add(Url::create(route('services.index'))->setPriority(0.7))
            ->add(Url::create(route('training.index'))->setPriority(0.6))
            ->add(Url::create(route('contact.index'))->setPriority(0.5));

        Product::query()->each(function (Product $product) use ($sitemap) {
            $sitemap->add(
                Url::create(route('shop.show', $product))
                    ->setLastModificationDate($product->updated_at)
                    ->setPriority(0.8)
            );
        });

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated: ' . public_path('sitemap.xml'));
    }
}
