<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateProfessorRatings extends Command
{
    protected $signature = 'professors:update-averages';
    protected $description = 'Update average ratings for all professors';

    public function handle()
    {

        $professorAverages = DB::table('course_professor')
            ->selectRaw('professor_id, AVG(average_rating) as new_rating')
            ->groupBy('professor_id')
            ->get();


        foreach ($professorAverages as $professor) {
            DB::table('users')
                ->where('id', $professor->professor_id)
                ->update(['average_rating' => $professor->new_rating]);
        }

        $this->info('Professor average ratings updated successfully!');
    }
}