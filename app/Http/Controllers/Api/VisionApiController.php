<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\VisionNotBelongsToUser;
use App\Http\Controllers\Controller;
use App\Http\Controllers\JsonResponseTrait;
use App\Http\Requests\Client\StoreClientVision;
use App\Http\Resources\Client\ClientVisionResource;
use App\Models\Vision;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image as Image;
use Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use File;

class VisionApiController extends Controller
{
    use JsonResponseTrait;

    public function index(Request $request)
    {
        $visions = auth()->user()->visions()->paginate(10);
        return $this->json('Client vision retrieved successfully', new LengthAwarePaginator(
            ClientVisionResource::collection($visions->items()),
            $visions->total(),
            $visions->perPage(),
            $visions->currentPage(),
            [
                'path' => app('url')->current()
            ]
        ));
    }

    public function store(StoreClientVision $request)
    {
        if($request->hasFile('images')){
           DB::transaction(function () use ($request){
               foreach($request->file('images') as $image){
                   $thumbnailImage = 'thumb-' . time() . '.' . $image->getClientOriginalExtension();
                   $originalImage = 'original-' . time() . '.' . $image->getClientOriginalExtension();
                   $destinationPath = public_path('uploads/' . auth()->user()->id . '/thumbnail');
                   if (!file_exists($destinationPath)) {
                       mkdir($destinationPath, 0777, true);
                   }
                   $img = Image::make($image->getRealPath());
                   $img->resize(100, 100, function ($constraint) {
                       $constraint->aspectRatio();
                   })->save($destinationPath . '/' . $thumbnailImage);

                   $destinationPath = public_path('uploads/' .Auth::id(). '/images');
                   if (!file_exists($destinationPath)) {
                       mkdir($destinationPath, 0777, true);
                   }
                   $image->move($destinationPath, $originalImage);
                   Vision::create([
                       'thumbnail_image' => $thumbnailImage,
                       'original_image' => $originalImage,
                       'user_id' => Auth::id(),
                   ]);
               }
           });
          return $this->json('Vision added successfully',Response::HTTP_CREATED);
        }
        return $this->bad("No images found",Response::HTTP_BAD_REQUEST);
    }

    public function destroy($id)
    {
        $vision = Vision::findOrFail($id);
        $this->visionUserCheck($vision);
        DB::transaction(function () use ($vision) {
            $thumbnail_image = public_path('uploads/' . auth()->user()->id . '/thumbnail/' . $vision->thumbnail_image);
            $original_image = public_path('uploads/' . auth()->user()->id . '/images/' . $vision->original_image);
            if (file_exists($thumbnail_image)) {
                File::delete($thumbnail_image);
            }
            if (file_exists($original_image)) {
                File::delete($original_image);
            }
            $vision->delete();
        });
        return $this->json('Vision deleted successfully', Response::HTTP_NO_CONTENT);
    }

    public function visionUserCheck($vision)
    {
        if (Auth::id() !== $vision->user_id)
         throw new VisionNotBelongsToUser;
    }
}
