<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class AddPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create posts for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        for ($i = 0; $i < 21; $i++) {
            $post = new Post();
            $post->user_id = 'b291f596-7099-42b9-8222-1181145a6f18';
            $post->postable_id ='????????????';
            $post->postable_type ='????????????';
            $post->save();
        }
    }
}