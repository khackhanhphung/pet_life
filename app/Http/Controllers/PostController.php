<?php
namespace App\Http\Controllers;

use App\Like;
use App\Post;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;


class PostController extends Controller
{
    public function getDashboard()
    {
        // if(!Auth::user())
        //     return redirect()->route('home');
        
        $productList    = array();
        $user = User::orderBy('created_at', 'desc')->get();
        foreach ($user as $item ) {
            if ($item->name) {
                $temp    = array(
                    'desc'  => $item->email,
                    'value' => $item->name,
                    'image' => $item->avatar ? route('account.image', ['filename' => $item->avatar]) : '/pet_life/public/src/images/boa_hancock_wallpaper_blue_red_by_gian519.png'    // user avatar
                );
                array_push($productList, $temp);
            }
        }
        
        $suggestUsers = json_encode($productList);
        File::put('users.js', 'var searchplus = '.$suggestUsers);

        $posts = Post::orderBy('created_at', 'desc')->get();
        $postLikes = \DB::table('likes') ->select('post_id', \DB::raw("count(likes.like) as total"))
                 ->groupBy('post_id')->orderBy('total', 'DESC')
                 ->get();
        $trendPosts = array();
        if($postLikes) {
            $count = 0;
            foreach ($postLikes as $key => $value) {
                $count++;
                if($count > 5)
                    break;
                $temp = $value->post_id;
                $trendPost = Post::select('*')->where('id',  $temp)->get()->first();
                array_push($trendPosts, $trendPost);
            }
        }

        return view('dashboard', ['posts' => $posts, 'postLikes' => $postLikes, 'user' => Auth::user(), 'trendPosts' => $trendPosts]);
    }

    public function postCreatePost(Request $request)
    {
        // $this->validate($request, [
        //     'body' => 'required|max:1000'
        // ]);
        $post = new Post();
        $user = Auth::user();
        $file = $request->file('att_files');
        if($file[0] != null){
            for ($i=0; $i < count($file); $i++) { 
                $extension = $file[$i]->getClientOriginalExtension();
                Storage::disk('local')->put($file[$i]->getFilename().'.'.$extension,  File::get($file[$i]));
                $post->mime .= $file[$i]->getClientMimeType();
                $post->original_filename .= $file[$i]->getClientOriginalName() .',';
                $post->filename .= $file[$i]->getFilename().'.'.$extension .',';   
            }
        }
        
        $post->body = $request['body'];
        $token = $request['_token'];
        $message = '';
        if ($request->user()->posts()->save($post)) {
            return redirect()->route('dashboard');
            // return response()->json(
            //     [
            //         'post_id'   => $post->id,
            //         'post_user' => $post->user->first_name,
            //         'post_body' => $post->body,
            //         'create_at' => date_format(date_create($post->created_at), 'D M Y'),
            //         'file_name' => $post->filename
            //     ],
            //     200
            // );
            // $message = 'Post successfully created!';
        }else{
            $message = 'An error occur when create post. Please try again!';
        }
    }

    public function getDeletePost($post_id)
    {
        $post = Post::where('id', $post_id)->first();
        $user = Auth::user();
        if ($user->id != $post->user->id) {
            return response()->json(['error' => 'You not permitted to delete this post'],401);
        }
        $post->delete();
        return response()->json(['ok' => 200],200);
    }

    public function postEditPost(Request $request)
    {
        $this->validate($request, [
            'body' => 'required'
        ]);
        $post = Post::find($request['postId']);
        if (Auth::user() != $post->user) {
            return redirect()->back();
        }
        $post->body = $request['body'];
        $post->update();
        return response()->json(['new_body' => $post->body], 200);
    }

    public function postLikePost(Request $request)
    {
        $post_id = $request['postId'];
        // $update = false;
        $post = Post::find($post_id);
        $totalLike = \DB::table('likes') ->select('post_id', \DB::raw("count(likes.like) as total"))
                ->where('post_id', $post_id)
                ->groupBy('post_id')->orderBy('total', 'DESC')
                ->get();
        $numLike = count($totalLike) ? $totalLike[0]->total : 0;
        
        if (!$post) {
            return null;
        }
        
        $user = Auth::user();
        $like = $user->likes()->where('post_id', $post_id)->first();
        if ($like) {
            $like->delete();
            return response()->json(['unlike' => $numLike - 1]);
        } else {
            $like = new Like();
        }
        $like->like = 1;
        $like->user_id = $user->id;
        $like->post_id = $post->id;

        $like->save();
        
        return response()->json(['like' => $numLike + 1]);
    }

    /**
    * get post view
    */
    public function getPostView($post_id){
        $post = Post::where('id', $post_id)->first();
        $like = \DB::table('likes') ->select('post_id', \DB::raw("count(likes.like) as total"))
                ->where('post_id', $post_id)
                ->groupBy('post_id')->orderBy('total', 'DESC')
                ->get();
        $numLike = $like[0]->total ? $like[0]->total : '';
        return view('post-view', ['post' => $post, 'like' => $numLike, 'user' => Auth::user()]);
    }

    /**
    * get post for news page
    */
    public function getPostNews(){
        $user = User::orderBy('created_at', 'desc')->get();
        $posts = Post::orderBy('created_at', 'desc')->get();
        $postLikes = \DB::table('likes') ->select('post_id', \DB::raw("count(likes.like) as total"))
                 ->groupBy('post_id')->orderBy('total', 'DESC')
                 ->get();
        $trendPosts = array();
        if($postLikes) {
            $count = 0;
            foreach ($postLikes as $key => $value) {
                $count++;
                if($count > 5)
                    break;
                $temp = $value->post_id;
                $trendPost = Post::select('*')->where('id',  $temp)->get()->first();
                array_push($trendPosts, $trendPost);
            }
        }
        return view('news', ['posts' => $posts, 'postLikes' => $postLikes, 'user' => Auth::user(), 'trendPosts' => $trendPosts]);
    }

    /**
    * get searched users
    */
    public function getSearchUsers(Request $request){
        $users = User::where('name', 'like', '%' . $request['q'] . '%')->get();
        return view('search', ['users' => $users]);
    }
}