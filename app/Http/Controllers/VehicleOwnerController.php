<?php

namespace App\Http\Controllers;

use App\Models\VehicleOwner;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class VehicleOwnerController extends Controller
{
    public function getVehicleOwnerImage($id) {
        $buf = VehicleOwner::find($id, 'picture');
        $filename = is_null($buf) ? null : $buf['picture'];
        $file = Storage::get($filename);
        $type = Storage::mimeType($filename);
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }

    public function newBlog(VehicleOwnerCreateRequest $request) {
        $date = $request->input('date');
        $location = $request->input('location');
        $duration = $request->input('duration');
        $title = $request->input('title');
        $body = $request->input('body');
        $goalAudience = $request->input('goal_audience');
        $tag = $request->input('tag');
        $image = $request->file('image');

        //$imageName = md5(time().uniqid()) . '.' . $image->getClientOriginalExtension();
        //$image->move(storage_path('app/images/blogs/'), $imageName);
        $imageName = $image->store('images/blogs');

        $blog = new Blog();

        $blog->date = $date;
        $blog->location = $location;
        $blog->duration = $duration;
        $blog->title = $title;
        $blog->body = $body;
        $blog->goal_audience = $goalAudience;
        $blog->wallpaper = $imageName;
        $blog->tag_id = $tag;
        $blog->user_id = auth()->id();

        try {
            $blog->save();
        } catch (QueryException $exception) {
            Storage::delete($imageName);
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 406);
        }

        return response()->json(['message' => 'new blog added', 'id' => $blog->id]);
    }
}
