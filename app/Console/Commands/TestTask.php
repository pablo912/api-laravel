<?php

namespace App\Console\Commands;

use App\Http\Controllers\Padron\PadronController;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TestTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probando cron job';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {   
        try{

            Log::debug('Init Download');
            $download = app(PadronController::class)->download();
            Log::debug('End Download: '. $download );  
            
            Log::debug('Init Extractor');
            $extractor = app(PadronController::class)->extract();
            Log::debug('End Extractor: '.$extractor);
    
            Log::debug('Init Load data');
            $loadtdata = app(PadronController::class)->loadtdata();
            Log::debug('End Load data: ' .$loadtdata );
    

        }catch(Exception $e){

            Log::debug($e->getMessage());
            
        }

     
    }
}
