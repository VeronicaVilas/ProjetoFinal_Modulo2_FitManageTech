<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Workout;
use App\Models\Exercise;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class StudentWorkoutController extends Controller
{
    public function index($studentId)
    {
        try {
            $userId = Auth::id();
            $student = Student::where('user_id', $userId)->find($studentId);

            if (!$student) {
                return $this->error('Estudante não encontrado ou não pertence ao usuário logado.', Response::HTTP_NOT_FOUND);
            }

            $workouts = Workout::query()
                ->where('student_id', $studentId)
                ->with(['exercise' => function ($query) {
                    $query->select('id', 'description');
                }])
                ->orderBy('day')
                ->orderBy('created_at')
                ->get(['*', 'workouts.exercise_id as exercise_id']);

            $groupedWorkouts = $workouts->groupBy('day');

            $response = [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'workouts' => [
                    'SEGUNDA' => $groupedWorkouts->get('SEGUNDA', []),
                    'TERÇA' => $groupedWorkouts->get('TERÇA', []),
                    'QUARTA' => $groupedWorkouts->get('QUARTA', []),
                    'QUINTA' => $groupedWorkouts->get('QUINTA', []),
                    'SEXTA' => $groupedWorkouts->get('SEXTA', []),
                    'SÁBADO' => $groupedWorkouts->get('SÁBADO', []),
                    'DOMINGO' => $groupedWorkouts->get('DOMINGO', []),
                ],
            ];

            return $response;

        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
