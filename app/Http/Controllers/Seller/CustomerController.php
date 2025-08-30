<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\BlockEmail;
use App\Models\BlockIp;
use App\Models\BlockList;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index()
    {
        $data = User::when(request()->filled("search"), function ($query) {
                        $search = '%'.request()->search.'%';
                        $query->where(function ($subQuery) use ($search) {
                            $subQuery->where("name", "like", $search)
                                    ->orWhere("email", "like", $search);
                        });
                    })
                    ->when(request()->filled("status"), function ($query) {
                        $query->where("status", request()->status);
                    })
                    ->when(request()->filled("from") && request()->filled("to"), function ($query) {
                        $query->whereBetween('created_at', [
                            Carbon::parse(request()->from)->startOfDay(),
                            Carbon::parse(request()->to)->endOfDay()
                        ]);
                    })
                    ->where('role', 0)
                    ->latest()
                    ->paginate(request()->get('per_page', 20));

        return response()->json([
            'status' => true,
            'data'   => $data->items(),
            'page_count' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ],
            'links' => [
                'first' => $data->url(1),
                'last'  => $data->url($data->lastPage()),
                'prev'  => $data->previousPageUrl(),
                'next'  => $data->nextPageUrl(),
            ]
        ], 200);
    }



    public function changeStatus(Request $request,$id)
    {

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1',
        ]);
        

        $user = User::find($id);

        if(!$user){
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->status = $request->status;
        $user->save();


        return response()->json([
            'status' => true,
            'message' => 'User status changed successfully'
        ], 200);
    }












    public function blockList()
    {
        $datas = BlockList::when(request()->filled("search"), function ($query) {
                                $query->where("value", "like", "%" . request()->search . "%");
                            })
                            ->when(request()->filled("type"), function ($query) {
                                $query->where("type", request()->type);
                            })
                            ->when(request()->filled("from") && request()->filled("to"), function ($query) {
                                $query->whereBetween('created_at', [
                                    Carbon::parse(request()->from)->startOfDay(),
                                    Carbon::parse(request()->to)->endOfDay()
                                ]);
                            })
                            ->latest()
                            ->paginate(request('per_page', 20));
   
        return response()->json([
            'status' => true,
            'data'   => $datas->items(),
            'page_count' => [
                'current_page' => $datas->currentPage(),
                'last_page'    => $datas->lastPage(),
                'per_page'     => $datas->perPage(),
                'total'        => $datas->total(),
            ],
            'links' => [
                'first' => $datas->url(1),
                'last'  => $datas->url($datas->lastPage()),
                'prev'  => $datas->previousPageUrl(),
                'next'  => $datas->nextPageUrl(),
            ]
        ], 200);
    }



    public function blockListView($id)
    {
        $block_list = BlockList::find($id);

        if(!$block_list){
            return response()->json([
                'status' => false,
                'message' => 'Block list item not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $block_list
        ], 200);
    }



    public function blockListStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value'    => 'required|unique:block_lists,value',
            'type'     => 'required|in:0,1',
            'reason'   => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        BlockList::create([
            'value'  => $request->value,
            'type'   => $request->type,
            'reason' => $request->reason
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Blocked successfully'
        ], 200);

    }


    
    public function blockListDestroy($id)
    {
        $block_list = BlockList::find($id);

        if(!$block_list){
            return response()->json([
                'status' => false,
                'message' => 'Block list item not found'
            ], 404);
        }

        $block_list->delete();


        return response()->json([
            'status' => true,
            'message' => 'Unblocked successfully'
        ], 200);
    }
}
