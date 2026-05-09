<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Group;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller {
    public function index(Group $group) {
        return TagResource::collection(Tag::where('group_id', $group->id)->orderBy('name')->get());
    }

    public function store(Request $request, Group $group) {
        $data = $request->validate(['name' => 'required|string|max:80', 'color' => 'nullable|string|size:7']);
        $tag  = Tag::firstOrCreate(['group_id' => $group->id, 'name' => $data['name']], $data + ['group_id' => $group->id]);
        return new TagResource($tag);
    }

    public function destroy(Group $group, Tag $tag) {
        $tag->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
