<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Firmware;
use App\Models\Image;
use App\Models\Post;
use App\Models\Question;
use App\Models\Reply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as FacadesFile;

use Symfony\Component\Finder\Finder;

class StatisticController extends Controller
{
    public function index()
    {
        // sleep(3);
        // Получаем количество записей для каждой сущности
        $postsCount = Post::count();
        $questionsCount = Question::count();
        $commentsCount = Comment::count();
        $imagesCount = Image::count();
        $firmwaresCount = Firmware::count();
        $userRegistrations = User::count();
        $commentsCount = Comment::count();
        $repiesCount = Reply::count();

        $directory = storage_path('app/public/firmwares');
        $firmwaresFilesCount = count(FacadesFile::files($directory));


        $finder = new Finder();
        $finder->files()->in(storage_path('app/public/images'));
        $usersImagesCount = iterator_count($finder);



        // Возвращаем статистическую информацию в формате JSON
        return response()->json([
            'postsCount' => $postsCount,
            'questionsCount' => $questionsCount,
            'commentsCount' => $commentsCount,
            'imagesCount' => $imagesCount,
            'firmwaresCount' => $firmwaresCount,
            'userRegistrations' => $userRegistrations,
            'firmwaresFilesCount' => $firmwaresFilesCount,
            'usersImagesCount' =>  $usersImagesCount,
            'commentsCount' => $commentsCount,
            'repliesCount' => $repiesCount
        ]);
    }
}
