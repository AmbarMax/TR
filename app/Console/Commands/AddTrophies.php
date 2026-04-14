<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Trophy;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AddTrophies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-tro';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create tro for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::beginTransaction();
            $user = User::where('id', 'c0ca5587-5288-4eb6-8341-db21aff3f8ce')->first();

            $trophy = new Trophy();
            $trophy->name = 'TestName2';
            $trophy->image ='TestImage2';
            $trophy->price = 11;
            $trophy->receive = 11;
            $trophy->description ='????????????';
            $trophy->save();

            $user->trophies()->attach($trophy->id);
            DB::commit();
            app('log')->info('Operation completed successfully.');

        }catch (\Exception $e){
            Log::error('FeedService@share: ' . $e->getMessage());
            DB::rollBack();
            app('log')->error('Operation failed. Check the error logs for details.');

        }
    }
}