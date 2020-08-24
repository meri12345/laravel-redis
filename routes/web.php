<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;


class Articles{
    public function all(){
       return \Cache::remember('articles.all',60*60,function(){
            return \App\Article::all();
        });
    }

}
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    Redis::incr('visits');
    $visits = Redis::get("visits");
    return view('home',compact('visits'));
});

Route::get('/videos/{id}', function ($id) {
   $downloads = Redis::get("videos.$id.downloads");
    return view('welcome',[
        'downloads'=>$downloads,
        'id'=>$id
    ]);
});

Route::get('/videos/{id}/download', function ($id) {
    Redis::incr("videos.$id.downloads");
    return redirect("/videos/$id");
});




Route::get('/articles',function(){
    $trending = Redis::zrevrange('trending',0,2);
//
//    $articles = \App\Article::hydrate(
//        array_map('json_decode',$trending)
//    );

    $articles = collect($trending)->map(function($id){
       return \App\Article::find($id);
    });
    return $articles;

});

Route::get('/articles/cache',function(Articles $articles){
//    if($value=Redis::get('all.articles')){
//
//        return  json_decode($value);
//    }
//
//    $articles = \App\Article::all();
//    Redis::set('all.articles',$articles);
//    return $articles;

     return $articles->all();


});

Route::get('/articles/{article}',function(\App\Article $article){
    $article = Redis::zincrby('trending',1,$article->id);
    //$article = Redis::zincrby('trending',1,$article);
    return $article;

});
