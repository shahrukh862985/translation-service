<?php

namespace App\Console\Commands;

use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:seed {count=100000} {lang=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with translations for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->argument('count');
        $lang = $this->argument('lang');
        $this->info("Seeding database with {$count} translations...");

        $tagIds = Tag::pluck('id')->toArray();
        
        // Calculate batch size based on memory limits
        $batchSize = 1000;
        $batches = ceil($count / $batchSize);
        
        $this->info("Seeding translations in {$batches} batches of {$batchSize}...");
        $bar = $this->output->createProgressBar($batches);
        $bar->start();
        
        for ($i = 0; $i < $batches; $i++) {
            DB::transaction(function () use ($batchSize, $tagIds,$lang) {
                $translations = Translation::factory()->count($batchSize)->create([
                    'language_id' => $lang
                ]);
                
                // Attach random tags to translations
                foreach ($translations as $translation) {
                    // Attach 1-3 random tags
                    $randomTagIds = array_rand(array_flip($tagIds), rand(1, 2));
                    if (!is_array($randomTagIds)) {
                        $randomTagIds = [$randomTagIds];
                    }
                    $translation->tags()->attach($randomTagIds);
                }
            });
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('Seeding completed successfully!');
        
        return Command::SUCCESS;
    }
}
