<?php

namespace App\Http\Controllers\Admin\Game;

use App\Exceptions\CustomException;
use App\Http\Requests\AdminRequest;
use App\Services\Game\GameApiService;
use App\Services\Paginator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RecordController extends Controller
{
    protected $per_page = 15;
    protected $page = 1;

    public function __construct(Request $request)
    {
        $this->per_page = $request->per_page ?: $this->per_page;
        $this->page = $request->page ?: $this->page;
    }

    public function show(AdminRequest $request)
    {
        try {
            $api = config('custom.game_api_records');
            $records = GameApiService::request('GET', $api);
            return Paginator::paginate($records, $this->per_page, $this->page);
        } catch (\Exception $exception) {
            throw new CustomException($exception->getMessage());
        }
    }

    public function search(AdminRequest $request)
    {
        $searchUid = $this->filterRequest($request);

        try {
            $api = config('custom.game_api_records');
            return GameApiService::request('POST', $api, [
                'uid' => $searchUid,
            ]);
        } catch (\Exception $exception) {
            throw new CustomException($exception->getMessage());
        }
    }

    protected function filterRequest($request)
    {
        $this->validate($request, [
            'uid' => 'required|numeric',
        ]);
        return $request->uid;
    }
}
