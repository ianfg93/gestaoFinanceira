<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Group;
use Illuminate\Http\Request;

class CategoryController extends Controller {
    public function index(Group $group) {
        $cats = Category::where('group_id', $group->id)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        return CategoryResource::collection($cats);
    }

    public function store(Request $request, Group $group) {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'color'      => 'nullable|string|size:7',
            'icon'       => 'nullable|string|max:50',
            'type'       => 'sometimes|in:expense,income,investment,all',
            'parent_id'  => 'nullable|integer|exists:categories,id',
            'sort_order' => 'sometimes|integer',
        ]);
        $cat = Category::create(array_merge($data, ['group_id' => $group->id]));
        return new CategoryResource($cat);
    }

    public function update(Request $request, Group $group, Category $category) {
        $data = $request->validate([
            'name'       => 'sometimes|string|max:100',
            'color'      => 'nullable|string|size:7',
            'icon'       => 'nullable|string|max:50',
            'type'       => 'sometimes|in:expense,income,investment,all',
            'sort_order' => 'sometimes|integer',
        ]);
        $category->update($data);
        return new CategoryResource($category);
    }

    public function destroy(Group $group, Category $category) {
        $category->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
