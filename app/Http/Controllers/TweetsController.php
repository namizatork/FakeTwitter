<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Tweet;
use App\Models\Comment;
use App\Models\Follower;

class TweetsController extends Controller
{
    public function index(Tweet $tweet, Follower $follower)
    {
        $user = auth()->user();
        $follow_ids = $follower->followingIds($user->id);
        if (!$follow_ids->isEmpty()) {
            foreach ($follow_ids as $follow_id) {
                $following_ids[] = $follow_id->followed_id;
            }
        } else {
            $following_ids = [];
        }

        $timelines = $tweet->getTimelines($user->id, $following_ids);

        return view('tweets.index', [
            'user'      => $user,
            'timelines' => $timelines
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        return view('tweets.create', [
            'user' => $user
        ]);
    }

    public function store(Request $request, Tweet $tweet)
    {
        $user = auth()->user();
        $data = $request->all();
        $validator = Validator::make($data, [
            'text' => ['required', 'string', 'max:140']
        ]);

        $validator->validate();
        $tweet->tweetStore($user->id, $data);

        return redirect('tweets');
    }

    public function show(Tweet $tweet, Comment $comment)
    {
        $user = auth()->user();
        $timeline = $tweet->getTimeline($tweet->id);
        $comments = $comment->getComments($tweet->id);

        return view('tweets.show', [
            'user'     => $user,
            'timeline' => $timeline,
            'comments' => $comments
        ]);
    }

    public function edit(Tweet $tweet)
    {
        $user = auth()->user();
        $tweets = $tweet->getTweet($user->id, $tweet->id);

        if (!isset($tweets)) {
            return redirect('tweets');
        }

        return view('tweets.edit', [
            'user'   => $user,
            'tweets' => $tweets
        ]);
    }

    public function update(Request $request, Tweet $tweet)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'text' => ['required', 'string', 'max:140']
        ]);

        $validator->validate();
        $tweet->tweetUpdate($tweet->id, $data);

        return redirect('tweets');
    }

    public function destroy(Tweet $tweet)
    {
        $user = auth()->user();
        $tweet->tweetDestroy($user->id, $tweet->id);

        return back();
    }
}
