<?php

namespace App\Providers\ResponseServiceProvider;

namespace App\Http\Controllers\API;

use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Providers\ResponseServiceProvider;
use App\Post;
use Exception;

class PostController extends Controller
{
    public function index()
    {
        return PostResource::collection(Post::all());
    }
//For show post top five
    public function indexTopFive()
    {
        $postsQuery = Post::withCount('comments')->orderBy('comments_count')->limit(5);
        return PostResource::collection($postsQuery->get());
    }

// For save Post
    public function store(CreatePostRequest $request)
    {
        try {
            $file_name = null;
            // converting base64 decoded image to simple image if exist
            if (!empty($request['attachment'])) {
                // upload Attachment
                $destinationPath = storage_path('\post\users\\');
                $request_type_aux = explode("/", $request['attachment']['mime']);
                $attachment_extention = $request_type_aux[1];
                $image_base64 = base64_decode($request['attachment']['data']);
                $file_name = uniqid() . '.' . $attachment_extention;
                $file = $destinationPath . $file_name;
                // saving in local storage
                file_put_contents($file, $image_base64);
            }
            $data = $request->validated();
            $post = new Post();
            $post->attachment = $file_name;
            $post->title = $data['title'];
            $post->body = $data['body'];
            // $post = Post::make($data);
            $post->user()->associate($data['user_id']);
            $post->save();
            if ($post) {
                $success['message'] =  "Post Create Successfully";
                return ResponseServiceProvider::sendResponse($success, 200);
            }
            return new PostResource($post->fresh());
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

//For show Post
    public function show(Post $post)
    {
        return new PostResource($post);
    }

//For Update post
    public function UpdatePost(UpdatePostRequest $request)
    {
        try {
            $input = $request->validated();
            $data = Post::find($request['id']);
            $data->title = $request->input('title');
            $data->body = $request->input('body');

            if (!empty($input['attachment'])) {
                // upload Attachment
                $destinationPath = storage_path('\post\users\\');
                $input_type_aux = explode("/", $input['attachment']['mime']);
                $attachment_extention = $input_type_aux[1];
                $image_base64 = base64_decode($input['attachment']['data']);
                $file_name = uniqid() . '.' . $attachment_extention;
                $file = $destinationPath . $file_name;
                // saving in local storage
                file_put_contents($file, $image_base64);
                $data->attachment = $file_name;
            }
            //store your file into directory and db
            $data->save();
            if ($data) {
                $success['message'] =  "Post Updated Successfully";
                return ResponseServiceProvider::sendResponse($success, 200);
            }
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

//For Delete post
    public function DeletePost($id)
    {
        try {
            $user = new Post();
            $user = Post::find($id);
            if ($user) {
                $user->delete();
                $success['message'] =  "Post Deleted Successfully";
                return ResponseServiceProvider::sendResponse($success, 200);
            } else {
                $success['message'] =  "Post not exist";
                throw new Exception($success['message']);
            }
        } catch (\Exception $success) {
            throw new Exception($success['message']);
        }
    }
}
